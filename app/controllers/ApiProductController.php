<?php
require_once 'app/config/database.php';
require_once 'app/models/ProductModel.php';
require_once 'app/models/CategoryModel.php';
require_once 'app/helpers/JwtHelper.php';

class ApiProductController
{
    private $productModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->productModel = new ProductModel($this->db);
    }

    /**
     * Xác thực JWT token và phân quyền
     */
    private function checkAuth($requiredRole = null)
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
                'message' => 'Lỗi phân quyền: Bạn không có quyền thực hiện hành động này.'
            ]);
            exit();
        }

        return $userData;
    }

    /**
     * GET /api/product/list
     */
    public function list()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $products = $this->productModel->getProducts();
            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'data' => $products
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

    /**
     * GET /api/product/show/{id}
     */
    public function show($id = null)
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Thiếu ID sản phẩm.']);
            exit();
        }

        try {
            $product = $this->productModel->getProductById($id);
            if ($product) {
                $gallery = $this->productModel->getGalleryImages($id);
                http_response_code(200);
                echo json_encode([
                    'status' => 'success',
                    'data' => [
                        'product' => $product,
                        'gallery' => $gallery
                    ]
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy sản phẩm.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
        exit();
    }

    /**
     * POST /api/product/save
     */
    public function save()
    {
        // 1. Kiểm tra quyền Admin
        $this->checkAuth('admin');

        header('Content-Type: application/json; charset=utf-8');

        // Hỗ trợ cả JSON body hoặc multipart/form-data
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = $_POST['price'] ?? 0;
        $category_id = $_POST['category_id'] ?? null;

        if ($_SERVER['CONTENT_TYPE'] && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
            $name = $input['name'] ?? $name;
            $description = $input['description'] ?? $description;
            $price = $input['price'] ?? $price;
            $category_id = $input['category_id'] ?? $category_id;
        }

        if (empty($name) || $price <= 0 || !$category_id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Vui lòng cung cấp đầy đủ thông tin sản phẩm hợp lệ.']);
            exit();
        }

        // 2. Upload ảnh đại diện nếu có gửi kèm file
        $imagePath = null;
        if (isset($_FILES['image'])) {
            $imagePath = $this->uploadFile($_FILES['image']);
        }

        try {
            $product_id = $this->productModel->addProduct($name, $description, $price, $category_id, $imagePath);

            if (is_numeric($product_id)) {
                // 3. Upload nhiều ảnh phụ (Gallery) nếu có
                if (isset($_FILES['gallery']) && !empty($_FILES['gallery']['name'][0])) {
                    $total_files = count($_FILES['gallery']['name']);
                    for ($i = 0; $i < $total_files; $i++) {
                        $file_array = [
                            'name' => $_FILES['gallery']['name'][$i],
                            'type' => $_FILES['gallery']['type'][$i],
                            'tmp_name' => $_FILES['gallery']['tmp_name'][$i],
                            'error' => $_FILES['gallery']['error'][$i],
                            'size' => $_FILES['gallery']['size'][$i]
                        ];
                        $galleryPath = $this->uploadFile($file_array);
                        if ($galleryPath) {
                            $this->productModel->addGalleryImage($product_id, $galleryPath);
                        }
                    }
                }

                http_response_code(201);
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Thêm sản phẩm thành công.',
                    'product_id' => $product_id
                ]);
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Không thể thêm sản phẩm.', 'details' => $product_id]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
        }
        exit();
    }

    /**
     * POST/PUT /api/product/update/{id}
     */
    public function update($id = null)
    {
        $this->checkAuth('admin');

        header('Content-Type: application/json; charset=utf-8');

        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Thiếu ID sản phẩm cần cập nhật.']);
            exit();
        }

        $currentProduct = $this->productModel->getProductById($id);
        if (!$currentProduct) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy sản phẩm cần cập nhật.']);
            exit();
        }

        // Nhận tham số
        $name = $_POST['name'] ?? $currentProduct->name;
        $description = $_POST['description'] ?? $currentProduct->description;
        $price = $_POST['price'] ?? $currentProduct->price;
        $category_id = $_POST['category_id'] ?? $currentProduct->category_id;
        $imagePath = $currentProduct->image;

        // Nếu request gửi kiểu JSON (thông qua phương thức PUT)
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input) {
            $name = $input['name'] ?? $name;
            $description = $input['description'] ?? $description;
            $price = $input['price'] ?? $price;
            $category_id = $input['category_id'] ?? $category_id;
        }

        // Kiểm tra ảnh mới gửi lên qua $_FILES (nếu gửi kiểu POST /api/product/update/{id})
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $newImagePath = $this->uploadFile($_FILES['image']);
            if ($newImagePath) {
                // Xóa file ảnh cũ để tránh lưu trữ rác trên server
                if ($currentProduct->image && file_exists($currentProduct->image)) {
                    @unlink($currentProduct->image);
                }
                $imagePath = $newImagePath;
            }
        }

        try {
            $success = $this->productModel->updateProduct($id, $name, $description, $price, $category_id, $imagePath);
            if ($success) {
                // Thay thế ảnh phụ nếu có tải lên album mới
                if (isset($_FILES['gallery']) && !empty($_FILES['gallery']['name'][0])) {
                    // 1. Xóa các file ảnh chi tiết cũ trên disk
                    $existingGallery = $this->productModel->getGalleryImages($id);
                    foreach ($existingGallery as $img) {
                        if (file_exists($img->image_path)) {
                            @unlink($img->image_path);
                        }
                    }
                    // 2. Xóa các bản ghi ảnh chi tiết cũ trong DB
                    $this->productModel->deleteGalleryImages($id);

                    // 3. Upload và lưu album ảnh chi tiết mới
                    $total_files = count($_FILES['gallery']['name']);
                    for ($i = 0; $i < $total_files; $i++) {
                        $file_array = [
                            'name' => $_FILES['gallery']['name'][$i],
                            'type' => $_FILES['gallery']['type'][$i],
                            'tmp_name' => $_FILES['gallery']['tmp_name'][$i],
                            'error' => $_FILES['gallery']['error'][$i],
                            'size' => $_FILES['gallery']['size'][$i]
                        ];
                        $galleryPath = $this->uploadFile($file_array);
                        if ($galleryPath) {
                            $this->productModel->addGalleryImage($id, $galleryPath);
                        }
                    }
                }

                http_response_code(200);
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Cập nhật sản phẩm thành công.'
                ]);
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Cập nhật sản phẩm thất bại.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
        }
        exit();
    }

    /**
     * DELETE /api/product/delete/{id}
     */
    public function delete($id = null)
    {
        $this->checkAuth('admin');

        header('Content-Type: application/json; charset=utf-8');

        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Thiếu ID sản phẩm cần xóa.']);
            exit();
        }

        try {
            // Xóa các file ảnh liên quan (ảnh đại diện và album ảnh chi tiết) trên disk trước khi xóa sản phẩm
            $currentProduct = $this->productModel->getProductById($id);
            if ($currentProduct) {
                if ($currentProduct->image && file_exists($currentProduct->image)) {
                    @unlink($currentProduct->image);
                }
                $existingGallery = $this->productModel->getGalleryImages($id);
                foreach ($existingGallery as $img) {
                    if (file_exists($img->image_path)) {
                        @unlink($img->image_path);
                    }
                }
            }

            $success = $this->productModel->deleteProduct($id);
            if ($success) {
                http_response_code(200);
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Xóa sản phẩm thành công.'
                ]);
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Xóa sản phẩm thất bại.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
        }
        exit();
    }

    // Sao chép các hàm xử lý ảnh từ ProductController
    private function uploadFile($file_input)
    {
        if (isset($file_input) && $file_input['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'public/images/';
            $fileName = time() . '_' . basename($file_input['name']);
            $targetPath = $uploadDir . $fileName;

            $imageInfo = @getimagesize($file_input['tmp_name']);
            if ($imageInfo) {
                if ($this->compressAndResizeImage($file_input['tmp_name'], $targetPath, 1200, 1200, 80)) {
                    return $targetPath;
                }
            }

            if (move_uploaded_file($file_input['tmp_name'], $targetPath)) {
                return $targetPath;
            }
        }
        return null;
    }

    private function compressAndResizeImage($sourcePath, $targetPath, $maxWidth = 1200, $maxHeight = 1200, $quality = 80)
    {
        $imageInfo = @getimagesize($sourcePath);
        if (!$imageInfo) return false;

        $mime = $imageInfo['mime'];
        $width = $imageInfo[0];
        $height = $imageInfo[1];

        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $image = @imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $image = @imagecreatefrompng($sourcePath);
                break;
            case 'image/gif':
                $image = @imagecreatefromgif($sourcePath);
                break;
            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    $image = @imagecreatefromwebp($sourcePath);
                } else {
                    return false;
                }
                break;
            default:
                return false;
        }

        if (!$image) return false;

        $newWidth = $width;
        $newHeight = $height;

        if ($width > $maxWidth || $height > $maxHeight) {
            $ratio = $width / $height;
            if ($ratio > 1) {
                $newWidth = $maxWidth;
                $newHeight = round($maxWidth / $ratio);
            } else {
                $newHeight = $maxHeight;
                $newWidth = round($maxHeight * $ratio);
            }
        }

        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        if ($mime == 'image/png' || $mime == 'image/gif') {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
        }

        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        $success = false;
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $success = imagejpeg($newImage, $targetPath, $quality);
                break;
            case 'image/png':
                $pngQuality = round((100 - $quality) / 10);
                $success = imagepng($newImage, $targetPath, $pngQuality);
                break;
            case 'image/gif':
                $success = imagegif($newImage, $targetPath);
                break;
            case 'image/webp':
                $success = imagewebp($newImage, $targetPath, $quality);
                break;
        }

        imagedestroy($image);
        imagedestroy($newImage);

        return $success;
    }
}
?>
