<?php include 'app/views/shares/header.php'; ?>

<h1>Thêm sản phẩm mới</h1>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <ul>
    <?php foreach ($errors as $error): ?>
        <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
    <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form action="/TranCongTien_4894/index.php?url=Product/save" method="POST" enctype="multipart/form-data">
    <div class="form-group">
        <label for="name">Tên sản phẩm:</label>
        <input type="text" id="name" name="name" class="form-control" required>
    </div>
    
    <div class="form-group mt-2">
        <label for="description">Mô tả:</label>
        <textarea id="description" name="description" class="form-control" required></textarea>
    </div>
    
    <div class="form-group mt-2">
        <label for="price">Giá:</label>
        <input type="number" id="price" name="price" class="form-control" step="0.01" required>
    </div>
    
    <div class="form-group mt-2">
        <label for="category_id">Danh mục:</label>
        <select id="category_id" name="category_id" class="form-control" required>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category->id; ?>">
                    <?php echo htmlspecialchars($category->name, ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group mt-3 border p-3 rounded">
        <label for="image" class="font-weight-bold">Ảnh đại diện:</label>
        <input type="file" id="image" name="image" class="form-control-file mt-2" accept="image/*">
    </div>

    <div class="form-group mt-3 border p-3 rounded">
        <label for="gallery" class="font-weight-bold">Các ảnh chi tiết:</label>
        <input type="file" id="gallery" name="gallery[]" class="form-control-file mt-2" accept="image/*" multiple>
    </div>

    <button type="submit" class="btn btn-primary mt-4">Thêm sản phẩm</button>
</form>

<a href="/TranCongTien_4894/index.php?url=Product/list" class="btn btn-secondary mt-3">Quay lại danh sách sản phẩm</a>

<?php include 'app/views/shares/footer.php'; ?>