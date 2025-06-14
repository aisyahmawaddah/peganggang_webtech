-- Create database
CREATE DATABASE flexstock_inventory;
USE flexstock_inventory;

-- Products table (matching your Vue.js data structure)
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    reorder_level INT NOT NULL DEFAULT 10,
    image_url VARCHAR(255) DEFAULT '',
    sold INT NOT NULL DEFAULT 0,
    sales DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inventory updates table (for Recent Activity tracking)
CREATE TABLE inventory_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    old_quantity INT,
    new_quantity INT,
    type ENUM('sale', 'restock', 'name_change', 'price_change', 'category_change', 'reorder_change', 'add', 'delete', 'update') NOT NULL,
    user VARCHAR(100) DEFAULT 'admin',
    old_name VARCHAR(255),
    new_name VARCHAR(255),
    old_price DECIMAL(10, 2),
    new_price DECIMAL(10, 2),
    old_category VARCHAR(100),
    new_category VARCHAR(100),
    old_reorder_level INT,
    new_reorder_level INT,
    product_name VARCHAR(255),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);