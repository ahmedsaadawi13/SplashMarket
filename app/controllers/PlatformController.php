<?php
// FILE: /app/controllers/PlatformController.php

/**
 * SplashMarket - Platform Admin Controller
 *
 * Manages platform-wide administration
 * PHP 7.0+ compatible
 */

class PlatformController extends Controller
{
    /**
     * Platform admin dashboard
     */
    public function dashboard()
    {
        $this->requireRole(['platform_admin']);

        $tenantModel = new Tenant();
        $orderModel = new Order();
        $userModel = new User();

        // Global statistics
        $stats = [
            'total_tenants' => $tenantModel->count(),
            'active_tenants' => $tenantModel->count(['is_active' => 1]),
            'total_users' => $userModel->count(),
            'total_orders' => $orderModel->count()
        ];

        // Get top performing tenants
        $sql = "SELECT t.id, t.store_name, COUNT(o.id) as order_count, SUM(o.total_amount) as revenue
                FROM tenants t
                LEFT JOIN orders o ON t.id = o.tenant_id
                WHERE t.is_active = 1
                GROUP BY t.id
                ORDER BY revenue DESC
                LIMIT 10";

        $db = Database::getInstance()->getConnection();
        $stmt = $db->query($sql);
        $topTenants = $stmt->fetchAll();

        // Recent tenants
        $recentTenants = $tenantModel->findAll([], 'created_at DESC', 5);

        $this->render('dashboard/platform', [
            'stats' => $stats,
            'topTenants' => $topTenants,
            'recentTenants' => $recentTenants
        ]);
    }

    /**
     * List all tenants
     */
    public function tenants()
    {
        $this->requireRole(['platform_admin']);

        $tenantModel = new Tenant();
        $page = $this->get('page', 1);
        $tenants = $tenantModel->paginate($page, 20);

        $this->render('platform/tenants', [
            'tenants' => $tenants['data'],
            'pagination' => $tenants
        ]);
    }

    /**
     * View tenant details
     */
    public function tenantView($tenantId)
    {
        $this->requireRole(['platform_admin']);

        $tenantModel = new Tenant();
        $tenant = $tenantModel->findById($tenantId);

        if (!$tenant) {
            Session::setFlash('error', 'Tenant not found');
            $this->redirect('/platform/tenants');
        }

        $stats = $tenantModel->getStats($tenantId);

        $subscriptionModel = new Subscription();
        $subscription = $subscriptionModel->getActiveSubscription($tenantId);

        $this->render('platform/tenant-view', [
            'tenant' => $tenant,
            'stats' => $stats,
            'subscription' => $subscription
        ]);
    }

    /**
     * Toggle tenant status
     */
    public function toggleTenant($tenantId)
    {
        $this->requireRole(['platform_admin']);
        $this->requireCsrf();

        $tenantModel = new Tenant();
        $tenant = $tenantModel->findById($tenantId);

        if (!$tenant) {
            Session::setFlash('error', 'Tenant not found');
            $this->redirect('/platform/tenants');
        }

        try {
            if ($tenant['is_active']) {
                $tenantModel->deactivate($tenantId);
                Session::setFlash('success', 'Tenant deactivated');
            } else {
                $tenantModel->activate($tenantId);
                Session::setFlash('success', 'Tenant activated');
            }
        } catch (Exception $e) {
            error_log('Tenant toggle error: ' . $e->getMessage());
            Session::setFlash('error', 'Failed to update tenant status');
        }

        $this->redirect('/platform/tenants/' . $tenantId);
    }

    /**
     * Manage subscription plans
     */
    public function plans()
    {
        $this->requireRole(['platform_admin']);

        $planModel = new Plan();
        $plans = $planModel->findAll([], 'price ASC');

        $this->render('platform/plans', [
            'plans' => $plans
        ]);
    }
}
