<?php
require_once 'app/config/database.php';
require_once 'app/models/ProductModel.php';
require_once 'app/models/OrderModel.php';

class CartController
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

    // Hàm thêm sản phẩm vào giỏ
    public function add($id)
    {
        // Khởi tạo giỏ hàng nếu chưa có
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Nếu sản phẩm đã có trong giỏ, tăng số lượng. Nếu chưa có, set bằng 1.
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]++;
        } else {
            $_SESSION['cart'][$id] = 1;
        }

        // Chuyển hướng về trang xem giỏ hàng
        header('Location: /TranCongTien_4894/index.php?url=Cart/index');
        exit();
    }

    // Hàm hiển thị giỏ hàng
    public function index()
    {
        $cartItems = [];
        $totalAmount = 0;

        if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $product_id => $quantity) {
                // Gọi Model để lấy thông tin chi tiết của sản phẩm đó
                $product = $this->productModel->getProductById($product_id);
                if ($product) {
                    $subtotal = $product->price * $quantity;
                    $totalAmount += $subtotal;
                    
                    // Đóng gói dữ liệu để đẩy ra View
                    $cartItems[] = [
                        'product' => $product,
                        'quantity' => $quantity,
                        'subtotal' => $subtotal
                    ];
                }
            }
        }

        include 'app/views/cart/index.php';
    }

    // Hàm xóa sản phẩm khỏi giỏ
    public function remove($id)
    {
        if (isset($_SESSION['cart'][$id])) {
            unset($_SESSION['cart'][$id]);
        }
        header('Location: /TranCongTien_4894/index.php?url=Cart/index');
        exit();
    }

    public function checkout()
    {
        if (empty($_SESSION['cart'])) {
            header('Location: /TranCongTien_4894/index.php?url=Cart/index');
            exit();
        }
        include 'app/views/cart/checkout.php';
    }

    // 2. Xử lý logic Thanh toán tự động
    public function processCheckout()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['customer_name'];
            $phone = $_POST['customer_phone'];

            $totalAmount = 0;
            $items = [];

            // Đóng gói mảng chi tiết sản phẩm
            foreach ($_SESSION['cart'] as $product_id => $quantity) {
                $product = $this->productModel->getProductById($product_id);
                if ($product) {
                    $totalAmount += ($product->price * $quantity);
                    $items[] = [
                        'product_id' => $product_id,
                        'quantity' => $quantity,
                        'price' => $product->price
                    ];
                }
            }

            // A. Tạo đơn hàng vào Database
            $order_id = $this->orderModel->createOrder($name, $phone, $totalAmount);

            if ($order_id) {
                // B. Lưu các sản phẩm trong giỏ vào đơn
                $this->orderModel->saveOrderDetails($order_id, $items);

                // C. THỰC THI THANH TOÁN TỰ ĐỘNG
                sleep(2); // Dừng 2 giây để giả lập thời gian xử lý với "ngân hàng"
                $this->orderModel->updatePaymentStatus($order_id, 'Paid');

                // D. Làm sạch giỏ hàng & Báo thành công
                unset($_SESSION['cart']);
                header('Location: /TranCongTien_4894/index.php?url=Cart/success');
                exit();
            }
        }
    }

    // 3. Hiển thị màn hình thành công
    public function success()
    {
        include 'app/views/cart/success.php';
    }
}
?>