<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý sản phẩm</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
      <a class="navbar-brand font-weight-bold" href="/TranCongTien_4894/index.php?url=Product/list">Tách Tách Store</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mr-auto">
          <li class="nav-item">
            <a class="nav-link" href="/TranCongTien_4894/index.php?url=Product/list">Sản phẩm</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/TranCongTien_4894/index.php?url=Cart/index">Giỏ hàng</a>
          </li>
        </ul>

        <ul class="navbar-nav ml-auto align-items-center">
          <?php if (SessionHelper::isLoggedIn()): ?>
            <li class="nav-item mr-3">
              <span class="navbar-text text-light">
                Xin chào, <strong
                  class="text-warning"><?php echo htmlspecialchars(SessionHelper::getUsername(), ENT_QUOTES, 'UTF-8'); ?></strong>!
              </span>
            </li>
            <li class="nav-item">
              <a class="btn btn-outline-danger btn-sm" href="/TranCongTien_4894/index.php?url=Account/logout">Đăng
                xuất</a>
            </li>
          <?php else: ?>
            <li class="nav-item mr-2">
              <a class="btn btn-outline-light btn-sm px-3" href="/TranCongTien_4894/index.php?url=Account/login">Đăng
                nhập</a>
            </li>
            <li class="nav-item">
              <a class="btn btn-primary btn-sm px-3" href="/TranCongTien_4894/index.php?url=Account/register">Đăng ký</a>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>
  <div class="container mt-4">