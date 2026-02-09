<?php 
session_start(); 
require 'db.php'; 

// Chặn truy cập nếu chưa đăng nhập
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
$uid = $_SESSION['user']['MaNguoiDung'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lịch Sử Đơn Hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }</style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-light bg-white shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="shop_index.php">
                <i class="fas fa-chevron-left text-primary"></i> Về Trang Chủ
            </a>
            <span class="fw-bold text-primary">LỊCH SỬ MUA HÀNG</span>
        </div>
    </nav>

    <div class="container">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">Danh sách đơn hàng của bạn</h5>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Mã đơn</th>
                                <th>Ngày đặt</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th class="text-center">Chi tiết</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT * FROM donhang WHERE MaNguoiDung = ? ORDER BY MaDonHang DESC";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("i", $uid);
                            $stmt->execute();
                            $rs = $stmt->get_result();

                            if ($rs->num_rows > 0):
                                while ($row = $rs->fetch_assoc()):
                                    // Màu sắc trạng thái
                                    $sttColor = 'bg-secondary';
                                    if($row['TrangThai'] == 'Mới đặt') $sttColor = 'bg-primary';
                                    if($row['TrangThai'] == 'Đang giao hàng') $sttColor = 'bg-warning text-dark';
                                    if($row['TrangThai'] == 'Đã hoàn thành') $sttColor = 'bg-success';
                                    if($row['TrangThai'] == 'Đã hủy') $sttColor = 'bg-danger';
                            ?>
                            <tr>
                                <td class="fw-bold">#<?= $row['MaDonHang'] ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($row['NgayDat'])) ?></td>
                                <td class="fw-bold text-danger"><?= number_format($row['TongTien'], 0, ',', '.') ?>đ</td>
                                <td><span class="badge <?= $sttColor ?> rounded-pill"><?= $row['TrangThai'] ?></span></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary btnDetail" data-id="<?= $row['MaDonHang'] ?>">
                                        <i class="fas fa-eye"></i> Xem
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="5" class="text-center py-4">Bạn chưa có đơn hàng nào.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetail" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content rounded-4 border-0">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Chi tiết đơn hàng #<span id="orderId"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Đơn giá</th>
                                <th>SL</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody id="detailBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function(){
        $(".btnDetail").click(function(){
            let id = $(this).data('id');
            $("#orderId").text(id);
            $("#detailBody").html('<tr><td colspan="4" class="text-center">Đang tải...</td></tr>');
            
            $.post("shop_api.php", { action: 'get_order_detail', id: id }, function(data){
                let list = JSON.parse(data);
                let html = "";
                $.each(list, function(i, v){
                    let img = v.HinhAnh ? `<img src="uploads/${v.HinhAnh}" width="40" class="me-2 rounded">` : '';
                    let total = v.DonGia * v.SoLuong;
                    html += `
                        <tr>
                            <td>${img} <span class="fw-bold">${v.TenSanPham}</span></td>
                            <td>${new Intl.NumberFormat('vi-VN').format(v.DonGia)}đ</td>
                            <td class="text-center">${v.SoLuong}</td>
                            <td class="fw-bold text-danger text-end">${new Intl.NumberFormat('vi-VN').format(total)}đ</td>
                        </tr>
                    `;
                });
                $("#detailBody").html(html);
                $("#modalDetail").modal("show");
            });
        });
    });
    </script>
</body>
</html>