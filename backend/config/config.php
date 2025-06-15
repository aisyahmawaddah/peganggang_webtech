<?php
// backend/config/config.php

// Application configuration
define('DEBUG_MODE', true); // Set to false in production
define('API_VERSION', '1.0.0');
define('API_NAME', 'FlexStock Inventory Management API');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'flexstock_inventory');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// API Settings
define('DEFAULT_TIMEZONE', 'UTC');
define('MAX_REQUEST_SIZE', '10M');
define('API_TIMEOUT', 30); // seconds

// Response messages
define('MSG_SUCCESS', 'Operation completed successfully');
define('MSG_ERROR', 'An error occurred');
define('MSG_NOT_FOUND', 'Resource not found');
define('MSG_INVALID_DATA', 'Invalid data provided');
define('MSG_DB_ERROR', 'Database error occurred');
define('MSG_AUTH_REQUIRED', 'Authentication required');

// HTTP Status codes (for reference)
define('HTTP_OK', 200);
define('HTTP_CREATED', 201);
define('HTTP_BAD_REQUEST', 400);
define('HTTP_UNAUTHORIZED', 401);
define('HTTP_FORBIDDEN', 403);
define('HTTP_NOT_FOUND', 404);
define('HTTP_METHOD_NOT_ALLOWED', 405);
define('HTTP_INTERNAL_ERROR', 500);
?>