<!-- FILE: /app/views/storefront/home.php -->
<div class="container">
    <!-- Hero Section -->
    <?php if (!empty($tenant['store_description'])): ?>
    <div class="hero">
        <h1><?php echo e($tenant['store_name']); ?></h1>
        <p><?php echo e($tenant['store_description']); ?></p>
    </div>
    <?php endif; ?>

    <!-- Featured Products -->
    <?php if (!empty($featuredProducts)): ?>
    <section class="section">
        <h2>Featured Products</h2>
        <div class="product-grid">
            <?php foreach ($featuredProducts as $product): ?>
            <div class="product-card">
                <?php if ($product['image']): ?>
                <img src="<?php echo uploadUrl($product['image']); ?>" alt="<?php echo e($product['name']); ?>">
                <?php endif; ?>

                <h3><?php echo e($product['name']); ?></h3>

                <div class="product-price">
                    <?php if ($product['compare_at_price'] && $product['compare_at_price'] > $product['price']): ?>
                    <span class="old-price"><?php echo money($product['compare_at_price']); ?></span>
                    <?php endif; ?>
                    <span class="price"><?php echo money($product['price']); ?></span>
                </div>

                <?php if ($product['stock_quantity'] > 0): ?>
                <a href="/store/product/<?php echo e($product['slug']); ?>?tenant_id=<?php echo $tenant['id']; ?>" class="btn btn-primary btn-block">View Details</a>
                <?php else: ?>
                <span class="out-of-stock">Out of Stock</span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Categories -->
    <?php if (!empty($categories)): ?>
    <section class="section">
        <h2>Shop by Category</h2>
        <div class="category-grid">
            <?php foreach ($categories as $category): ?>
            <a href="/store/category/<?php echo e($category['slug']); ?>?tenant_id=<?php echo $tenant['id']; ?>" class="category-card">
                <?php if ($category['image']): ?>
                <img src="<?php echo uploadUrl($category['image']); ?>" alt="<?php echo e($category['name']); ?>">
                <?php endif; ?>
                <h3><?php echo e($category['name']); ?></h3>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Products on Sale -->
    <?php if (!empty($saleProducts)): ?>
    <section class="section">
        <h2>On Sale</h2>
        <div class="product-grid">
            <?php foreach ($saleProducts as $product): ?>
            <div class="product-card sale">
                <?php if ($product['image']): ?>
                <img src="<?php echo uploadUrl($product['image']); ?>" alt="<?php echo e($product['name']); ?>">
                <?php endif; ?>

                <h3><?php echo e($product['name']); ?></h3>

                <div class="product-price">
                    <?php if ($product['compare_at_price']): ?>
                    <span class="old-price"><?php echo money($product['compare_at_price']); ?></span>
                    <?php endif; ?>
                    <span class="price"><?php echo money($product['price']); ?></span>
                </div>

                <?php if ($product['stock_quantity'] > 0): ?>
                <a href="/store/product/<?php echo e($product['slug']); ?>?tenant_id=<?php echo $tenant['id']; ?>" class="btn btn-primary btn-block">View Details</a>
                <?php else: ?>
                <span class="out-of-stock">Out of Stock</span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>
