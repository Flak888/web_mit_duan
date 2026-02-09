<?php
// File: donhang_api.php
require 'db.php';

$action = $_POST['action'] ?? '';

// 1. LẤY DANH SÁCH ĐƠN HÀNG (Kèm tên khách hàng)
if ($action == 'fetch') {
    $sql = "SELECT dh.*, nd.HoTen 
            FROM donhang dh 
            LEFT JOIN nguoidung nd ON dh.MaNguoiDung = nd.MaNguoiDung 
            ORDER BY dh.NgayDat DESC";
    $rs = $conn->query($sql);
    $data = [];
    while ($row = $rs->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
}

// 2. THÊM ĐƠN HÀNG MỚI
if ($action == 'insert') {
    $maKH = $_POST['maKH'];
    $nguoiNhan = $_POST['nguoiNhan'];
    $sdt = $_POST['sdt'];
    $diaChi = $_POST['diaChi'];
    $tongTien = $_POST['tongTien'];
    $trangThai = $_POST['trangThai'];
    
    $ngayDat = date('Y-m-d H:i:s'); // Lấy giờ hiện tại

    $sql = "INSERT INTO donhang (MaNguoiDung, NguoiNhan, SDT_Nhan, DiaChiGiao, TongTien, TrangThai, NgayDat) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssdss", $maKH, $nguoiNhan, $sdt, $diaChi, $tongTien, $trangThai, $ngayDat);
    echo $stmt->execute();
}

// 3. CẬP NHẬT ĐƠN HÀNG
if ($action == 'update') {
    $id = $_POST['id'];
    $maKH = $_POST['maKH'];
    $nguoiNhan = $_POST['nguoiNhan'];
    $sdt = $_POST['sdt'];
    $diaChi = $_POST['diaChi'];
    $tongTien = $_POST['tongTien'];
    $trangThai = $_POST['trangThai'];

    $sql = "UPDATE donhang SET MaNguoiDung=?, NguoiNhan=?, SDT_Nhan=?, DiaChiGiao=?, TongTien=?, TrangThai=? 
            WHERE MaDonHang=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssdsi", $maKH, $nguoiNhan, $sdt, $diaChi, $tongTien, $trangThai, $id);
    echo $stmt->execute();
}

// 4. XÓA ĐƠN HÀNG
if ($action == 'delete') {
    $id = $_POST['id'];
    // Lưu ý: Nếu muốn chặt chẽ, nên xóa cả chi tiết đơn hàng trước
    $conn->query("DELETE FROM chitietdonhang WHERE MaDonHang = $id");
    
    $stmt = $conn->prepare("DELETE FROM donhang WHERE MaDonHang=?");
    $stmt->bind_param("i", $id);
    echo $stmt->execute();
}

// 5. API LẤY DANH SÁCH KHÁCH HÀNG (Để đổ vào Select box)
if ($action == 'fetch_khachhang') {
    // Lấy tất cả người dùng (hoặc lọc WHERE VaiTro = 0 nếu chỉ muốn lấy khách)
    $rs = $conn->query("SELECT MaNguoiDung, HoTen FROM nguoidung"); 
    $data = [];
    while ($row = $rs->fetch_assoc()) $data[] = $row;
    echo json_encode($data);
}

// ================================================================
// 6. [MỚI] API LẤY CHI TIẾT ĐƠN HÀNG (QUAN TRỌNG)
// ================================================================
if ($action == 'get_chitiet') {
    $id = $_POST['id']; // ID Đơn hàng gửi lên
    
    // Join bảng chitietdonhang với bảng sanpham để lấy Tên và Hình ảnh
    $sql = "SELECT ct.*, sp.TenSanPham, sp.HinhAnh 
            FROM chitietdonhang ct 
            LEFT JOIN sanpham sp ON ct.MaSanPham = sp.MaSanPham 
            WHERE ct.MaDonHang = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
}
?>