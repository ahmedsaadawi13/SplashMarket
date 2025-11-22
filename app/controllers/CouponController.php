<?php
// FILE: /app/controllers/CouponController.php

/**
 * SplashMarket - Coupon Controller
 *
 * Manages discount coupons
 * PHP 7.0+ compatible
 */

class CouponController extends Controller
{
    private $couponModel;

    public function __construct()
    {
        parent::__construct();
        $this->couponModel = new Coupon();
    }

    /**
     * List all coupons
     */
    public function index()
    {
        $this->requireAuth();
        $tenantId = $this->getTenantId();

        $coupons = $this->couponModel->getByTenant($tenantId);

        $this->render('coupons/index', [
            'coupons' => $coupons
        ]);
    }

    /**
     * Show create coupon form
     */
    public function create()
    {
        $this->requireAuth();
        $this->render('coupons/create');
    }

    /**
     * Store new coupon
     */
    public function store()
    {
        $this->requireAuth();
        $this->requireCsrf();

        $tenantId = $this->getTenantId();

        // Validate input
        $validator = new Validator($_POST);
        $validator->required('code')
                 ->required('discount_type')->in('discount_type', ['percentage', 'fixed'])
                 ->required('discount_value')->numeric('discount_value');

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError());
            $this->redirect('/coupons/create');
        }

        // Check if code exists
        if ($this->couponModel->codeExists($this->post('code'), $tenantId)) {
            Session::setFlash('error', 'Coupon code already exists');
            $this->redirect('/coupons/create');
        }

        try {
            $couponId = $this->couponModel->create([
                'tenant_id' => $tenantId,
                'code' => $this->post('code'),
                'discount_type' => $this->post('discount_type'),
                'discount_value' => $this->post('discount_value'),
                'min_order_amount' => $this->post('min_order_amount') ?: null,
                'max_uses' => $this->post('max_uses') ?: null,
                'start_date' => $this->post('start_date') ?: null,
                'end_date' => $this->post('end_date') ?: null,
                'is_active' => $this->post('is_active', 1)
            ]);

            Session::setFlash('success', 'Coupon created successfully');
            $this->redirect('/coupons');

        } catch (Exception $e) {
            error_log('Coupon creation error: ' . $e->getMessage());
            Session::setFlash('error', 'Failed to create coupon');
            $this->redirect('/coupons/create');
        }
    }

    /**
     * Show edit coupon form
     */
    public function edit($couponId)
    {
        $this->requireAuth();
        $tenantId = $this->getTenantId();

        $coupon = $this->couponModel->findById($couponId);

        if (!$coupon || $coupon['tenant_id'] != $tenantId) {
            Session::setFlash('error', 'Coupon not found');
            $this->redirect('/coupons');
        }

        $this->render('coupons/edit', [
            'coupon' => $coupon
        ]);
    }

    /**
     * Update coupon
     */
    public function update($couponId)
    {
        $this->requireAuth();
        $this->requireCsrf();

        $tenantId = $this->getTenantId();

        $coupon = $this->couponModel->findById($couponId);

        if (!$coupon || $coupon['tenant_id'] != $tenantId) {
            Session::setFlash('error', 'Coupon not found');
            $this->redirect('/coupons');
        }

        // Validate input
        $validator = new Validator($_POST);
        $validator->required('code')
                 ->required('discount_type')->in('discount_type', ['percentage', 'fixed'])
                 ->required('discount_value')->numeric('discount_value');

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError());
            $this->redirect('/coupons/' . $couponId . '/edit');
        }

        // Check if code exists (excluding current coupon)
        if ($this->couponModel->codeExists($this->post('code'), $tenantId, $couponId)) {
            Session::setFlash('error', 'Coupon code already exists');
            $this->redirect('/coupons/' . $couponId . '/edit');
        }

        try {
            $this->couponModel->updateCoupon($couponId, [
                'code' => $this->post('code'),
                'discount_type' => $this->post('discount_type'),
                'discount_value' => $this->post('discount_value'),
                'min_order_amount' => $this->post('min_order_amount') ?: null,
                'max_uses' => $this->post('max_uses') ?: null,
                'start_date' => $this->post('start_date') ?: null,
                'end_date' => $this->post('end_date') ?: null,
                'is_active' => $this->post('is_active', 0)
            ]);

            Session::setFlash('success', 'Coupon updated successfully');
            $this->redirect('/coupons');

        } catch (Exception $e) {
            error_log('Coupon update error: ' . $e->getMessage());
            Session::setFlash('error', 'Failed to update coupon');
            $this->redirect('/coupons/' . $couponId . '/edit');
        }
    }

    /**
     * Delete coupon
     */
    public function delete($couponId)
    {
        $this->requireAuth();
        $this->requireCsrf();

        $tenantId = $this->getTenantId();

        $coupon = $this->couponModel->findById($couponId);

        if (!$coupon || $coupon['tenant_id'] != $tenantId) {
            Session::setFlash('error', 'Coupon not found');
            $this->redirect('/coupons');
        }

        try {
            $this->couponModel->delete($couponId);
            Session::setFlash('success', 'Coupon deleted successfully');
        } catch (Exception $e) {
            error_log('Coupon deletion error: ' . $e->getMessage());
            Session::setFlash('error', 'Failed to delete coupon');
        }

        $this->redirect('/coupons');
    }
}
