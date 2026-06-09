# HƯỚNG DẪN CHI TIẾT SỬ DỤNG POSTMAN ĐỂ TEST RESTFUL API

Tài liệu này hướng dẫn chi tiết từ cách cài đặt đến cách thực hiện từng Request (GET, POST, PUT, DELETE) trên công cụ **Postman** dành cho các API của dự án.

> [!IMPORTANT]
> **LƯU Ý VỀ CỔNG KẾT NỐI (PORT):**
> Máy chủ Laragon của bạn đang chạy ở cổng **`8000`**. 
> Do đó, tất cả các URL kiểm thử trong Postman phải sử dụng tiền tố là **`http://localhost:8000`**.

---

## 1. Cài đặt Postman

1. Tải phần mềm tại: [https://www.postman.com/downloads/](https://www.postman.com/downloads/)
2. Chọn phiên bản dành cho **Windows** và tải file `.exe` về cài đặt.
3. Khi phần mềm mở ra, chọn **Skip Sign In** ở dưới để vào thẳng giao diện làm việc.

---

## 2. Giao diện làm việc cơ bản trong Postman

Nhấn nút **`+` (New Tab)** để mở bảng làm việc mới:
1. **Method Selector**: Ô dropdown chọn phương thức (`GET`, `POST`, `PUT`, `DELETE`).
2. **URL Bar**: Ô nhập link địa chỉ API.
3. **Send**: Nút màu xanh dương để thực thi gửi request.
4. **Bảng cấu hình Request** (nằm dưới thanh URL):
   - **Authorization (Auth)**: Cấu hình mã xác thực JWT Token (Chọn kiểu **Bearer Token**).
   - **Body**: Chọn **raw** -> **JSON** hoặc **form-data** để nhập dữ liệu gửi lên.
5. **Response** (ở dưới cùng): Kết quả JSON trả về từ Server PHP.

## PHẦN A. XÁC THỰC (AUTHENTICATION) - LẤY TOKEN

### Cách 1: Đăng nhập bằng tài khoản Admin có sẵn (Được chuẩn bị sẵn)
Dự án đã thiết lập sẵn tài khoản Admin trong hệ thống của bạn để tiện kiểm thử:
*   **Tên đăng nhập (Username)**: `admin`
*   **Mật khẩu (Password)**: `admin123`

**Các bước lấy Token Admin:**
1. Mở một Tab mới trong Postman.
2. Chọn phương thức: **`POST`**.
3. Nhập URL:
   ```text
   http://localhost:8000/TranCongTien_4894/index.php?url=api/account/login
   ```
4. Click chọn tab **`Body`** $\rightarrow$ chọn **`raw`** $\rightarrow$ chọn định dạng **`JSON`**.
5. Nhập dữ liệu JSON sau:
   ```json
   {
     "username": "admin",
     "password": "admin123"
   }
   ```
6. Nhấn nút **`Send`**.
7. Phía dưới kết quả trả về, bạn sẽ nhận được mã Token. Hãy **sao chép (copy)** chuỗi ký tự trong mục `"token"` này. Đây chính là **Token Admin** dùng cho các chức năng Thêm, Sửa, Xóa sản phẩm ở Phần B.

---

### Cách 2: Đăng ký & Đăng nhập tài khoản Khách hàng (Quyền User)
Dùng khi bạn muốn lấy Token quyền Khách hàng để thực hiện thanh toán đơn hàng ở Phần C:
1. Gửi request **`POST`** đăng ký tài khoản mới đến URL:
   `http://localhost:8000/TranCongTien_4894/index.php?url=api/account/register`
   - Body (JSON):
     ```json
     {
       "username": "khachhang_test",
       "password": "password123",
       "confirm_password": "password123"
     }
     ```
2. Sau khi đăng ký thành công, hãy thực hiện lại bước đăng nhập (giống Cách 1) bằng tài khoản `khachhang_test` và mật khẩu `password123` để lấy **Token Khách hàng (User Token)**.

---

## PHẦN B. THỬ NGHIỆM CÁC API SẢN PHẨM (PRODUCT FILE)

### Bước B1: Lấy danh sách sản phẩm (Phương thức GET)
*Mục tiêu: Xem toàn bộ danh sách sản phẩm công khai.*
*   **Method**: `GET`
*   **URL**: `http://localhost:8000/TranCongTien_4894/index.php?url=api/product/list`
*   **Auth**: Chọn **No Auth** (Không cần token).
*   **Kết quả mong đợi**: HTTP `200 OK`, trả về danh sách các sản phẩm dạng JSON.

---

### Bước B2: Thêm sản phẩm mới kèm ảnh (Phương thức POST)
*Mục tiêu: Thêm sản phẩm mới và tải ảnh đại diện lên server.*
*   **Method**: `POST`
*   **URL**: `http://localhost:8000/TranCongTien_4894/index.php?url=api/product/save`
*   **Auth**: Chọn **Bearer Token** $\rightarrow$ Dán mã Token Admin của bạn vào ô Token.
*   **Body**: Chọn **form-data** (không chọn raw). Nhập các dòng Key-Value:
    - `name` (Text): `Laptop Lenovo ThinkPad`
    - `price` (Text): `28000000`
    - `category_id` (Text): `1`
    - `description` (Text): `Laptop doanh nhan`
    - `image` (Chọn kiểu **File** ở dropdown cuối ô Key): Chọn 1 file hình ảnh từ máy tính của bạn.
*   **Kết quả mong đợi**: HTTP `201 Created`, trả về thông báo thành công kèm theo `product_id` vừa tạo (Ví dụ: `"product_id": 5`).

---

### Bước B3: Xem chi tiết sản phẩm vừa tạo (Phương thức GET)
*Mục tiêu: Đọc thông tin chi tiết của sản phẩm cụ thể.*
*   **Method**: `GET`
*   **URL**: `http://localhost:8000/TranCongTien_4894/index.php?url=api/product/show/5` *(Thay số 5 bằng ID sản phẩm thực tế vừa tạo ở Bước B2)*
*   **Auth**: Chọn **No Auth**.
*   **Kết quả mong đợi**: HTTP `200 OK`, hiển thị thông tin sản phẩm và mảng ảnh phụ `gallery`.

---

### Bước B4: Cập nhật sản phẩm (Phương thức PUT hoặc POST)
*Mục tiêu: Sửa đổi giá hoặc tên sản phẩm.*

#### Cách 1: Chỉ sửa văn bản (Phương thức PUT với JSON)
*   **Method**: `PUT`
*   **URL**: `http://localhost:8000/TranCongTien_4894/index.php?url=api/product/update/5` *(Thay số 5 bằng ID sản phẩm)*
*   **Auth**: Chọn **Bearer Token** $\rightarrow$ Dán Token Admin.
*   **Body**: Chọn **raw** $\rightarrow$ **JSON**. Nhập nội dung sửa đổi:
    ```json
    {
      "name": "Laptop Lenovo ThinkPad (Đã Sửa)",
      "price": 29500000,
      "category_id": 1
    }
    ```
*   **Kết quả mong đợi**: HTTP `200 OK`, thông báo cập nhật thành công.

#### Cách 2: Sửa thông tin và cập nhật ảnh đại diện mới (Phương thức POST)
*   **Method**: `POST`
*   **URL**: `http://localhost:8000/TranCongTien_4894/index.php?url=api/product/update/5`
*   **Auth**: Chọn **Bearer Token** $\rightarrow$ Dán Token Admin.
*   **Body**: Chọn **form-data**. Nhập thông tin và chọn file ảnh mới tại key `image`.

---

### Bước B5: Xóa sản phẩm vừa tạo (Phương thức DELETE)
*Mục tiêu: Loại bỏ hoàn toàn sản phẩm khỏi cơ sở dữ liệu.*
*   **Method**: `DELETE`
*   **URL**: `http://localhost:8000/TranCongTien_4894/index.php?url=api/product/delete/5` *(Thay số 5 bằng ID sản phẩm cần xóa)*
*   **Auth**: Chọn **Bearer Token** $\rightarrow$ Dán Token Admin.
*   **Kết quả mong đợi**: HTTP `200 OK`, thông báo `"Xóa sản phẩm thành công"`.
*   *Kiểm chứng*: Gửi lại request ở **Bước B3** cho sản phẩm này sẽ trả về lỗi `404 Not Found`.

---

## PHẦN C. GIỎ HÀNG & THANH TOÁN (CART & CHECKOUT)

### Bước C1: Đặt hàng thanh toán (Phương thức POST)
*   **Method**: `POST`
*   **URL**: `http://localhost:8000/TranCongTien_4894/index.php?url=api/cart/checkout`
*   **Auth**: Chọn **Bearer Token** $\rightarrow$ Dán mã Token (Quyền `user`).
*   **Body**: Chọn **raw** $\rightarrow$ **JSON**.
*   **Nội dung JSON**:
    ```json
    {
      "customer_name": "Nguyen Khach",
      "customer_phone": "0987654321",
      "customer_address": "TP.HCM",
      "cart": [
        {
          "product_id": 1,
          "quantity": 3
        }
      ]
    }
    ```
*   **Kết quả mong đợi**: HTTP `201 Created`, trả về thông báo thanh toán thành công kèm theo `order_id` và `total_amount` thực tế.
