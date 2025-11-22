<?php
// FILE: /app/controllers/ProductController.php

/**
 * SplashMarket - Product Controller
 *
 * Manages product CRUD operations
 * PHP 7.0+ compatible
 */

class ProductController extends Controller
{
    private $productModel;
    private $categoryModel;
    private $subscriptionModel;

    public function __construct()
    {
        parent::__construct();
        $this->productModel = new Product();
        $this->categoryModel = new Category();
        $this->subscriptionModel = new Subscription();
    }

    /**
     * List all products
     */
    public function index()
    {
        $this->requireAuth();
        $tenantId = $this->getTenantId();

        $page = $this->get('page', 1);
        $filters = [
            'status' => $this->get('status'),
            'category_id' => $this->get('category_id'),
            'search' => $this->get('search')
        ];

        $products = $this->productModel->getByTenant($tenantId, $filters, $page, 20);
        $categories = $this->categoryModel->getByTenant($tenantId);

        $this->render('products/index', [
            'products' => $products['data'],
            'pagination' => $products,
            'categories' => $categories,
            'filters' => $filters
        ]);
    }

    /**
     * Show create product form
     */
    public function create()
    {
        $this->requireAuth();
        $tenantId = $this->getTenantId();

        // Check subscription limits
        $canAdd = $this->subscriptionModel->canAddProduct($tenantId);
        if (!$canAdd['allowed']) {
            Session::setFlash('error', $canAdd['message']);
            $this->redirect('/products');
        }

        $categories = $this->categoryModel->getByTenant($tenantId, true);

        $this->render('products/create', [
            'categories' => $categories
        ]);
    }

    /**
     * Store new product
     */
    public function store()
    {
        $this->requireAuth();
        $this->requireCsrf();

        $tenantId = $this->getTenantId();

        // Check subscription limits
        $canAdd = $this->subscriptionModel->canAddProduct($tenantId);
        if (!$canAdd['allowed']) {
            Session::setFlash('error', $canAdd['message']);
            $this->redirect('/products');
        }

        // Validate input
        $validator = new Validator($_POST);
        $validator->required('name')
                 ->required('sku')
                 ->required('price')->numeric('price')
                 ->required('stock_quantity')->numeric('stock_quantity');

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError());
            $this->redirect('/products/create');
        }

        // Check if SKU exists
        if ($this->productModel->skuExists($this->post('sku'), $tenantId)) {
            Session::setFlash('error', 'SKU already exists');
            $this->redirect('/products/create');
        }

        try {
            // Handle image upload
            $imagePath = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $imagePath = $this->handleImageUpload($_FILES['image'], 'products');
            }

            // Create product
            $productId = $this->productModel->create([
                'tenant_id' => $tenantId,
                'category_id' => $this->post('category_id') ?: null,
                'name' => $this->post('name'),
                'sku' => $this->post('sku'),
                'description' => $this->post('description'),
                'price' => $this->post('price'),
                'compare_at_price' => $this->post('compare_at_price') ?: null,
                'stock_quantity' => $this->post('stock_quantity'),
                'image' => $imagePath,
                'status' => $this->post('status', 'draft'),
                'is_featured' => $this->post('is_featured', 0),
                'is_on_sale' => $this->post('is_on_sale', 0)
            ]);

            Session::setFlash('success', 'Product created successfully');
            $this->redirect('/products/' . $productId . '/edit');

        } catch (Exception $e) {
            error_log('Product creation error: ' . $e->getMessage());
            Session::setFlash('error', 'Failed to create product');
            $this->redirect('/products/create');
        }
    }

    /**
     * Show edit product form
     */
    public function edit($productId)
    {
        $this->requireAuth();
        $tenantId = $this->getTenantId();

        $product = $this->productModel->findById($productId);

        if (!$product || $product['tenant_id'] != $tenantId) {
            Session::setFlash('error', 'Product not found');
            $this->redirect('/products');
        }

        $categories = $this->categoryModel->getByTenant($tenantId);

        $this->render('products/edit', [
            'product' => $product,
            'categories' => $categories
        ]);
    }

    /**
     * Update product
     */
    public function update($productId)
    {
        $this->requireAuth();
        $this->requireCsrf();

        $tenantId = $this->getTenantId();

        $product = $this->productModel->findById($productId);

        if (!$product || $product['tenant_id'] != $tenantId) {
            Session::setFlash('error', 'Product not found');
            $this->redirect('/products');
        }

        // Validate input
        $validator = new Validator($_POST);
        $validator->required('name')
                 ->required('sku')
                 ->required('price')->numeric('price')
                 ->required('stock_quantity')->numeric('stock_quantity');

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError());
            $this->redirect('/products/' . $productId . '/edit');
        }

        // Check if SKU exists (excluding current product)
        if ($this->productModel->skuExists($this->post('sku'), $tenantId, $productId)) {
            Session::setFlash('error', 'SKU already exists');
            $this->redirect('/products/' . $productId . '/edit');
        }

        try {
            $updateData = [
                'category_id' => $this->post('category_id') ?: null,
                'name' => $this->post('name'),
                'sku' => $this->post('sku'),
                'description' => $this->post('description'),
                'price' => $this->post('price'),
                'compare_at_price' => $this->post('compare_at_price') ?: null,
                'stock_quantity' => $this->post('stock_quantity'),
                'status' => $this->post('status', 'draft'),
                'is_featured' => $this->post('is_featured', 0),
                'is_on_sale' => $this->post('is_on_sale', 0)
            ];

            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $imagePath = $this->handleImageUpload($_FILES['image'], 'products');
                $updateData['image'] = $imagePath;
            }

            $this->productModel->updateProduct($productId, $updateData);

            Session::setFlash('success', 'Product updated successfully');
            $this->redirect('/products/' . $productId . '/edit');

        } catch (Exception $e) {
            error_log('Product update error: ' . $e->getMessage());
            Session::setFlash('error', 'Failed to update product');
            $this->redirect('/products/' . $productId . '/edit');
        }
    }

    /**
     * Delete product
     */
    public function delete($productId)
    {
        $this->requireAuth();
        $this->requireCsrf();

        $tenantId = $this->getTenantId();

        $product = $this->productModel->findById($productId);

        if (!$product || $product['tenant_id'] != $tenantId) {
            Session::setFlash('error', 'Product not found');
            $this->redirect('/products');
        }

        try {
            $this->productModel->delete($productId);
            Session::setFlash('success', 'Product deleted successfully');
        } catch (Exception $e) {
            error_log('Product deletion error: ' . $e->getMessage());
            Session::setFlash('error', 'Failed to delete product');
        }

        $this->redirect('/products');
    }

    /**
     * Handle image upload
     *
     * @param array $file Uploaded file
     * @param string $folder Folder name
     * @return string File path
     */
    private function handleImageUpload($file, $folder)
    {
        return uploadFile($file, $folder);
    }
}
