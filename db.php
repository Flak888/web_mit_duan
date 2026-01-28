<?php
// File: db.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ch_data"; // <--- BẠN KIỂM TRA LẠI TÊN DATABASE Ở ĐÂY NHÉ

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");

if ($conn->connect_error) {
    die("Lỗi kết nối: " . $conn->connect_error);
}
?>