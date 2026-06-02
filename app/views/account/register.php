<?php include 'app/views/shares/header.php'; ?>

<div class="row justify-content-center mt-5">
    <div class="col-md-5">
        <div class="card shadow-sm border rounded">
            <div class="card-header bg-primary text-white text-center py-3">
                <h4 class="mb-0 font-weight-bold">Đăng ký tài khoản</h4>
            </div>
            
            <div class="card-body p-4">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger shadow-xs">
                        <?php echo $errors; ?>
                    </div>
                <?php endif; ?>

                <form action="/TranCongTien_4894/index.php?url=Account/register" method="POST">
                    <div class="form-group">
                        <label class="font-weight-bold text-secondary">Tên đăng nhập:</label>
                        <input type="text" name="username" class="form-control mt-1" required placeholder="Nhập tên đăng nhập..." value="<?php echo htmlspecialchars($username ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="form-group mt-3">
                        <label class="font-weight-bold text-secondary">Mật khẩu (tối thiểu 6 ký tự):</label>
                        <input type="password" name="password" class="form-control mt-1" required placeholder="Nhập mật khẩu...">
                    </div>

                    <div class="form-group mt-3">
                        <label class="font-weight-bold text-secondary">Xác nhận mật khẩu:</label>
                        <input type="password" name="confirm_password" class="form-control mt-1" required placeholder="Xác nhận mật khẩu...">
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg mt-4 w-100 font-weight-bold shadow-xs">
                        Đăng ký ngay
                    </button>
                </form>
            </div>
            
            <div class="card-footer bg-light text-center py-3">
                <span class="text-muted">Đã có tài khoản?</span>
                <a href="/TranCongTien_4894/index.php?url=Account/login" class="text-primary font-weight-bold ml-1">Đăng nhập</a>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
