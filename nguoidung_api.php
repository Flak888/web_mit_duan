<?php
// File: nguoidung_api.php
require 'db.php';

$action = $_POST['action'] ?? '';

// 1. LẤY DANH SÁCH NGƯỜI DÙNG
if ($action == 'fetch') {
    $rs = $conn->query("SELECT * FROM nguoidung ORDER BY MaNguoiDung DESC");
    $data = [];
    while ($row = $rs->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
}

// 2. THÊM NGƯỜI DÙNG MỚI
if ($action == 'insert') {
    $hoten = $_POST['hoten'];
    $email = $_POST['email'];
    $pass  = $_POST['pass']; // Lưu ý: Dự án thực tế nên mã hóa md5 hoặc password_hash
    $sdt   = $_POST['sdt'];
    $diachi= $_POST['diachi'];
    $vaitro= $_POST['vaitro']; // 1: Admin, 0: Khách hàng

    $sql = "INSERT INTO nguoidung (HoTen, Email, MatKhau, SoDienThoai, DiaChi, VaiTro) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $hoten, $email, $pass, $sdt, $diachi, $vaitro);
    echo $stmt->execute();
}

// 3. CẬP NHẬT NGƯỜI DÙNG
if ($action == 'update') {
    $id    = $_POST['id'];
    $hoten = $_POST['hoten'];
    $email = $_POST['email'];
    $pass  = $_POST['pass'];
    $sdt   = $_POST['sdt'];
    $diachi= $_POST['diachi'];
    $vaitro= $_POST['vaitro'];

    $sql = "UPDATE nguoidung SET HoTen=?, Email=?, MatKhau=?, SoDienThoai=?, DiaChi=?, VaiTro=? 
            WHERE MaNguoiDung=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssii", $hoten, $email, $pass, $sdt, $diachi, $vaitro, $id);
    echo $stmt->execute();
}

// 4. XÓA NGƯỜI DÙNG
if ($action == 'delete') {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM nguoidung WHERE MaNguoiDung=?");
    $stmt->bind_param("i", $id);
    echo $stmt->execute();
}
?>