<?php
// FILE: /app/controllers/SubscriptionController.php

/**
 * SplashMarket - Subscription Controller
 *
 * Manages subscription plans and billing
 * PHP 7.0+ compatible
 */

class SubscriptionController extends Controller
{
    private $subscriptionModel;
    private $planModel;
    private $invoiceModel;
    private $paymentModel;

    public function __construct()
    {
        parent::__construct();
        $this->subscriptionModel = new Subscription();
        $this->planModel = new Plan();
        $this->invoiceModel = new Invoice();
        $this->paymentModel = new Payment();
    }

    /**
     * Show subscription overview
     */
    public function index()
    {
        $this->requireAuth();
        $tenantId = $this->getTenantId();

        $subscription = $this->subscriptionModel->getActiveSubscription($tenantId);
        $usageStats = $this->subscriptionModel->getUsageStats($tenantId);
        $availablePlans = $this->planModel->getActive();

        // Get billing history
        $invoices = $this->invoiceModel->getByTenant($tenantId, 10);

        $this->render('subscriptions/index', [
            'subscription' => $subscription,
            'usageStats' => $usageStats,
            'availablePlans' => $availablePlans,
            'invoices' => $invoices
        ]);
    }

    /**
     * Show plan selection
     */
    public function changePlan()
    {
        $this->requireRole(['tenant_admin']);
        $tenantId = $this->getTenantId();

        $currentSubscription = $this->subscriptionModel->getActiveSubscription($tenantId);
        $availablePlans = $this->planModel->getActive();

        $this->render('subscriptions/change-plan', [
            'currentSubscription' => $currentSubscription,
            'plans' => $availablePlans
        ]);
    }

    /**
     * Process plan change
     */
    public function updatePlan()
    {
        $this->requireRole(['tenant_admin']);
        $this->requireCsrf();

        $tenantId = $this->getTenantId();
        $planId = $this->post('plan_id');

        $plan = $this->planModel->findById($planId);

        if (!$plan || !$plan['is_active']) {
            Session::setFlash('error', 'Invalid plan selected');
            $this->redirect('/subscription/change-plan');
        }

        try {
            // Cancel current subscription
            $currentSubscription = $this->subscriptionModel->getActiveSubscription($tenantId);
            if ($currentSubscription) {
                $this->subscriptionModel->cancel($currentSubscription['id']);
            }

            // Create new subscription
            $subscriptionId = $this->subscriptionModel->create([
                'tenant_id' => $tenantId,
                'plan_id' => $planId,
                'start_date' => date('Y-m-d H:i:s'),
                'status' => 'active'
            ]);

            // Create invoice
            $invoiceId = $this->invoiceModel->create([
                'tenant_id' => $tenantId,
                'subscription_id' => $subscriptionId,
                'amount' => $plan['price'],
                'status' => 'pending'
            ]);

            Session::setFlash('success', 'Plan changed successfully');
            $this->redirect('/subscription/payment/' . $invoiceId);

        } catch (Exception $e) {
            error_log('Plan change error: ' . $e->getMessage());
            Session::setFlash('error', 'Failed to change plan');
            $this->redirect('/subscription/change-plan');
        }
    }

    /**
     * Show payment page
     */
    public function payment($invoiceId)
    {
        $this->requireAuth();
        $tenantId = $this->getTenantId();

        $invoice = $this->invoiceModel->findById($invoiceId);

        if (!$invoice || $invoice['tenant_id'] != $tenantId) {
            Session::setFlash('error', 'Invoice not found');
            $this->redirect('/subscription');
        }

        $this->render('subscriptions/payment', [
            'invoice' => $invoice
        ]);
    }

    /**
     * Process payment (simulated)
     */
    public function processPayment($invoiceId)
    {
        $this->requireAuth();
        $this->requireCsrf();

        $tenantId = $this->getTenantId();

        $invoice = $this->invoiceModel->findById($invoiceId);

        if (!$invoice || $invoice['tenant_id'] != $tenantId) {
            Session::setFlash('error', 'Invoice not found');
            $this->redirect('/subscription');
        }

        try {
            // Simulate payment processing
            $paymentMethod = $this->post('payment_method', 'dummy');

            // Create payment record
            $paymentId = $this->paymentModel->create([
                'tenant_id' => $tenantId,
                'invoice_id' => $invoiceId,
                'amount' => $invoice['amount'],
                'payment_method' => $paymentMethod,
                'status' => 'completed'
            ]);

            // Mark invoice as paid
            $this->invoiceModel->markAsPaid($invoiceId);

            Session::setFlash('success', 'Payment processed successfully');
            $this->redirect('/subscription');

        } catch (Exception $e) {
            error_log('Payment processing error: ' . $e->getMessage());
            Session::setFlash('error', 'Failed to process payment');
            $this->redirect('/subscription/payment/' . $invoiceId);
        }
    }
}
