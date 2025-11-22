<?php
// FILE: /app/controllers/StorefrontController.php

/**
 * SplashMarket - Storefront Controller
 *
 * Public-facing storefront pages (cart, checkout, etc.)
 * PHP 7.0+ compatible
 */

class StorefrontController extends Controller
{
    private $tenantModel;
    private $productModel;
    private $categoryModel;
    private $cartModel;
    private $orderModel;
    private $customerModel;
    private $couponModel;

    public function __construct()
    {
        parent::__construct();
        $this->tenantModel = new Tenant();
        $this->productModel = new Product();
        $this->categoryModel = new Category();
        $this->cartModel = new Cart();
        $this->orderModel = new Order();
        $this->customerModel = new Customer();
        $this->couponModel = new Coupon();
    }

    /**
     * Get tenant from subdomain or parameter
     */
    private function getTenant()
    {
        // Try to get tenant from subdomain or parameter
        $tenantId = Session::get('storefront_tenant_id');

        if ($tenantId) {
            return $this->tenantModel->findById($tenantId);
        }

        return null;
    }

    /**
     * Store home page
     */
    public function home()
    {
        $tenant = $this->getTenant();

        if (!$tenant) {
            die('Store not found');
        }

        // Get featured products
        $featuredProducts = $this->productModel->getFeatured($tenant['id'], 8);

        // Get categories
        $categories = $this->categoryModel->getRootCategories($tenant['id'], true);

        // Get products on sale
        $saleProducts = $this->productModel->getOnSale($tenant['id'], 8);

        $this->render('storefront/home', [
            'tenant' => $tenant,
            'featuredProducts' => $featuredProducts,
            'categories' => $categories,
            'saleProducts' => $saleProducts
        ], 'storefront');
    }

    /**
     * Category page
     */
    public function category($categorySlug)
    {
        $tenant = $this->getTenant();

        if (!$tenant) {
            die('Store not found');
        }

        $category = $this->categoryModel->findBySlug($categorySlug, $tenant['id']);

        if (!$category) {
            die('Category not found');
        }

        $page = $this->get('page', 1);
        $filters = [
            'status' => 'published',
            'category_id' => $category['id'],
            'search' => $this->get('search')
        ];

        $products = $this->productModel->getByTenant($tenant['id'], $filters, $page, 12);

        $this->render('storefront/category', [
            'tenant' => $tenant,
            'category' => $category,
            'products' => $products['data'],
            'pagination' => $products
        ], 'storefront');
    }

    /**
     * Product detail page
     */
    public function product($productSlug)
    {
        $tenant = $this->getTenant();

        if (!$tenant) {
            die('Store not found');
        }

        $product = $this->productModel->findBySlug($productSlug, $tenant['id']);

        if (!$product || $product['status'] != 'published') {
            die('Product not found');
        }

        $this->render('storefront/product', [
            'tenant' => $tenant,
            'product' => $product
        ], 'storefront');
    }

    /**
     * Add to cart
     */
    public function addToCart()
    {
        $tenant = $this->getTenant();

        if (!$tenant) {
            $this->json(['success' => false, 'message' => 'Store not found'], 400);
        }

        $productId = $this->post('product_id');
        $quantity = (int) $this->post('quantity', 1);

        if (!$productId || $quantity < 1) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
        }

        // Verify product exists and is available
        $product = $this->productModel->findById($productId);

        if (!$product || $product['tenant_id'] != $tenant['id'] || $product['status'] != 'published') {
            $this->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        if ($product['stock_quantity'] < $quantity) {
            $this->json(['success' => false, 'message' => 'Insufficient stock'], 400);
        }

        // Add to cart
        $sessionId = session_id();
        $this->cartModel->addItem($sessionId, $tenant['id'], $productId, $quantity);

        $cartTotal = $this->cartModel->getCartTotal($sessionId, $tenant['id']);

        $this->json([
            'success' => true,
            'message' => 'Product added to cart',
            'cart_count' => $cartTotal['itemCount']
        ]);
    }

    /**
     * View cart
     */
    public function cart()
    {
        $tenant = $this->getTenant();

        if (!$tenant) {
            die('Store not found');
        }

        $sessionId = session_id();
        $cartData = $this->cartModel->getCartTotal($sessionId, $tenant['id']);

        $this->render('storefront/cart', [
            'tenant' => $tenant,
            'cartItems' => $cartData['items'],
            'subtotal' => $cartData['subtotal']
        ], 'storefront');
    }

    /**
     * Update cart
     */
    public function updateCart()
    {
        $this->requireCsrf();

        $tenant = $this->getTenant();

        if (!$tenant) {
            $this->redirect('/');
        }

        $cartItemId = $this->post('cart_item_id');
        $quantity = (int) $this->post('quantity');

        $this->cartModel->updateQuantity($cartItemId, $quantity);

        Session::setFlash('success', 'Cart updated');
        $this->redirect('/cart');
    }

    /**
     * Remove from cart
     */
    public function removeFromCart()
    {
        $this->requireCsrf();

        $tenant = $this->getTenant();

        if (!$tenant) {
            $this->redirect('/');
        }

        $cartItemId = $this->post('cart_item_id');
        $this->cartModel->removeItem($cartItemId);

        Session::setFlash('success', 'Item removed from cart');
        $this->redirect('/cart');
    }

    /**
     * Checkout page
     */
    public function checkout()
    {
        $tenant = $this->getTenant();

        if (!$tenant) {
            die('Store not found');
        }

        $sessionId = session_id();
        $cartData = $this->cartModel->getCartTotal($sessionId, $tenant['id']);

        if (empty($cartData['items'])) {
            Session::setFlash('error', 'Your cart is empty');
            $this->redirect('/cart');
        }

        $shippingCost = 10.00; // Flat rate
        $total = $cartData['subtotal'] + $shippingCost;

        $this->render('storefront/checkout', [
            'tenant' => $tenant,
            'cartItems' => $cartData['items'],
            'subtotal' => $cartData['subtotal'],
            'shippingCost' => $shippingCost,
            'total' => $total
        ], 'storefront');
    }

    /**
     * Process checkout
     */
    public function processCheckout()
    {
        $this->requireCsrf();

        $tenant = $this->getTenant();

        if (!$tenant) {
            die('Store not found');
        }

        $sessionId = session_id();
        $cartData = $this->cartModel->getCartTotal($sessionId, $tenant['id']);

        if (empty($cartData['items'])) {
            Session::setFlash('error', 'Your cart is empty');
            $this->redirect('/cart');
        }

        // Validate input
        $validator = new Validator($_POST);
        $validator->required('customer_email')->email('customer_email')
                 ->required('customer_name')
                 ->required('shipping_address')
                 ->required('payment_method');

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError());
            $this->redirect('/checkout');
        }

        try {
            // Find or create customer
            $customer = $this->customerModel->findByEmail($this->post('customer_email'), $tenant['id']);

            if (!$customer) {
                $nameParts = explode(' ', $this->post('customer_name'), 2);
                $customerId = $this->customerModel->create([
                    'tenant_id' => $tenant['id'],
                    'first_name' => $nameParts[0],
                    'last_name' => isset($nameParts[1]) ? $nameParts[1] : '',
                    'email' => $this->post('customer_email'),
                    'phone' => $this->post('customer_phone')
                ]);
            } else {
                $customerId = $customer['id'];
            }

            // Calculate totals
            $subtotal = $cartData['subtotal'];
            $shippingCost = 10.00;
            $discount = 0;

            // Apply coupon if provided
            if ($this->post('coupon_code')) {
                $validation = $this->couponModel->validateCoupon($this->post('coupon_code'), $tenant['id'], $subtotal);
                if ($validation['valid']) {
                    $discount = $this->couponModel->calculateDiscount($validation['coupon'], $subtotal);
                }
            }

            $total = $subtotal + $shippingCost - $discount;

            // Create order
            $orderId = $this->orderModel->create([
                'tenant_id' => $tenant['id'],
                'customer_id' => $customerId,
                'customer_email' => $this->post('customer_email'),
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'discount_amount' => $discount,
                'total_amount' => $total,
                'status' => 'pending',
                'payment_method' => $this->post('payment_method'),
                'shipping_address' => json_encode([
                    'name' => $this->post('customer_name'),
                    'address' => $this->post('shipping_address'),
                    'city' => $this->post('city'),
                    'postal_code' => $this->post('postal_code'),
                    'phone' => $this->post('customer_phone')
                ])
            ]);

            // Create order items and decrease stock
            $orderItemModel = new OrderItem();

            foreach ($cartData['items'] as $item) {
                $orderItemModel->create([
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'sku' => $item['sku'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'line_total' => $item['price'] * $item['quantity']
                ]);

                // Decrease stock
                $this->productModel->decreaseStock($item['product_id'], $item['quantity']);
            }

            // Increment coupon usage
            if ($discount > 0 && isset($validation['coupon'])) {
                $this->couponModel->incrementUsage($validation['coupon']['id']);
            }

            // Clear cart
            $this->cartModel->clearCart($sessionId, $tenant['id']);

            // Send notification
            $notificationModel = new Notification();
            $notificationModel->sendOrderConfirmation($orderId, $this->post('customer_email'));

            // Get order for confirmation
            $order = $this->orderModel->findById($orderId);

            Session::set('order_confirmation', $order);
            $this->redirect('/order-confirmation');

        } catch (Exception $e) {
            error_log('Checkout error: ' . $e->getMessage());
            Session::setFlash('error', 'An error occurred during checkout');
            $this->redirect('/checkout');
        }
    }

    /**
     * Order confirmation page
     */
    public function orderConfirmation()
    {
        $tenant = $this->getTenant();

        if (!$tenant) {
            die('Store not found');
        }

        $order = Session::get('order_confirmation');

        if (!$order) {
            $this->redirect('/');
        }

        // Clear from session
        Session::remove('order_confirmation');

        $this->render('storefront/order-confirmation', [
            'tenant' => $tenant,
            'order' => $order
        ], 'storefront');
    }
}
