<?php
// get_warga_profile.php
include 'config.php'; // Sertakan file koneksi database Anda

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'] ?? '';

    if (empty($user_id)) {
        $response['status'] = 'error';
        $response['message'] = 'User ID tidak boleh kosong.';
        echo json_encode($response);
        exit();
    }

    // Asumsi tabel 'warga' memiliki kolom 'user_id' yang merupakan foreign key ke 'users.id'
    $stmt = $conn->prepare("SELECT nama, alamat, no_telepon, rt, rw, created_at FROM warga WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $warga_data = $result->fetch_assoc();
        $response['status'] = 'success';
        $response['message'] = 'Data profil warga ditemukan.';
        $response['data'] = $warga_data;
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Data profil warga tidak ditemukan.';
    }

    $stmt->close();
} else {
    $response['status'] = 'error';
    $response['message'] = 'Metode request tidak diizinkan.';
}

echo json_encode($response);
$conn->close();
?>