<?php
// FILE: /app/models/Invoice.php

/**
 * SplashMarket - Invoice Model
 *
 * Manages subscription invoices
 * PHP 7.0+ compatible
 */

class Invoice extends Model
{
    protected $table = 'invoices';

    /**
     * Get invoices by tenant
     *
     * @param int $tenantId Tenant ID
     * @param int $limit Limit
     * @return array
     */
    public function getByTenant($tenantId, $limit = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE tenant_id = ? ORDER BY created_at DESC";

        if ($limit) {
            $sql .= " LIMIT $limit";
        }

        return $this->query($sql, [$tenantId])->fetchAll();
    }

    /**
     * Find invoice by invoice number
     *
     * @param string $invoiceNumber Invoice number
     * @return array|null
     */
    public function findByNumber($invoiceNumber)
    {
        return $this->findOne(['invoice_number' => $invoiceNumber]);
    }

    /**
     * Create invoice
     *
     * @param array $data Invoice data
     * @return int Invoice ID
     */
    public function create($data)
    {
        // Generate invoice number if not provided
        if (!isset($data['invoice_number'])) {
            $data['invoice_number'] = $this->generateInvoiceNumber();
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['status'] = isset($data['status']) ? $data['status'] : 'pending';

        return $this->insert($data);
    }

    /**
     * Generate unique invoice number
     *
     * @return string
     */
    private function generateInvoiceNumber()
    {
        // Format: INV-YYYYMMDD-XXXXX
        $prefix = 'INV-' . date('Ymd') . '-';
        $number = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $invoiceNumber = $prefix . $number;

        // Check if exists
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE invoice_number = ?";
        $result = $this->query($sql, [$invoiceNumber])->fetch();

        if ($result['count'] > 0) {
            return $this->generateInvoiceNumber();
        }

        return $invoiceNumber;
    }

    /**
     * Mark invoice as paid
     *
     * @param int $invoiceId Invoice ID
     * @return bool
     */
    public function markAsPaid($invoiceId)
    {
        return $this->update($invoiceId, [
            'status' => 'paid',
            'paid_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Mark invoice as failed
     *
     * @param int $invoiceId Invoice ID
     * @return bool
     */
    public function markAsFailed($invoiceId)
    {
        return $this->update($invoiceId, ['status' => 'failed']);
    }
}
