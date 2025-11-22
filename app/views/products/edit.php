<!-- FILE: /app/views/products/edit.php -->
<div class="page-header">
    <h1>Edit Product</h1>
    <a href="/products" class="btn">Back to Products</a>
</div>

<form method="POST" action="/products/<?php echo $product['id']; ?>/edit" enctype="multipart/form-data" class="form">
    <?php echo csrf_field(); ?>

    <div class="form-grid">
        <div class="form-group">
            <label for="name">Product Name *</label>
            <input type="text" id="name" name="name" required value="<?php echo e($product['name']); ?>">
        </div>

        <div class="form-group">
            <label for="sku">SKU *</label>
            <input type="text" id="sku" name="sku" required value="<?php echo e($product['sku']); ?>">
        </div>

        <div class="form-group">
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id">
                <option value="">No Category</option>
                <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category['id']; ?>" <?php echo selected($product['category_id'], $category['id']); ?>>
                    <?php echo e($category['name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="price">Price *</label>
            <input type="number" id="price" name="price" step="0.01" required value="<?php echo $product['price']; ?>">
        </div>

        <div class="form-group">
            <label for="compare_at_price">Compare at Price</label>
            <input type="number" id="compare_at_price" name="compare_at_price" step="0.01" value="<?php echo $product['compare_at_price']; ?>">
        </div>

        <div class="form-group">
            <label for="stock_quantity">Stock Quantity *</label>
            <input type="number" id="stock_quantity" name="stock_quantity" required value="<?php echo $product['stock_quantity']; ?>">
        </div>
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="5"><?php echo e($product['description']); ?></textarea>
    </div>

    <?php if ($product['image']): ?>
    <div class="form-group">
        <label>Current Image</label>
        <img src="<?php echo uploadUrl($product['image']); ?>" alt="<?php echo e($product['name']); ?>" style="max-width: 200px;">
    </div>
    <?php endif; ?>

    <div class="form-group">
        <label for="image">Upload New Image</label>
        <input type="file" id="image" name="image" accept="image/*">
    </div>

    <div class="form-group">
        <label for="status">Status</label>
        <select id="status" name="status">
            <option value="draft" <?php echo selected($product['status'], 'draft'); ?>>Draft</option>
            <option value="published" <?php echo selected($product['status'], 'published'); ?>>Published</option>
        </select>
    </div>

    <div class="form-group">
        <label>
            <input type="checkbox" name="is_featured" value="1" <?php echo checked($product['is_featured'], 1); ?>> Featured Product
        </label>
    </div>

    <div class="form-group">
        <label>
            <input type="checkbox" name="is_on_sale" value="1" <?php echo checked($product['is_on_sale'], 1); ?>> On Sale
        </label>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Update Product</button>

        <form method="POST" action="/products/<?php echo $product['id']; ?>/delete" style="display:inline;" onsubmit="return confirm('Are you sure?');">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-danger">Delete Product</button>
        </form>
    </div>
</form>
