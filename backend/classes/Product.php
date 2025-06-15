<?php
class Product {
    private $conn;
    private $table_name = "products";

    public $id;
    public $name;
    public $category;
    public $price;
    public $stock;
    public $reorder_level;
    public $image_url;
    public $sold;
    public $sales;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all products
    function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Read single product
    function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->name = $row['name'];
            $this->category = $row['category'];
            $this->price = $row['price'];
            $this->stock = $row['stock'];
            $this->reorder_level = $row['reorder_level'];
            $this->image_url = $row['image_url'];
            $this->sold = $row['sold'];
            $this->sales = $row['sales'];
            return true;
        }
        return false;
    }

    // Create product
    function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET name=:name, category=:category, price=:price, 
                      stock=:stock, reorder_level=:reorder_level, 
                      image_url=:image_url, sold=:sold, sales=:sales";

        $stmt = $this->conn->prepare($query);

        // Calculate sales if not provided
        if (empty($this->sales)) {
            $this->sales = $this->price * $this->sold;
        }

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":stock", $this->stock);
        $stmt->bindParam(":reorder_level", $this->reorder_level);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindParam(":sold", $this->sold);
        $stmt->bindParam(":sales", $this->sales);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Update product
    function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET name=:name, category=:category, price=:price, 
                      stock=:stock, reorder_level=:reorder_level, 
                      image_url=:image_url, sold=:sold, sales=:sales
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        // Calculate sales
        $this->sales = $this->price * $this->sold;

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":stock", $this->stock);
        $stmt->bindParam(":reorder_level", $this->reorder_level);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindParam(":sold", $this->sold);
        $stmt->bindParam(":sales", $this->sales);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete product
   public function delete()
        {
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);

            return $stmt->execute();
        }

}
?>