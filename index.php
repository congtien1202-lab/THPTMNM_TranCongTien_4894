<?php
require_once 'app/helpers/SessionHelper.php';
SessionHelper::init();

$url = $_GET['url'] ?? '';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

$isApi = isset($url[0]) && strtolower($url[0]) === 'api';

if ($isApi) {
    // Định tuyến cho API (ví dụ: api/product/list -> ApiProductController -> list)
    $controllerName = isset($url[1]) && $url[1] != '' ? 'Api' . ucfirst($url[1]) . 'Controller' : 'ApiDefaultController';
    $action = isset($url[2]) && $url[2] != '' ? $url[2] : 'index';
    $params = array_slice($url, 3);
} else {
    // Định tuyến cho trang web MVC thông thường
    $controllerName = isset($url[0]) && $url[0] != '' ? ucfirst($url[0]) . 'Controller' : 'DefaultController';
    $action = isset($url[1]) && $url[1] != '' ? $url[1] : 'index';
    $params = array_slice($url, 2);
}

// Chỉ thực hiện Middleware Session-based cho Web thông thường (không phải API)
if (!$isApi) {
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

    if (in_array($requestedRoute, $adminRoutes) && !SessionHelper::isAdmin()) {
        die('Truy cap bi tu choi: Chi tai khoan Quan tri vien (Admin) moi co quyen thuc hien chuc nang nay.');
    }

    if (in_array($requestedRoute, $userRoutes) && SessionHelper::isAdmin()) {
        die('Truy cap bi tu choi: Tai khoan Admin chi co quyen quan ly, khong the thuc hien dat hang/thanh toan.');
    }
}

// Kiểm tra xem controller có tồn tại không
if (!file_exists('app/controllers/' . $controllerName . '.php')) {
    if ($isApi) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'API Endpoint not found (Controller missing)']);
    } else {
        die('Controller not found');
    }
    exit();
}

require_once 'app/controllers/' . $controllerName . '.php';
$controller = new $controllerName();

// Kiểm tra xem action có tồn tại trong controller không
if (!method_exists($controller, $action)) {
    if ($isApi) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'API Endpoint not found (Action missing)']);
    } else {
        die('Action not found');
    }
    exit();
}

// Gọi action với các tham số
call_user_func_array([$controller, $action], $params);
?>
