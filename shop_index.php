<?php 
session_start(); 
require 'db.php'; 

// --- PHẦN 1: XỬ LÝ LOGIC LỌC SẢN PHẨM ---

// Khởi tạo câu truy vấn cơ bản
$sql = "SELECT * FROM sanpham WHERE 1=1";
$params = [];
$types = "";

// 1. Lọc theo Từ khóa (Tên sản phẩm)
$keyword = $_GET['keyword'] ?? '';
if (!empty($keyword)) {
    $sql .= " AND TenSanPham LIKE ?";
    $types .= "s";
    $params[] = "%" . $keyword . "%";
}

// 2. Lọc theo Danh mục
$dm = $_GET['dm'] ?? '';
if (!empty($dm)) {
    $sql .= " AND MaDanhMuc = ?";
    $types .= "i";
    $params[] = $dm;
}

// 3. Lọc theo Giá tiền
$price = $_GET['price'] ?? '';
if (!empty($price)) {
    switch ($price) {
        case 'duoi-5tr':
            $sql .= " AND DonGia < 5000000";
            break;
        case '5tr-10tr':
            $sql .= " AND DonGia BETWEEN 5000000 AND 10000000";
            break;
        case '10tr-20tr':
            $sql .= " AND DonGia BETWEEN 10000000 AND 20000000";
            break;
        case 'tren-20tr':
            $sql .= " AND DonGia > 20000000";
            break;
    }
}

// Sắp xếp giảm dần theo ID (Mới nhất lên đầu)
$sql .= " ORDER BY MaSanPham DESC";

// Chuẩn bị truy vấn (Prepared Statement để bảo mật)
$stmt = $conn->prepare($sql);
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$rs = $stmt->get_result();

// --- LẤY DANH SÁCH DANH MỤC (Để đổ vào Dropdown lọc) ---
$rsDM = $conn->query("SELECT * FROM danhmuc");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tech Store - Mua Sắm</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }
        .navbar { background: #2c3e50; }
        
        /* Filter Box */
        .filter-box {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        /* Product Card */
        .product-card {
            border: none; border-radius: 15px; transition: all 0.3s ease;
            background: white; overflow: hidden; height: 100%;
        }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .card-img-wrapper {
            height: 220px; display: flex; align-items: center; justify-content: center;
            padding: 20px; background: #fff; position: relative; overflow: hidden;
        }
        .card-img-top { max-height: 100%; max-width: 100%; object-fit: contain; transition: transform 0.3s; }
        .product-card:hover .card-img-top { transform: scale(1.08); }
        .price-tag { color: #d63384; font-weight: 700; font-size: 1.1rem; }
        
        /* Buttons */
        .btn-add {
            border-radius: 50px; background: #f1f3f5; color: #2c3e50; border: none;
            width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; transition: 0.3s;
        }
        .btn-add:hover { background: #4e73df; color: white; }
        
        .cart-float {
            position: fixed; bottom: 30px; right: 30px;
            background: linear-gradient(135deg, #ff6b6b, #ee5253);
            color: white; width: 60px; height: 60px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; box-shadow: 0 8px 20px rgba(238, 82, 83, 0.4);
            z-index: 999; text-decoration: none; transition: transform 0.2s;
        }
        .cart-float:hover { transform: scale(1.1); color: white; }
        .cart-count {
            position: absolute; top: -2px; right: -2px;
            background: white; color: #ee5253; font-size: 12px; font-weight: bold;
            width: 24px; height: 24px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; border: 2px solid #ee5253;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark sticky-top shadow-sm py-3">
        <div class="container">
            <a class="navbar-brand fw-bold text-uppercase d-flex align-items-center gap-2" href="shop_index.php">
                <i class="fas fa-microchip text-warning fs-3"></i> Tech Store
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <div class="d-flex align-items-center gap-3 mt-3 mt-lg-0">
                    <a href="shop_cart.php" class="btn btn-outline-light position-relative rounded-pill px-4">
                        <i class="fas fa-shopping-cart"></i> Giỏ hàng
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cartBadge">
                            <?= isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0 ?>
                        </span>
                    </a>
                    <?php if(isset($_SESSION['user'])): ?>
                        <div class="dropdown">
                            <button class="btn btn-warning dropdown-toggle rounded-pill px-4 fw-bold text-dark" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i> <?= $_SESSION['user']['HoTen'] ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 rounded-4">
                                <?php if($_SESSION['user']['VaiTro'] == 1): ?>
                                    <li><a class="dropdown-item py-2 fw-bold text-danger" href="index.html"><i class="fas fa-user-shield me-2"></i> Trang Quản Trị</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item py-2" href="shop_history.php"><i class="fas fa-history text-primary me-2"></i> Lịch sử đơn hàng</a></li>
                                <li><a class="dropdown-item py-2 text-danger" href="shop_api.php?action=logout"><i class="fas fa-sign-out-alt me-2"></i> Đăng xuất</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-light rounded-pill px-4 fw-bold text-primary">Đăng Nhập</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="p-5 mb-4 bg-primary rounded-4 text-white shadow position-relative overflow-hidden" 
             style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
            <div class="position-relative z-1 py-3">
                <h1 class="display-5 fw-bold">Công Nghệ Tương Lai</h1>
                <p class="col-md-8 fs-5 mb-4 opacity-75">Săn ngay các thiết bị công nghệ mới nhất.</p>
            </div>
            <i class="fas fa-laptop position-absolute text-white opacity-10" style="font-size: 300px; right: -50px; bottom: -50px; transform: rotate(-20deg);"></i>
        </div>
    </div>

    <div class="container mb-5" id="listProduct">
        
        <div class="filter-box">
            <form action="shop_index.php" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted">Tên sản phẩm</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="keyword" class="form-control border-start-0 bg-light" placeholder="Nhập tên..." value="<?= htmlspecialchars($keyword) ?>">
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">Danh mục</label>
                    <select name="dm" class="form-select bg-light">
                        <option value="">-- Tất cả --</option>
                        <?php while ($cat = $rsDM->fetch_assoc()): ?>
                            <option value="<?= $cat['MaDanhMuc'] ?>" <?= ($dm == $cat['MaDanhMuc']) ? 'selected' : '' ?>>
                                <?= $cat['TenDanhMuc'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">Mức giá</label>
                    <select name="price" class="form-select bg-light">
                        <option value="">-- Tất cả mức giá --</option>
                        <option value="duoi-5tr" <?= ($price == 'duoi-5tr') ? 'selected' : '' ?>>Dưới 5 triệu</option>
                        <option value="5tr-10tr" <?= ($price == '5tr-10tr') ? 'selected' : '' ?>>5 - 10 triệu</option>
                        <option value="10tr-20tr" <?= ($price == '10tr-20tr') ? 'selected' : '' ?>>10 - 20 triệu</option>
                        <option value="tren-20tr" <?= ($price == 'tren-20tr') ? 'selected' : '' ?>>Trên 20 triệu</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100 fw-bold"><i class="fas fa-filter"></i> Lọc Ngay</button>
                </div>
            </form>
        </div>

        <div class="d-flex align-items-center justify-content-between mb-4">
            <h3 class="fw-bold text-secondary text-uppercase border-start border-4 border-warning ps-3 m-0">
                <?php if(!empty($keyword) || !empty($dm) || !empty($price)): ?>
                    Kết quả tìm kiếm
                <?php else: ?>
                    Sản phẩm nổi bật
                <?php endif; ?>
            </h3>
            <?php if(!empty($keyword) || !empty($dm) || !empty($price)): ?>
                <a href="shop_index.php" class="btn btn-sm btn-outline-secondary rounded-pill">Xóa bộ lọc</a>
            <?php endif; ?>
        </div>
        
        <div class="row g-4">
            <?php
            if ($rs->num_rows > 0):
                while ($row = $rs->fetch_assoc()) {
                    $hinh = $row['HinhAnh'] ? "uploads/".$row['HinhAnh'] : "https://via.placeholder.com/300x300?text=No+Image";
                    $linkDetail = "shop_detail.php?id=" . $row['MaSanPham'];
            ?>
            <div class="col-lg-3 col-md-4 col-6">
                <div class="card product-card h-100 shadow-sm">
                    <a href="<?= $linkDetail ?>" class="card-img-wrapper text-decoration-none">
                        <img src="<?= $hinh ?>" class="card-img-top" alt="<?= $row['TenSanPham'] ?>">
                    </a>
                    <div class="card-body d-flex flex-column pt-3">
                        <a href="<?= $linkDetail ?>" class="text-decoration-none text-dark">
                            <h6 class="card-title fw-bold text-truncate mb-1" title="<?= $row['TenSanPham'] ?>">
                                <?= $row['TenSanPham'] ?>
                            </h6>
                        </a>
                        <small class="text-muted mb-3">Chính hãng</small>
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <span class="price-tag"><?= number_format($row['DonGia'], 0, ',', '.') ?>đ</span>
                            <button class="btn btn-add btnAddToCart" 
                                data-id="<?= $row['MaSanPham'] ?>"
                                data-ten="<?= $row['TenSanPham'] ?>"
                                data-gia="<?= $row['DonGia'] ?>"
                                data-hinh="<?= $row['HinhAnh'] ?>">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php 
                } 
            else: ?>
                <div class="col-12 text-center py-5">
                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="80" class="mb-3 opacity-50">
                    <h5 class="text-muted">Không tìm thấy sản phẩm nào!</h5>
                    <p class="text-muted small">Vui lòng thử lại với từ khóa hoặc bộ lọc khác.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <a href="shop_cart.php" class="cart-float">
        <i class="fas fa-shopping-bag"></i>
        <span class="cart-count" id="floatCount"><?= isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0 ?></span>
    </a>

    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p class="mb-0 fw-bold">TECH STORE &copy; 2024</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    $(document).ready(function() {
        $(".btnAddToCart").click(function() {
            let btn = $(this);
            let icon = btn.find('i');
            icon.removeClass('fa-cart-plus').addClass('fa-spinner fa-spin');

            $.post("shop_api.php", {
                action: 'add_to_cart',
                id: btn.data('id'),
                ten: btn.data('ten'),
                gia: btn.data('gia'),
                hinh: btn.data('hinh')
            }, function(data) {
                setTimeout(function() {
                    $("#cartBadge").text(data);
                    $("#floatCount").text(data);
                    icon.removeClass('fa-spinner fa-spin').addClass('fa-check');
                    btn.addClass('bg-success text-white'); 
                    setTimeout(function(){ 
                        icon.removeClass('fa-check').addClass('fa-cart-plus');
                        btn.removeClass('bg-success text-white');
                    }, 1500);
                }, 300);
            });
        });
    });
    </script>
</body>
</html>