<?php
// FILE: /app/controllers/CategoryController.php

/**
 * SplashMarket - Category Controller
 *
 * Manages product categories
 * PHP 7.0+ compatible
 */

class CategoryController extends Controller
{
    private $categoryModel;

    public function __construct()
    {
        parent::__construct();
        $this->categoryModel = new Category();
    }

    /**
     * List all categories
     */
    public function index()
    {
        $this->requireAuth();
        $tenantId = $this->getTenantId();

        $categories = $this->categoryModel->getWithProductCount($tenantId);

        $this->render('categories/index', [
            'categories' => $categories
        ]);
    }

    /**
     * Show create category form
     */
    public function create()
    {
        $this->requireAuth();
        $tenantId = $this->getTenantId();

        $categories = $this->categoryModel->getByTenant($tenantId);

        $this->render('categories/create', [
            'categories' => $categories
        ]);
    }

    /**
     * Store new category
     */
    public function store()
    {
        $this->requireAuth();
        $this->requireCsrf();

        $tenantId = $this->getTenantId();

        // Validate input
        $validator = new Validator($_POST);
        $validator->required('name');

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError());
            $this->redirect('/categories/create');
        }

        try {
            // Handle image upload
            $imagePath = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $imagePath = uploadFile($_FILES['image'], 'categories');
            }

            $categoryId = $this->categoryModel->create([
                'tenant_id' => $tenantId,
                'parent_id' => $this->post('parent_id') ?: null,
                'name' => $this->post('name'),
                'description' => $this->post('description'),
                'image' => $imagePath,
                'is_active' => $this->post('is_active', 1)
            ]);

            Session::setFlash('success', 'Category created successfully');
            $this->redirect('/categories');

        } catch (Exception $e) {
            error_log('Category creation error: ' . $e->getMessage());
            Session::setFlash('error', 'Failed to create category');
            $this->redirect('/categories/create');
        }
    }

    /**
     * Show edit category form
     */
    public function edit($categoryId)
    {
        $this->requireAuth();
        $tenantId = $this->getTenantId();

        $category = $this->categoryModel->findById($categoryId);

        if (!$category || $category['tenant_id'] != $tenantId) {
            Session::setFlash('error', 'Category not found');
            $this->redirect('/categories');
        }

        $categories = $this->categoryModel->getByTenant($tenantId);

        $this->render('categories/edit', [
            'category' => $category,
            'categories' => $categories
        ]);
    }

    /**
     * Update category
     */
    public function update($categoryId)
    {
        $this->requireAuth();
        $this->requireCsrf();

        $tenantId = $this->getTenantId();

        $category = $this->categoryModel->findById($categoryId);

        if (!$category || $category['tenant_id'] != $tenantId) {
            Session::setFlash('error', 'Category not found');
            $this->redirect('/categories');
        }

        // Validate input
        $validator = new Validator($_POST);
        $validator->required('name');

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError());
            $this->redirect('/categories/' . $categoryId . '/edit');
        }

        try {
            $updateData = [
                'parent_id' => $this->post('parent_id') ?: null,
                'name' => $this->post('name'),
                'description' => $this->post('description'),
                'is_active' => $this->post('is_active', 1)
            ];

            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $imagePath = uploadFile($_FILES['image'], 'categories');
                $updateData['image'] = $imagePath;
            }

            $this->categoryModel->updateCategory($categoryId, $updateData);

            Session::setFlash('success', 'Category updated successfully');
            $this->redirect('/categories');

        } catch (Exception $e) {
            error_log('Category update error: ' . $e->getMessage());
            Session::setFlash('error', 'Failed to update category');
            $this->redirect('/categories/' . $categoryId . '/edit');
        }
    }

    /**
     * Delete category
     */
    public function delete($categoryId)
    {
        $this->requireAuth();
        $this->requireCsrf();

        $tenantId = $this->getTenantId();

        $category = $this->categoryModel->findById($categoryId);

        if (!$category || $category['tenant_id'] != $tenantId) {
            Session::setFlash('error', 'Category not found');
            $this->redirect('/categories');
        }

        if (!$this->categoryModel->deleteCategory($categoryId)) {
            Session::setFlash('error', 'Cannot delete category with products or subcategories');
        } else {
            Session::setFlash('success', 'Category deleted successfully');
        }

        $this->redirect('/categories');
    }
}
