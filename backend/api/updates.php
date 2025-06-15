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
            // Create new update record
            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data || !isset($data['type'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Missing required field: type'
                ]);
                break;
            }
            
            // Prepare update data with defaults
            $update_data = [
                'product_id' => isset($data['product_id']) ? intval($data['product_id']) : null,
                'old_quantity' => isset($data['old_quantity']) ? intval($data['old_quantity']) : null,
                'new_quantity' => isset($data['new_quantity']) ? intval($data['new_quantity']) : null,
                'type' => $data['type'],
                'user' => isset($data['user']) ? $data['user'] : 'admin',
                'product_name' => isset($data['product_name']) ? $data['product_name'] : null,
                'old_name' => isset($data['old_name']) ? $data['old_name'] : null,
                'new_name' => isset($data['new_name']) ? $data['new_name'] : null,
                'old_price' => isset($data['old_price']) ? floatval($data['old_price']) : null,
                'new_price' => isset($data['new_price']) ? floatval($data['new_price']) : null,
                'old_category' => isset($data['old_category']) ? $data['old_category'] : null,
                'new_category' => isset($data['new_category']) ? $data['new_category'] : null,
                'old_reorder_level' => isset($data['old_reorder_level']) ? intval($data['old_reorder_level']) : null,
                'new_reorder_level' => isset($data['new_reorder_level']) ? intval($data['new_reorder_level']) : null
            ];
            
            if ($update->create($update_data)) {
                http_response_code(201);
                echo json_encode([
                    'success' => true,
                    'message' => 'Update record created successfully'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to create update record'
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