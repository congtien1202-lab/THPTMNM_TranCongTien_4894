<?php
session_start();
$url = $_GET['url'] ?? '';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);
// Kiể, m tra phầ. n đầ. u tiển cu,a URL để,xác định controller
$controllerName = isset($url[0]) && $url[0] != '' ? ucfirst($url[0]) .
'Controller' : 'DefaultController';
// Kiể, m tra phầ. n thứ hai cu,a URL để,xác định action
$action = isset($url[1]) && $url[1] != '' ? $url[1] : 'index';
// Kiể, m tra xem controller và action có tồ. n tại khồng
if (!file_exists('app/controllers/' . $controllerName . '.php')) {
// Xử,lý khồng tìm thầJ y controller
die('Controller not found');
}
require_once 'app/controllers/' . $controllerName . '.php';
$controller = new $controllerName();
if (!method_exists($controller, $action)) {
// Xử,lý khồng tìm thầJ y action
die('Action not found');
}
// Gọi action với các tham sồJcòn lại (nểJ u có)
call_user_func_array([$controller, $action], array_slice($url, 2));