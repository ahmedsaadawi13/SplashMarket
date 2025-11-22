<!-- FILE: /app/views/storefront/cart.php -->
<div class="container">
    <h1>Shopping Cart</h1>

    <?php if (!empty($cartItems)): ?>
    <div class="cart-content">
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cartItems as $item): ?>
                <tr>
                    <td>
                        <div class="cart-product">
                            <?php if ($item['image']): ?>
                            <img src="<?php echo uploadUrl($item['image']); ?>" alt="<?php echo e($item['name']); ?>">
                            <?php endif; ?>
                            <div>
                                <h4><?php echo e($item['name']); ?></h4>
                                <p class="sku"><?php echo e($item['sku']); ?></p>
                            </div>
                        </div>
                    </td>
                    <td><?php echo money($item['price']); ?></td>
                    <td>
                        <form method="POST" action="/store/cart/update?tenant_id=<?php echo $tenant['id']; ?>" class="quantity-form">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="cart_item_id" value="<?php echo $item['id']; ?>">
                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock_quantity']; ?>">
                            <button type="submit" class="btn btn-sm">Update</button>
                        </form>
                    </td>
                    <td><?php echo money($item['price'] * $item['quantity']); ?></td>
                    <td>
                        <form method="POST" action="/store/cart/remove?tenant_id=<?php echo $tenant['id']; ?>" onsubmit="return confirm('Remove this item?');">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="cart_item_id" value="<?php echo $item['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="cart-summary">
            <h3>Cart Summary</h3>
            <div class="summary-row">
                <span>Subtotal:</span>
                <span><?php echo money($subtotal); ?></span>
            </div>

            <a href="/store/checkout?tenant_id=<?php echo $tenant['id']; ?>" class="btn btn-primary btn-lg btn-block">Proceed to Checkout</a>
            <a href="/store/?tenant_id=<?php echo $tenant['id']; ?>" class="btn btn-secondary btn-block">Continue Shopping</a>
        </div>
    </div>
    <?php else: ?>
    <div class="empty-cart">
        <p>Your cart is empty.</p>
        <a href="/store/?tenant_id=<?php echo $tenant['id']; ?>" class="btn btn-primary">Start Shopping</a>
    </div>
    <?php endif; ?>
</div>
