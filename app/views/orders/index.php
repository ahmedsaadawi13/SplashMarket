<!-- FILE: /app/views/orders/index.php -->
<div class="page-header">
    <h1>Orders</h1>
</div>

<!-- Filters -->
<div class="filters">
    <form method="GET" action="/orders" class="filter-form">
        <input type="text" name="search" placeholder="Search orders..." value="<?php echo e($filters['search'] ?? ''); ?>">
        <select name="status">
            <option value="">All Status</option>
            <option value="pending" <?php echo selected($filters['status'] ?? '', 'pending'); ?>>Pending</option>
            <option value="completed" <?php echo selected($filters['status'] ?? '', 'completed'); ?>>Completed</option>
        </select>
        <button type="submit" class="btn">Filter</button>
    </form>
</div>

<?php if (!empty($orders)): ?>
<table class="data-table">
    <thead>
        <tr>
            <th>Order #</th>
            <th>Customer</th>
            <th>Total</th>
            <th>Status</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($orders as $order): ?>
        <tr>
            <td><?php echo e($order['order_number']); ?></td>
            <td><?php echo e($order['customer_email']); ?></td>
            <td><?php echo money($order['total_amount']); ?></td>
            <td><?php echo statusBadge($order['status']); ?></td>
            <td><?php echo formatDate($order['created_at']); ?></td>
            <td><a href="/orders/<?php echo $order['id']; ?>" class="btn btn-sm">View</a></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php echo paginate($pagination, '/orders'); ?>
<?php else: ?>
<p>No orders found.</p>
<?php endif; ?>
