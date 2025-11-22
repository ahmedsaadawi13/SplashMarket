<?php
// FILE: /app/models/User.php

/**
 * SplashMarket - User Model
 *
 * Manages user accounts and authentication
 * PHP 7.0+ compatible
 */

class User extends Model
{
    protected $table = 'users';

    /**
     * Find user by email
     *
     * @param string $email User email
     * @return array|null
     */
    public function findByEmail($email)
    {
        return $this->findOne(['email' => $email]);
    }

    /**
     * Create new user
     *
     * @param array $data User data
     * @return int User ID
     */
    public function create($data)
    {
        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        // Set defaults
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['is_active'] = isset($data['is_active']) ? $data['is_active'] : 1;

        return $this->insert($data);
    }

    /**
     * Update user password
     *
     * @param int $userId User ID
     * @param string $newPassword New password
     * @return bool
     */
    public function updatePassword($userId, $newPassword)
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($userId, ['password' => $hashedPassword]);
    }

    /**
     * Update last login timestamp
     *
     * @param int $userId User ID
     * @return bool
     */
    public function updateLastLogin($userId)
    {
        return $this->update($userId, ['last_login' => date('Y-m-d H:i:s')]);
    }

    /**
     * Get users by tenant ID
     *
     * @param int $tenantId Tenant ID
     * @return array
     */
    public function getByTenant($tenantId)
    {
        return $this->findAll(['tenant_id' => $tenantId], 'created_at DESC');
    }

    /**
     * Get users by role
     *
     * @param string $role User role
     * @param int|null $tenantId Optional tenant ID filter
     * @return array
     */
    public function getByRole($role, $tenantId = null)
    {
        $conditions = ['role' => $role];
        if ($tenantId !== null) {
            $conditions['tenant_id'] = $tenantId;
        }
        return $this->findAll($conditions, 'created_at DESC');
    }

    /**
     * Check if email exists
     *
     * @param string $email Email to check
     * @param int|null $excludeUserId User ID to exclude (for updates)
     * @return bool
     */
    public function emailExists($email, $excludeUserId = null)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = ?";
        $params = [$email];

        if ($excludeUserId) {
            $sql .= " AND id != ?";
            $params[] = $excludeUserId;
        }

        $result = $this->query($sql, $params)->fetch();
        return $result['count'] > 0;
    }

    /**
     * Activate user
     *
     * @param int $userId User ID
     * @return bool
     */
    public function activate($userId)
    {
        return $this->update($userId, ['is_active' => 1]);
    }

    /**
     * Deactivate user
     *
     * @param int $userId User ID
     * @return bool
     */
    public function deactivate($userId)
    {
        return $this->update($userId, ['is_active' => 0]);
    }
}
