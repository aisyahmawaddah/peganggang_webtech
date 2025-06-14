<?php
// backend/api/index.php
include_once '../config/cors.php';

// API Information and Routes
$api_info = array(
    "name" => "FlexStock Inventory Management API",
    "version" => "1.0.0",
    "description" => "RESTful API for FlexStock inventory management system",
    "endpoints" => array(
        "products" => array(
            "url" => "products.php",
            "methods" => array("GET", "POST", "PUT", "DELETE"),
            "description" => "Manage inventory products"
        ),
        "updates" => array(
            "url" => "updates.php", 
            "methods" => array("GET"),
            "description" => "Get inventory activity updates"
        )
    ),
    "examples" => array(
        "get_all_products" => "GET /api/products.php",
        "get_single_product" => "GET /api/products.php?id=1",
        "create_product" => "POST /api/products.php",
        "update_product" => "PUT /api/products.php",
        "delete_product" => "DELETE /api/products.php",
        "get_updates" => "GET /api/updates.php"
    ),
    "data_format" => array(
        "request" => "JSON",
        "response" => "JSON"
    ),
    "authentication" => "None (for development)",
    "cors" => "Enabled for localhost:8080"
);

// Set content type to JSON
header('Content-Type: application/json');

// Handle GET request to show API info
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    http_response_code(200);
    echo json_encode($api_info, JSON_PRETTY_PRINT);
} else {
    http_response_code(405);
    echo json_encode(array(
        "error" => "Method not allowed",
        "message" => "This endpoint only supports GET requests for API information"
    ));
}
?>