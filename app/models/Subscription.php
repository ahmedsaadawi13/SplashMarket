<?php
// FILE: /app/models/Subscription.php

/**
 * SplashMarket - Tenant Subscription Model
 *
 * Manages tenant subscriptions and usage tracking
 * PHP 7.0+ compatible
 */

class Subscription extends Model
{
    protected $table = 'tenant_subscriptions';

    /**
     * Get active subscription for tenant
     *
     * @param int $tenantId Tenant ID
     * @return array|null
     */
    public function getActiveSubscription($tenantId)
    {
        $sql = "SELECT ts.*, p.name as plan_name, p.max_products, p.max_orders_per_month, p.max_storage_mb
                FROM {$this->table} ts
                INNER JOIN plans p ON ts.plan_id = p.id
                WHERE ts.tenant_id = ? AND ts.status = 'active'
                ORDER BY ts.created_at DESC
                LIMIT 1";

        $result = $this->query($sql, [$tenantId])->fetch();
        return $result ?: null;
    }

    /**
     * Get subscription with plan details
     *
     * @param int $subscriptionId Subscription ID
     * @return array|null
     */
    public function getWithPlan($subscriptionId)
    {
        $sql = "SELECT ts.*, p.name as plan_name, p.max_products, p.max_orders_per_month, p.max_storage_mb
                FROM {$this->table} ts
                INNER JOIN plans p ON ts.plan_id = p.id
                WHERE ts.id = ?
                LIMIT 1";

        $result = $this->query($sql, [$subscriptionId])->fetch();
        return $result ?: null;
    }

    /**
     * Create subscription
     *
     * @param array $data Subscription data
     * @return int Subscription ID
     */
    public function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['status'] = isset($data['status']) ? $data['status'] : 'active';

        return $this->insert($data);
    }

    /**
     * Update subscription
     *
     * @param int $subscriptionId Subscription ID
     * @param array $data Subscription data
     * @return bool
     */
    public function updateSubscription($subscriptionId, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($subscriptionId, $data);
    }

    /**
     * Cancel subscription
     *
     * @param int $subscriptionId Subscription ID
     * @return bool
     */
    public function cancel($subscriptionId)
    {
        return $this->update($subscriptionId, [
            'status' => 'canceled',
            'end_date' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Check if tenant can add more products
     *
     * @param int $tenantId Tenant ID
     * @return array Result with 'allowed' boolean and 'message'
     */
    public function canAddProduct($tenantId)
    {
        $subscription = $this->getActiveSubscription($tenantId);

        if (!$subscription) {
            return ['allowed' => false, 'message' => 'No active subscription'];
        }

        // Get current product count
        $productModel = new Product();
        $currentCount = $productModel->count(['tenant_id' => $tenantId]);

        if ($currentCount >= $subscription['max_products']) {
            return ['allowed' => false, 'message' => 'Product limit reached'];
        }

        return ['allowed' => true];
    }

    /**
     * Check if tenant can create more orders this month
     *
     * @param int $tenantId Tenant ID
     * @return array Result with 'allowed' boolean and 'message'
     */
    public function canCreateOrder($tenantId)
    {
        $subscription = $this->getActiveSubscription($tenantId);

        if (!$subscription) {
            return ['allowed' => false, 'message' => 'No active subscription'];
        }

        // Get current month order count
        $orderModel = new Order();
        $currentMonth = date('Y-m');
        $currentCount = $orderModel->getMonthlyCount($tenantId, $currentMonth);

        if ($currentCount >= $subscription['max_orders_per_month']) {
            return ['allowed' => false, 'message' => 'Monthly order limit reached'];
        }

        return ['allowed' => true];
    }

    /**
     * Get usage statistics for tenant
     *
     * @param int $tenantId Tenant ID
     * @return array
     */
    public function getUsageStats($tenantId)
    {
        $subscription = $this->getActiveSubscription($tenantId);

        if (!$subscription) {
            return null;
        }

        // Get product count
        $productModel = new Product();
        $productCount = $productModel->count(['tenant_id' => $tenantId]);

        // Get current month order count
        $orderModel = new Order();
        $currentMonth = date('Y-m');
        $orderCount = $orderModel->getMonthlyCount($tenantId, $currentMonth);

        // Calculate storage (simplified - just count product images)
        $storageMb = 0; // Placeholder - would need to calculate actual file sizes

        return [
            'products' => [
                'current' => $productCount,
                'limit' => $subscription['max_products'],
                'percentage' => ($productCount / $subscription['max_products']) * 100
            ],
            'orders' => [
                'current' => $orderCount,
                'limit' => $subscription['max_orders_per_month'],
                'percentage' => ($orderCount / $subscription['max_orders_per_month']) * 100
            ],
            'storage' => [
                'current' => $storageMb,
                'limit' => $subscription['max_storage_mb'],
                'percentage' => $subscription['max_storage_mb'] > 0 ? ($storageMb / $subscription['max_storage_mb']) * 100 : 0
            ]
        ];
    }

}
