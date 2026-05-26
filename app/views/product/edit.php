<?php include 'app/views/shares/header.php'; ?>

<h1>Sửa sản phẩm</h1>

<form action="/TranCongTien_4894/index.php?url=Product/update" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo $product->id; ?>">

    <div class="form-group">
        <label for="name">Tên sản phẩm:</label>
        <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>" required>
    </div>

    <div class="form-group mt-2">
        <label for="description">Mô tả:</label>
        <textarea id="description" name="description" class="form-control" required><?php echo htmlspecialchars($product->description, ENT_QUOTES, 'UTF-8'); ?></textarea>
    </div>

    <div class="form-group mt-2">
        <label for="price">Giá:</label>
        <input type="number" id="price" name="price" class="form-control" step="0.01" value="<?php echo htmlspecialchars($product->price, ENT_QUOTES, 'UTF-8'); ?>" required>
    </div>

    <div class="form-group mt-2">
        <label for="category_id">Danh mục:</label>
        <select id="category_id" name="category_id" class="form-control" required>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category->id; ?>" <?php echo ($category->id == $product->category_id) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($category->name, ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group mt-3 p-3 border rounded">
        <label>Ảnh đại diện hiện tại:</label><br>
        <?php if (!empty($product->image)): ?>
            <img src="/TranCongTien_4894/<?php echo $product->image; ?>" alt="Ảnh đại diện" class="img-thumbnail mb-2" style="max-width: 200px;">
        <?php else: ?>
            <p class="text-muted">Chưa có ảnh đại diện</p>
        <?php endif; ?>
        <br>
        <label for="image">Chọn ảnh mới:</label>
        <input type="file" id="image" name="image" class="form-control-file" accept="image/*">
    </div>

    <div class="form-group mt-3 p-3 border rounded">
        <label>Các ảnh chi tiết hiện tại:</label><br>
        <div class="d-flex flex-wrap mb-2">
            <?php if (!empty($gallery)): ?>
                <?php foreach ($gallery as $img): ?>
                    <div class="me-2 mb-2">
                        <img src="/TranCongTien_4894/<?php echo $img->image_path; ?>" class="img-thumbnail" style="max-width: 100px; max-height: 100px; object-fit: cover;">
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted">Chưa có ảnh chi tiết</p>
            <?php endif; ?>
        </div>
        <label for="gallery">Thêm ảnh chi tiết mới:</label>
        <input type="file" id="gallery" name="gallery[]" class="form-control-file" accept="image/*" multiple>
    </div>

    <button type="submit" class="btn btn-primary mt-4">Lưu thay đổi</button>
</form>

<a href="/TranCongTien_4894/index.php?url=Product/index" class="btn btn-secondary mt-2">Quay lại danh sách</a>

<?php include 'app/views/shares/footer.php'; ?>