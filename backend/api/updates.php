<?php
// backend/api/updates.php

// Include CORS configuration
include_once '../config/cors.php';

// Include database and objects
include_once '../config/database.php';
include_once '../classes/Update.php';

// Instantiate database and update object
$database = new Database();
$db = $database->getConnection();

// Check database connection
if ($db === null) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit();
}

$update = new Update($db);

// Get request method
$request_method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($request_method) {
        case 'GET':
            // Get all updates
            $stmt = $update->read();
            $updates = [];
            
            while ($row = $stmt->fetch()) {
                $updates[] = [
                    'id' => intval($row['id']),
                    'product_id' => $row['product_id'] ? intval($row['product_id']) : null,
                    'old_quantity' => $row['old_quantity'] ? intval($row['old_quantity']) : null,
                    'new_quantity' => $row['new_quantity'] ? intval($row['new_quantity']) : null,
                    'type' => $row['type'],
                    'user' => $row['user'],
                    'product_name' => $row['product_name'],
                    'current_product_name' => $row['current_product_name'], // From JOIN
                    'old_name' => $row['old_name'],
                    'new_name' => $row['new_name'],
                    'old_price' => $row['old_price'] ? floatval($row['old_price']) : null,
                    'new_price' => $row['new_price'] ? floatval($row['new_price']) : null,
                    'old_category' => $row['old_category'],
                    'new_category' => $row['new_category'],
                    'old_reorder_level' => $row['old_reorder_level'] ? intval($row['old_reorder_level']) : null,
                    'new_reorder_level' => $row['new_reorder_level'] ? intval($row['new_reorder_level']) : null,
                    'timestamp' => $row['timestamp']
                ];
            }
            
            echo json_encode([
                'success' => true,
                'data' => $updates,
                'count' => count($updates)
            ]);
            break;
            
        case 'POST':
        // Get JSON data
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data || !isset($data['id'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Missing product ID or invalid request'
            ]);
            break;
        }

        // Prepare product data for update
        $product_data = [
            'id' => intval($data['id']),
            'name' => $data['new_name'] ?? $data['name'] ?? '',
            'category' => $data['new_category'] ?? $data['category'] ?? '',
            'price' => floatval($data['new_price'] ?? $data['price'] ?? 0),
            'stock' => intval($data['new_quantity'] ?? $data['stock'] ?? 0),
            'reorder_level' => intval($data['new_reorder_level'] ?? $data['reorder_level'] ?? 0)
        ];

        // First: update the product table
        $updated = $update->updateProduct($product_data);

        if (!$updated) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update product details'
            ]);
            break;
        }

        // Then: log the update in inventory_updates
        $update_data = [
            'product_id' => $product_data['id'],
            'old_quantity' => $data['old_quantity'] ?? null,
            'new_quantity' => $data['new_quantity'] ?? null,
            'type' => $data['type'] ?? 'edit',
            'user' => $data['user'] ?? 'admin',
            'product_name' => $data['product_name'] ?? $product_data['name'],
            'old_name' => $data['old_name'] ?? null,
            'new_name' => $data['new_name'] ?? null,
            'old_price' => $data['old_price'] ?? null,
            'new_price' => $data['new_price'] ?? null,
            'old_category' => $data['old_category'] ?? null,
            'new_category' => $data['new_category'] ?? null,
            'old_reorder_level' => $data['old_reorder_level'] ?? null,
            'new_reorder_level' => $data['new_reorder_level'] ?? null
        ];

        if ($update->create($update_data)) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Product updated and update record saved'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Product updated, but failed to save update record'
            ]);
        }
        break;

            
        default:
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed'
            ]);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>