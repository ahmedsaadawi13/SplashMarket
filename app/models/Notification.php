<?php
// FILE: /app/models/Notification.php

/**
 * SplashMarket - Notification Model
 *
 * Manages email notifications (simulated)
 * PHP 7.0+ compatible
 */

class Notification extends Model
{
    protected $table = 'notifications';

    /**
     * Create notification log
     *
     * @param array $data Notification data
     * @return int Notification ID
     */
    public function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['status'] = isset($data['status']) ? $data['status'] : 'sent';

        return $this->insert($data);
    }

    /**
     * Send order confirmation (simulated)
     *
     * @param int $orderId Order ID
     * @param string $recipientEmail Recipient email
     * @return int Notification ID
     */
    public function sendOrderConfirmation($orderId, $recipientEmail)
    {
        $orderModel = new Order();
        $order = $orderModel->getWithItems($orderId);

        if (!$order) {
            return false;
        }

        $subject = "Order Confirmation - " . $order['order_number'];
        $body = $this->generateOrderConfirmationEmail($order);

        return $this->create([
            'tenant_id' => $order['tenant_id'],
            'type' => 'order_confirmation',
            'recipient_email' => $recipientEmail,
            'subject' => $subject,
            'body' => $body,
            'related_id' => $orderId
        ]);
    }

    /**
     * Send order status update (simulated)
     *
     * @param int $orderId Order ID
     * @param string $recipientEmail Recipient email
     * @param string $status New status
     * @return int Notification ID
     */
    public function sendOrderStatusUpdate($orderId, $recipientEmail, $status)
    {
        $orderModel = new Order();
        $order = $orderModel->findById($orderId);

        if (!$order) {
            return false;
        }

        $subject = "Order " . $order['order_number'] . " - Status Updated";
        $body = $this->generateOrderStatusEmail($order, $status);

        return $this->create([
            'tenant_id' => $order['tenant_id'],
            'type' => 'order_status_update',
            'recipient_email' => $recipientEmail,
            'subject' => $subject,
            'body' => $body,
            'related_id' => $orderId
        ]);
    }

    /**
     * Generate order confirmation email body
     *
     * @param array $order Order data with items
     * @return string Email body
     */
    private function generateOrderConfirmationEmail($order)
    {
        $body = "Thank you for your order!\n\n";
        $body .= "Order Number: " . $order['order_number'] . "\n";
        $body .= "Order Date: " . $order['created_at'] . "\n";
        $body .= "Total: $" . number_format($order['total_amount'], 2) . "\n\n";
        $body .= "Items:\n";

        foreach ($order['items'] as $item) {
            $body .= "- " . $item['product_name'] . " (x" . $item['quantity'] . ") - $" . number_format($item['line_total'], 2) . "\n";
        }

        $body .= "\nWe'll notify you when your order ships.";

        return $body;
    }

    /**
     * Generate order status update email body
     *
     * @param array $order Order data
     * @param string $status New status
     * @return string Email body
     */
    private function generateOrderStatusEmail($order, $status)
    {
        $body = "Your order status has been updated.\n\n";
        $body .= "Order Number: " . $order['order_number'] . "\n";
        $body .= "New Status: " . ucfirst($status) . "\n\n";

        $statusMessages = [
            'confirmed' => 'Your order has been confirmed and is being prepared.',
            'processing' => 'Your order is being processed.',
            'shipped' => 'Your order has been shipped!',
            'completed' => 'Your order has been completed.',
            'canceled' => 'Your order has been canceled.',
            'refunded' => 'Your order has been refunded.'
        ];

        if (isset($statusMessages[$status])) {
            $body .= $statusMessages[$status];
        }

        return $body;
    }

    /**
     * Get notifications by tenant
     *
     * @param int $tenantId Tenant ID
     * @param int $limit Limit
     * @return array
     */
    public function getByTenant($tenantId, $limit = 50)
    {
        $sql = "SELECT * FROM {$this->table} WHERE tenant_id = ? ORDER BY created_at DESC LIMIT ?";
        return $this->query($sql, [$tenantId, $limit])->fetchAll();
    }
}
