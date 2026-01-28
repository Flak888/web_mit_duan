<?php
// File: danh_muc_api.php
require 'db.php';

// Lấy action gửi lên từ Ajax
$action = $_POST['action'] ?? '';

// 1. LẤY DANH SÁCH (FETCH)
if ($action == 'fetch') {
    // Lưu ý: Tên bảng là 'danhmuc', sắp xếp theo MaDanhMuc mới nhất
    $rs = $conn->query("SELECT * FROM danhmuc ORDER BY MaDanhMuc DESC");
    $data = [];
    while ($row = $rs->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
}

// 2. THÊM MỚI (INSERT)
if ($action == 'insert') {
    $ten = $_POST['ten'];
    $moTa = $_POST['moTa'];

    // Cột trong SQL là TenDanhMuc, MoTa
    $stmt = $conn->prepare("INSERT INTO danhmuc(TenDanhMuc, MoTa) VALUES (?,?)");
    $stmt->bind_param("ss", $ten, $moTa);
    echo $stmt->execute();
}

// 3. CẬP NHẬT (UPDATE)
if ($action == 'update') {
    $id = $_POST['id']; // Cái này là MaDanhMuc
    $ten = $_POST['ten'];
    $moTa = $_POST['moTa'];

    $stmt = $conn->prepare("UPDATE danhmuc SET TenDanhMuc=?, MoTa=? WHERE MaDanhMuc=?");
    $stmt->bind_param("ssi", $ten, $moTa, $id);
    echo $stmt->execute();
}

// 4. XÓA (DELETE)
if ($action == 'delete') {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM danhmuc WHERE MaDanhMuc=?");
    $stmt->bind_param("i", $id);
    echo $stmt->execute();
}
?>