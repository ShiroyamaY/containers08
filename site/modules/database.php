<?php
/**
 * Database class for SQLite operations
 * Provides CRUD operations for database tables
 */
class Database {
    private $pdo;

    /**
     * Constructor - initializes database connection
     * 
     * @param string $path Path to SQLite database file
     */
    public function __construct($path) {
        $this->pdo = new PDO("sqlite:{$path}");
        // Set error mode to exception for better error handling
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Execute SQL query
     * 
     * @param string $sql SQL query to execute
     * @return bool Result of query execution
     */
    public function Execute($sql) {
        return $this->pdo->exec($sql);
    }

    /**
     * Execute SQL query and fetch results
     * 
     * @param string $sql SQL query to execute
     * @return array Result as associative array
     */
    public function Fetch($sql) {
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create new record in table
     * 
     * @param string $table Table name
     * @param array $data Associative array of column names and values
     * @return int ID of created record
     */
    public function Create($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($data));
        
        return $this->pdo->lastInsertId();
    }

    /**
     * Read record from table by ID
     * 
     * @param string $table Table name
     * @param int $id Record ID
     * @return array|null Record data or null if not found
     */
    public function Read($table, $id) {
        $sql = "SELECT * FROM {$table} WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result : null;
    }

    /**
     * Update record in table
     * 
     * @param string $table Table name
     * @param int $id Record ID
     * @param array $data Associative array of column names and values to update
     * @return bool Result of update operation
     */
    public function Update($table, $id, $data) {
        $setParts = [];
        foreach ($data as $column => $value) {
            $setParts[] = "{$column} = ?";
        }
        $setClause = implode(', ', $setParts);
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        
        $values = array_values($data);
        $values[] = $id;
        
        return $stmt->execute($values);
    }

    /**
     * Delete record from table
     * 
     * @param string $table Table name
     * @param int $id Record ID
     * @return bool Result of delete operation
     */
    public function Delete($table, $id) {
        $sql = "DELETE FROM {$table} WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Count records in table
     * 
     * @param string $table Table name
     * @return int Number of records
     */
    public function Count($table) {
        $sql = "SELECT COUNT(*) as count FROM {$table}";
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $result['count'];
    }
}