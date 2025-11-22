<?php
// FILE: /app/controllers/DashboardController.php

/**
 * SplashMarket - Dashboard Controller
 *
 * Displays vendor dashboard with analytics
 * PHP 7.0+ compatible
 */

class DashboardController extends Controller
{
    /**
     * Show dashboard
     */
    public function index()
    {
        $this->requireAuth();

        $user = Auth::user();
        $tenantId = $this->getTenantId();

        // Platform admin sees different dashboard
        if (Auth::isPlatformAdmin()) {
            $this->redirect('/platform/dashboard');
        }

        // Get tenant info
        $tenantModel = new Tenant();
        $tenant = $tenantModel->findById($tenantId);

        // Get statistics
        $orderModel = new Order();
        $productModel = new Product();
        $customerModel = new Customer();

        // Total counts
        $stats = [
            'total_orders' => $orderModel->count(['tenant_id' => $tenantId]),
            'total_revenue' => $orderModel->getRevenue($tenantId),
            'total_customers' => $customerModel->count(['tenant_id' => $tenantId]),
            'total_products' => $productModel->count(['tenant_id' => $tenantId]),
            'out_of_stock' => count($productModel->getOutOfStock($tenantId)),
            'low_stock' => count($productModel->getLowStock($tenantId, 10))
        ];

        // Order status counts
        $stats['status_counts'] = $orderModel->getStatusCounts($tenantId);

        // Recent orders
        $recentOrders = $orderModel->getByTenant($tenantId, [], 1, 5);

        // Daily sales (last 30 days)
        $dailySales = $orderModel->getDailySales($tenantId, 30);

        // Top selling products
        $orderItemModel = new OrderItem();
        $topProducts = $orderItemModel->getTopSelling($tenantId, 5);

        // Subscription info
        $subscriptionModel = new Subscription();
        $subscription = $subscriptionModel->getActiveSubscription($tenantId);
        $usageStats = $subscriptionModel->getUsageStats($tenantId);

        $this->render('dashboard/index', [
            'tenant' => $tenant,
            'stats' => $stats,
            'recentOrders' => $recentOrders['data'],
            'dailySales' => $dailySales,
            'topProducts' => $topProducts,
            'subscription' => $subscription,
            'usageStats' => $usageStats
        ]);
    }
}
