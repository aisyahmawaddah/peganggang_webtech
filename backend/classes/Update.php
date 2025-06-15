<?php
class Update {
    private $conn;
    private $table_name = "inventory_updates";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all updates
    function read() {
        $query = "SELECT iu.*, p.name as current_product_name 
                  FROM " . $this->table_name . " iu
                  LEFT JOIN products p ON iu.product_id = p.id
                  ORDER BY iu.timestamp DESC 
                  LIMIT 50";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Create update record
    function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET product_id=:product_id, old_quantity=:old_quantity, 
                      new_quantity=:new_quantity, type=:type, user=:user, 
                      product_name=:product_name, old_name=:old_name, 
                      new_name=:new_name, old_price=:old_price, new_price=:new_price,
                      old_category=:old_category, new_category=:new_category,
                      old_reorder_level=:old_reorder_level, new_reorder_level=:new_reorder_level";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":product_id", $data['product_id']);
        $stmt->bindParam(":old_quantity", $data['old_quantity']);
        $stmt->bindParam(":new_quantity", $data['new_quantity']);
        $stmt->bindParam(":type", $data['type']);
        $stmt->bindParam(":user", $data['user']);
        $stmt->bindParam(":product_name", $data['product_name']);
        $stmt->bindParam(":old_name", $data['old_name']);
        $stmt->bindParam(":new_name", $data['new_name']);
        $stmt->bindParam(":old_price", $data['old_price']);
        $stmt->bindParam(":new_price", $data['new_price']);
        $stmt->bindParam(":old_category", $data['old_category']);
        $stmt->bindParam(":new_category", $data['new_category']);
        $stmt->bindParam(":old_reorder_level", $data['old_reorder_level']);
        $stmt->bindParam(":new_reorder_level", $data['new_reorder_level']);

        return $stmt->execute();
    }

    // ✅ New method to update product info
    public function updateProduct($data) {
        $query = "UPDATE products 
                  SET name = :name, category = :category, price = :price, 
                      stock = :stock, reorder_level = :reorder_level 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $data['id']);
        $stmt->bindParam(":name", $data['name']);
        $stmt->bindParam(":category", $data['category']);
        $stmt->bindParam(":price", $data['price']);
        $stmt->bindParam(":stock", $data['stock']);
        $stmt->bindParam(":reorder_level", $data['reorder_level']);

        return $stmt->execute();
    }
} // <--- ✅ This closing bracket ends the class!
?>
