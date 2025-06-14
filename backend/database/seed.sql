-- Insert your existing mock data
INSERT INTO products (name, category, price, stock, reorder_level, image_url, sold, sales) VALUES
('Wireless Headphones', 'Electronics', 89.99, 5, 10, 'headphones.jpg', 35, 3149.65),
('Smartphone Case', 'Accessories', 19.99, 128, 30, 'case.jpg', 105, 2098.95),
('4K Smart TV', 'Electronics', 699.99, 12, 5, 'tv.jpg', 8, 5599.92),
('Bluetooth Speaker', 'Electronics', 79.99, 32, 8, 'speaker.jpg', 42, 3359.58),
('Gaming Mouse', 'Gaming', 49.99, 0, 15, 'mouse.jpg', 55, 2749.45),
('Mechanical Keyboard', 'Gaming', 129.99, 3, 7, 'keyboard.jpg', 24, 3119.76),
('Tablet Stand', 'Accessories', 24.99, 85, 20, 'stand.jpg', 77, 1924.23);

-- Insert sample inventory updates
INSERT INTO inventory_updates (product_id, old_quantity, new_quantity, type, user, timestamp) VALUES
(1, 50, 45, 'sale', 'system', '2025-05-01 09:23:44'),
(2, 120, 128, 'restock', 'admin', '2025-05-02 10:45:21'),
(3, 14, 12, 'sale', 'system', '2025-05-03 14:12:33'),
(5, 60, 60, 'name_change', 'admin', '2025-05-04 11:32:16'),
(4, 32, 32, 'price_change', 'admin', '2025-05-05 15:17:42');