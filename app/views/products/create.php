<!-- FILE: /app/views/products/create.php -->
<div class="page-header">
    <h1>Add New Product</h1>
    <a href="/products" class="btn">Back to Products</a>
</div>

<form method="POST" action="/products/create" enctype="multipart/form-data" class="form">
    <?php echo csrf_field(); ?>

    <div class="form-grid">
        <div class="form-group">
            <label for="name">Product Name *</label>
            <input type="text" id="name" name="name" required value="<?php echo old('name'); ?>">
        </div>

        <div class="form-group">
            <label for="sku">SKU *</label>
            <input type="text" id="sku" name="sku" required value="<?php echo old('sku'); ?>">
        </div>

        <div class="form-group">
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id">
                <option value="">No Category</option>
                <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category['id']; ?>"><?php echo e($category['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="price">Price *</label>
            <input type="number" id="price" name="price" step="0.01" required value="<?php echo old('price'); ?>">
        </div>

        <div class="form-group">
            <label for="compare_at_price">Compare at Price</label>
            <input type="number" id="compare_at_price" name="compare_at_price" step="0.01" value="<?php echo old('compare_at_price'); ?>">
        </div>

        <div class="form-group">
            <label for="stock_quantity">Stock Quantity *</label>
            <input type="number" id="stock_quantity" name="stock_quantity" required value="<?php echo old('stock_quantity', 0); ?>">
        </div>
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="5"><?php echo old('description'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="image">Product Image</label>
        <input type="file" id="image" name="image" accept="image/*">
    </div>

    <div class="form-group">
        <label for="status">Status</label>
        <select id="status" name="status">
            <option value="draft">Draft</option>
            <option value="published">Published</option>
        </select>
    </div>

    <div class="form-group">
        <label>
            <input type="checkbox" name="is_featured" value="1"> Featured Product
        </label>
    </div>

    <div class="form-group">
        <label>
            <input type="checkbox" name="is_on_sale" value="1"> On Sale
        </label>
    </div>

    <button type="submit" class="btn btn-primary">Create Product</button>
</form>
