# FlexStock Inventory Management

A Vue.js + PHP inventory management system with real-time dashboard, smart recommendations, and CRUD operations.

## 🚀 Quick Setup

### Prerequisites
- Node.js (v14+)
- PHP (v8.0+)
- MySQL (v8.0+)

### 1. Clone Repository
```bash
git clone https://github.com/aisyahmawaddah/peganggang_webtech.git
cd peganggang_webtech
```

### 2. Frontend Setup
```bash
# Install dependencies
npm install

# Start development server
npm run serve
```
Frontend will run on `http://localhost:8080`

### 3. Backend Setup
```bash
# Navigate to backend folder
cd backend

# Configure database
# Edit backend/config/config.php with your database credentials

# Import database
mysql -u username -p database_name < database/schema.sql
mysql -u username -p database_name < database/seed.sql

# Start PHP server
php -S localhost:8000 -t public/
```
Backend API will run on `http://localhost:8000`

### 4. Database Configuration
Edit `backend/config/config.php`:
```php
<?php
return [
    'database' => [
        'host' => 'localhost',
        'database' => 'flexstock_inventory',
        'username' => 'your_username',
        'password' => 'your_password'
    ]
];
?>
```

## 📁 Project Structure
```
peganggang_webtech/
├── backend/           # PHP API
│   ├── api/          # REST endpoints
│   ├── classes/      # PHP classes
│   ├── config/       # Configuration
│   └── database/     # SQL files
├── src/              # Vue.js frontend
│   └── components/   # Vue components
└── public/           # Static files
```

## 🛠️ Tech Stack
- **Frontend**: Vue.js 3, Bootstrap 5, Chart.js
- **Backend**: PHP 8, MySQL, RESTful API
- **Features**: Real-time dashboard, smart alerts, inventory management

## 🎯 Main Features
- Interactive dashboard with 6 key metrics
- Real-time stock alerts (pulsing red borders)
- Category-based product filtering
- CRUD operations for inventory
- Sales trend analysis

## 🔗 API Endpoints
- `GET /api/products` - Get all products
- `POST /api/products` - Create product
- `PUT /api/products/{id}` - Update product
- `DELETE /api/products/{id}` - Delete product
- `GET /api/updates` - Get activity feed

## 📝 Database Schema

### Products Table
```sql
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
```

### Inventory Updates Table
```sql
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
```

### Sample Data
The seed.sql includes:
- 7 sample products across Electronics, Gaming, and Accessories categories
- Products with realistic stock levels, prices, and sales data
- Sample inventory update records for activity tracking

## 🚦 Usage
1. Access frontend at `http://localhost:8080`
2. Use Dashboard to view inventory overview
3. Click cards to filter products
4. Navigate to "Update Inventory" for CRUD operations
