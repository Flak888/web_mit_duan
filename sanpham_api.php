<?php
// File: sanpham_api.php
require 'db.php';

$action = $_POST['action'] ?? '';

// 1. LẤY DANH SÁCH SẢN PHẨM
if ($action == 'fetch') {
    // Join với bảng danh mục để lấy tên danh mục
    $sql = "SELECT s.*, d.TenDanhMuc 
            FROM sanpham s 
            LEFT JOIN danhmuc d ON s.MaDanhMuc = d.MaDanhMuc 
            ORDER BY s.MaSanPham DESC";
    $rs = $conn->query($sql);
    $data = [];
    while ($row = $rs->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
}

// HÀM HỖ TRỢ UPLOAD ẢNH
function uploadImage($file) {
    if (isset($file) && $file['error'] == 0) {
        $target_dir = "uploads/";
        // Đổi tên file để tránh trùng: time_tenfilegoc
        $filename = time() . "_" . basename($file["name"]);
        $target_file = $target_dir . $filename;
        
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            return $filename; // Trả về tên file để lưu vào DB
        }
    }
    return ""; // Không có ảnh hoặc lỗi
}

// 2. THÊM SẢN PHẨM MỚI
if ($action == 'insert') {
    $ten = $_POST['ten'];
    $gia = $_POST['gia']; // Cột DonGia
    $ton = $_POST['ton']; // Cột SoLuongTon
    $mota = $_POST['mota'];
    $madm = $_POST['madm'];
    
    // Xử lý ảnh
    $hinhAnh = uploadImage($_FILES['hinhAnh'] ?? null);

    $sql = "INSERT INTO sanpham(MaDanhMuc, TenSanPham, DonGia, SoLuongTon, HinhAnh, MoTa) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isdiss", $madm, $ten, $gia, $ton, $hinhAnh, $mota);
    echo $stmt->execute();
}

// 3. CẬP NHẬT SẢN PHẨM
if ($action == 'update') {
    $id = $_POST['id'];
    $ten = $_POST['ten'];
    $gia = $_POST['gia'];
    $ton = $_POST['ton'];
    $mota = $_POST['mota'];
    $madm = $_POST['madm'];
    $oldImg = $_POST['oldImg']; // Ảnh cũ

    // Nếu người dùng chọn ảnh mới thì upload, không thì giữ ảnh cũ
    $hinhAnh = $oldImg;
    if (isset($_FILES['hinhAnh']) && $_FILES['hinhAnh']['error'] == 0) {
        $hinhAnh = uploadImage($_FILES['hinhAnh']);
    }

    $sql = "UPDATE sanpham SET MaDanhMuc=?, TenSanPham=?, DonGia=?, SoLuongTon=?, HinhAnh=?, MoTa=? WHERE MaSanPham=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isdissi", $madm, $ten, $gia, $ton, $hinhAnh, $mota, $id);
    echo $stmt->execute();
}

// 4. XÓA SẢN PHẨM
if ($action == 'delete') {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM sanpham WHERE MaSanPham=?");
    $stmt->bind_param("i", $id);
    echo $stmt->execute();
}

// 5. API LẤY DANH MỤC (Cho Select Box)
if ($action == 'fetch_danhmuc') {
    $rs = $conn->query("SELECT * FROM danhmuc");
    $data = [];
    while ($row = $rs->fetch_assoc()) $data[] = $row;
    echo json_encode($data);
}
?>