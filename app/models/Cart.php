<?php
// FILE: /app/models/Cart.php

/**
 * SplashMarket - Shopping Cart Model
 *
 * Manages shopping cart functionality
 * PHP 7.0+ compatible
 */

class Cart extends Model
{
    protected $table = 'cart_items';

    /**
     * Get cart items for session
     *
     * @param string $sessionId Session ID
     * @param int $tenantId Tenant ID
     * @return array
     */
    public function getItems($sessionId, $tenantId)
    {
        $sql = "SELECT ci.*, p.name, p.price, p.image, p.stock_quantity, p.status
                FROM {$this->table} ci
                INNER JOIN products p ON ci.product_id = p.id
                WHERE ci.session_id = ? AND ci.tenant_id = ?
                ORDER BY ci.created_at DESC";

        return $this->query($sql, [$sessionId, $tenantId])->fetchAll();
    }

    /**
     * Add item to cart
     *
     * @param string $sessionId Session ID
     * @param int $tenantId Tenant ID
     * @param int $productId Product ID
     * @param int $quantity Quantity
     * @return int Cart item ID
     */
    public function addItem($sessionId, $tenantId, $productId, $quantity = 1)
    {
        // Check if item already exists in cart
        $existing = $this->findOne([
            'session_id' => $sessionId,
            'tenant_id' => $tenantId,
            'product_id' => $productId
        ]);

        if ($existing) {
            // Update quantity
            $newQuantity = $existing['quantity'] + $quantity;
            $this->update($existing['id'], ['quantity' => $newQuantity]);
            return $existing['id'];
        } else {
            // Add new item
            return $this->insert([
                'session_id' => $sessionId,
                'tenant_id' => $tenantId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Update cart item quantity
     *
     * @param int $cartItemId Cart item ID
     * @param int $quantity New quantity
     * @return bool
     */
    public function updateQuantity($cartItemId, $quantity)
    {
        if ($quantity <= 0) {
            return $this->delete($cartItemId);
        }

        return $this->update($cartItemId, ['quantity' => $quantity]);
    }

    /**
     * Remove item from cart
     *
     * @param int $cartItemId Cart item ID
     * @return bool
     */
    public function removeItem($cartItemId)
    {
        return $this->delete($cartItemId);
    }

    /**
     * Clear cart
     *
     * @param string $sessionId Session ID
     * @param int $tenantId Tenant ID
     * @return bool
     */
    public function clearCart($sessionId, $tenantId)
    {
        $sql = "DELETE FROM {$this->table} WHERE session_id = ? AND tenant_id = ?";
        $stmt = $this->query($sql, [$sessionId, $tenantId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Get cart total
     *
     * @param string $sessionId Session ID
     * @param int $tenantId Tenant ID
     * @return array
     */
    public function getCartTotal($sessionId, $tenantId)
    {
        $items = $this->getItems($sessionId, $tenantId);

        $subtotal = 0;
        $itemCount = 0;

        foreach ($items as $item) {
            $subtotal += $item['price'] * $item['quantity'];
            $itemCount += $item['quantity'];
        }

        return [
            'subtotal' => $subtotal,
            'itemCount' => $itemCount,
            'items' => $items
        ];
    }

    /**
     * Get cart item count
     *
     * @param string $sessionId Session ID
     * @param int $tenantId Tenant ID
     * @return int
     */
    public function getItemCount($sessionId, $tenantId)
    {
        $sql = "SELECT SUM(quantity) as total FROM {$this->table} WHERE session_id = ? AND tenant_id = ?";
        $result = $this->query($sql, [$sessionId, $tenantId])->fetch();
        return $result['total'] ?: 0;
    }

    /**
     * Clean old cart items (older than 7 days)
     */
    public function cleanOldCarts()
    {
        $sql = "DELETE FROM {$this->table} WHERE created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)";
        return $this->query($sql);
    }
}
