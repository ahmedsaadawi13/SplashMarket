<?php
// FILE: /app/core/Validator.php

/**
 * SplashMarket - Input Validator
 *
 * Provides validation methods for user input
 * PHP 7.0+ compatible
 */

class Validator
{
    private $errors = [];
    private $data = [];

    /**
     * Initialize validator with data
     *
     * @param array $data Data to validate
     */
    public function __construct($data = [])
    {
        $this->data = $data;
    }

    /**
     * Validate required field
     *
     * @param string $field Field name
     * @param string $message Error message
     * @return self
     */
    public function required($field, $message = null)
    {
        if (!isset($this->data[$field]) || trim($this->data[$field]) === '') {
            $this->errors[$field] = $message ?: "$field is required";
        }
        return $this;
    }

    /**
     * Validate email format
     *
     * @param string $field Field name
     * @param string $message Error message
     * @return self
     */
    public function email($field, $message = null)
    {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message ?: "$field must be a valid email";
        }
        return $this;
    }

    /**
     * Validate minimum length
     *
     * @param string $field Field name
     * @param int $length Minimum length
     * @param string $message Error message
     * @return self
     */
    public function min($field, $length, $message = null)
    {
        if (isset($this->data[$field]) && strlen($this->data[$field]) < $length) {
            $this->errors[$field] = $message ?: "$field must be at least $length characters";
        }
        return $this;
    }

    /**
     * Validate maximum length
     *
     * @param string $field Field name
     * @param int $length Maximum length
     * @param string $message Error message
     * @return self
     */
    public function max($field, $length, $message = null)
    {
        if (isset($this->data[$field]) && strlen($this->data[$field]) > $length) {
            $this->errors[$field] = $message ?: "$field must not exceed $length characters";
        }
        return $this;
    }

    /**
     * Validate numeric value
     *
     * @param string $field Field name
     * @param string $message Error message
     * @return self
     */
    public function numeric($field, $message = null)
    {
        if (isset($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field] = $message ?: "$field must be numeric";
        }
        return $this;
    }

    /**
     * Validate field matches another field
     *
     * @param string $field Field name
     * @param string $matchField Field to match
     * @param string $message Error message
     * @return self
     */
    public function matches($field, $matchField, $message = null)
    {
        if (isset($this->data[$field]) && isset($this->data[$matchField])) {
            if ($this->data[$field] !== $this->data[$matchField]) {
                $this->errors[$field] = $message ?: "$field must match $matchField";
            }
        }
        return $this;
    }

    /**
     * Validate URL format
     *
     * @param string $field Field name
     * @param string $message Error message
     * @return self
     */
    public function url($field, $message = null)
    {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_URL)) {
            $this->errors[$field] = $message ?: "$field must be a valid URL";
        }
        return $this;
    }

    /**
     * Validate value is in array
     *
     * @param string $field Field name
     * @param array $values Allowed values
     * @param string $message Error message
     * @return self
     */
    public function in($field, $values, $message = null)
    {
        if (isset($this->data[$field]) && !in_array($this->data[$field], $values)) {
            $this->errors[$field] = $message ?: "$field contains invalid value";
        }
        return $this;
    }

    /**
     * Add custom error
     *
     * @param string $field Field name
     * @param string $message Error message
     * @return self
     */
    public function addError($field, $message)
    {
        $this->errors[$field] = $message;
        return $this;
    }

    /**
     * Check if validation passed
     *
     * @return bool
     */
    public function passes()
    {
        return empty($this->errors);
    }

    /**
     * Check if validation failed
     *
     * @return bool
     */
    public function fails()
    {
        return !$this->passes();
    }

    /**
     * Get all errors
     *
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Get first error
     *
     * @return string|null
     */
    public function firstError()
    {
        return !empty($this->errors) ? reset($this->errors) : null;
    }
}
