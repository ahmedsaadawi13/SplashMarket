<?php
// FILE: /app/models/Product.php

/**
 * SplashMarket - Product Model
 *
 * Manages product catalog
 * PHP 7.0+ compatible
 */

class Product extends Model
{
    protected $table = 'products';

    /**
     * Get products by tenant with filters
     *
     * @param int $tenantId Tenant ID
     * @param array $filters Filter options
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array
     */
    public function getByTenant($tenantId, $filters = [], $page = 1, $perPage = 20)
    {
        $offset = ($page - 1) * $perPage;
        $where = ["p.tenant_id = ?"];
        $params = [$tenantId];

        // Apply filters
        if (isset($filters['status']) && $filters['status'] !== '') {
            $where[] = "p.status = ?";
            $params[] = $filters['status'];
        }

        if (isset($filters['category_id']) && $filters['category_id']) {
            $where[] = "p.category_id = ?";
            $params[] = $filters['category_id'];
        }

        if (isset($filters['search']) && $filters['search']) {
            $where[] = "(p.name LIKE ? OR p.sku LIKE ? OR p.description LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (isset($filters['featured']) && $filters['featured']) {
            $where[] = "p.is_featured = 1";
        }

        if (isset($filters['on_sale']) && $filters['on_sale']) {
            $where[] = "p.is_on_sale = 1";
        }

        $whereClause = implode(' AND ', $where);

        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} p WHERE $whereClause";
        $totalResult = $this->query($countSql, $params)->fetch();
        $total = $totalResult['total'];

        // Get products with category info
        $sql = "SELECT p.*, c.name as category_name
                FROM {$this->table} p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE $whereClause
                ORDER BY p.created_at DESC
                LIMIT $perPage OFFSET $offset";

        $products = $this->query($sql, $params)->fetchAll();

        return [
            'data' => $products,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }

    /**
     * Get product by slug
     *
     * @param string $slug Product slug
     * @param int $tenantId Tenant ID
     * @return array|null
     */
    public function findBySlug($slug, $tenantId)
    {
        $sql = "SELECT p.*, c.name as category_name
                FROM {$this->table} p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.slug = ? AND p.tenant_id = ?
                LIMIT 1";

        $result = $this->query($sql, [$slug, $tenantId])->fetch();
        return $result ?: null;
    }

    /**
     * Create product
     *
     * @param array $data Product data
     * @return int Product ID
     */
    public function create($data)
    {
        // Generate slug if not provided
        if (!isset($data['slug']) && isset($data['name'])) {
            $data['slug'] = $this->generateSlug($data['name'], $data['tenant_id']);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['status'] = isset($data['status']) ? $data['status'] : 'draft';

        return $this->insert($data);
    }

    /**
     * Update product
     *
     * @param int $productId Product ID
     * @param array $data Product data
     * @return bool
     */
    public function updateProduct($productId, $data)
    {
        // Regenerate slug if name changed
        if (isset($data['name'])) {
            $product = $this->findById($productId);
            if ($product) {
                $data['slug'] = $this->generateSlug($data['name'], $product['tenant_id'], $productId);
            }
        }

        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($productId, $data);
    }

    /**
     * Generate unique slug
     *
     * @param string $name Product name
     * @param int $tenantId Tenant ID
     * @param int|null $excludeId Product ID to exclude
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
     * @param int|null $excludeId Product ID to exclude
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
     * Check if SKU exists
     *
     * @param string $sku SKU to check
     * @param int $tenantId Tenant ID
     * @param int|null $excludeId Product ID to exclude
     * @return bool
     */
    public function skuExists($sku, $tenantId, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE sku = ? AND tenant_id = ?";
        $params = [$sku, $tenantId];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = $this->query($sql, $params)->fetch();
        return $result['count'] > 0;
    }

    /**
     * Update stock quantity
     *
     * @param int $productId Product ID
     * @param int $quantity New quantity
     * @return bool
     */
    public function updateStock($productId, $quantity)
    {
        return $this->update($productId, ['stock_quantity' => $quantity]);
    }

    /**
     * Decrease stock
     *
     * @param int $productId Product ID
     * @param int $quantity Quantity to decrease
     * @return bool
     */
    public function decreaseStock($productId, $quantity)
    {
        $sql = "UPDATE {$this->table} SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?";
        $stmt = $this->query($sql, [$quantity, $productId, $quantity]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Increase stock
     *
     * @param int $productId Product ID
     * @param int $quantity Quantity to increase
     * @return bool
     */
    public function increaseStock($productId, $quantity)
    {
        $sql = "UPDATE {$this->table} SET stock_quantity = stock_quantity + ? WHERE id = ?";
        $stmt = $this->query($sql, [$quantity, $productId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Get featured products
     *
     * @param int $tenantId Tenant ID
     * @param int $limit Limit
     * @return array
     */
    public function getFeatured($tenantId, $limit = 10)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE tenant_id = ? AND is_featured = 1 AND status = 'published'
                ORDER BY created_at DESC
                LIMIT ?";

        return $this->query($sql, [$tenantId, $limit])->fetchAll();
    }

    /**
     * Get products on sale
     *
     * @param int $tenantId Tenant ID
     * @param int $limit Limit
     * @return array
     */
    public function getOnSale($tenantId, $limit = 10)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE tenant_id = ? AND is_on_sale = 1 AND status = 'published'
                ORDER BY created_at DESC
                LIMIT ?";

        return $this->query($sql, [$tenantId, $limit])->fetchAll();
    }

    /**
     * Get low stock products
     *
     * @param int $tenantId Tenant ID
     * @param int $threshold Stock threshold
     * @return array
     */
    public function getLowStock($tenantId, $threshold = 10)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE tenant_id = ? AND stock_quantity <= ? AND stock_quantity > 0
                ORDER BY stock_quantity ASC";

        return $this->query($sql, [$tenantId, $threshold])->fetchAll();
    }

    /**
     * Get out of stock products
     *
     * @param int $tenantId Tenant ID
     * @return array
     */
    public function getOutOfStock($tenantId)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE tenant_id = ? AND stock_quantity = 0
                ORDER BY name ASC";

        return $this->query($sql, [$tenantId])->fetchAll();
    }
}
