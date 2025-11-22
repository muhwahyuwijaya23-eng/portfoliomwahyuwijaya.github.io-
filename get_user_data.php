<?php
// get_user_data.php
include 'config.php'; // Include file koneksi database Anda

$response = array(); // Inisialisasi array respons

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Pastikan user_id dikirimkan dari aplikasi Android
    $user_id = $_POST['user_id'] ?? '';

    if (empty($user_id)) {
        $response['status'] = 'error';
        $response['message'] = 'User ID tidak boleh kosong.';
        echo json_encode($response);
        exit();
    }

    // Mengambil data user dari tabel 'users' dan 'warga'
    // Menggunakan JOIN untuk menggabungkan data dari kedua tabel
    $stmt = $conn->prepare("SELECT u.username, w.nama, w.alamat, w.rt, w.rw, w.no_telepon FROM users u JOIN warga w ON u.id = w.user_id WHERE u.id = ?");
    $stmt->bind_param("i", $user_id); // 'i' untuk integer

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user_data = $result->fetch_assoc();
            $response['status'] = 'success';
            $response['message'] = 'Data pengguna ditemukan.';
            $response['data'] = $user_data; // Kirim semua data pengguna
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Pengguna tidak ditemukan.';
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Gagal mengambil data: ' . $stmt->error;
    }

    $stmt->close();
} else {
    $response['status'] = 'error';
    $response['message'] = 'Metode request tidak diizinkan.';
}

echo json_encode($response);
$conn->close();
?>