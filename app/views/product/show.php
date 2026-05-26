<?php include 'app/views/shares/header.php'; ?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="main-image text-center border p-2 rounded mb-3 shadow-sm">
                <?php if (!empty($product->image)): ?>
                    <img src="/TranCongTien_4894/<?php echo $product->image; ?>" class="img-fluid rounded" alt="Ảnh chính" style="max-height: 400px;">
                <?php else: ?>
                    <div class="p-5 text-muted bg-light rounded" style="height: 300px; line-height: 250px;">Chưa có ảnh đại diện</div>
                <?php endif; ?>
            </div>

            <div class="d-flex flex-wrap justify-content-center">
                <?php if (!empty($gallery)): ?>
                    <?php foreach ($gallery as $img): ?>
                        <div class="border rounded mx-1 mb-2 p-1 shadow-sm">
                            <img src="/TranCongTien_4894/<?php echo $img->image_path; ?>" alt="Ảnh phụ" style="width: 80px; height: 80px; object-fit: cover; border-radius: 4px;">
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-6">
            <h1 class="display-5 text-primary mb-3">
                <?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>
            </h1>
            
            <h2 class="text-danger font-weight-bold mb-4">
                <?php echo number_format($product->price); ?> VNĐ
            </h2>
            
            <div class="product-description p-3 bg-light rounded border mb-4">
                <h5 class="text-secondary">Chi tiết sản phẩm:</h5>
                <p class="text-dark mb-0" style="font-size: 1.1rem; line-height: 1.6;">
                    <?php echo nl2br(htmlspecialchars($product->description, ENT_QUOTES, 'UTF-8')); ?>
                </p>
            </div>

            <div class="mt-4">
                <a href="/TranCongTien_4894/index.php?url=Cart/add/<?php echo $product->id; ?>" class="btn btn-primary btn-lg shadow-sm px-4">
                    Thêm vào giỏ hàng
                </a>
                <a href="/TranCongTien_4894/index.php?url=Product/list" class="btn btn-secondary btn-lg shadow-sm px-4 mx-2">
                    Quay lại danh sách
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>