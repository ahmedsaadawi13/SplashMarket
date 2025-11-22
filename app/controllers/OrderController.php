<?php
// FILE: /app/controllers/OrderController.php

/**
 * SplashMarket - Order Controller
 *
 * Manages order viewing and status updates
 * PHP 7.0+ compatible
 */

class OrderController extends Controller
{
    private $orderModel;
    private $orderItemModel;

    public function __construct()
    {
        parent::__construct();
        $this->orderModel = new Order();
        $this->orderItemModel = new OrderItem();
    }

    /**
     * List all orders
     */
    public function index()
    {
        $this->requireAuth();
        $tenantId = $this->getTenantId();

        $page = $this->get('page', 1);
        $filters = [
            'status' => $this->get('status'),
            'search' => $this->get('search'),
            'date_from' => $this->get('date_from'),
            'date_to' => $this->get('date_to')
        ];

        $orders = $this->orderModel->getByTenant($tenantId, $filters, $page, 20);

        $this->render('orders/index', [
            'orders' => $orders['data'],
            'pagination' => $orders,
            'filters' => $filters
        ]);
    }

    /**
     * View order details
     */
    public function view($orderId)
    {
        $this->requireAuth();
        $tenantId = $this->getTenantId();

        $order = $this->orderModel->getWithItems($orderId);

        if (!$order || $order['tenant_id'] != $tenantId) {
            Session::setFlash('error', 'Order not found');
            $this->redirect('/orders');
        }

        // Get customer info if exists
        $customer = null;
        if ($order['customer_id']) {
            $customerModel = new Customer();
            $customer = $customerModel->findById($order['customer_id']);
        }

        $this->render('orders/view', [
            'order' => $order,
            'customer' => $customer
        ]);
    }

    /**
     * Update order status
     */
    public function updateStatus($orderId)
    {
        $this->requireAuth();
        $this->requireCsrf();

        $tenantId = $this->getTenantId();

        $order = $this->orderModel->findById($orderId);

        if (!$order || $order['tenant_id'] != $tenantId) {
            Session::setFlash('error', 'Order not found');
            $this->redirect('/orders');
        }

        $newStatus = $this->post('status');
        $note = $this->post('note');

        $validStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'completed', 'canceled', 'refunded'];

        if (!in_array($newStatus, $validStatuses)) {
            Session::setFlash('error', 'Invalid status');
            $this->redirect('/orders/' . $orderId);
        }

        try {
            $this->orderModel->updateStatus($orderId, $newStatus, $note);

            // Send notification to customer
            if ($order['customer_email']) {
                $notificationModel = new Notification();
                $notificationModel->sendOrderStatusUpdate($orderId, $order['customer_email'], $newStatus);
            }

            Session::setFlash('success', 'Order status updated');
        } catch (Exception $e) {
            error_log('Order status update error: ' . $e->getMessage());
            Session::setFlash('error', 'Failed to update order status');
        }

        $this->redirect('/orders/' . $orderId);
    }

    /**
     * Print invoice
     */
    public function invoice($orderId)
    {
        $this->requireAuth();
        $tenantId = $this->getTenantId();

        $order = $this->orderModel->getWithItems($orderId);

        if (!$order || $order['tenant_id'] != $tenantId) {
            die('Order not found');
        }

        // Get tenant info
        $tenantModel = new Tenant();
        $tenant = $tenantModel->findById($tenantId);

        $this->render('orders/invoice', [
            'order' => $order,
            'tenant' => $tenant
        ], null);
    }
}
