<?php
class ProductModel
{
    private $db; // Thuộc tính lưu trữ kết nối CSDL

    // Hàm khởi tạo nhận biến $db từ Controller truyền sang
    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getProducts()
    {
        // Đã bổ sung thêm p.image vào câu lệnh SELECT
        $query = "SELECT p.id, p.name, p.description, p.price, p.image, c.name as category_name
                  FROM product p
                  LEFT JOIN category c ON p.category_id = c.id";
                  
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_OBJ);
        return $result;
    }

    public function getProductById($id)
    {
        $query = "SELECT * FROM product WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result;
    }

    public function addProduct($name, $description, $price, $category_id, $image = null)
    {
        try {
            $query = "INSERT INTO product (name, description, price, category_id, image) 
                      VALUES (:name, :description, :price, :category_id, :image)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':category_id', $category_id);
            $stmt->bindParam(':image', $image);
            $stmt->execute();
            
            return $this->db->lastInsertId(); 
        } catch (PDOException $e) {
            return ["Lỗi DB: " . $e->getMessage()];
        }
    }

    public function addGalleryImage($product_id, $image_path)
    {
        $query = "INSERT INTO product_image (product_id, image_path) VALUES (:product_id, :image_path)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':image_path', $image_path);
        return $stmt->execute();
    }   

    public function getGalleryImages($product_id)
    {
        $query = "SELECT * FROM product_image WHERE product_id = :product_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }   

    public function updateProduct($id, $name, $description, $price, $category_id, $image = null)
    {
        // Cập nhật câu lệnh SQL để lưu cả đường dẫn ảnh mới
        $query = "UPDATE product SET name=:name, description=:description, price=:price, category_id=:category_id, image=:image WHERE id=:id";
        $stmt = $this->db->prepare($query);
        
        $name = htmlspecialchars(strip_tags($name));
        $description = htmlspecialchars(strip_tags($description));
        $price = htmlspecialchars(strip_tags($price));
        $category_id = htmlspecialchars(strip_tags($category_id));
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':image', $image); // Ràng buộc thêm biến image
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function deleteProduct($id)
    {
        $query = "DELETE FROM product WHERE id=:id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>