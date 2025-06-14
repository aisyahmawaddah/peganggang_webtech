<?php
// backend/classes/Database.php

// Check if class already exists to prevent redeclaration
if (!class_exists('Database')) {
    class Database {
        // Database configuration
        private $host = 'localhost';
        private $db_name = 'flexstock_inventory';
        private $username = 'root';
        private $password = '';
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
                
                // Set charset
                $this->conn->exec("set names utf8mb4");
                
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
        
        /**
         * Get database configuration info (without sensitive data)
         * @return array
         */
        public function getConnectionInfo() {
            return [
                'host' => $this->host,
                'database' => $this->db_name,
                'charset' => $this->charset,
                'connected' => $this->testConnection()
            ];
        }
        
        /**
         * Execute a simple query to check if tables exist
         * @return bool
         */
        public function checkTables() {
            try {
                $conn = $this->getConnection();
                if (!$conn) {
                    return false;
                }
                
                // Check if main tables exist
                $stmt = $conn->prepare("SHOW TABLES LIKE 'products'");
                $stmt->execute();
                $products_exists = $stmt->rowCount() > 0;
                
                $stmt = $conn->prepare("SHOW TABLES LIKE 'inventory_updates'");
                $stmt->execute();
                $updates_exists = $stmt->rowCount() > 0;
                
                return $products_exists && $updates_exists;
                
            } catch(PDOException $e) {
                error_log("Table check error: " . $e->getMessage());
                return false;
            }
        }
        
        /**
         * Get database statistics
         * @return array
         */
        public function getStats() {
            try {
                $conn = $this->getConnection();
                if (!$conn) {
                    return ['error' => 'No database connection'];
                }
                
                $stats = [];
                
                // Count products
                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM products");
                $stmt->execute();
                $result = $stmt->fetch();
                $stats['total_products'] = $result['count'];
                
                // Count updates
                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM inventory_updates");
                $stmt->execute();
                $result = $stmt->fetch();
                $stats['total_updates'] = $result['count'];
                
                // Get latest update
                $stmt = $conn->prepare("SELECT timestamp FROM inventory_updates ORDER BY timestamp DESC LIMIT 1");
                $stmt->execute();
                $result = $stmt->fetch();
                $stats['last_update'] = $result ? $result['timestamp'] : null;
                
                return $stats;
                
            } catch(PDOException $e) {
                error_log("Stats error: " . $e->getMessage());
                return ['error' => 'Failed to get database statistics'];
            }
        }
    }
}
?>