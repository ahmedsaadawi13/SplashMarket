<!-- FILE: /app/views/storefront/checkout.php -->
<div class="container">
    <h1>Checkout</h1>

    <div class="checkout-container">
        <div class="checkout-form">
            <form method="POST" action="/store/checkout?tenant_id=<?php echo $tenant['id']; ?>">
                <?php echo csrf_field(); ?>

                <div class="section">
                    <h2>Customer Information</h2>

                    <div class="form-group">
                        <label for="customer_email">Email *</label>
                        <input type="email" id="customer_email" name="customer_email" required>
                    </div>

                    <div class="form-group">
                        <label for="customer_name">Full Name *</label>
                        <input type="text" id="customer_name" name="customer_name" required>
                    </div>

                    <div class="form-group">
                        <label for="customer_phone">Phone</label>
                        <input type="tel" id="customer_phone" name="customer_phone">
                    </div>
                </div>

                <div class="section">
                    <h2>Shipping Address</h2>

                    <div class="form-group">
                        <label for="shipping_address">Street Address *</label>
                        <input type="text" id="shipping_address" name="shipping_address" required>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="city">City *</label>
                            <input type="text" id="city" name="city" required>
                        </div>

                        <div class="form-group">
                            <label for="postal_code">Postal Code *</label>
                            <input type="text" id="postal_code" name="postal_code" required>
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h2>Payment Method</h2>

                    <div class="form-group">
                        <label>
                            <input type="radio" name="payment_method" value="cod" checked> Cash on Delivery
                        </label>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="radio" name="payment_method" value="online"> Online Payment (Dummy)
                        </label>
                    </div>
                </div>

                <div class="section">
                    <h2>Coupon Code</h2>

                    <div class="form-group">
                        <input type="text" name="coupon_code" placeholder="Enter coupon code (optional)">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block">Place Order</button>
            </form>
        </div>

        <div class="order-summary">
            <h2>Order Summary</h2>

            <?php foreach ($cartItems as $item): ?>
            <div class="summary-item">
                <span><?php echo e($item['name']); ?> x <?php echo $item['quantity']; ?></span>
                <span><?php echo money($item['price'] * $item['quantity']); ?></span>
            </div>
            <?php endforeach; ?>

            <div class="summary-divider"></div>

            <div class="summary-row">
                <span>Subtotal:</span>
                <span><?php echo money($subtotal); ?></span>
            </div>

            <div class="summary-row">
                <span>Shipping:</span>
                <span><?php echo money($shippingCost); ?></span>
            </div>

            <div class="summary-divider"></div>

            <div class="summary-row total">
                <span>Total:</span>
                <span><?php echo money($total); ?></span>
            </div>
        </div>
    </div>
</div>
