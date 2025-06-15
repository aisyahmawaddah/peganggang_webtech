<?php
// backend/api/products.php

// Include configuration and CORS first
require_once '../config/config.php';
require_once '../config/cors.php';
require_once '../config/database.php';
require_once '../classes/Product.php';
require_once '../classes/Update.php';

try {
    // Instantiate database and product object
    $database = new Database();
    $db = $database->getConnection();

    // Check database connection
    if ($db === null) {
        handleError(MSG_DB_ERROR, HTTP_INTERNAL_ERROR);
    }

    $product = new Product($db);
    $update = new Update($db);

    // Get request method
    $request_method = $_SERVER['REQUEST_METHOD'];

    // Get product ID if provided in URL
    $product_id = null;
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $product_id = intval($_GET['id']);
    }

    switch ($request_method) {
        case 'GET':
            if ($product_id) {
                // Get single product
                $product->id = $product_id;
                if ($product->readOne()) {
                    sendJsonResponse([
                        'success' => true,
                        'data' => [
                            'id' => intval($product->id),
                            'name' => $product->name,
                            'category' => $product->category,
                            'price' => floatval($product->price),
                            'stock' => intval($product->stock),
                            'reorder_level' => intval($product->reorder_level),
                            'image_url' => $product->image_url,
                            'sold' => intval($product->sold),
                            'sales' => floatval($product->sales)
                        ]
                    ], HTTP_OK);
                } else {
                    handleError('Product not found', HTTP_NOT_FOUND);
                }
            } else {
                // Get all products
                $stmt = $product->read();
                $products = [];
                
                while ($row = $stmt->fetch()) {
                    $products[] = [
                        'id' => intval($row['id']),
                        'name' => $row['name'],
                        'category' => $row['category'],
                        'price' => floatval($row['price']),
                        'stock' => intval($row['stock']),
                        'reorder_level' => intval($row['reorder_level']),
                        'image_url' => $row['image_url'],
                        'sold' => intval($row['sold']),
                        'sales' => floatval($row['sales'])
                    ];
                }
                
                sendJsonResponse([
                    'success' => true,
                    'data' => $products,
                    'count' => count($products)
                ], HTTP_OK);
            }
            break;
            
        case 'POST':
            // Get and validate input data
            $input = file_get_contents("php://input");
            $data = json_decode($input, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                handleError('Invalid JSON data', HTTP_BAD_REQUEST);
            }
            
            if (!$data || !isset($data['name']) || !isset($data['category']) || !isset($data['price'])) {
                handleError('Missing required fields: name, category, price', HTTP_BAD_REQUEST);
            }

            // Validate data types and values
            if (empty(trim($data['name']))) {
                handleError('Product name cannot be empty', HTTP_BAD_REQUEST);
            }

            if (!is_numeric($data['price']) || floatval($data['price']) <= 0) {
                handleError('Price must be a positive number', HTTP_BAD_REQUEST);
            }

            // Set product properties
            $product->name = trim($data['name']);
            $product->category = $data['category'];
            $product->price = floatval($data['price']);
            $product->stock = isset($data['stock']) ? intval($data['stock']) : 0;
            $product->reorder_level = isset($data['reorder_level']) ? intval($data['reorder_level']) : 10;
            $product->image_url = isset($data['image_url']) ? trim($data['image_url']) : '';
            $product->sold = isset($data['sold']) ? intval($data['sold']) : 0;
            $product->sales = $product->price * $product->sold;
            
            if ($product->create()) {
                // Log the creation
                $update_data = [
                    'product_id' => $product->id,
                    'old_quantity' => 0,
                    'new_quantity' => $product->stock,
                    'type' => 'add',
                    'user' => 'admin',
                    'product_name' => $product->name,
                    'old_name' => null,
                    'new_name' => $product->name,
                    'old_price' => null,
                    'new_price' => $product->price,
                    'old_category' => null,
                    'new_category' => $product->category,
                    'old_reorder_level' => null,
                    'new_reorder_level' => $product->reorder_level
                ];
                
                $update->create($update_data);
                
                sendJsonResponse([
                    'success' => true,
                    'message' => 'Product created successfully',
                    'data' => [
                        'id' => intval($product->id),
                        'name' => $product->name,
                        'category' => $product->category,
                        'price' => floatval($product->price),
                        'stock' => intval($product->stock),
                        'reorder_level' => intval($product->reorder_level),
                        'image_url' => $product->image_url,
                        'sold' => intval($product->sold),
                        'sales' => floatval($product->sales)
                    ]
                ], HTTP_CREATED);
            } else {
                handleError('Failed to create product', HTTP_INTERNAL_ERROR);
            }
            break;
            
        case 'PUT':
            if (!$product_id) {
                handleError('Product ID is required for update', HTTP_BAD_REQUEST);
            }
            
            $input = file_get_contents("php://input");
            $data = json_decode($input, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                handleError('Invalid JSON data', HTTP_BAD_REQUEST);
            }
            
            if (!$data) {
                handleError('No data provided for update', HTTP_BAD_REQUEST);
            }
            
            // Get current product data
            $product->id = $product_id;
            if (!$product->readOne()) {
                handleError('Product not found', HTTP_NOT_FOUND);
            }
            
            // Store old values for update log
            $old_values = [
                'name' => $product->name,
                'category' => $product->category,
                'price' => $product->price,
                'stock' => $product->stock,
                'reorder_level' => $product->reorder_level
            ];
            
            // Update fields if provided
            if (isset($data['name'])) {
                if (empty(trim($data['name']))) {
                    handleError('Product name cannot be empty', HTTP_BAD_REQUEST);
                }
                $product->name = trim($data['name']);
            }
            
            if (isset($data['category'])) $product->category = $data['category'];
            
            if (isset($data['price'])) {
                if (!is_numeric($data['price']) || floatval($data['price']) <= 0) {
                    handleError('Price must be a positive number', HTTP_BAD_REQUEST);
                }
                $product->price = floatval($data['price']);
            }
            
            if (isset($data['stock'])) $product->stock = intval($data['stock']);
            if (isset($data['reorder_level'])) $product->reorder_level = intval($data['reorder_level']);
            if (isset($data['image_url'])) $product->image_url = trim($data['image_url']);
            if (isset($data['sold'])) $product->sold = intval($data['sold']);
            
            if ($product->update()) {
                // Log the update
                $update->create([
                    'product_id' => $product->id,
                    'old_quantity' => $old_values['stock'],
                    'new_quantity' => $product->stock,
                    'type' => 'update',
                    'user' => 'admin',
                    'product_name' => $product->name,
                    'old_name' => $old_values['name'],
                    'new_name' => $product->name,
                    'old_price' => $old_values['price'],
                    'new_price' => $product->price,
                    'old_category' => $old_values['category'],
                    'new_category' => $product->category,
                    'old_reorder_level' => $old_values['reorder_level'],
                    'new_reorder_level' => $product->reorder_level
                ]);
                
                sendJsonResponse([
                    'success' => true,
                    'message' => 'Product updated successfully',
                    'data' => [
                        'id' => intval($product->id),
                        'name' => $product->name,
                        'category' => $product->category,
                        'price' => floatval($product->price),
                        'stock' => intval($product->stock),
                        'reorder_level' => intval($product->reorder_level),
                        'image_url' => $product->image_url,
                        'sold' => intval($product->sold),
                        'sales' => floatval($product->sales)
                    ]
                ], HTTP_OK);
            } else {
                handleError('Failed to update product', HTTP_INTERNAL_ERROR);
            }
            break;
            
      case 'DELETE':
    if (!$product_id) {
        handleError('Product ID is required for delete', HTTP_BAD_REQUEST);
    }

    $product->id = $product_id;

    if (!$product->readOne()) {
        handleError('Product not found', HTTP_NOT_FOUND);
    }

    if ($product->delete()) {
        sendJsonResponse([
            'success' => true,
            'message' => 'Product deleted successfully'
        ], HTTP_OK);
    } else {
        handleError('Failed to delete product', HTTP_INTERNAL_ERROR);
    }
    break;

    }
    
} catch (Exception $e) {
    // Log the full error
    error_log('API Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
    
    // Send generic error response
    handleError('An unexpected error occurred', HTTP_INTERNAL_ERROR, 
        DEBUG_MODE ? [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ] : null
    );
}
?>