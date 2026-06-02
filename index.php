<?php
require_once 'app/helpers/SessionHelper.php';
SessionHelper::init();

$url = $_GET['url'] ?? '';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

// Kiểm tra phần đầu tiên của URL để xác định controller
$controllerName = isset($url[0]) && $url[0] != '' ? ucfirst($url[0]) . 'Controller' : 'DefaultController';
// Kiểm tra phần thứ hai của URL để xác định action
$action = isset($url[1]) && $url[1] != '' ? $url[1] : 'index';

// Middleware kiểm tra quyền truy cập phân quyền (Role-based Authorization)
$adminRoutes = [
    'product/add',
    'product/save',
    'product/edit',
    'product/update',
    'product/delete'
];
$userRoutes = [
    'cart/checkout',
    'cart/processcheckout'
];
$requestedRoute = strtolower(($url[0] ?? 'default') . '/' . ($url[1] ?? 'index'));

$allProtectedRoutes = array_merge($adminRoutes, $userRoutes);

if (in_array($requestedRoute, $allProtectedRoutes) && !SessionHelper::isLoggedIn()) {
    header('Location: /TranCongTien_4894/index.php?url=Account/login');
    exit();
}

// 1. Chỉ tài khoản Admin mới được Thêm/Sửa/Xóa sản phẩm
if (in_array($requestedRoute, $adminRoutes) && !SessionHelper::isAdmin()) {
    die('Truy cap bi tu choi: Chi tai khoan Quan tri vien (Admin) moi co quyen thuc hien chuc nang nay.');
}

// 2. Chỉ tài khoản User mới được Đặt hàng/Thanh toán
if (in_array($requestedRoute, $userRoutes) && SessionHelper::isAdmin()) {
    die('Truy cap bi tu choi: Tai khoan Admin chi co quyen quan ly, khong the thuc hien dat hang/thanh toan.');
}

// Kiểm tra xem controller và action có tồn tại không
if (!file_exists('app/controllers/' . $controllerName . '.php')) {
    // Xử lý không tìm thấy controller
    die('Controller not found');
}
require_once 'app/controllers/' . $controllerName . '.php';
$controller = new $controllerName();
if (!method_exists($controller, $action)) {
    // Xử lý không tìm thấy action
    die('Action not found');
}
// Gọi action với các tham số còn lại (nếu có)
call_user_func_array([$controller, $action], array_slice($url, 2));