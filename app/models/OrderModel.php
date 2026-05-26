<?php
class OrderModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // Tạo đơn hàng mới với trạng thái mặc định là 'Pending' (Chờ xử lý)
    public function createOrder($name, $phone, $total_amount)
    {
        $query = "INSERT INTO orders (customer_name, customer_phone, total_amount, payment_status) 
                  VALUES (:name, :phone, :total_amount, 'Pending')";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':total_amount', $total_amount);
        $stmt->execute();
        
        return $this->db->lastInsertId(); // Trả về ID của đơn hàng vừa tạo
    }

    // Lưu chi tiết từng sản phẩm khách mua
    public function saveOrderDetails($order_id, $items)
    {
        $query = "INSERT INTO order_details (order_id, product_id, quantity, price) 
                  VALUES (:order_id, :product_id, :quantity, :price)";
        $stmt = $this->db->prepare($query);

        foreach ($items as $item) {
            $stmt->bindParam(':order_id', $order_id);
            $stmt->bindParam(':product_id', $item['product_id']);
            $stmt->bindParam(':quantity', $item['quantity']);
            $stmt->bindParam(':price', $item['price']);
            $stmt->execute();
        }
    }

    // Hàm cập nhật trạng thái thanh toán
    public function updatePaymentStatus($order_id, $status)
    {
        $query = "UPDATE orders SET payment_status = :status WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $order_id);
        return $stmt->execute();
    }
}
?>