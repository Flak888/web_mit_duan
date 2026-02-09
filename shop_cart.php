<?php session_start(); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Giỏ Hàng Của Bạn</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }
        .rounded-4 { border-radius: 20px !important; }
        .table img { object-fit: cover; border-radius: 10px; }
        .qty-input { width: 60px; border-radius: 10px; border: 1px solid #ced4da; }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-light bg-white shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold text-uppercase" href="shop_index.php">
                <i class="fas fa-arrow-left text-primary"></i> Tiếp tục mua hàng
            </a>
            <span class="fw-bold fs-5 text-primary">GIỎ HÀNG</span>
        </div>
    </nav>

    <div class="container mb-5">
        <div class="row g-4">
            
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <?php if (empty($_SESSION['cart'])): ?>
                            <div class="text-center py-5">
                                <img src="https://cdn-icons-png.flaticon.com/512/11329/11329060.png" width="120" class="mb-3 opacity-50">
                                <h5 class="text-muted fw-bold">Giỏ hàng của bạn đang trống!</h5>
                                <p class="text-muted mb-4">Hãy chọn thêm sản phẩm để mua sắm nhé.</p>
                                <a href="shop_index.php" class="btn btn-primary rounded-pill px-4 py-2 fw-bold">Quay lại cửa hàng</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Sản phẩm</th>
                                            <th class="text-end">Giá</th>
                                            <th class="text-center" width="120">Số lượng</th>
                                            <th class="text-end">Thành tiền</th>
                                            <th class="text-center">Xóa</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $tongTien = 0;
                                        foreach ($_SESSION['cart'] as $item): 
                                            $thanhTien = $item['gia'] * $item['qty'];
                                            $tongTien += $thanhTien;
                                            $hinh = $item['hinh'] ? "uploads/".$item['hinh'] : "https://via.placeholder.com/60";
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?= $hinh ?>" width="60" height="60" class="me-3 border">
                                                    <div>
                                                        <h6 class="mb-0 fw-bold text-dark"><?= $item['ten'] ?></h6>
                                                        <small class="text-muted">ID: #<?= $item['id'] ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-end fw-bold text-secondary">
                                                <?= number_format($item['gia'], 0, ',', '.') ?>
                                            </td>
                                            <td class="text-center">
                                                <input type="number" class="form-control text-center qty-input mx-auto" 
                                                    value="<?= $item['qty'] ?>" min="1" data-id="<?= $item['id'] ?>">
                                            </td>
                                            <td class="text-end fw-bold text-primary">
                                                <?= number_format($thanhTien, 0, ',', '.') ?>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-light text-danger rounded-circle btnRemove" data-id="<?= $item['id'] ?>">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 bg-white sticky-top" style="top: 20px;">
                    <div class="card-header bg-white border-0 pt-4 pb-0">
                        <h5 class="fw-bold mb-0 text-uppercase"><i class="fas fa-receipt me-2"></i>Thanh Toán</h5>
                    </div>
                    <div class="card-body p-4">
                        
                        <?php if(isset($_SESSION['user'])): 
                            $u = $_SESSION['user']; 
                        ?>
                            <div class="alert alert-success d-flex align-items-center p-2 rounded-3 mb-3">
                                <i class="fas fa-check-circle fs-4 me-2"></i>
                                <div><small>Đang đặt hàng với tư cách:</small><br><strong><?= $u['HoTen'] ?></strong></div>
                            </div>

                            <div class="mb-3">
                                <label class="fw-bold small text-muted">Người nhận hàng</label>
                                <input type="text" id="hoten" class="form-control rounded-3" value="<?= $u['HoTen'] ?>">
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold small text-muted">Số điện thoại</label>
                                <input type="text" id="sdt" class="form-control rounded-3" value="<?= $u['SoDienThoai'] ?>">
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold small text-muted">Địa chỉ giao hàng</label>
                                <textarea id="diachi" class="form-control rounded-3" rows="3"><?= $u['DiaChi'] ?></textarea>
                            </div>

                            <div class="border-top pt-3 mt-3">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <span class="fw-bold text-muted">Tổng cộng:</span>
                                    <span class="fw-bold text-danger fs-4"><?= number_format($tongTien ?? 0, 0, ',', '.') ?> ₫</span>
                                </div>
                                
                                <button class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow-sm" id="btnCheckout" <?= empty($_SESSION['cart']) ? 'disabled' : '' ?>>
                                    <i class="fas fa-paper-plane me-2"></i> XÁC NHẬN ĐẶT HÀNG
                                </button>
                            </div>

                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-lock fa-3x text-warning mb-3"></i>
                                <h5 class="fw-bold">Bạn chưa đăng nhập!</h5>
                                <p class="text-muted small">Vui lòng đăng nhập để thanh toán đơn hàng này để chúng tôi có thể liên hệ với bạn.</p>
                                
                                <div class="d-grid gap-2 mt-3">
                                    <a href="login.php" class="btn btn-primary rounded-pill fw-bold">Đăng Nhập</a>
                                    <a href="register.php" class="btn btn-outline-secondary rounded-pill fw-bold">Tạo Tài Khoản Mới</a>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
            
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script>
    $(document).ready(function() {
        // Cập nhật số lượng
        $(".qty-input").change(function() {
            let id = $(this).data('id');
            let qty = $(this).val();
            updateCart(id, qty);
        });

        // Xóa sản phẩm
        $(".btnRemove").click(function() {
            if(confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) {
                updateCart($(this).data('id'), 0);
            }
        });

        function updateCart(id, qty) {
            $.post("shop_api.php", { action: 'update_cart', id: id, qty: qty }, function() {
                location.reload();
            });
        }

        // Xử lý nút Đặt Hàng
        $("#btnCheckout").click(function() {
            let hoten = $("#hoten").val().trim();
            let sdt = $("#sdt").val().trim();
            let diachi = $("#diachi").val().trim();

            if(hoten == '' || sdt == '' || diachi == '') {
                alert("Vui lòng kiểm tra lại thông tin giao hàng!");
                return;
            }

            // Disable nút để tránh click nhiều lần
            let btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang xử lý...');

            $.post("shop_api.php", {
                action: 'checkout',
                hoten: hoten, sdt: sdt, diachi: diachi
            }, function(response) {
                if(response.trim() === 'success') {
                    alert("🎉 Đặt hàng thành công! Cảm ơn bạn đã mua sắm.");
                    window.location.href = "shop_index.php";
                } else {
                    alert("Có lỗi xảy ra: " + response);
                    btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i> XÁC NHẬN ĐẶT HÀNG');
                }
            });
        });
    });
    </script>
</body>
</html>