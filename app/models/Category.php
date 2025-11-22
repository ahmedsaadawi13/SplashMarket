<?php
// FILE: /app/models/Category.php

/**
 * SplashMarket - Category Model
 *
 * Manages product categories (hierarchical)
 * PHP 7.0+ compatible
 */

class Category extends Model
{
    protected $table = 'categories';

    /**
     * Get categories by tenant
     *
     * @param int $tenantId Tenant ID
     * @param bool $activeOnly Get only active categories
     * @return array
     */
    public function getByTenant($tenantId, $activeOnly = false)
    {
        $conditions = ['tenant_id' => $tenantId];

        if ($activeOnly) {
            $conditions['is_active'] = 1;
        }

        return $this->findAll($conditions, 'name ASC');
    }

    /**
     * Get root categories (no parent)
     *
     * @param int $tenantId Tenant ID
     * @param bool $activeOnly Get only active categories
     * @return array
     */
    public function getRootCategories($tenantId, $activeOnly = false)
    {
        $sql = "SELECT * FROM {$this->table} WHERE tenant_id = ? AND (parent_id IS NULL OR parent_id = 0)";
        $params = [$tenantId];

        if ($activeOnly) {
            $sql .= " AND is_active = 1";
        }

        $sql .= " ORDER BY name ASC";

        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Get child categories
     *
     * @param int $parentId Parent category ID
     * @param bool $activeOnly Get only active categories
     * @return array
     */
    public function getChildren($parentId, $activeOnly = false)
    {
        $conditions = ['parent_id' => $parentId];

        if ($activeOnly) {
            $conditions['is_active'] = 1;
        }

        return $this->findAll($conditions, 'name ASC');
    }

    /**
     * Get category by slug
     *
     * @param string $slug Category slug
     * @param int $tenantId Tenant ID
     * @return array|null
     */
    public function findBySlug($slug, $tenantId)
    {
        return $this->findOne(['slug' => $slug, 'tenant_id' => $tenantId]);
    }

    /**
     * Create category
     *
     * @param array $data Category data
     * @return int Category ID
     */
    public function create($data)
    {
        // Generate slug if not provided
        if (!isset($data['slug']) && isset($data['name'])) {
            $data['slug'] = $this->generateSlug($data['name'], $data['tenant_id']);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['is_active'] = isset($data['is_active']) ? $data['is_active'] : 1;

        return $this->insert($data);
    }

    /**
     * Update category
     *
     * @param int $categoryId Category ID
     * @param array $data Category data
     * @return bool
     */
    public function updateCategory($categoryId, $data)
    {
        // Regenerate slug if name changed
        if (isset($data['name'])) {
            $category = $this->findById($categoryId);
            if ($category) {
                $data['slug'] = $this->generateSlug($data['name'], $category['tenant_id'], $categoryId);
            }
        }

        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($categoryId, $data);
    }

    /**
     * Generate unique slug
     *
     * @param string $name Category name
     * @param int $tenantId Tenant ID
     * @param int|null $excludeId Category ID to exclude
     * @return string
     */
    private function generateSlug($name, $tenantId, $excludeId = null)
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $tenantId, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug exists
     *
     * @param string $slug Slug to check
     * @param int $tenantId Tenant ID
     * @param int|null $excludeId Category ID to exclude
     * @return bool
     */
    private function slugExists($slug, $tenantId, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE slug = ? AND tenant_id = ?";
        $params = [$slug, $tenantId];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = $this->query($sql, $params)->fetch();
        return $result['count'] > 0;
    }

    /**
     * Get category with product count
     *
     * @param int $tenantId Tenant ID
     * @return array
     */
    public function getWithProductCount($tenantId)
    {
        $sql = "SELECT c.*, COUNT(p.id) as product_count
                FROM {$this->table} c
                LEFT JOIN products p ON c.id = p.category_id AND p.tenant_id = c.tenant_id
                WHERE c.tenant_id = ?
                GROUP BY c.id
                ORDER BY c.name ASC";

        return $this->query($sql, [$tenantId])->fetchAll();
    }

    /**
     * Delete category (with safety check)
     *
     * @param int $categoryId Category ID
     * @return bool
     */
    public function deleteCategory($categoryId)
    {
        // Check if category has products
        $sql = "SELECT COUNT(*) as count FROM products WHERE category_id = ?";
        $result = $this->query($sql, [$categoryId])->fetch();

        if ($result['count'] > 0) {
            return false; // Cannot delete category with products
        }

        // Check if category has children
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE parent_id = ?";
        $result = $this->query($sql, [$categoryId])->fetch();

        if ($result['count'] > 0) {
            return false; // Cannot delete category with children
        }

        return $this->delete($categoryId);
    }
}
