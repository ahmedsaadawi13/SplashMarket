<?php
// FILE: /app/controllers/ApiController.php

/**
 * SplashMarket - API Controller
 *
 * Public REST API for storefronts
 * PHP 7.0+ compatible
 */

class ApiController extends Controller
{
    private $tenantModel;
    private $productModel;
    private $orderModel;
    private $customerModel;

    public function __construct()
    {
        parent::__construct();
        $this->tenantModel = new Tenant();
        $this->productModel = new Product();
        $this->orderModel = new Order();
        $this->customerModel = new Customer();
    }

    /**
     * Authenticate API request
     *
     * @return array|null Tenant data or null
     */
    private function authenticateApi()
    {
        $apiKey = null;

        // Check for API key in header
        if (isset($_SERVER['HTTP_X_API_KEY'])) {
            $apiKey = $_SERVER['HTTP_X_API_KEY'];
        } elseif (isset($_GET['api_key'])) {
            $apiKey = $_GET['api_key'];
        }

        if (!$apiKey) {
            return null;
        }

        return $this->tenantModel->findByApiKey($apiKey);
    }

    /**
     * List products (public API)
     * GET /api/products
     */
    public function products()
    {
        $tenant = $this->authenticateApi();

        if (!$tenant) {
            $this->json(['error' => 'Invalid API key'], 401);
        }

        $filters = [
            'status' => 'published',
            'category_id' => $this->get('category_id'),
            'search' => $this->get('search')
        ];

        $page = $this->get('page', 1);
        $perPage = min($this->get('per_page', 20), 100); // Max 100 items per page

        $result = $this->productModel->getByTenant($tenant['id'], $filters, $page, $perPage);

        // Format response
        $products = [];
        foreach ($result['data'] as $product) {
            $products[] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'slug' => $product['slug'],
                'sku' => $product['sku'],
                'description' => $product['description'],
                'price' => $product['price'],
                'compare_at_price' => $product['compare_at_price'],
                'stock_quantity' => $product['stock_quantity'],
                'in_stock' => $product['stock_quantity'] > 0,
                'image' => $product['image'],
                'is_featured' => (bool) $product['is_featured'],
                'is_on_sale' => (bool) $product['is_on_sale'],
                'category_id' => $product['category_id']
            ];
        }

        $this->json([
            'success' => true,
            'data' => $products,
            'pagination' => [
                'page' => $result['page'],
                'per_page' => $result['perPage'],
                'total' => $result['total'],
                'total_pages' => $result['totalPages']
            ]
        ]);
    }

    /**
     * Get single product
     * GET /api/products/:id
     */
    public function productDetail($productId)
    {
        $tenant = $this->authenticateApi();

        if (!$tenant) {
            $this->json(['error' => 'Invalid API key'], 401);
        }

        $product = $this->productModel->findById($productId);

        if (!$product || $product['tenant_id'] != $tenant['id'] || $product['status'] != 'published') {
            $this->json(['error' => 'Product not found'], 404);
        }

        $this->json([
            'success' => true,
            'data' => [
                'id' => $product['id'],
                'name' => $product['name'],
                'slug' => $product['slug'],
                'sku' => $product['sku'],
                'description' => $product['description'],
                'price' => $product['price'],
                'compare_at_price' => $product['compare_at_price'],
                'stock_quantity' => $product['stock_quantity'],
                'in_stock' => $product['stock_quantity'] > 0,
                'image' => $product['image'],
                'is_featured' => (bool) $product['is_featured'],
                'is_on_sale' => (bool) $product['is_on_sale'],
                'category_id' => $product['category_id']
            ]
        ]);
    }

    /**
     * Create order (external API)
     * POST /api/orders
     */
    public function createOrder()
    {
        $tenant = $this->authenticateApi();

        if (!$tenant) {
            $this->json(['error' => 'Invalid API key'], 401);
        }

        // Check subscription limits
        $subscriptionModel = new Subscription();
        $canCreate = $subscriptionModel->canCreateOrder($tenant['id']);

        if (!$canCreate['allowed']) {
            $this->json(['error' => $canCreate['message']], 403);
        }

        // Parse JSON body
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            $this->json(['error' => 'Invalid JSON'], 400);
        }

        // Validate required fields
        if (!isset($input['items']) || !is_array($input['items']) || empty($input['items'])) {
            $this->json(['error' => 'Items are required'], 400);
        }

        if (!isset($input['customer_email']) || !filter_var($input['customer_email'], FILTER_VALIDATE_EMAIL)) {
            $this->json(['error' => 'Valid customer email is required'], 400);
        }

        try {
            // Calculate totals and validate products
            $subtotal = 0;
            $validatedItems = [];

            foreach ($input['items'] as $item) {
                if (!isset($item['product_id']) || !isset($item['quantity'])) {
                    $this->json(['error' => 'Invalid item format'], 400);
                }

                $product = $this->productModel->findById($item['product_id']);

                if (!$product || $product['tenant_id'] != $tenant['id'] || $product['status'] != 'published') {
                    $this->json(['error' => 'Product not found: ' . $item['product_id']], 400);
                }

                if ($product['stock_quantity'] < $item['quantity']) {
                    $this->json(['error' => 'Insufficient stock for product: ' . $product['name']], 400);
                }

                $lineTotal = $product['price'] * $item['quantity'];
                $subtotal += $lineTotal;

                $validatedItems[] = [
                    'product_id' => $product['id'],
                    'product_name' => $product['name'],
                    'sku' => $product['sku'],
                    'price' => $product['price'],
                    'quantity' => $item['quantity'],
                    'line_total' => $lineTotal
                ];
            }

            // Calculate shipping (flat rate for simplicity)
            $shippingCost = isset($input['shipping_cost']) ? (float) $input['shipping_cost'] : 10.00;
            $total = $subtotal + $shippingCost;

            // Create or get customer
            $customer = $this->customerModel->findByEmail($input['customer_email'], $tenant['id']);

            if (!$customer && isset($input['customer_name'])) {
                $nameParts = explode(' ', $input['customer_name'], 2);
                $customerId = $this->customerModel->create([
                    'tenant_id' => $tenant['id'],
                    'first_name' => $nameParts[0],
                    'last_name' => isset($nameParts[1]) ? $nameParts[1] : '',
                    'email' => $input['customer_email'],
                    'phone' => isset($input['customer_phone']) ? $input['customer_phone'] : null
                ]);
            } else {
                $customerId = $customer ? $customer['id'] : null;
            }

            // Create order
            $orderId = $this->orderModel->create([
                'tenant_id' => $tenant['id'],
                'customer_id' => $customerId,
                'customer_email' => $input['customer_email'],
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total_amount' => $total,
                'status' => 'pending',
                'shipping_address' => isset($input['shipping_address']) ? json_encode($input['shipping_address']) : null,
                'payment_method' => isset($input['payment_method']) ? $input['payment_method'] : 'api'
            ]);

            // Create order items and decrease stock
            $orderItemModel = new OrderItem();

            foreach ($validatedItems as $item) {
                $orderItemModel->create([
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'sku' => $item['sku'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'line_total' => $item['line_total']
                ]);

                // Decrease stock
                $this->productModel->decreaseStock($item['product_id'], $item['quantity']);
            }

            // Get order details
            $order = $this->orderModel->findById($orderId);

            // Send notification
            $notificationModel = new Notification();
            $notificationModel->sendOrderConfirmation($orderId, $input['customer_email']);

            $this->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => [
                    'order_id' => $orderId,
                    'order_number' => $order['order_number'],
                    'status' => $order['status'],
                    'total' => $order['total_amount']
                ]
            ], 201);

        } catch (Exception $e) {
            error_log('API order creation error: ' . $e->getMessage());
            $this->json(['error' => 'Failed to create order'], 500);
        }
    }
}
