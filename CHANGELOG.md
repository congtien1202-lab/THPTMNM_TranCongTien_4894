# Nhật Ký Thay Đổi Code (CHANGELOG)

Tài liệu này ghi lại chi tiết các thay đổi trong mã nguồn của dự án `TranCongTien_4894`. Mỗi khi có bất kỳ chỉnh sửa hay tính năng mới nào được thêm vào, nhật ký này sẽ được cập nhật để bạn dễ dàng theo dõi.

---

## [2026-06-09] - Sửa lỗi thêm/thay thế ảnh chi tiết (Gallery) khi quản lý sản phẩm

### Chi tiết thay đổi:
1. **Cơ sở dữ liệu & Model - [ProductModel.php](file:///c:/laragon/www/TranCongTien_4894/app/models/ProductModel.php):**
   - Bổ sung hàm `deleteGalleryImages($product_id)` nhằm hỗ trợ xóa toàn bộ bản ghi ảnh phụ cũ liên kết với sản phẩm trong bảng `product_image`.

2. **Logic Backend API - [ApiProductController.php](file:///c:/laragon/www/TranCongTien_4894/app/controllers/ApiProductController.php):**
   - **Cập nhật sản phẩm (`update`)**: 
     - Xóa tệp tin ảnh đại diện cũ trên ổ đĩa nếu người dùng tải lên ảnh đại diện mới.
     - Kiểm tra nếu có album ảnh chi tiết (`$_FILES['gallery']`) được gửi lên, tự động truy vấn danh sách ảnh chi tiết hiện tại, xóa toàn bộ các tệp tin ảnh chi tiết cũ trên disk, gọi hàm xóa bản ghi DB tương ứng và tiến hành tải lên & ghi nhận các ảnh chi tiết mới.
   - **Xóa sản phẩm (`delete`)**: 
     - Tự động xóa tệp tin ảnh đại diện và tất cả tệp tin ảnh chi tiết của sản phẩm đó trên disk trước khi thực hiện xóa bản ghi sản phẩm khỏi cơ sở dữ liệu (tận dụng CASCADE trong DB để dọn dẹp các bản ghi liên quan trong bảng `product_image`).

3. **Giao diện Frontend - [api_demo.html](file:///c:/laragon/www/TranCongTien_4894/public/api_demo.html):**
   - **Giao diện Form & Button Custom**: 
     - Ẩn thanh chọn tệp tin thô mặc định của trình duyệt.
     - Thiết kế thêm các nút bấm tùy chỉnh phong cách Premium: `#btnTriggerImage` (Chọn ảnh đại diện) và `#btnTriggerGallery` (Chọn các ảnh chi tiết) để kích hoạt hộp thoại chọn file của hệ điều hành.
   - **Bản xem trước ảnh mới chọn**: Tích hợp tính năng hiển thị xem trước tức thời các tệp tin hình ảnh mới vừa được lựa chọn (`#newImagePreviewContainer` và `#newGalleryPreviewContainer`) bằng đối tượng `FileReader` trước khi người dùng thực hiện lưu dữ liệu.
   - **Tải dữ liệu sửa**: Cập nhật sự kiện click `.btn-edit-product` để lấy danh sách album ảnh phụ hiện tại từ API và hiển thị thành các ảnh thu nhỏ (thumbnails) làm bản xem trước.
   - **Dọn dẹp form**: Reset các trường nhập file ẩn và tự động dọn sạch/ẩn tất cả các khung ảnh xem trước cũ/mới khi mở modal tạo sản phẩm hoặc sau khi submit form thành công.
   - **Gửi dữ liệu**: Cập nhật hàm xử lý submit form, duyệt qua các file trong `#prodGallery` và đính kèm vào `FormData` dưới tham số mảng `gallery[]` để gửi lên API.
   - **Xoay ảnh chi tiết**: 
     - Bao bọc ảnh chính của modal chi tiết trong thẻ căn chỉnh flex với `height: 300px` và `overflow: hidden` để giữ vững bố cục giao diện.
     - Thiết kế thêm nút bấm `Xoay dọc ảnh ngang` (`#btnRotateImage`) tự động ẩn/hiện tùy theo định dạng của ảnh (chỉ hiển thị nếu ảnh có chiều rộng lớn hơn chiều cao).
     - Khi click xoay 90/270 độ, tự động căn tỉ lệ co giãn `scale(0.7)` để ảnh đứng vừa vặn trong khung hình mà không bị khuất cạnh, và tự động reset góc xoay về 0 khi chuyển đổi hình ảnh hoặc đóng hộp thoại.

4. **Đồng bộ danh mục động từ CSDL:**
   - **Tạo API Controller - [ApiCategoryController.php](file:///c:/laragon/www/TranCongTien_4894/app/controllers/ApiCategoryController.php)**: Khai báo endpoint `GET /api/category/list` sử dụng `CategoryModel` để lấy toàn bộ danh mục từ database và trả về định dạng JSON.
   - **Đồng bộ giao diện - [api_demo.html](file:///c:/laragon/www/TranCongTien_4894/public/api_demo.html)**: 
     - Loại bỏ các tùy chọn danh mục cứng (hardcoded) trong HTML.
     - Viết hàm `fetchCategories()` tự động lấy dữ liệu từ API và điền vào ô chọn danh mục `#prodCategory` của form quản trị sản phẩm khi tải trang.

### Lý do thay đổi:
- Khắc phục lỗi thiếu giao diện chọn album ảnh và thiếu cơ chế đính kèm tệp tin album khi tương tác với API. Đảm bảo việc thay thế album ảnh chi tiết được đồng bộ sạch sẽ cả trên cơ sở dữ liệu lẫn tài nguyên lưu trữ trên máy chủ, tránh gây lãng phí dung lượng disk. Ngoài ra, tích hợp công cụ xoay ảnh ngang và tải danh mục động từ CSDL giúp dữ liệu đồng nhất và nâng cao trải nghiệm xem ảnh của khách hàng.

---

## [2026-06-09] - Phân biệt nhà cung cấp đăng nhập (Google / GitHub) trong CSDL

### Chi tiết thay đổi:
1. **Cơ sở dữ liệu (Database):**
   - Thêm cột `provider` với kiểu dữ liệu `VARCHAR(50)` và giá trị mặc định là `'local'` vào bảng `users`.
   - Phân loại các tài khoản cũ trong database dựa vào tên đăng nhập (ví dụ: các tài khoản chứa `@gmail.com` cập nhật thành `google`, tài khoản bắt đầu bằng `github_` cập nhật thành `github`).

2. **Cập nhật Logic Code trong [UserModel.php](file:///c:/laragon/www/TranCongTien_4894/app/models/UserModel.php):**
   - **Hàm `findOrCreateGoogleUser($email, $name)`:**
     - Khi chèn tài khoản Google mới, lưu giá trị cột `provider` là `'google'`.
     - Nếu tài khoản đã tồn tại từ trước nhưng cột `provider` chưa được gắn là `'google'`, tiến hành cập nhật lại giá trị thành `'google'`.
   - **Hàm `findOrCreateGithubUser($githubUsername)`:**
     - Khi chèn tài khoản GitHub mới, lưu giá trị cột `provider` là `'github'`.
     - Nếu tài khoản đã tồn tại từ trước nhưng cột `provider` chưa được gắn là `'github'`, tiến hành cập nhật lại giá trị thành `'github'`.

### Lý do thay đổi:
- Giúp hệ thống biết chính xác người dùng đăng nhập bằng phương thức nào (Tài khoản thường, Google hay GitHub) khi kiểm tra cơ sở dữ liệu.

---

## [2026-06-09] - Xây dựng Backend RESTful API và Bảo mật JWT

### Chi tiết thay đổi:
1. **Helper mới - [JwtHelper.php](file:///c:/laragon/www/TranCongTien_4894/app/helpers/JwtHelper.php):**
   - Hỗ trợ tạo (generate) và kiểm tra (validate) JWT Token dựa trên thuật toán mã hóa `HMAC-SHA256` không cần thư viện ngoài.
   
2. **Bộ định tuyến - [index.php](file:///c:/laragon/www/TranCongTien_4894/index.php):**
   - Hỗ trợ định tuyến các request có tiền tố `api/` (ví dụ: `api/product/list`) đến các controller API tương ứng (`Api*Controller`).
   - Bỏ qua cơ chế session-based redirect middleware cho các yêu cầu API.
   - Trả về thông báo lỗi định dạng JSON (thay vì trang HTML/chuỗi die thô) nếu không tìm thấy API endpoint.

3. **Controllers API mới:**
   - **[ApiAccountController.php](file:///c:/laragon/www/TranCongTien_4894/app/controllers/ApiAccountController.php):**
     - `POST /api/account/register`: Đăng ký tài khoản khách hàng.
     - `POST /api/account/login`: Xác thực tài khoản và trả về JWT Token chứa thông tin user.
   - **[ApiProductController.php](file:///c:/laragon/www/TranCongTien_4894/app/controllers/ApiProductController.php):**
     - `GET /api/product/list`: Lấy danh sách sản phẩm (Công khai).
     - `GET /api/product/show/{id}`: Xem chi tiết sản phẩm và gallery ảnh (Công khai).
     - `POST /api/product/save`: Thêm sản phẩm mới kèm ảnh (Yêu cầu JWT Token - Quyền Admin).
     - `POST /api/product/update/{id}`: Cập nhật sản phẩm và ảnh mới (Yêu cầu JWT Token - Quyền Admin).
     - `DELETE /api/product/delete/{id}`: Xóa sản phẩm (Yêu cầu JWT Token - Quyền Admin).
   - **[ApiCartController.php](file:///c:/laragon/www/TranCongTien_4894/app/controllers/ApiCartController.php):**
     - `POST /api/cart/checkout`: Nhận thông tin khách hàng và giỏ hàng phi trạng thái (stateless) từ client, tự động đối chiếu giá từ DB, lưu đơn hàng và chuyển trạng thái đã thanh toán (Yêu cầu JWT Token - Quyền User).

### Lý do thay đổi:
- Cho phép tách biệt phần xử lý logic Backend và Giao diện (Frontend). Chuẩn bị dữ liệu phục vụ kết nối với Frontend jQuery/React/App di động và bảo mật các thao tác dữ liệu bằng Token thay vì Session thông thường.

---

## [2026-06-09] - Xây dựng Giao diện Frontend Single-Page Application (SPA) bằng jQuery

### Chi tiết thay đổi:
1. **Trang giao diện mới - [api_demo.html](file:///c:/laragon/www/TranCongTien_4894/public/api_demo.html):**
   - Xây dựng giao diện Single-Page Application (SPA) hiện đại kết hợp CSS Custom Variables, Bootstrap 5 và FontAwesome.
   - Thiết kế chuẩn phong cách Glassmorphism (hiệu ứng mờ kính, viền phát sáng, các hiệu ứng hover mượt mà và micro-animations).

2. **Xử lý Logic Javascript (jQuery & AJAX):**
   - **Quản lý phiên (State & Auth)**: Tự động lưu trữ JWT Token và phân quyền user (Admin / User) trong `localStorage`. Tự động đính kèm header `Authorization: Bearer <Token>` vào tất cả request AJAX đến API được bảo mật.
   - **Giỏ hàng phi trạng thái (Stateless Cart)**: Lưu trữ các sản phẩm đã chọn tại `localStorage`. Tự động đồng bộ số lượng và tính tiền trên giao diện.
   - **Tương tác Admin CRUD**: Hiển thị bảng điều khiển thêm sản phẩm, nút Sửa/Xóa riêng biệt cho Admin. Sử dụng `FormData` để upload ảnh trực tiếp qua AJAX.
   - **Thanh toán**: Tự động chuyển đổi dữ liệu và gửi thông tin thanh toán lên API checkout khi có vai trò Khách hàng hợp lệ.
   - **Thông báo**: Thiết kế hệ thống Toast thông báo trạng thái giao dịch (Thành công, Cảnh báo, Lỗi) trực quan.

### Lý do thay đổi:
- Cung cấp một ứng dụng giao diện hoàn chỉnh kết nối trực tiếp với RESTful API, cho phép người dùng đăng ký, đăng nhập, đặt hàng (Khách hàng) và quản trị sản phẩm kèm hình ảnh (Admin) trực quan trên một trang duy nhất.

---

## [2026-06-09] - Sửa lỗi tương tác Đăng nhập/Đăng ký và bổ sung nút Xem chi tiết sản phẩm

### Chi tiết thay đổi:
1. **Sửa lỗi sự kiện Đăng nhập/Đăng ký trên [api_demo.html](file:///c:/laragon/www/TranCongTien_4894/public/api_demo.html):**
   - Chuyển đổi các bộ lắng nghe sự kiện click tĩnh (`$('#btnShowLoginModal').click(...)`) thành sự kiện ủy quyền (delegated events: `$(document).on('click', '#btnShowLoginModal', ...)`).
   - Giải quyết triệt để lỗi mất nút chức năng Đăng nhập/Đăng ký khi giao diện thanh điều hướng vẽ lại sau khi người dùng Đăng xuất.

2. **Bổ sung chức năng và nút "Xem chi tiết" sản phẩm:**
   - Cập nhật hàm `fetchProducts()` để bổ sung nút **"Xem"** trên card sản phẩm cho cả khách hàng, khách vãng lai và quản trị viên.
   - Thiết kế và thêm mã nguồn hộp thoại chi tiết mới **`#productDetailModal`** hiển thị đầy đủ hình ảnh đại diện, giá cả, danh mục, mô tả chi tiết sản phẩm.
   - Thêm bộ hiển thị Album ảnh phụ (Gallery) dưới dạng ảnh thu nhỏ (thumbnails), hỗ trợ click để đổi ảnh chính của sản phẩm.
   - Động hóa nút hành động bên trong modal chi tiết (Khách hàng thấy nút "Thêm vào giỏ", Admin thấy nút "Sửa" và "Xóa").

### Lý do thay đổi:
- Đảm bảo các tương tác Đăng nhập/Đăng ký hoạt động ổn định không bị đứt quãng trong suốt phiên làm việc. Khôi phục đầy đủ trải nghiệm xem chi tiết sản phẩm và xem bộ sưu tập ảnh giống phiên bản web MVC trước đó.

---

## [2026-06-09] - Tích hợp Đăng nhập Google và GitHub cho Frontend API

### Chi tiết thay đổi:
1. **Giao diện Frontend - [api_demo.html](file:///c:/laragon/www/TranCongTien_4894/public/api_demo.html):**
   - Thêm nút Đăng nhập bằng Google và GitHub (với định dạng nút màu sắc và SVG biểu tượng chính xác) bên trong form Đăng nhập của `#authModal`.
   - Ẩn/Hiện nút mạng xã hội thông minh (chỉ hiển thị khi ở tab "Đăng nhập", ẩn khi người dùng chuyển sang tab "Đăng ký").
   - Thêm mã nguồn JS tự động bóc tách các tham số `token`, `username`, `role` hoặc `error` từ URL khi trang web được chuyển hướng về từ các OAuth callbacks, tự động lưu thông tin đăng nhập vào `localStorage`, dọn dẹp URL bằng `window.history.replaceState` và hiển thị thông báo Toast chào mừng.

2. **Logic Backend API - [ApiAccountController.php](file:///c:/laragon/www/TranCongTien_4894/app/controllers/ApiAccountController.php):**
   - Thêm các endpoint `googleLogin()` và `githubLogin()` riêng biệt cho API.
   - Gửi yêu cầu xác thực sang Google/GitHub kèm theo tham số nhận diện trạng thái `state` lần lượt là `'api_google_state'` và `'api_github_state'`.

3. **Cập nhật Callbacks - [AccountController.php](file:///c:/laragon/www/TranCongTien_4894/app/controllers/AccountController.php):**
   - Chỉnh sửa `googleCallback()` và `githubCallback()` để kiểm tra tham số `state`.
   - Nếu phát hiện yêu cầu từ API: tự động sinh mã JWT token chứa thông tin của tài khoản mạng xã hội đó, sau đó chuyển hướng người dùng quay trở lại trang giao diện API (`public/api_demo.html`) kèm theo JWT token và thông tin đăng nhập dạng query string thay vì lưu session PHP thông thường.

### Lý do thay đổi:
- Cho phép người dùng sử dụng đầy đủ các tính năng đăng nhập một chạm bằng Google và GitHub trên giao diện Single-Page Application (SPA) mới của API mà không làm gián đoạn hay mất đi tính năng xác thực của ứng dụng gốc.

---


