<?php include 'app/views/shares/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4 mt-3">
    <h1>Danh sách sản phẩm</h1>
    <a href="/TranCongTien_4894/index.php?url=Product/add" class="btn btn-success">Thêm sản phẩm mới</a>
</div>

<ul class="list-group">
    
    <?php foreach ($products as $product): ?>
        
        <li class="list-group-item mb-3 shadow-sm rounded border">
            
            <h2 class="text-primary mb-1"> 
                <a href="/TranCongTien_4894/index.php?url=Product/show/<?php echo $product->id; ?>" class="text-decoration-none">
                    <?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>
                </a>
            </h2>
            
            <div class="text-danger font-weight-bold mb-2">
                Giá: <?php echo number_format($product->price); ?> VNĐ
            </div>

            <div class="product-image mt-2 mb-3">
                <?php if (!empty($product->image)): ?>
                    <img src="/TranCongTien_4894/<?php echo $product->image; ?>" alt="Ảnh đại diện" class="img-thumbnail" style="max-width: 150px; height: auto;">
                <?php else: ?>
                    <div class="border p-4 text-center text-muted rounded" style="width: 150px; background: #f8f9fa;">
                        No Image
                    </div>
                <?php endif; ?>
            </div>

            <p class="text-dark"><?php echo nl2br(htmlspecialchars($product->description, ENT_QUOTES, 'UTF-8')); ?></p>
            
            <div class="mt-3">
    <a href="/TranCongTien_4894/index.php?url=Product/show/<?php echo $product->id; ?>" class="btn btn-info text-white">Xem</a>
    
    <a href="/TranCongTien_4894/index.php?url=Cart/add/<?php echo $product->id; ?>" class="btn btn-primary mx-1">Thêm vào giỏ</a>
    <a href="/TranCongTien_4894/index.php?url=Product/edit/<?php echo $product->id; ?>" class="btn btn-warning">Sửa</a>
    <a href="/TranCongTien_4894/index.php?url=Product/delete/<?php echo $product->id; ?>" class="btn btn-danger mx-1" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');">Xóa</a>
</div>
            
        </li> 
        <?php endforeach; ?>
    
</ul> 
<?php include 'app/views/shares/footer.php'; ?>