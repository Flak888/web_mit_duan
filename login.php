<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng Nhập</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">

    <div class="card shadow p-4" style="width: 350px; border-radius: 15px;">
        <h3 class="text-center text-primary mb-4">ĐĂNG NHẬP</h3>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" id="email" class="form-control">
        </div>
        <div class="mb-3">
            <label>Mật khẩu</label>
            <input type="password" id="pass" class="form-control">
        </div>
        <button id="btnLogin" class="btn btn-primary w-100 rounded-pill fw-bold">ĐĂNG NHẬP</button>
        <div class="text-center mt-3">
            <a href="register.php">Chưa có tài khoản? Đăng ký</a>
        </div>
        <div class="text-center mt-2">
            <a href="shop_index.php" class="text-secondary"><small>Về trang chủ</small></a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script>
        $("#btnLogin").click(function(){
            $.post("shop_api.php", {
                action: 'login',
                email: $("#email").val(),
                pass: $("#pass").val()
            }, function(res){
                if(res.trim() == 'success'){
                    alert("Đăng nhập thành công!");
                    window.location.href = "shop_index.php"; // Về trang chủ
                } else {
                    alert(res);
                }
            });
        });
    </script>
</body>
</html>