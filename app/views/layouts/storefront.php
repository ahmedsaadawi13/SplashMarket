<!-- FILE: /app/views/layouts/storefront.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($tenant['meta_title']) && $tenant['meta_title'] ? e($tenant['meta_title']) : e($tenant['store_name']); ?></title>
    <?php if (isset($tenant['meta_description']) && $tenant['meta_description']): ?>
    <meta name="description" content="<?php echo e($tenant['meta_description']); ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?php echo asset('css/storefront.css'); ?>">
    <?php if (isset($tenant['theme_color']) && $tenant['theme_color']): ?>
    <style>
        :root {
            --theme-color: <?php echo e($tenant['theme_color']); ?>;
        }
    </style>
    <?php endif; ?>
</head>
<body>
    <!-- Header -->
    <header class="storefront-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <?php if (isset($tenant['logo']) && $tenant['logo']): ?>
                        <img src="<?php echo uploadUrl($tenant['logo']); ?>" alt="<?php echo e($tenant['store_name']); ?>">
                    <?php else: ?>
                        <h1><?php echo e($tenant['store_name']); ?></h1>
                    <?php endif; ?>
                </div>

                <nav class="main-nav">
                    <a href="/store/?tenant_id=<?php echo $tenant['id']; ?>">Home</a>
                    <a href="/store/cart?tenant_id=<?php echo $tenant['id']; ?>">
                        🛒 Cart
                        <?php
                        $cartModel = new Cart();
                        $cartCount = $cartModel->getItemCount(session_id(), $tenant['id']);
                        if ($cartCount > 0):
                        ?>
                        <span class="cart-badge"><?php echo $cartCount; ?></span>
                        <?php endif; ?>
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Banner -->
    <?php if (isset($tenant['banner_image']) && $tenant['banner_image']): ?>
    <div class="banner">
        <img src="<?php echo uploadUrl($tenant['banner_image']); ?>" alt="Banner">
    </div>
    <?php endif; ?>

    <!-- Flash Messages -->
    <?php
    $flashMessages = Session::getAllFlash();
    foreach ($flashMessages as $type => $message):
    ?>
    <div class="container">
        <div class="alert alert-<?php echo e($type); ?>">
            <?php echo e($message); ?>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Main Content -->
    <main class="storefront-main">
        <?php echo $content; ?>
    </main>

    <!-- Footer -->
    <footer class="storefront-footer">
        <div class="container">
            <p><?php echo isset($tenant['footer_text']) && $tenant['footer_text'] ? e($tenant['footer_text']) : '&copy; ' . date('Y') . ' ' . e($tenant['store_name']); ?></p>
            <?php if (isset($tenant['contact_email'])): ?>
            <p>Contact: <?php echo e($tenant['contact_email']); ?></p>
            <?php endif; ?>
        </div>
    </footer>

    <script src="<?php echo asset('js/storefront.js'); ?>"></script>
</body>
</html>
