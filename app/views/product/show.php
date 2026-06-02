<?php include 'app/views/shares/header.php'; ?>

<style>
    .thumbnail-container {
        transition: all 0.2s ease-in-out;
        opacity: 0.6;
        background-color: #fff;
    }
    .thumbnail-container:hover {
        opacity: 1;
        border-color: #007bff !important;
    }
    .thumbnail-container.active {
        opacity: 1;
        border-color: #007bff !important;
        box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25) !important;
    }
    #btnRotate {
        transition: background-color 0.2s, opacity 0.2s;
        opacity: 0.8;
    }
    #btnRotate:hover {
        opacity: 1;
        background-color: #343a40 !important;
    }
</style>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="main-image text-center border p-2 rounded mb-3 shadow-sm bg-white position-relative" style="height: 420px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                <?php if (!empty($product->image)): ?>
                    <img id="mainProductImage" src="/TranCongTien_4894/<?php echo $product->image; ?>" class="img-fluid rounded" alt="Ảnh chính" style="max-height: 400px; object-fit: contain; transition: transform 0.3s cubic-bezier(0.25, 1, 0.5, 1), opacity 0.15s ease-in-out; transform-origin: center center;">
                <?php else: ?>
                    <div id="mainImagePlaceholder" class="p-5 text-muted bg-light rounded w-100" style="height: 100%; display: flex; align-items: center; justify-content: center;">Chưa có ảnh đại diện</div>
                <?php endif; ?>

                <button id="btnRotate" class="btn btn-dark btn-sm position-absolute" style="top: 10px; right: 10px; z-index: 10; display: <?php echo !empty($product->image) ? 'block' : 'none'; ?>;" title="Xoay ảnh 90 độ">
                    Xoay ảnh 🔄
                </button>
            </div>

            <div class="d-flex flex-wrap justify-content-center">
                <?php if (!empty($product->image)): ?>
                    <div class="border rounded mx-1 mb-2 p-1 shadow-sm thumbnail-container active" style="cursor: pointer; width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                        <img src="/TranCongTien_4894/<?php echo $product->image; ?>" class="gallery-thumbnail" alt="Ảnh chính" style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px;">
                    </div>
                <?php endif; ?>
                <?php if (!empty($gallery)): ?>
                    <?php foreach ($gallery as $img): ?>
                        <div class="border rounded mx-1 mb-2 p-1 shadow-sm thumbnail-container" style="cursor: pointer; width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                            <img src="/TranCongTien_4894/<?php echo $img->image_path; ?>" class="gallery-thumbnail" alt="Ảnh phụ" style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px;">
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
                <?php if (!SessionHelper::isAdmin()): ?>
                    <a href="/TranCongTien_4894/index.php?url=Cart/add/<?php echo $product->id; ?>" class="btn btn-primary btn-lg shadow-sm px-4">
                        Thêm vào giỏ hàng
                    </a>
                <?php endif; ?>
                <a href="/TranCongTien_4894/index.php?url=Product/list" class="btn btn-secondary btn-lg shadow-sm px-4 <?php echo !SessionHelper::isAdmin() ? 'mx-2' : ''; ?>">
                    Quay lại danh sách
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const thumbnails = document.querySelectorAll('.thumbnail-container');
    const placeholder = document.getElementById('mainImagePlaceholder');
    const btnRotate = document.getElementById('btnRotate');
    
    let currentRotation = 0;

    // Reset rotation and scale
    function resetRotation(img) {
        currentRotation = 0;
        if (img) {
            img.style.transform = 'none';
        }
    }

    // Function to rotate image and scale to fit container
    function rotateImage(img) {
        currentRotation = (currentRotation + 90) % 360;
        
        if (currentRotation === 90 || currentRotation === 270) {
            // When rotated 90 or 270 deg, height and width swap bounding spaces.
            // We scale it down so that the swapped dimensions fit inside container limits.
            const containerHeight = 400; // max-height of image
            const containerWidth = img.parentElement.offsetWidth - 20; // container padding width
            
            // Rendered size of the image before rotation
            const currentHeight = img.offsetHeight;
            const currentWidth = img.offsetWidth;
            
            const scaleHeight = containerHeight / currentWidth;
            const scaleWidth = containerWidth / currentHeight;
            
            const scale = Math.min(scaleHeight, scaleWidth, 1);
            img.style.transform = `rotate(${currentRotation}deg) scale(${scale})`;
        } else {
            img.style.transform = `rotate(${currentRotation}deg) scale(1)`;
        }
    }

    if (btnRotate) {
        btnRotate.addEventListener('click', function(e) {
            e.stopPropagation();
            const mainImg = document.getElementById('mainProductImage');
            if (mainImg) {
                rotateImage(mainImg);
            }
        });
    }

    thumbnails.forEach(function(thumb) {
        thumb.addEventListener('click', function() {
            // Remove active class from all
            thumbnails.forEach(t => t.classList.remove('active'));
            // Add active class to clicked
            this.classList.add('active');

            const newSrc = this.querySelector('img').src;
            const mainImg = document.getElementById('mainProductImage');

            if (mainImg) {
                resetRotation(mainImg);
                mainImg.style.opacity = 0;
                setTimeout(() => {
                    mainImg.src = newSrc;
                    mainImg.style.opacity = 1;
                }, 150);
            } else if (placeholder) {
                // If there was no main image originally, replace placeholder with img element
                const imgContainer = document.querySelector('.main-image');
                imgContainer.innerHTML = `<img id="mainProductImage" src="${newSrc}" class="img-fluid rounded" alt="Ảnh chính" style="max-height: 400px; object-fit: contain; transition: transform 0.3s cubic-bezier(0.25, 1, 0.5, 1), opacity 0.15s ease-in-out; transform-origin: center center;">`;
                
                if (btnRotate) {
                    btnRotate.style.display = 'block';
                }
            }
        });
    });
});
</script>

<?php include 'app/views/shares/footer.php'; ?>