<?php
// backend/api/test.php
include_once '../config/cors.php';
include_once '../config/database.php';

// Test endpoint to verify everything is working
$database = new Database();
$conn = $database->getConnection();

$test_results = array(
    "timestamp" => date('Y-m-d H:i:s'),
    "server_info" => array(
        "php_version" => phpversion(),
        "server_software" => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
    ),
    "database" => array()
);

// Test database connection
if ($conn) {
    $test_results["database"]["connection"] = true;
    $test_results["database"]["message"] = "Database connected successfully";
    
    try {
        // Check if tables exist
        $stmt = $conn->prepare("SHOW TABLES LIKE 'products'");
        $stmt->execute();
        $products_exists = $stmt->rowCount() > 0;
        
        $stmt = $conn->prepare("SHOW TABLES LIKE 'inventory_updates'");
        $stmt->execute();
        $updates_exists = $stmt->rowCount() > 0;
        
        $test_results["database"]["tables"] = array(
            "products_table_exists" => $products_exists,
            "inventory_updates_table_exists" => $updates_exists,
            "all_tables_exist" => $products_exists && $updates_exists
        );
        
        // Get some basic stats if tables exist
        if ($products_exists) {
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM products");
            $stmt->execute();
            $result = $stmt->fetch();
            $test_results["database"]["stats"]["total_products"] = $result['count'];
        }
        
        if ($updates_exists) {
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM inventory_updates");
            $stmt->execute();
            $result = $stmt->fetch();
            $test_results["database"]["stats"]["total_updates"] = $result['count'];
        }
        
    } catch(PDOException $e) {
        $test_results["database"]["table_check_error"] = $e->getMessage();
    }
    
} else {
    $test_results["database"]["connection"] = false;
    $test_results["database"]["message"] = "Failed to connect to database";
}

// Add CORS test
$test_results["cors"] = array(
    "origin_header_set" => isset($_SERVER['HTTP_ORIGIN']),
    "access_control_headers" => array(
        "Access-Control-Allow-Origin" => "http://localhost:8080",
        "Access-Control-Allow-Methods" => "GET, POST, PUT, DELETE, OPTIONS",
        "Access-Control-Allow-Headers" => "Content-Type, Authorization, X-Requested-With"
    )
);

// API endpoints test
$test_results["api_endpoints"] = array(
    "products" => "http://localhost/peganggang_webtech/backend/api/products.php",
    "updates" => "http://localhost/peganggang_webtech/backend/api/updates.php",
    "api_info" => "http://localhost/peganggang_webtech/backend/api/index.php"
);

// Return test results
header('Content-Type: application/json');
http_response_code(200);
echo json_encode($test_results, JSON_PRETTY_PRINT);
?>