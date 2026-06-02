<?php
// Require SessionHelper and other necessary files
require_once('app/config/database.php');
require_once('app/models/ProductModel.php');
require_once('app/models/CategoryModel.php');
class ProductController
{
private $productModel;
private $db;
public function __construct()
{
$this->db = (new Database())->getConnection();
$this->productModel = new ProductModel($this->db);
}
public function list()
{
$products = $this->productModel->getProducts();
include 'app/views/product/list.php';
}
public function show($id)
    {
        // Lấy thông tin cơ bản của sản phẩm
        $product = $this->productModel->getProductById($id);
        
        if ($product) {
            // Lấy thêm danh sách các ảnh chi tiết (Gallery) từ bảng product_image
            $gallery = $this->productModel->getGalleryImages($id);
            
            // Gọi View để hiển thị
            include 'app/views/product/show.php';
        } else {
            echo "Không tìm thấy sản phẩm.";
        }
    }
public function add()
{
$categories = (new CategoryModel($this->db))->getCategories();
include_once 'app/views/product/add.php';
}
// Hàm hỗ trợ nén và đổi kích thước hình ảnh sử dụng thư viện GD
    private function compressAndResizeImage($sourcePath, $targetPath, $maxWidth = 1200, $maxHeight = 1200, $quality = 80)
    {
        $imageInfo = @getimagesize($sourcePath);
        if (!$imageInfo) {
            return false;
        }

        $mime = $imageInfo['mime'];
        $width = $imageInfo[0];
        $height = $imageInfo[1];

        // Tạo đối tượng hình ảnh từ nguồn tương ứng với định dạng
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

        if (!$image) {
            return false;
        }

        // Tính toán tỷ lệ kích thước mới
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

        // Tạo khung ảnh mới true color
        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        // Giữ độ trong suốt (transparency) cho PNG và GIF
        if ($mime == 'image/png' || $mime == 'image/gif') {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
        }

        // Copy và đổi kích thước hình ảnh
        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Lưu hình ảnh đã tối ưu hóa
        $success = false;
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $success = imagejpeg($newImage, $targetPath, $quality);
                break;
            case 'image/png':
                $pngQuality = round((100 - $quality) / 10); // Lân cận chất lượng PNG từ 0 đến 9
                $success = imagepng($newImage, $targetPath, $pngQuality);
                break;
            case 'image/gif':
                $success = imagegif($newImage, $targetPath);
                break;
            case 'image/webp':
                $success = imagewebp($newImage, $targetPath, $quality);
                break;
        }

        // Giải phóng bộ nhớ giải thuật ảnh
        imagedestroy($image);
        imagedestroy($newImage);

        return $success;
    }

    // Hàm hỗ trợ xử lý file upload - Đã chuyển hướng sang thư mục public/images/ và thêm tối ưu hóa hình ảnh
    private function uploadFile($file_input)
    {
        if (isset($file_input) && $file_input['error'] === UPLOAD_ERR_OK) {
            // Đường dẫn trỏ thẳng vào thư mục images bạn đã tạo
            $uploadDir = 'public/images/';
            
            // Tạo tên file ngẫu nhiên bằng hàm time() để tránh bị ghi đè khi upload trùng tên ảnh
            $fileName = time() . '_' . basename($file_input['name']);
            $targetPath = $uploadDir . $fileName;

            // Kiểm tra xem có phải định dạng ảnh để tối ưu hóa không
            $imageInfo = @getimagesize($file_input['tmp_name']);
            if ($imageInfo) {
                // Tự động nén và điều chỉnh kích thước ảnh về tối đa 1200px để giảm dung lượng file xuống dưới 200-300KB
                if ($this->compressAndResizeImage($file_input['tmp_name'], $targetPath, 1200, 1200, 80)) {
                    return $targetPath;
                }
            }

            // Nếu không tối ưu được hoặc không phải là hình ảnh được hỗ trợ, tiến hành copy thô thông thường
            if (move_uploaded_file($file_input['tmp_name'], $targetPath)) {
                return $targetPath; // Trả về chuỗi có dạng: public/images/1716715xxx_tenanh.jpg
            }
        }
        return null;
    }

    // Hàm save() xử lý lưu dữ liệu sản phẩm và gọi upload album ảnh
public function save()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $price = $_POST['price'] ?? '';
            $category_id = $_POST['category_id'] ?? null;
            
            // 1. Upload ảnh đại diện vào public/images/
            $imagePath = $this->uploadFile($_FILES['image']);

            // 2. Gọi Model để lưu (addProduct cần được sửa để nhận biến $imagePath)
            $product_id = $this->productModel->addProduct($name, $description, $price, $category_id, $imagePath);

            if (is_numeric($product_id)) {
                
                // 3. Duyệt và upload nhiều ảnh chi tiết (Gallery) vào public/images/
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
                            // Lưu từng ảnh phụ vào bảng product_image
                            $this->productModel->addGalleryImage($product_id, $galleryPath);
                        }
                    }
                }
                
                header('Location: /TranCongTien_4894/index.php?url=Product/list');
                exit();
            } else {
                $errors = $product_id;
                $categories = (new CategoryModel($this->db))->getCategories();
                include 'app/views/product/add.php';
            }
        }
    }
public function edit($id)
    {
        // Lấy thông tin sản phẩm
        $product = $this->productModel->getProductById($id);
        // Lấy danh sách danh mục
        $categories = (new CategoryModel($this->db))->getCategories();
        // Lấy danh sách ảnh phụ (Gallery)
        $gallery = $this->productModel->getGalleryImages($id);

        if ($product) {
            include 'app/views/product/edit.php';
        } else {
            echo "Không thấy sản phẩm.";
        }
    }
public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $category_id = $_POST['category_id'];

            // 1. Lấy thông tin sản phẩm cũ để giữ lại đường dẫn ảnh nếu người dùng không đổi ảnh mới
            $currentProduct = $this->productModel->getProductById($id);
            $imagePath = $currentProduct->image;

            // 2. Kiểm tra xem người dùng có upload ảnh đại diện mới không
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $newImagePath = $this->uploadFile($_FILES['image']);
                if ($newImagePath) {
                    $imagePath = $newImagePath; // Ghi đè bằng đường dẫn ảnh mới
                }
            }

            // 3. Tiến hành cập nhật thông tin sản phẩm
            $edit = $this->productModel->updateProduct($id, $name, $description, $price, $category_id, $imagePath);

            if ($edit) {
                // 4. Xử lý upload thêm ảnh phụ (Gallery) nếu có chọn ở trang sửa
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
                            $this->productModel->addGalleryImage($id, $galleryPath);
                        }
                    }
                }

                // Cập nhật thành công, quay về danh sách qua index.php
                header('Location: /TranCongTien_4894/index.php?url=Product/list');
                exit();
            } else {
                echo "Đã xảy ra lỗi khi cập nhật sản phẩm.";
            }
        }
    }
public function delete($id)
{
if ($this->productModel->deleteProduct($id)) {
header('Location: /TranCongTien_4894/index.php?url=Product/list');
} else {
echo "Đã xảy ra lỗi khi xóa sản phẩm.";
}
}
}
?>