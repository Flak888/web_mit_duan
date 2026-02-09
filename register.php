<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng Ký Tài Khoản</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">

    <div class="card shadow p-4" style="width: 400px; border-radius: 15px;">
        <h3 class="text-center text-primary mb-4">ĐĂNG KÝ</h3>
        <div class="mb-3">
            <label>Họ và tên</label>
            <input type="text" id="hoten" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" id="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Mật khẩu</label>
            <input type="password" id="pass" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Số điện thoại</label>
            <input type="text" id="sdt" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Địa chỉ</label>
            <input type="text" id="diachi" class="form-control" required>
        </div>
        <button id="btnReg" class="btn btn-primary w-100 rounded-pill">Đăng Ký Ngay</button>
        <div class="text-center mt-3">
            <a href="login.php">Đã có tài khoản? Đăng nhập</a>
        </div>
        <div class="text-center mt-2">
            <a href="shop_index.php" class="text-secondary"><small>Về trang chủ</small></a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script>
        $("#btnReg").click(function(){
            $.post("shop_api.php", {
                action: 'register',
                hoten: $("#hoten").val(),
                email: $("#email").val(),
                pass: $("#pass").val(),
                sdt: $("#sdt").val(),
                diachi: $("#diachi").val()
            }, function(res){
                if(res.trim() == 'success'){
                    alert("Đăng ký thành công! Vui lòng đăng nhập.");
                    window.location.href = "login.php";
                } else {
                    alert(res);
                }
            });
        });
    </script>
</body>
</html>