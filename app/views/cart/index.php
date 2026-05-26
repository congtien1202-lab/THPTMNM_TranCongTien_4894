<?php include 'app/views/shares/header.php'; ?>

<h1>Giỏ hàng của bạn</h1>

<?php if (empty($cartItems)): ?>
    <div class="alert alert-info mt-3">Giỏ hàng đang trống.</div>
    <a href="/TranCongTien_4894/index.php?url=Product/list" class="btn btn-primary">Tiếp tục mua sắm</a>
<?php else: ?>
    <table class="table table-bordered mt-3">
        <thead class="thead-light">
            <tr>
                <th>Ảnh</th>
                <th>Tên sản phẩm</th>
                <th>Giá</th>
                <th>Số lượng</th>
                <th>Thành tiền</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cartItems as $item): ?>
                <tr>
                    <td>
                        <?php if (!empty($item['product']->image)): ?>
                            <img src="/TranCongTien_4894/<?php echo $item['product']->image; ?>" alt="Ảnh" style="width: 50px;">
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($item['product']->name, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo number_format($item['product']->price); ?> VNĐ</td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><?php echo number_format($item['subtotal']); ?> VNĐ</td>
                    <td>
                        <a href="/TranCongTien_4894/index.php?url=Cart/remove/<?php echo $item['product']->id; ?>" class="btn btn-sm btn-danger">Xóa</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="text-right">
        <h3>Tổng tiền: <span class="text-danger"><?php echo number_format($totalAmount); ?> VNĐ</span></h3>
        <a href="/TranCongTien_4894/index.php?url=Product/list" class="btn btn-secondary">Tiếp tục mua sắm</a>
        <a href="/TranCongTien_4894/index.php?url=Cart/checkout" class="btn btn-success">Tiến hành Thanh toán</a>
    </div>
<?php endif; ?>

<?php include 'app/views/shares/footer.php'; ?>