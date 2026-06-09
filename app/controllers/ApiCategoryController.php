<?php
require_once 'app/config/database.php';
require_once 'app/models/CategoryModel.php';

class ApiCategoryController
{
    private $categoryModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->categoryModel = new CategoryModel($this->db);
    }

    /**
     * GET /api/category/list
     */
    public function list()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $categories = $this->categoryModel->getCategories();
            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'data' => $categories
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ]);
        }
        exit();
    }
}
