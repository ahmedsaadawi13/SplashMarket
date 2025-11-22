<?php
// FILE: /app/models/Payment.php

/**
 * SplashMarket - Payment Model
 *
 * Manages payment records
 * PHP 7.0+ compatible
 */

class Payment extends Model
{
    protected $table = 'payments';

    /**
     * Get payments by tenant
     *
     * @param int $tenantId Tenant ID
     * @param int $limit Limit
     * @return array
     */
    public function getByTenant($tenantId, $limit = null)
    {
        $sql = "SELECT p.*, i.invoice_number
                FROM {$this->table} p
                INNER JOIN invoices i ON p.invoice_id = i.id
                WHERE p.tenant_id = ?
                ORDER BY p.created_at DESC";

        if ($limit) {
            $sql .= " LIMIT $limit";
        }

        return $this->query($sql, [$tenantId])->fetchAll();
    }

    /**
     * Get payments by invoice
     *
     * @param int $invoiceId Invoice ID
     * @return array
     */
    public function getByInvoice($invoiceId)
    {
        return $this->findAll(['invoice_id' => $invoiceId], 'created_at DESC');
    }

    /**
     * Create payment
     *
     * @param array $data Payment data
     * @return int Payment ID
     */
    public function create($data)
    {
        // Generate transaction ID if not provided
        if (!isset($data['transaction_id'])) {
            $data['transaction_id'] = $this->generateTransactionId();
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['status'] = isset($data['status']) ? $data['status'] : 'pending';

        return $this->insert($data);
    }

    /**
     * Generate unique transaction ID
     *
     * @return string
     */
    private function generateTransactionId()
    {
        if (function_exists('random_bytes')) {
            return 'TXN-' . strtoupper(bin2hex(random_bytes(16)));
        } else {
            return 'TXN-' . strtoupper(bin2hex(openssl_random_pseudo_bytes(16)));
        }
    }

    /**
     * Mark payment as completed
     *
     * @param int $paymentId Payment ID
     * @return bool
     */
    public function markAsCompleted($paymentId)
    {
        return $this->update($paymentId, ['status' => 'completed']);
    }

    /**
     * Mark payment as failed
     *
     * @param int $paymentId Payment ID
     * @param string|null $reason Failure reason
     * @return bool
     */
    public function markAsFailed($paymentId, $reason = null)
    {
        $data = ['status' => 'failed'];
        if ($reason) {
            $data['notes'] = $reason;
        }
        return $this->update($paymentId, $data);
    }
}
