<?php
// FILE: /app/models/Plan.php

/**
 * SplashMarket - Subscription Plan Model
 *
 * Manages subscription plans
 * PHP 7.0+ compatible
 */

class Plan extends Model
{
    protected $table = 'plans';

    /**
     * Get all active plans
     *
     * @return array
     */
    public function getActive()
    {
        return $this->findAll(['is_active' => 1], 'price ASC');
    }

    /**
     * Get plan by slug
     *
     * @param string $slug Plan slug
     * @return array|null
     */
    public function findBySlug($slug)
    {
        return $this->findOne(['slug' => $slug]);
    }

    /**
     * Create plan
     *
     * @param array $data Plan data
     * @return int Plan ID
     */
    public function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['is_active'] = isset($data['is_active']) ? $data['is_active'] : 1;

        return $this->insert($data);
    }

    /**
     * Update plan
     *
     * @param int $planId Plan ID
     * @param array $data Plan data
     * @return bool
     */
    public function updatePlan($planId, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($planId, $data);
    }
}
