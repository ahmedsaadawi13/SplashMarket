<!-- FILE: /app/views/storefront/product.php -->
<div class="container">
    <div class="product-detail">
        <div class="product-images">
            <?php if ($product['image']): ?>
            <img src="<?php echo uploadUrl($product['image']); ?>" alt="<?php echo e($product['name']); ?>" class="main-image">
            <?php endif; ?>
        </div>

        <div class="product-info">
            <h1><?php echo e($product['name']); ?></h1>

            <div class="product-price">
                <?php if ($product['compare_at_price'] && $product['compare_at_price'] > $product['price']): ?>
                <span class="old-price"><?php echo money($product['compare_at_price']); ?></span>
                <span class="discount">Save <?php echo percentage($product['compare_at_price'] - $product['price'], $product['compare_at_price']); ?>%</span>
                <?php endif; ?>
                <span class="price"><?php echo money($product['price']); ?></span>
            </div>

            <?php if ($product['description']): ?>
            <div class="product-description">
                <h3>Description</h3>
                <p><?php echo nl2br(e($product['description'])); ?></p>
            </div>
            <?php endif; ?>

            <div class="product-meta">
                <p><strong>SKU:</strong> <?php echo e($product['sku']); ?></p>
                <p>
                    <strong>Availability:</strong>
                    <?php if ($product['stock_quantity'] > 0): ?>
                        <span class="in-stock">In Stock (<?php echo $product['stock_quantity']; ?> available)</span>
                    <?php else: ?>
                        <span class="out-of-stock">Out of Stock</span>
                    <?php endif; ?>
                </p>
            </div>

            <?php if ($product['stock_quantity'] > 0): ?>
            <form action="/store/cart/add?tenant_id=<?php echo $tenant['id']; ?>" method="POST" class="add-to-cart-form">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">

                <div class="quantity-selector">
                    <label for="quantity">Quantity:</label>
                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" required>
                </div>

                <button type="submit" class="btn btn-primary btn-lg">Add to Cart</button>
            </form>
            <?php else: ?>
            <button class="btn btn-disabled btn-lg" disabled>Out of Stock</button>
            <?php endif; ?>
        </div>
    </div>
</div>
