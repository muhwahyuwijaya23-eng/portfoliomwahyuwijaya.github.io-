<?php
// config.php
define('DB_HOST', 'localhost'); // Ganti jika MySQL kamu di server lain, tapi untuk XAMPP di laptop, 'localhost' sudah benar
define('DB_USER', 'root');     // Ganti dengan username MySQL yang kamu gunakan (misal: 'android_user' seperti saran sebelumnya)
define('DB_PASS', '');         // Ganti dengan password MySQL kamu (jika ada)
define('DB_NAME', 'rtrw');     // Nama database kamu

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Set header untuk mengizinkan CORS (Cross-Origin Resource Sharing)
// Ini penting agar aplikasi Android bisa mengakses API
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}
?>