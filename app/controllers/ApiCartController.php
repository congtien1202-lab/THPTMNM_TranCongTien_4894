<?php
require_once 'app/config/database.php';
require_once 'app/models/ProductModel.php';
require_once 'app/models/OrderModel.php';
require_once 'app/helpers/JwtHelper.php';

class ApiCartController
{
    private $productModel;
    private $orderModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->productModel = new ProductModel($this->db);
        $this->orderModel = new OrderModel($this->db);
    }

    /**
     * Xác thực JWT token và phân quyền chỉ cho vai trò 'user'
     */
    private function checkAuth($requiredRole = 'user')
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        $token = '';

        if (preg_match('/Bearer\s(\S+)/i', $authHeader, $matches)) {
            $token = $matches[1];
        }

        $userData = JwtHelper::validate($token);
        if (!$userData) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(401);
            echo json_encode([
                'status' => 'error',
                'message' => 'Truy cập bị từ chối: Token không hợp lệ hoặc đã hết hạn.'
            ]);
            exit();
        }

        if ($requiredRole && (!isset($userData['role']) || $userData['role'] !== $requiredRole)) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(403);
            echo json_encode([
                'status' => 'error',
                'message' => 'Lỗi phân quyền: Chỉ tài khoản Khách hàng (User) mới có quyền thực hiện đặt hàng/thanh toán.'
            ]);
            exit();
        }

        return $userData;
    }

    /**
     * POST /api/cart/checkout
     */
    public function checkout()
    {
        // 1. Xác thực người dùng (Chỉ cho phép tài khoản 'user' đặt hàng)
        $this->checkAuth('user');

        header('Content-Type: application/json; charset=utf-8');

        // Nhận dữ liệu JSON gửi lên
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Dữ liệu yêu cầu không hợp lệ (Không phải định dạng JSON).'
            ]);
            exit();
        }

        $name = trim($input['customer_name'] ?? '');
        $phone = trim($input['customer_phone'] ?? '');
        $address = trim($input['customer_address'] ?? '');
        $cart = $input['cart'] ?? [];

        if (empty($name) || empty($phone) || empty($address)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Vui lòng cung cấp đầy đủ thông tin khách hàng (Tên, Số điện thoại, Địa chỉ).'
            ]);
            exit();
        }

        if (empty($cart) || !is_array($cart)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Giỏ hàng của bạn đang trống.'
            ]);
            exit();
        }

        try {
            $totalAmount = 0;
            $items = [];

            // 2. Tính toán lại tổng tiền từ Database dựa trên Product ID (Tránh việc Front-end gửi khống giá tiền)
            foreach ($cart as $item) {
                $productId = $item['product_id'] ?? null;
                $quantity = $item['quantity'] ?? 0;

                if (!$productId || $quantity <= 0) {
                    http_response_code(400);
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Dữ liệu giỏ hàng chứa sản phẩm hoặc số lượng không hợp lệ.'
                    ]);
                    exit();
                }

                $product = $this->productModel->getProductById($productId);
                if (!$product) {
                    http_response_code(404);
                    echo json_encode([
                        'status' => 'error',
                        'message' => "Sản phẩm với ID $productId không tồn tại."
                    ]);
                    exit();
                }

                $totalAmount += ($product->price * $quantity);
                $items[] = [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $product->price
                ];
            }

            // 3. Tạo đơn hàng trong CSDL
            $orderId = $this->orderModel->createOrder($name, $phone, $address, $totalAmount);

            if ($orderId) {
                // 4. Lưu chi tiết đơn hàng
                $this->orderModel->saveOrderDetails($orderId, $items);

                // 5. Cập nhật trạng thái thanh toán (Giả lập thanh toán trực tuyến thành công)
                $this->orderModel->updatePaymentStatus($orderId, 'Paid');

                http_response_code(201);
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Đặt hàng và thanh toán thành công!',
                    'order_id' => $orderId,
                    'total_amount' => $totalAmount,
                    'payment_status' => 'Paid'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Không thể khởi tạo đơn hàng trên hệ thống.'
                ]);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Lỗi hệ thống khi xử lý thanh toán: ' . $e->getMessage()
            ]);
        }
        exit();
    }
}
?>
