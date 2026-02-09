<?php
// File: shop_api.php
session_start();
require 'db.php';

// Khởi tạo giỏ hàng
if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = []; }

// === SỬA DÒNG NÀY ===
// Dùng $_REQUEST để nhận được cả GET (từ link) và POST (từ form)
$action = $_REQUEST['action'] ?? ''; 
// ====================

// 1. ĐĂNG KÝ
if ($action == 'register') {
    $hoten = $_POST['hoten'];
    $email = $_POST['email'];
    $pass  = $_POST['pass'];
    $sdt   = $_POST['sdt'];
    $diachi= $_POST['diachi'];

    $check = $conn->query("SELECT * FROM nguoidung WHERE Email = '$email'");
    if ($check->num_rows > 0) {
        echo "Email đã tồn tại!"; exit;
    }

    $sql = "INSERT INTO nguoidung (HoTen, Email, MatKhau, SoDienThoai, DiaChi, VaiTro) VALUES (?, ?, ?, ?, ?, 0)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $hoten, $email, $pass, $sdt, $diachi);
    
    if ($stmt->execute()) echo "success";
    else echo "Lỗi: " . $conn->error;
}

// 2. ĐĂNG NHẬP
if ($action == 'login') {
    $email = $_POST['email'];
    $pass  = $_POST['pass'];

    $sql = "SELECT * FROM nguoidung WHERE Email = ? AND MatKhau = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $pass);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user'] = $user;
        echo "success";
    } else {
        echo "Sai email hoặc mật khẩu!";
    }
}

// 3. ĐĂNG XUẤT (Logic này giờ sẽ hoạt động)
if ($action == 'logout') {
    unset($_SESSION['user']); // Xóa session người dùng
    header("Location: shop_index.php"); // Chuyển hướng về trang chủ
    exit;
}

// 4. CÁC API KHÁC (GIỎ HÀNG, ĐẶT HÀNG...) - GIỮ NGUYÊN KHÔNG ĐỔI
if ($action == 'add_to_cart') {
    $id = $_POST['id'];
    $ten = $_POST['ten'];
    $gia = $_POST['gia'];
    $hinh = $_POST['hinh'];
    
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $id) {
            $item['qty']++;
            $found = true;
            break;
        }
    }
    if (!$found) $_SESSION['cart'][] = ['id' => $id, 'ten' => $ten, 'gia' => $gia, 'hinh' => $hinh, 'qty' => 1];
    echo count($_SESSION['cart']);
}

if ($action == 'update_cart') {
    $id = $_POST['id'];
    $qty = $_POST['qty'];
    foreach ($_SESSION['cart'] as $key => &$item) {
        if ($item['id'] == $id) {
            if ($qty <= 0) unset($_SESSION['cart'][$key]);
            else $item['qty'] = $qty;
            break;
        }
    }
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    echo "ok";
}

if ($action == 'checkout') {
    if (!isset($_SESSION['user'])) { echo "Vui lòng đăng nhập!"; exit; }

    $u = $_SESSION['user'];
    $maNguoiDung = $u['MaNguoiDung'];
    $hotenNhan = $_POST['hoten'];
    $sdtNhan   = $_POST['sdt'];
    $diaChiNhan= $_POST['diachi'];
    
    $tongtien = 0;
    foreach ($_SESSION['cart'] as $item) $tongtien += $item['gia'] * $item['qty'];

    if ($tongtien == 0) { echo "Giỏ hàng rỗng"; exit; }

    $ngayDat = date('Y-m-d H:i:s');
    $sql = "INSERT INTO donhang (MaNguoiDung, NguoiNhan, SDT_Nhan, DiaChiGiao, TongTien, TrangThai, NgayDat) 
            VALUES (?, ?, ?, ?, ?, 'Mới đặt', ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssds", $maNguoiDung, $hotenNhan, $sdtNhan, $diaChiNhan, $tongtien, $ngayDat);
    
    if ($stmt->execute()) {
        $maDonHang = $stmt->insert_id;
        $sqlDetail = "INSERT INTO chitietdonhang (MaDonHang, MaSanPham, SoLuong, DonGia) VALUES (?, ?, ?, ?)";
        $stmtDetail = $conn->prepare($sqlDetail);
        foreach ($_SESSION['cart'] as $item) {
            $stmtDetail->bind_param("iiid", $maDonHang, $item['id'], $item['qty'], $item['gia']);
            $stmtDetail->execute();
        }
        unset($_SESSION['cart']);
        echo "success";
    } else {
        echo "Lỗi: " . $conn->error;
    }
}

// 5. LẤY CHI TIẾT ĐƠN HÀNG (Lịch sử)
if ($action == 'get_order_detail') {
    if (!isset($_SESSION['user'])) exit;
    $maDonHang = $_POST['id'];
    $maNguoiDung = $_SESSION['user']['MaNguoiDung'];

    $sql = "SELECT dh.* FROM donhang dh WHERE dh.MaDonHang = ? AND dh.MaNguoiDung = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $maDonHang, $maNguoiDung);
    $stmt->execute();
    if($stmt->get_result()->num_rows == 0) { echo json_encode([]); exit; }

    $sqlCt = "SELECT ct.*, sp.TenSanPham, sp.HinhAnh FROM chitietdonhang ct JOIN sanpham sp ON ct.MaSanPham = sp.MaSanPham WHERE ct.MaDonHang = ?";
    $stmtCt = $conn->prepare($sqlCt);
    $stmtCt->bind_param("i", $maDonHang);
    $stmtCt->execute();
    $result = $stmtCt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) $data[] = $row;
    echo json_encode($data);
}

// 6. GỬI ĐÁNH GIÁ SẢN PHẨM
if ($action == 'submit_review') {
    // Kiểm tra đăng nhập
    if (!isset($_SESSION['user'])) {
        echo "Vui lòng đăng nhập để đánh giá!";
        exit;
    }

    $maSP = $_POST['maSP'];
    $soSao = $_POST['soSao'];
    $noiDung = $_POST['noiDung'];
    $maNguoiDung = $_SESSION['user']['MaNguoiDung'];

    // Kiểm tra xem đã đánh giá chưa (Mỗi người chỉ đánh giá 1 lần/1 sản phẩm)
    // Nếu muốn cho đánh giá nhiều lần thì bỏ đoạn kiểm tra này
    $check = $conn->query("SELECT * FROM danhgia WHERE MaSanPham = $maSP AND MaNguoiDung = $maNguoiDung");
    if ($check->num_rows > 0) {
        echo "Bạn đã đánh giá sản phẩm này rồi!";
        exit;
    }

    $sql = "INSERT INTO danhgia (MaSanPham, MaNguoiDung, SoSao, NoiDung) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiis", $maSP, $maNguoiDung, $soSao, $noiDung);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Lỗi: " . $conn->error;
    }
}
?>