<!-- FILE: /app/views/orders/view.php -->
<div class="page-header">
    <h1>Order #<?php echo e($order['order_number']); ?></h1>
    <a href="/orders" class="btn">Back to Orders</a>
</div>

<div class="order-detail">
    <div class="order-info">
        <h3>Order Information</h3>
        <p><strong>Status:</strong> <?php echo statusBadge($order['status']); ?></p>
        <p><strong>Date:</strong> <?php echo formatDatetime($order['created_at']); ?></p>
        <p><strong>Customer:</strong> <?php echo e($order['customer_email']); ?></p>
        <p><strong>Payment Method:</strong> <?php echo e($order['payment_method']); ?></p>
    </div>

    <div class="order-items">
        <h3>Order Items</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order['items'] as $item): ?>
                <tr>
                    <td><?php echo e($item['product_name']); ?></td>
                    <td><?php echo e($item['sku']); ?></td>
                    <td><?php echo money($item['price']); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><?php echo money($item['line_total']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="order-totals">
            <p><strong>Subtotal:</strong> <?php echo money($order['subtotal']); ?></p>
            <p><strong>Shipping:</strong> <?php echo money($order['shipping_cost']); ?></p>
            <p class="total"><strong>Total:</strong> <?php echo money($order['total_amount']); ?></p>
        </div>
    </div>

    <div class="order-actions">
        <h3>Update Status</h3>
        <form method="POST" action="/orders/<?php echo $order['id']; ?>/status">
            <?php echo csrf_field(); ?>
            <select name="status">
                <option value="pending" <?php echo selected($order['status'], 'pending'); ?>>Pending</option>
                <option value="confirmed" <?php echo selected($order['status'], 'confirmed'); ?>>Confirmed</option>
                <option value="processing" <?php echo selected($order['status'], 'processing'); ?>>Processing</option>
                <option value="shipped" <?php echo selected($order['status'], 'shipped'); ?>>Shipped</option>
                <option value="completed" <?php echo selected($order['status'], 'completed'); ?>>Completed</option>
                <option value="canceled" <?php echo selected($order['status'], 'canceled'); ?>>Canceled</option>
            </select>
            <button type="submit" class="btn btn-primary">Update Status</button>
        </form>
    </div>
</div>
