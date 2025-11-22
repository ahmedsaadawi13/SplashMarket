<!-- FILE: /app/views/layouts/admin.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? e($pageTitle) . ' - ' : ''; ?>SplashMarket</title>
    <link rel="stylesheet" href="<?php echo asset('css/admin.css'); ?>">
</head>
<body>
    <?php if (Auth::check()): ?>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1>SplashMarket</h1>
                <?php if (!Auth::isPlatformAdmin()): ?>
                    <?php $tenant = (new Tenant())->findById(Auth::tenantId()); ?>
                    <p class="store-name"><?php echo e($tenant['store_name']); ?></p>
                <?php endif; ?>
            </div>

            <nav class="sidebar-nav">
                <?php if (Auth::isPlatformAdmin()): ?>
                    <a href="/platform/dashboard" class="nav-item">
                        <span>📊</span> Dashboard
                    </a>
                    <a href="/platform/tenants" class="nav-item">
                        <span>🏪</span> Tenants
                    </a>
                    <a href="/platform/plans" class="nav-item">
                        <span>💳</span> Plans
                    </a>
                <?php else: ?>
                    <a href="/dashboard" class="nav-item">
                        <span>📊</span> Dashboard
                    </a>
                    <a href="/products" class="nav-item">
                        <span>📦</span> Products
                    </a>
                    <a href="/categories" class="nav-item">
                        <span>🏷️</span> Categories
                    </a>
                    <a href="/orders" class="nav-item">
                        <span>🛒</span> Orders
                    </a>
                    <a href="/customers" class="nav-item">
                        <span>👥</span> Customers
                    </a>
                    <a href="/coupons" class="nav-item">
                        <span>🎟️</span> Coupons
                    </a>
                    <?php if (Auth::isTenantAdmin()): ?>
                    <a href="/store/settings" class="nav-item">
                        <span>⚙️</span> Store Settings
                    </a>
                    <a href="/subscription" class="nav-item">
                        <span>💰</span> Subscription
                    </a>
                    <?php endif; ?>
                <?php endif; ?>

                <a href="/logout" class="nav-item">
                    <span>🚪</span> Logout
                </a>
            </nav>

            <div class="sidebar-footer">
                <p>Logged in as:<br><strong><?php echo e(Auth::user()['name']); ?></strong></p>
                <p><small><?php echo ucfirst(Auth::user()['role']); ?></small></p>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Flash Messages -->
            <?php
            $flashMessages = Session::getAllFlash();
            foreach ($flashMessages as $type => $message):
            ?>
            <div class="alert alert-<?php echo e($type); ?>">
                <?php echo e($message); ?>
            </div>
            <?php endforeach; ?>

            <!-- Page Content -->
            <?php echo $content; ?>
        </main>
    </div>
    <?php else: ?>
        <!-- Not logged in, show content only -->
        <?php echo $content; ?>
    <?php endif; ?>

    <script src="<?php echo asset('js/admin.js'); ?>"></script>
</body>
</html>
