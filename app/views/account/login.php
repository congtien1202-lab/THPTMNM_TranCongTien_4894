<?php include 'app/views/shares/header.php'; ?>

<div class="row justify-content-center mt-5">
    <div class="col-md-5">
        <div class="card shadow-sm border rounded">
            <div class="card-header bg-success text-white text-center py-3">
                <h4 class="mb-0 font-weight-bold">Đăng nhập hệ thống</h4>
            </div>

            <div class="card-body p-4">
                <?php
                // Hiển thị thông báo đăng ký thành công nếu có
                if (isset($_SESSION['register_success'])):
                    ?>
                    <div class="alert alert-success shadow-xs">
                        <?php
                        echo $_SESSION['register_success'];
                        unset($_SESSION['register_success']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger shadow-xs">
                        <?php echo $errors; ?>
                    </div>
                <?php endif; ?>

                <form action="/TranCongTien_4894/index.php?url=Account/login" method="POST">
                    <div class="form-group">
                        <label class="font-weight-bold text-secondary">Tên đăng nhập:</label>
                        <input type="text" name="username" class="form-control mt-1" required
                            placeholder="Nhập tên đăng nhập..."
                            value="<?php echo htmlspecialchars($username ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="form-group mt-3">
                        <label class="font-weight-bold text-secondary">Mật khẩu:</label>
                        <input type="password" name="password" class="form-control mt-1" required
                            placeholder="Nhập mật khẩu...">
                    </div>

                    <button type="submit"
                        class="btn btn-success btn-block btn-lg mt-4 w-100 font-weight-bold shadow-xs">
                        Đăng nhập
                    </button>

                    <div class="text-center my-3 text-muted font-weight-bold"
                        style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;">Hoặc</div>

                    <a href="/TranCongTien_4894/index.php?url=Account/googleLogin"
                        class="btn btn-outline-danger btn-block btn-lg w-100 font-weight-bold shadow-xs d-flex align-items-center justify-content-center"
                        style="border-width: 2px;">
                        <svg class="mr-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 48 48"
                            style="margin-right: 8px;">
                            <path fill="#EA4335"
                                d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z" />
                            <path fill="#4285F4"
                                d="M46.5 24c0-1.61-.15-3.16-.42-4.67H24v8.87h12.71c-.55 2.87-2.17 5.3-4.61 6.93l7.2 5.58C43.51 36.4 46.5 30.76 46.5 24z" />
                            <path fill="#FBBC05"
                                d="M10.54 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.98-6.19z" />
                            <path fill="#34A853"
                                d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.2-5.58c-2.11 1.41-4.8 2.25-8.69 2.25-6.26 0-11.57-4.22-13.46-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z" />
                        </svg>
                        Đăng nhập bằng Google
                    </a>

                    <a href="/TranCongTien_4894/index.php?url=Account/githubLogin"
                        class="btn btn-outline-dark btn-block btn-lg w-100 font-weight-bold shadow-xs d-flex align-items-center justify-content-center mt-2"
                        style="border-width: 2px;">
                        <svg class="mr-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="currentColor" style="margin-right: 8px;">
                            <path
                                d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z" />
                        </svg>
                        Đăng nhập bằng GitHub
                    </a>
                </form>
            </div>

            <div class="card-footer bg-light text-center py-3">
                <span class="text-muted">Chưa có tài khoản?</span>
                <a href="/TranCongTien_4894/index.php?url=Account/register"
                    class="text-success font-weight-bold ml-1">Đăng ký ngay</a>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>