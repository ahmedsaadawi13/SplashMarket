<?php
// FILE: /app/models/Tenant.php

/**
 * SplashMarket - Tenant Model
 *
 * Manages tenant/store data for multi-tenant SaaS
 * PHP 7.0+ compatible
 */

class Tenant extends Model
{
    protected $table = 'tenants';

    /**
     * Create new tenant
     *
     * @param array $data Tenant data
     * @return int Tenant ID
     */
    public function create($data)
    {
        // Set defaults
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['is_active'] = isset($data['is_active']) ? $data['is_active'] : 1;

        // Generate API key if not provided
        if (!isset($data['api_key'])) {
            $data['api_key'] = $this->generateApiKey();
        }

        return $this->insert($data);
    }

    /**
     * Generate unique API key
     *
     * @return string
     */
    private function generateApiKey()
    {
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes(32));
        } else {
            return bin2hex(openssl_random_pseudo_bytes(32));
        }
    }

    /**
     * Get tenant by subdomain
     *
     * @param string $subdomain Subdomain
     * @return array|null
     */
    public function findBySubdomain($subdomain)
    {
        return $this->findOne(['subdomain' => $subdomain]);
    }

    /**
     * Get tenant by API key
     *
     * @param string $apiKey API key
     * @return array|null
     */
    public function findByApiKey($apiKey)
    {
        return $this->findOne(['api_key' => $apiKey]);
    }

    /**
     * Get active tenants
     *
     * @return array
     */
    public function getActive()
    {
        return $this->findAll(['is_active' => 1], 'created_at DESC');
    }

    /**
     * Get all tenants with pagination
     *
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array
     */
    public function paginate($page = 1, $perPage = 20)
    {
        $offset = ($page - 1) * $perPage;
        $tenants = $this->findAll([], 'created_at DESC', $perPage, $offset);
        $total = $this->count();

        return [
            'data' => $tenants,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }

    /**
     * Update tenant settings
     *
     * @param int $tenantId Tenant ID
     * @param array $settings Settings data
     * @return bool
     */
    public function updateSettings($tenantId, $settings)
    {
        $allowedFields = [
            'store_name', 'store_description', 'logo', 'banner_image',
            'theme_color', 'contact_email', 'contact_phone', 'address',
            'city', 'state', 'country', 'postal_code', 'currency',
            'meta_title', 'meta_description', 'footer_text', 'subdomain'
        ];

        $updateData = [];
        foreach ($settings as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $updateData[$key] = $value;
            }
        }

        if (!empty($updateData)) {
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            return $this->update($tenantId, $updateData);
        }

        return false;
    }

    /**
     * Regenerate API key for tenant
     *
     * @param int $tenantId Tenant ID
     * @return string New API key
     */
    public function regenerateApiKey($tenantId)
    {
        $newKey = $this->generateApiKey();
        $this->update($tenantId, ['api_key' => $newKey]);
        return $newKey;
    }

    /**
     * Check if subdomain exists
     *
     * @param string $subdomain Subdomain to check
     * @param int|null $excludeTenantId Tenant ID to exclude
     * @return bool
     */
    public function subdomainExists($subdomain, $excludeTenantId = null)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE subdomain = ?";
        $params = [$subdomain];

        if ($excludeTenantId) {
            $sql .= " AND id != ?";
            $params[] = $excludeTenantId;
        }

        $result = $this->query($sql, $params)->fetch();
        return $result['count'] > 0;
    }

    /**
     * Activate tenant
     *
     * @param int $tenantId Tenant ID
     * @return bool
     */
    public function activate($tenantId)
    {
        return $this->update($tenantId, ['is_active' => 1]);
    }

    /**
     * Deactivate tenant
     *
     * @param int $tenantId Tenant ID
     * @return bool
     */
    public function deactivate($tenantId)
    {
        return $this->update($tenantId, ['is_active' => 0]);
    }

    /**
     * Get tenant statistics
     *
     * @param int $tenantId Tenant ID
     * @return array
     */
    public function getStats($tenantId)
    {
        $stats = [];

        // Product count
        $sql = "SELECT COUNT(*) as count FROM products WHERE tenant_id = ?";
        $result = $this->query($sql, [$tenantId])->fetch();
        $stats['products'] = $result['count'];

        // Order count
        $sql = "SELECT COUNT(*) as count FROM orders WHERE tenant_id = ?";
        $result = $this->query($sql, [$tenantId])->fetch();
        $stats['orders'] = $result['count'];

        // Customer count
        $sql = "SELECT COUNT(*) as count FROM customers WHERE tenant_id = ?";
        $result = $this->query($sql, [$tenantId])->fetch();
        $stats['customers'] = $result['count'];

        // Total revenue
        $sql = "SELECT SUM(total_amount) as revenue FROM orders WHERE tenant_id = ? AND status IN ('completed', 'processing', 'shipped')";
        $result = $this->query($sql, [$tenantId])->fetch();
        $stats['revenue'] = $result['revenue'] ?: 0;

        return $stats;
    }
}
