<!-- FILE: /app/views/storefront/category.php -->
<div class="container">
    <h1><?php echo e($category['name']); ?></h1>
    <?php if ($category['description']): ?>
    <p><?php echo e($category['description']); ?></p>
    <?php endif; ?>

    <?php if (!empty($products)): ?>
    <div class="product-grid">
        <?php foreach ($products as $product): ?>
        <div class="product-card">
            <?php if ($product['image']): ?>
            <img src="<?php echo uploadUrl($product['image']); ?>" alt="<?php echo e($product['name']); ?>">
            <?php endif; ?>
            <h3><?php echo e($product['name']); ?></h3>
            <div class="product-price">
                <span class="price"><?php echo money($product['price']); ?></span>
            </div>
            <a href="/store/product/<?php echo e($product['slug']); ?>?tenant_id=<?php echo $tenant['id']; ?>" class="btn btn-primary btn-block">View Details</a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p>No products in this category.</p>
    <?php endif; ?>
</div>
