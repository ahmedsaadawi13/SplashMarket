<!-- FILE: /app/views/products/index.php -->
<div class="page-header">
    <h1>Products</h1>
    <a href="/products/create" class="btn btn-primary">Add Product</a>
</div>

<!-- Filters -->
<div class="filters">
    <form method="GET" action="/products" class="filter-form">
        <input type="text" name="search" placeholder="Search products..." value="<?php echo e($filters['search'] ?? ''); ?>">

        <select name="status">
            <option value="">All Status</option>
            <option value="draft" <?php echo selected($filters['status'] ?? '', 'draft'); ?>>Draft</option>
            <option value="published" <?php echo selected($filters['status'] ?? '', 'published'); ?>>Published</option>
        </select>

        <select name="category_id">
            <option value="">All Categories</option>
            <?php foreach ($categories as $category): ?>
            <option value="<?php echo $category['id']; ?>" <?php echo selected($filters['category_id'] ?? '', $category['id']); ?>>
                <?php echo e($category['name']); ?>
            </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn">Filter</button>
    </form>
</div>

<!-- Products Table -->
<?php if (!empty($products)): ?>
<table class="data-table">
    <thead>
        <tr>
            <th>Image</th>
            <th>Name</th>
            <th>SKU</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Category</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($products as $product): ?>
        <tr>
            <td>
                <?php if ($product['image']): ?>
                <img src="<?php echo uploadUrl($product['image']); ?>" alt="<?php echo e($product['name']); ?>" class="product-thumb">
                <?php endif; ?>
            </td>
            <td><?php echo e($product['name']); ?></td>
            <td><?php echo e($product['sku']); ?></td>
            <td><?php echo money($product['price']); ?></td>
            <td><?php echo $product['stock_quantity']; ?></td>
            <td><?php echo e($product['category_name'] ?? '-'); ?></td>
            <td><?php echo statusBadge($product['status']); ?></td>
            <td>
                <a href="/products/<?php echo $product['id']; ?>/edit" class="btn btn-sm">Edit</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php echo paginate($pagination, '/products'); ?>

<?php else: ?>
<p>No products found.</p>
<?php endif; ?>
