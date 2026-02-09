<?php 
require 'db.php'; 
session_start(); 

$id = $_GET['id'] ?? 0;

// 1. TRUY VẤN SẢN PHẨM HIỆN TẠI
$stmt = $conn->prepare("SELECT * FROM sanpham WHERE MaSanPham = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if(!$product) { header("Location: shop_index.php"); exit; }

$hinh = $product['HinhAnh'] ? "uploads/".$product['HinhAnh'] : "https://via.placeholder.com/500x500?text=No+Image";

// 2. TÍNH ĐIỂM ĐÁNH GIÁ TRUNG BÌNH
$sqlRating = "SELECT AVG(SoSao) as DTB, COUNT(*) as SL FROM danhgia WHERE MaSanPham = $id";
$rsRating = $conn->query($sqlRating)->fetch_assoc();
$diemTrungBinh = round($rsRating['DTB'], 1); 
$soLuotDanhGia = $rsRating['SL'];

// Hàm hiển thị ngôi sao
function showStars($rating) {
    $stars = "";
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) $stars .= '<i class="fas fa-star text-warning"></i>'; 
        else if ($i - 0.5 <= $rating) $stars .= '<i class="fas fa-star-half-alt text-warning"></i>'; 
        else $stars .= '<i class="far fa-star text-secondary opacity-25"></i>'; 
    }
    return $stars;
}

// 3. LẤY SẢN PHẨM TƯƠNG TỰ (Cùng danh mục, khác ID này)
$catId = $product['MaDanhMuc'];
$stmtRelated = $conn->prepare("SELECT * FROM sanpham WHERE MaDanhMuc = ? AND MaSanPham != ? ORDER BY RAND() LIMIT 4");
$stmtRelated->bind_param("ii", $catId, $id);
$stmtRelated->execute();
$relatedProducts = $stmtRelated->get_result();

// 4. LẤY DANH SÁCH BÌNH LUẬN
$sqlReviews = "SELECT d.*, u.HoTen FROM danhgia d JOIN nguoidung u ON d.MaNguoiDung = u.MaNguoiDung WHERE d.MaSanPham = $id ORDER BY d.NgayDanhGia DESC";
$listReviews = $conn->query($sqlReviews);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?= $product['TenSanPham'] ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }
        .detail-img { width: 100%; max-height: 450px; object-fit: contain; }
        .price-text { color: #d63384; font-size: 2rem; font-weight: 700; }
        
        /* Star Rating Style */
        .rating-stars { direction: rtl; unicode-bidi: bidi-override; display: inline-block; }
        .rating-stars input { display: none; }
        .rating-stars label { color: #ddd; font-size: 2rem; padding: 0 5px; cursor: pointer; transition: 0.2s; }
        .rating-stars label:hover,
        .rating-stars label:hover ~ label,
        .rating-stars input:checked ~ label { color: #ffc107; }

        /* Product Card Style (Cho phần tương tự) */
        .product-card { border: none; border-radius: 15px; background: white; transition: 0.3s; overflow: hidden; height: 100%; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .card-img-wrapper { height: 180px; display: flex; align-items: center; justify-content: center; padding: 15px; position: relative; }
        .card-img-top { max-height: 100%; max-width: 100%; object-fit: contain; }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="shop_index.php"><i class="fas fa-arrow-left text-primary"></i> Quay lại</a>
            <a href="shop_cart.php" class="btn btn-outline-danger position-relative rounded-pill px-4">
                <i class="fas fa-shopping-cart"></i> Giỏ hàng
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cartBadge">
                    <?= isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0 ?>
                </span>
            </a>
        </div>
    </nav>

    <div class="container py-5">
        
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-5">
            <div class="row g-0">
                <div class="col-md-6 p-4 d-flex align-items-center justify-content-center bg-white border-end">
                    <img src="<?= $hinh ?>" class="detail-img">
                </div>
                
                <div class="col-md-6 p-5">
                    <small class="text-uppercase text-muted fw-bold">Chi tiết sản phẩm</small>
                    <h2 class="fw-bold mt-2 mb-1"><?= $product['TenSanPham'] ?></h2>
                    
                    <div class="mb-3">
                        <?= showStars($diemTrungBinh) ?>
                        <span class="text-muted ms-2 small">(<?= $soLuotDanhGia ?> đánh giá)</span>
                    </div>

                    <div class="price-text mb-4"><?= number_format($product['DonGia'], 0, ',', '.') ?> ₫</div>

                    <div class="mb-4">
                        <?php if($product['SoLuongTon'] > 0): ?>
                            <span class="badge bg-success p-2">Còn hàng (<?= $product['SoLuongTon'] ?>)</span>
                        <?php else: ?>
                            <span class="badge bg-secondary p-2">Hết hàng</span>
                        <?php endif; ?>
                    </div>

                    <div class="mb-5">
                        <h6 class="fw-bold">Mô tả:</h6>
                        <p class="text-muted" style="text-align: justify;"><?= nl2br($product['MoTa']) ?></p>
                    </div>

                    <button class="btn btn-primary btn-lg w-100 rounded-pill py-3 fw-bold btnAddToCart" 
                        data-id="<?= $product['MaSanPham'] ?>"
                        data-ten="<?= $product['TenSanPham'] ?>"
                        data-gia="<?= $product['DonGia'] ?>"
                        data-hinh="<?= $product['HinhAnh'] ?>">
                        <i class="fas fa-cart-plus me-2"></i> THÊM VÀO GIỎ HÀNG
                    </button>
                </div>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-md-8 mx-auto">
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h4 class="fw-bold mb-4 border-start border-4 border-warning ps-3">Đánh giá khách hàng</h4>

                    <?php if(isset($_SESSION['user'])): ?>
                    <div class="bg-light p-4 rounded-3 mb-4">
                        <h6 class="fw-bold">Viết đánh giá của bạn</h6>
                        <form id="formReview">
                            <input type="hidden" name="maSP" value="<?= $id ?>">
                            <input type="hidden" name="action" value="submit_review">
                            
                            <div class="rating-stars mb-3">
                                <input type="radio" name="soSao" id="star5" value="5" checked><label for="star5">★</label>
                                <input type="radio" name="soSao" id="star4" value="4"><label for="star4">★</label>
                                <input type="radio" name="soSao" id="star3" value="3"><label for="star3">★</label>
                                <input type="radio" name="soSao" id="star2" value="2"><label for="star2">★</label>
                                <input type="radio" name="soSao" id="star1" value="1"><label for="star1">★</label>
                            </div>

                            <div class="mb-3">
                                <textarea name="noiDung" class="form-control" rows="3" placeholder="Chia sẻ cảm nhận..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-warning fw-bold"><i class="fas fa-paper-plane"></i> Gửi</button>
                        </form>
                    </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">
                            Vui lòng <a href="login.php" class="fw-bold text-decoration-none">Đăng nhập</a> để viết đánh giá.
                        </div>
                    <?php endif; ?>

                    <div class="review-list">
                        <?php if ($listReviews->num_rows > 0): ?>
                            <?php while($rv = $listReviews->fetch_assoc()): ?>
                                <div class="d-flex mb-4 border-bottom pb-3">
                                    <div class="flex-shrink-0">
                                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($rv['HoTen']) ?>&background=random" class="rounded-circle" width="50">
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="fw-bold mb-0"><?= $rv['HoTen'] ?></h6>
                                        <div class="mb-1">
                                            <?= showStars($rv['SoSao']) ?>
                                            <small class="text-muted ms-2"><?= date('d/m/Y', strtotime($rv['NgayDanhGia'])) ?></small>
                                        </div>
                                        <p class="text-secondary mb-0"><?= nl2br(htmlspecialchars($rv['NoiDung'])) ?></p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-center text-muted py-3">Chưa có đánh giá nào.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if ($relatedProducts->num_rows > 0): ?>
        <div class="related-section">
            <h4 class="fw-bold text-uppercase mb-4 border-start border-4 border-primary ps-3">Sản phẩm tương tự</h4>
            <div class="row g-4">
                <?php while ($row = $relatedProducts->fetch_assoc()): 
                    $imgRelated = $row['HinhAnh'] ? "uploads/".$row['HinhAnh'] : "https://via.placeholder.com/300x300";
                    $linkRelated = "shop_detail.php?id=" . $row['MaSanPham'];
                ?>
                <div class="col-lg-3 col-md-4 col-6">
                    <div class="card product-card h-100 shadow-sm">
                        <a href="<?= $linkRelated ?>" class="card-img-wrapper text-decoration-none">
                            <img src="<?= $imgRelated ?>" class="card-img-top">
                        </a>
                        <div class="card-body d-flex flex-column pt-2">
                            <a href="<?= $linkRelated ?>" class="text-decoration-none text-dark">
                                <h6 class="card-title fw-bold text-truncate mb-1"><?= $row['TenSanPham'] ?></h6>
                            </a>
                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <span class="text-danger fw-bold"><?= number_format($row['DonGia'], 0, ',', '.') ?>đ</span>
                                <button class="btn btn-sm btn-light rounded-circle border btnAddToCart" 
                                    data-id="<?= $row['MaSanPham'] ?>"
                                    data-ten="<?= $row['TenSanPham'] ?>"
                                    data-gia="<?= $row['DonGia'] ?>"
                                    data-hinh="<?= $row['HinhAnh'] ?>">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script>
    $(document).ready(function() {
        // Xử lý nút Thêm giỏ hàng (Dùng chung cho cả trang)
        $(".btnAddToCart").click(function() {
            let btn = $(this);
            let oldHtml = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin"></i>');
            
            $.post("shop_api.php", {
                action: 'add_to_cart',
                id: btn.data('id'),
                ten: btn.data('ten'),
                gia: btn.data('gia'),
                hinh: btn.data('hinh')
            }, function(data) {
                $("#cartBadge").text(data);
                btn.html('<i class="fas fa-check"></i> Đã thêm');
                setTimeout(() => { btn.html(oldHtml); }, 1500);
            });
        });

        // Xử lý form đánh giá
        $("#formReview").submit(function(e){
            e.preventDefault();
            $.post("shop_api.php", $(this).serialize(), function(res){
                if(res.trim() == "success"){
                    alert("Cảm ơn bạn đã đánh giá!");
                    location.reload();
                } else {
                    alert(res);
                }
            });
        });
    });
    </script>
</body>
</html>