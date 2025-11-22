<?php
// FILE: /app/models/Customer.php

/**
 * SplashMarket - Customer Model
 *
 * Manages customer accounts
 * PHP 7.0+ compatible
 */

class Customer extends Model
{
    protected $table = 'customers';

    /**
     * Get customers by tenant
     *
     * @param int $tenantId Tenant ID
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array
     */
    public function getByTenant($tenantId, $page = 1, $perPage = 20)
    {
        $offset = ($page - 1) * $perPage;
        $customers = $this->findAll(['tenant_id' => $tenantId], 'created_at DESC', $perPage, $offset);
        $total = $this->count(['tenant_id' => $tenantId]);

        return [
            'data' => $customers,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }

    /**
     * Find customer by email
     *
     * @param string $email Customer email
     * @param int $tenantId Tenant ID
     * @return array|null
     */
    public function findByEmail($email, $tenantId)
    {
        return $this->findOne(['email' => $email, 'tenant_id' => $tenantId]);
    }

    /**
     * Create customer
     *
     * @param array $data Customer data
     * @return int Customer ID
     */
    public function create($data)
    {
        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->insert($data);
    }

    /**
     * Update customer
     *
     * @param int $customerId Customer ID
     * @param array $data Customer data
     * @return bool
     */
    public function updateCustomer($customerId, $data)
    {
        // Hash password if being updated
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($customerId, $data);
    }

    /**
     * Verify customer password
     *
     * @param string $email Customer email
     * @param string $password Password
     * @param int $tenantId Tenant ID
     * @return array|null Customer data or null
     */
    public function verifyCredentials($email, $password, $tenantId)
    {
        $customer = $this->findByEmail($email, $tenantId);

        if (!$customer || !isset($customer['password'])) {
            return null;
        }

        if (password_verify($password, $customer['password'])) {
            unset($customer['password']); // Remove password from returned data
            return $customer;
        }

        return null;
    }

    /**
     * Check if email exists for tenant
     *
     * @param string $email Email to check
     * @param int $tenantId Tenant ID
     * @param int|null $excludeId Customer ID to exclude
     * @return bool
     */
    public function emailExists($email, $tenantId, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = ? AND tenant_id = ?";
        $params = [$email, $tenantId];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = $this->query($sql, $params)->fetch();
        return $result['count'] > 0;
    }

    /**
     * Search customers
     *
     * @param int $tenantId Tenant ID
     * @param string $search Search term
     * @return array
     */
    public function search($tenantId, $search)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE tenant_id = ? AND (
                    first_name LIKE ? OR
                    last_name LIKE ? OR
                    email LIKE ? OR
                    phone LIKE ?
                )
                ORDER BY created_at DESC
                LIMIT 50";

        $searchTerm = '%' . $search . '%';
        return $this->query($sql, [$tenantId, $searchTerm, $searchTerm, $searchTerm, $searchTerm])->fetchAll();
    }

    /**
     * Get customer with order stats
     *
     * @param int $customerId Customer ID
     * @return array|null
     */
    public function getWithStats($customerId)
    {
        $customer = $this->findById($customerId);
        if (!$customer) {
            return null;
        }

        // Get order count
        $sql = "SELECT COUNT(*) as order_count, SUM(total_amount) as total_spent
                FROM orders
                WHERE customer_id = ?";
        $result = $this->query($sql, [$customerId])->fetch();

        $customer['order_count'] = $result['order_count'];
        $customer['total_spent'] = $result['total_spent'] ?: 0;

        return $customer;
    }
}
