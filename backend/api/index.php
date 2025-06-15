<?php
// backend/api/index.php

// Include CORS configuration
include_once '../config/cors.php';
include_once '../config/database.php';

// Test database connection
$database = new Database();
$db = $database->getConnection();

$response = [
    'api' => 'FlexStock Inventory Management API',
    'version' => '1.0.0',
    'status' => 'online',
    'database' => $db ? 'connected' : 'disconnected',
    'endpoints' => [
        'products' => [
            'GET /api/products' => 'Get all products',
            'GET /api/products?id={id}' => 'Get single product',
            'POST /api/products' => 'Create new product',
            'PUT /api/products?id={id}' => 'Update product',
            'DELETE /api/products?id={id}' => 'Delete product'
        ],
        'updates' => [
            'GET /api/updates' => 'Get all inventory updates',
            'POST /api/updates' => 'Create new update record'
        ]
    ],
    'documentation' => 'See project documentation for detailed API usage',
    'timestamp' => date('Y-m-d H:i:s')
];

// If database is connected, add some statistics
if ($db) {
    try {
        // Count products
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM products");
        $stmt->execute();
        $result = $stmt->fetch();
        $response['stats']['total_products'] = intval($result['count']);
        
        // Count updates
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM inventory_updates");
        $stmt->execute();
        $result = $stmt->fetch();
        $response['stats']['total_updates'] = intval($result['count']);
        
        // Count low stock items
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM products WHERE stock <= reorder_level");
        $stmt->execute();
        $result = $stmt->fetch();
        $response['stats']['low_stock_items'] = intval($result['count']);
        
    } catch (Exception $e) {
        $response['stats'] = 'Error retrieving statistics';
    }
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>