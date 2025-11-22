<?php
// FILE: /app/models/OrderItem.php

/**
 * SplashMarket - Order Item Model
 *
 * Manages individual items in orders
 * PHP 7.0+ compatible
 */

class OrderItem extends Model
{
    protected $table = 'order_items';

    /**
     * Get items for an order
     *
     * @param int $orderId Order ID
     * @return array
     */
    public function getByOrder($orderId)
    {
        return $this->findAll(['order_id' => $orderId], 'id ASC');
    }

    /**
     * Create order item
     *
     * @param array $data Order item data
     * @return int Order item ID
     */
    public function create($data)
    {
        return $this->insert($data);
    }

    /**
     * Get top selling products
     *
     * @param int $tenantId Tenant ID
     * @param int $limit Limit
     * @param string|null $dateFrom Date from
     * @param string|null $dateTo Date to
     * @return array
     */
    public function getTopSelling($tenantId, $limit = 10, $dateFrom = null, $dateTo = null)
    {
        $sql = "SELECT oi.product_id, oi.product_name, p.image,
                SUM(oi.quantity) as total_quantity,
                SUM(oi.line_total) as total_revenue,
                COUNT(DISTINCT oi.order_id) as order_count
                FROM {$this->table} oi
                INNER JOIN orders o ON oi.order_id = o.id
                LEFT JOIN products p ON oi.product_id = p.id
                WHERE o.tenant_id = ?";

        $params = [$tenantId];

        if ($dateFrom) {
            $sql .= " AND DATE(o.created_at) >= ?";
            $params[] = $dateFrom;
        }

        if ($dateTo) {
            $sql .= " AND DATE(o.created_at) <= ?";
            $params[] = $dateTo;
        }

        $sql .= " GROUP BY oi.product_id, oi.product_name, p.image
                  ORDER BY total_quantity DESC
                  LIMIT ?";

        $params[] = $limit;

        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Get total items sold for a product
     *
     * @param int $productId Product ID
     * @return int
     */
    public function getTotalSold($productId)
    {
        $sql = "SELECT SUM(quantity) as total FROM {$this->table} WHERE product_id = ?";
        $result = $this->query($sql, [$productId])->fetch();
        return $result['total'] ?: 0;
    }
}
