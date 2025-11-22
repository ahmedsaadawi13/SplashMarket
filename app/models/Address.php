<?php
// FILE: /app/models/Address.php

/**
 * SplashMarket - Address Model
 *
 * Manages customer addresses
 * PHP 7.0+ compatible
 */

class Address extends Model
{
    protected $table = 'addresses';

    /**
     * Get addresses for customer
     *
     * @param int $customerId Customer ID
     * @return array
     */
    public function getByCustomer($customerId)
    {
        return $this->findAll(['customer_id' => $customerId], 'is_default DESC, created_at DESC');
    }

    /**
     * Get default address for customer
     *
     * @param int $customerId Customer ID
     * @return array|null
     */
    public function getDefault($customerId)
    {
        return $this->findOne(['customer_id' => $customerId, 'is_default' => 1]);
    }

    /**
     * Create address
     *
     * @param array $data Address data
     * @return int Address ID
     */
    public function create($data)
    {
        // If this is marked as default, unset other defaults for this customer
        if (isset($data['is_default']) && $data['is_default']) {
            $this->unsetDefaults($data['customer_id']);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->insert($data);
    }

    /**
     * Update address
     *
     * @param int $addressId Address ID
     * @param array $data Address data
     * @return bool
     */
    public function updateAddress($addressId, $data)
    {
        // If this is being set as default, unset other defaults
        if (isset($data['is_default']) && $data['is_default']) {
            $address = $this->findById($addressId);
            if ($address) {
                $this->unsetDefaults($address['customer_id']);
            }
        }

        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($addressId, $data);
    }

    /**
     * Set address as default
     *
     * @param int $addressId Address ID
     * @return bool
     */
    public function setAsDefault($addressId)
    {
        $address = $this->findById($addressId);
        if (!$address) {
            return false;
        }

        // Unset other defaults for this customer
        $this->unsetDefaults($address['customer_id']);

        // Set this as default
        return $this->update($addressId, ['is_default' => 1]);
    }

    /**
     * Unset all default addresses for customer
     *
     * @param int $customerId Customer ID
     */
    private function unsetDefaults($customerId)
    {
        $sql = "UPDATE {$this->table} SET is_default = 0 WHERE customer_id = ?";
        $this->query($sql, [$customerId]);
    }
}
