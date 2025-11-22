<?php
// FILE: /app/models/Order.php

/**
 * SplashMarket - Order Model
 *
 * Manages customer orders
 * PHP 7.0+ compatible
 */

class Order extends Model
{
    protected $table = 'orders';

    /**
     * Get orders by tenant
     *
     * @param int $tenantId Tenant ID
     * @param array $filters Filters
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array
     */
    public function getByTenant($tenantId, $filters = [], $page = 1, $perPage = 20)
    {
        $offset = ($page - 1) * $perPage;
        $where = ["o.tenant_id = ?"];
        $params = [$tenantId];

        // Apply filters
        if (isset($filters['status']) && $filters['status'] !== '') {
            $where[] = "o.status = ?";
            $params[] = $filters['status'];
        }

        if (isset($filters['search']) && $filters['search']) {
            $where[] = "(o.order_number LIKE ? OR c.email LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (isset($filters['date_from']) && $filters['date_from']) {
            $where[] = "DATE(o.created_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (isset($filters['date_to']) && $filters['date_to']) {
            $where[] = "DATE(o.created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        $whereClause = implode(' AND ', $where);

        // Get total count
        $countSql = "SELECT COUNT(*) as total
                     FROM {$this->table} o
                     LEFT JOIN customers c ON o.customer_id = c.id
                     WHERE $whereClause";
        $totalResult = $this->query($countSql, $params)->fetch();
        $total = $totalResult['total'];

        // Get orders with customer info
        $sql = "SELECT o.*, c.first_name, c.last_name, c.email as customer_email
                FROM {$this->table} o
                LEFT JOIN customers c ON o.customer_id = c.id
                WHERE $whereClause
                ORDER BY o.created_at DESC
                LIMIT $perPage OFFSET $offset";

        $orders = $this->query($sql, $params)->fetchAll();

        return [
            'data' => $orders,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }

    /**
     * Find order by order number
     *
     * @param string $orderNumber Order number
     * @param int $tenantId Tenant ID
     * @return array|null
     */
    public function findByOrderNumber($orderNumber, $tenantId)
    {
        $sql = "SELECT o.*, c.first_name, c.last_name, c.email as customer_email, c.phone as customer_phone
                FROM {$this->table} o
                LEFT JOIN customers c ON o.customer_id = c.id
                WHERE o.order_number = ? AND o.tenant_id = ?
                LIMIT 1";

        $result = $this->query($sql, [$orderNumber, $tenantId])->fetch();
        return $result ?: null;
    }

    /**
     * Create order
     *
     * @param array $data Order data
     * @return int Order ID
     */
    public function create($data)
    {
        // Generate order number if not provided
        if (!isset($data['order_number'])) {
            $data['order_number'] = $this->generateOrderNumber();
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['status'] = isset($data['status']) ? $data['status'] : 'pending';

        return $this->insert($data);
    }

    /**
     * Generate unique order number
     *
     * @return string
     */
    private function generateOrderNumber()
    {
        // Format: ORD-YYYYMMDD-XXXXX
        $prefix = 'ORD-' . date('Ymd') . '-';
        $number = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $orderNumber = $prefix . $number;

        // Check if exists, regenerate if needed
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE order_number = ?";
        $result = $this->query($sql, [$orderNumber])->fetch();

        if ($result['count'] > 0) {
            return $this->generateOrderNumber(); // Recursively generate new number
        }

        return $orderNumber;
    }

    /**
     * Update order status
     *
     * @param int $orderId Order ID
     * @param string $status New status
     * @param string|null $note Optional note
     * @return bool
     */
    public function updateStatus($orderId, $status, $note = null)
    {
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($note) {
            $data['notes'] = $note;
        }

        return $this->update($orderId, $data);
    }

    /**
     * Get order with items
     *
     * @param int $orderId Order ID
     * @return array|null
     */
    public function getWithItems($orderId)
    {
        $order = $this->findById($orderId);
        if (!$order) {
            return null;
        }

        // Get order items
        $sql = "SELECT * FROM order_items WHERE order_id = ? ORDER BY id ASC";
        $order['items'] = $this->query($sql, [$orderId])->fetchAll();

        return $order;
    }

    /**
     * Get orders by customer
     *
     * @param int $customerId Customer ID
     * @param int $limit Limit
     * @return array
     */
    public function getByCustomer($customerId, $limit = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE customer_id = ? ORDER BY created_at DESC";

        if ($limit) {
            $sql .= " LIMIT $limit";
        }

        return $this->query($sql, [$customerId])->fetchAll();
    }

    /**
     * Get revenue for tenant
     *
     * @param int $tenantId Tenant ID
     * @param string|null $dateFrom Date from
     * @param string|null $dateTo Date to
     * @return float
     */
    public function getRevenue($tenantId, $dateFrom = null, $dateTo = null)
    {
        $sql = "SELECT SUM(total_amount) as revenue
                FROM {$this->table}
                WHERE tenant_id = ? AND status IN ('completed', 'processing', 'shipped')";
        $params = [$tenantId];

        if ($dateFrom) {
            $sql .= " AND DATE(created_at) >= ?";
            $params[] = $dateFrom;
        }

        if ($dateTo) {
            $sql .= " AND DATE(created_at) <= ?";
            $params[] = $dateTo;
        }

        $result = $this->query($sql, $params)->fetch();
        return $result['revenue'] ?: 0;
    }

    /**
     * Get daily sales for period
     *
     * @param int $tenantId Tenant ID
     * @param int $days Number of days
     * @return array
     */
    public function getDailySales($tenantId, $days = 30)
    {
        $sql = "SELECT DATE(created_at) as date, COUNT(*) as order_count, SUM(total_amount) as revenue
                FROM {$this->table}
                WHERE tenant_id = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                GROUP BY DATE(created_at)
                ORDER BY date DESC";

        return $this->query($sql, [$tenantId, $days])->fetchAll();
    }

    /**
     * Get order count by status
     *
     * @param int $tenantId Tenant ID
     * @return array
     */
    public function getStatusCounts($tenantId)
    {
        $sql = "SELECT status, COUNT(*) as count
                FROM {$this->table}
                WHERE tenant_id = ?
                GROUP BY status";

        $results = $this->query($sql, [$tenantId])->fetchAll();

        $counts = [];
        foreach ($results as $row) {
            $counts[$row['status']] = $row['count'];
        }

        return $counts;
    }

    /**
     * Get monthly order count for tenant
     *
     * @param int $tenantId Tenant ID
     * @param string $month Month (YYYY-MM format)
     * @return int
     */
    public function getMonthlyCount($tenantId, $month)
    {
        $sql = "SELECT COUNT(*) as count
                FROM {$this->table}
                WHERE tenant_id = ? AND DATE_FORMAT(created_at, '%Y-%m') = ?";

        $result = $this->query($sql, [$tenantId, $month])->fetch();
        return $result['count'];
    }
}
