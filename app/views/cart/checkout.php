<?php include 'app/views/shares/header.php'; ?>

<h1>Thông tin Thanh toán</h1>
<div class="row mt-4">
    <div class="col-md-6">
        <form action="/TranCongTien_4894/index.php?url=Cart/processCheckout" method="POST" class="border p-4 rounded shadow-sm">
            <div class="form-group">
                <label class="font-weight-bold">Họ và tên người nhận:</label>
                <input type="text" name="customer_name" class="form-control mt-1" required placeholder="Nhập họ và tên...">
            </div>
            
            <div class="form-group mt-3">
                <label class="font-weight-bold">Số điện thoại liên hệ:</label>
                <input type="text" name="customer_phone" class="form-control mt-1" required placeholder="Nhập số điện thoại...">
            </div>
            
            <button type="submit" class="btn btn-success btn-lg btn-block mt-4 w-100">
                Xác nhận Đặt hàng & Thanh toán tự động
            </button>
        </form>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>