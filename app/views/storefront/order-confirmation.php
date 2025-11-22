<!-- FILE: /app/views/storefront/order-confirmation.php -->
<div class="container">
    <div class="confirmation-page">
        <div class="success-icon">✓</div>

        <h1>Order Confirmed!</h1>

        <p>Thank you for your order. Your order has been received and is being processed.</p>

        <div class="order-details">
            <h2>Order Details</h2>

            <div class="detail-row">
                <span>Order Number:</span>
                <strong><?php echo e($order['order_number']); ?></strong>
            </div>

            <div class="detail-row">
                <span>Order Date:</span>
                <strong><?php echo formatDate($order['created_at']); ?></strong>
            </div>

            <div class="detail-row">
                <span>Total Amount:</span>
                <strong><?php echo money($order['total_amount']); ?></strong>
            </div>

            <div class="detail-row">
                <span>Status:</span>
                <strong><?php echo ucfirst($order['status']); ?></strong>
            </div>

            <div class="detail-row">
                <span>Payment Method:</span>
                <strong><?php echo ucfirst($order['payment_method']); ?></strong>
            </div>
        </div>

        <p>A confirmation email has been sent to <strong><?php echo e($order['customer_email']); ?></strong></p>

        <a href="/store/?tenant_id=<?php echo $tenant['id']; ?>" class="btn btn-primary">Continue Shopping</a>
    </div>
</div>
