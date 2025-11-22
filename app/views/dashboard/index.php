<!-- FILE: /app/views/dashboard/index.php -->
<div class="dashboard">
    <h1>Dashboard</h1>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Orders</h3>
            <p class="stat-value"><?php echo number_format($stats['total_orders']); ?></p>
        </div>

        <div class="stat-card">
            <h3>Total Revenue</h3>
            <p class="stat-value"><?php echo money($stats['total_revenue']); ?></p>
        </div>

        <div class="stat-card">
            <h3>Total Customers</h3>
            <p class="stat-value"><?php echo number_format($stats['total_customers']); ?></p>
        </div>

        <div class="stat-card">
            <h3>Total Products</h3>
            <p class="stat-value"><?php echo number_format($stats['total_products']); ?></p>
        </div>
    </div>

    <!-- Subscription Usage -->
    <?php if ($subscription && $usageStats): ?>
    <div class="section">
        <h2>Subscription Usage</h2>
        <div class="usage-stats">
            <div class="usage-bar">
                <label>Products: <?php echo $usageStats['products']['current']; ?> / <?php echo $usageStats['products']['limit']; ?></label>
                <div class="progress">
                    <div class="progress-bar" style="width: <?php echo min($usageStats['products']['percentage'], 100); ?>%"></div>
                </div>
            </div>

            <div class="usage-bar">
                <label>Orders this month: <?php echo $usageStats['orders']['current']; ?> / <?php echo $usageStats['orders']['limit']; ?></label>
                <div class="progress">
                    <div class="progress-bar" style="width: <?php echo min($usageStats['orders']['percentage'], 100); ?>%"></div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recent Orders -->
    <div class="section">
        <h2>Recent Orders</h2>
        <?php if (!empty($recentOrders)): ?>
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
                <?php foreach ($recentOrders as $order): ?>
                <tr>
                    <td><?php echo e($order['order_number']); ?></td>
                    <td><?php echo e($order['customer_email']); ?></td>
                    <td><?php echo money($order['total_amount']); ?></td>
                    <td><?php echo statusBadge($order['status']); ?></td>
                    <td><?php echo formatDate($order['created_at']); ?></td>
                    <td>
                        <a href="/orders/<?php echo $order['id']; ?>" class="btn btn-sm">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>No orders yet.</p>
        <?php endif; ?>
    </div>

    <!-- Top Products -->
    <?php if (!empty($topProducts)): ?>
    <div class="section">
        <h2>Top Selling Products</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Total Sold</th>
                    <th>Revenue</th>
                    <th>Orders</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($topProducts as $product): ?>
                <tr>
                    <td><?php echo e($product['product_name']); ?></td>
                    <td><?php echo number_format($product['total_quantity']); ?></td>
                    <td><?php echo money($product['total_revenue']); ?></td>
                    <td><?php echo number_format($product['order_count']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
