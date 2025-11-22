<?php
// FILE: /app/models/StockHistory.php

/**
 * SplashMarket - Stock History Model
 *
 * Tracks stock adjustments
 * PHP 7.0+ compatible
 */

class StockHistory extends Model
{
    protected $table = 'stock_history';

    /**
     * Log stock adjustment
     *
     * @param int $productId Product ID
     * @param int $tenantId Tenant ID
     * @param int $oldQuantity Old quantity
     * @param int $newQuantity New quantity
     * @param string $reason Reason for adjustment
     * @param int|null $userId User ID who made the change
     * @return int Stock history ID
     */
    public function logAdjustment($productId, $tenantId, $oldQuantity, $newQuantity, $reason, $userId = null)
    {
        return $this->insert([
            'product_id' => $productId,
            'tenant_id' => $tenantId,
            'old_quantity' => $oldQuantity,
            'new_quantity' => $newQuantity,
            'change_amount' => $newQuantity - $oldQuantity,
            'reason' => $reason,
            'user_id' => $userId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get history for product
     *
     * @param int $productId Product ID
     * @param int $limit Limit
     * @return array
     */
    public function getByProduct($productId, $limit = 50)
    {
        $sql = "SELECT sh.*, u.name as user_name
                FROM {$this->table} sh
                LEFT JOIN users u ON sh.user_id = u.id
                WHERE sh.product_id = ?
                ORDER BY sh.created_at DESC
                LIMIT ?";

        return $this->query($sql, [$productId, $limit])->fetchAll();
    }

    /**
     * Get history by tenant
     *
     * @param int $tenantId Tenant ID
     * @param int $limit Limit
     * @return array
     */
    public function getByTenant($tenantId, $limit = 100)
    {
        $sql = "SELECT sh.*, p.name as product_name, p.sku, u.name as user_name
                FROM {$this->table} sh
                INNER JOIN products p ON sh.product_id = p.id
                LEFT JOIN users u ON sh.user_id = u.id
                WHERE sh.tenant_id = ?
                ORDER BY sh.created_at DESC
                LIMIT ?";

        return $this->query($sql, [$tenantId, $limit])->fetchAll();
    }
}
