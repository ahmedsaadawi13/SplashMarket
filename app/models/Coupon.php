<?php
// FILE: /app/models/Coupon.php

/**
 * SplashMarket - Coupon Model
 *
 * Manages discount coupons
 * PHP 7.0+ compatible
 */

class Coupon extends Model
{
    protected $table = 'coupons';

    /**
     * Get coupons by tenant
     *
     * @param int $tenantId Tenant ID
     * @param bool $activeOnly Get only active coupons
     * @return array
     */
    public function getByTenant($tenantId, $activeOnly = false)
    {
        $conditions = ['tenant_id' => $tenantId];

        if ($activeOnly) {
            $conditions['is_active'] = 1;
        }

        return $this->findAll($conditions, 'created_at DESC');
    }

    /**
     * Find coupon by code
     *
     * @param string $code Coupon code
     * @param int $tenantId Tenant ID
     * @return array|null
     */
    public function findByCode($code, $tenantId)
    {
        return $this->findOne(['code' => strtoupper($code), 'tenant_id' => $tenantId]);
    }

    /**
     * Validate coupon
     *
     * @param string $code Coupon code
     * @param int $tenantId Tenant ID
     * @param float $orderTotal Order total
     * @return array Result with 'valid' boolean and 'message' string
     */
    public function validateCoupon($code, $tenantId, $orderTotal)
    {
        $coupon = $this->findByCode($code, $tenantId);

        if (!$coupon) {
            return ['valid' => false, 'message' => 'Coupon not found'];
        }

        // Check if active
        if (!$coupon['is_active']) {
            return ['valid' => false, 'message' => 'Coupon is not active'];
        }

        // Check start date
        if ($coupon['start_date'] && strtotime($coupon['start_date']) > time()) {
            return ['valid' => false, 'message' => 'Coupon is not yet valid'];
        }

        // Check end date
        if ($coupon['end_date'] && strtotime($coupon['end_date']) < time()) {
            return ['valid' => false, 'message' => 'Coupon has expired'];
        }

        // Check minimum order amount
        if ($coupon['min_order_amount'] && $orderTotal < $coupon['min_order_amount']) {
            return ['valid' => false, 'message' => 'Order total does not meet minimum requirement'];
        }

        // Check usage limit
        if ($coupon['max_uses'] && $coupon['times_used'] >= $coupon['max_uses']) {
            return ['valid' => false, 'message' => 'Coupon usage limit reached'];
        }

        return ['valid' => true, 'coupon' => $coupon];
    }

    /**
     * Calculate discount amount
     *
     * @param array $coupon Coupon data
     * @param float $orderTotal Order total
     * @return float Discount amount
     */
    public function calculateDiscount($coupon, $orderTotal)
    {
        if ($coupon['discount_type'] === 'percentage') {
            return ($orderTotal * $coupon['discount_value']) / 100;
        } else {
            // Fixed amount
            return min($coupon['discount_value'], $orderTotal);
        }
    }

    /**
     * Increment coupon usage
     *
     * @param int $couponId Coupon ID
     * @return bool
     */
    public function incrementUsage($couponId)
    {
        $sql = "UPDATE {$this->table} SET times_used = times_used + 1 WHERE id = ?";
        $stmt = $this->query($sql, [$couponId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Create coupon
     *
     * @param array $data Coupon data
     * @return int Coupon ID
     */
    public function create($data)
    {
        // Convert code to uppercase
        if (isset($data['code'])) {
            $data['code'] = strtoupper($data['code']);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['is_active'] = isset($data['is_active']) ? $data['is_active'] : 1;
        $data['times_used'] = 0;

        return $this->insert($data);
    }

    /**
     * Update coupon
     *
     * @param int $couponId Coupon ID
     * @param array $data Coupon data
     * @return bool
     */
    public function updateCoupon($couponId, $data)
    {
        // Convert code to uppercase
        if (isset($data['code'])) {
            $data['code'] = strtoupper($data['code']);
        }

        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($couponId, $data);
    }

    /**
     * Check if coupon code exists
     *
     * @param string $code Coupon code
     * @param int $tenantId Tenant ID
     * @param int|null $excludeId Coupon ID to exclude
     * @return bool
     */
    public function codeExists($code, $tenantId, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE code = ? AND tenant_id = ?";
        $params = [strtoupper($code), $tenantId];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = $this->query($sql, $params)->fetch();
        return $result['count'] > 0;
    }
}
