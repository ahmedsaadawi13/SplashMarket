<?php
// FILE: /app/core/Database.php

/**
 * SplashMarket - Database Connection Handler
 *
 * Manages PDO database connections with singleton pattern
 * PHP 7.0+ compatible
 */

class Database
{
    private static $instance = null;
    private $connection;
    private $host;
    private $database;
    private $username;
    private $password;
    private $charset;

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct()
    {
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->database = getenv('DB_NAME') ?: 'splashmarket';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASS') ?: '';
        $this->charset = 'utf8mb4';

        $this->connect();
    }

    /**
     * Get singleton instance of Database
     *
     * @return Database
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Establish PDO connection
     */
    private function connect()
    {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->database};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            // Log error and display user-friendly message
            error_log('Database Connection Error: ' . $e->getMessage());
            die('Database connection failed. Please check your configuration.');
        }
    }

    /**
     * Get PDO connection instance
     *
     * @return PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Prevent cloning of instance
     */
    private function __clone()
    {
    }

    /**
     * Prevent unserialization of instance
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}
