<?php
// backend/config/database.php

class Database {
    // Database configuration
    private $host = 'sql203.infinityfree.com';
    private $db_name = 'if0_39389102_flexstock_inventory';
    private $username = 'if0_39389102';
    private $password = 'pV7jQiQLXULzDj1';
    private $charset = 'utf8mb4';
    
    // Database connection
    private $conn;
    
    /**
     * Get database connection
     * @return PDO|null
     */
    public function getConnection() {
        $this->conn = null;
        
        try {
            // Data Source Name
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            
            // PDO options
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            
            // Create PDO instance
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch(PDOException $exception) {
            // Log error (in production, you'd log this to a file)
            error_log("Database connection error: " . $exception->getMessage());
            
            // Return null on connection failure
            return null;
        }
        
        return $this->conn;
    }
    
    /**
     * Close database connection
     */
    public function closeConnection() {
        $this->conn = null;
    }
    
    /**
     * Test database connection
     * @return bool
     */
    public function testConnection() {
        $connection = $this->getConnection();
        return $connection !== null;
    }
}
?>