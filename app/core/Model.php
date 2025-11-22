<?php
// FILE: /app/core/Model.php

/**
 * SplashMarket - Base Model Class
 *
 * Provides database access and common CRUD operations for all models
 * PHP 7.0+ compatible
 */

class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    /**
     * Initialize database connection
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Execute a prepared SQL query
     *
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters to bind
     * @return PDOStatement
     */
    protected function query($sql, $params = [])
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log('Query Error: ' . $e->getMessage() . ' | SQL: ' . $sql);
            throw new Exception('Database query failed');
        }
    }

    /**
     * Find all records with optional filtering
     *
     * @param array $conditions WHERE conditions
     * @param string $orderBy ORDER BY clause
     * @param int $limit LIMIT clause
     * @param int $offset OFFSET clause
     * @return array
     */
    public function findAll($conditions = [], $orderBy = null, $limit = null, $offset = null)
    {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "$key = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }

        if ($limit) {
            $sql .= " LIMIT $limit";
        }

        if ($offset) {
            $sql .= " OFFSET $offset";
        }

        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Find a single record by ID
     *
     * @param int $id Primary key value
     * @return array|null
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1";
        $result = $this->query($sql, [$id])->fetch();
        return $result ?: null;
    }

    /**
     * Find a single record by conditions
     *
     * @param array $conditions WHERE conditions
     * @return array|null
     */
    public function findOne($conditions)
    {
        $where = [];
        $params = [];

        foreach ($conditions as $key => $value) {
            $where[] = "$key = ?";
            $params[] = $value;
        }

        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $where) . " LIMIT 1";
        $result = $this->query($sql, $params)->fetch();
        return $result ?: null;
    }

    /**
     * Insert a new record
     *
     * @param array $data Column => value pairs
     * @return int Last insert ID
     */
    public function insert($data)
    {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ")
                VALUES (" . implode(', ', $placeholders) . ")";

        $this->query($sql, array_values($data));
        return $this->db->lastInsertId();
    }

    /**
     * Update a record by ID
     *
     * @param int $id Primary key value
     * @param array $data Column => value pairs
     * @return bool Success status
     */
    public function update($id, $data)
    {
        $set = [];
        $params = [];

        foreach ($data as $key => $value) {
            $set[] = "$key = ?";
            $params[] = $value;
        }

        $params[] = $id;

        $sql = "UPDATE {$this->table} SET " . implode(', ', $set) . "
                WHERE {$this->primaryKey} = ?";

        $stmt = $this->query($sql, $params);
        return $stmt->rowCount() > 0;
    }

    /**
     * Delete a record by ID
     *
     * @param int $id Primary key value
     * @return bool Success status
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->query($sql, [$id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Count records with optional conditions
     *
     * @param array $conditions WHERE conditions
     * @return int
     */
    public function count($conditions = [])
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "$key = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $result = $this->query($sql, $params)->fetch();
        return (int) $result['total'];
    }

    /**
     * Begin database transaction
     */
    public function beginTransaction()
    {
        return $this->db->beginTransaction();
    }

    /**
     * Commit database transaction
     */
    public function commit()
    {
        return $this->db->commit();
    }

    /**
     * Rollback database transaction
     */
    public function rollback()
    {
        return $this->db->rollBack();
    }
}
