<?php
// File: donhang_api.php
require 'db.php';

$action = $_POST['action'] ?? '';

// 1. LẤY DANH SÁCH ĐƠN HÀNG (Kèm tên khách hàng từ bảng nguoidung)
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

// 2. THÊM ĐƠN HÀNG
if ($action == 'insert') {
    $maKH = $_POST['maKH'];
    $nguoiNhan = $_POST['nguoiNhan'];
    $sdt = $_POST['sdt'];
    $diaChi = $_POST['diaChi'];
    $tongTien = $_POST['tongTien'];
    $trangThai = $_POST['trangThai'];
    
    // Lấy thời gian hiện tại cho NgayDat
    $ngayDat = date('Y-m-d H:i:s');

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
    $stmt = $conn->prepare("DELETE FROM donhang WHERE MaDonHang=?");
    $stmt->bind_param("i", $id);
    echo $stmt->execute();
}

// 5. API PHỤ: Lấy danh sách Khách hàng để chọn
if ($action == 'fetch_khachhang') {
    $rs = $conn->query("SELECT MaNguoiDung, HoTen FROM nguoidung WHERE VaiTro = 0"); // Giả sử 0 là khách hàng
    $data = [];
    while ($row = $rs->fetch_assoc()) $data[] = $row;
    echo json_encode($data);
}
?>