<?php
// submit_kependudukan.php
include 'config.php'; // Sertakan file koneksi database Anda

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'] ?? '';
    $nama_lengkap = $_POST['nama_lengkap'] ?? ''; // Akan jadi 'judul' di aplikasi Android
    $keperluan = $_POST['keperluan'] ?? ''; // Akan jadi 'isi' di aplikasi Android

    if (empty($user_id) || empty($nama_lengkap) || empty($keperluan)) {
        $response['status'] = 'error';
        $response['message'] = 'User ID, nama lengkap, dan keperluan tidak boleh kosong.';
        echo json_encode($response);
        exit();
    }

    // Masukkan data kependudukan ke tabel 'pengaduan'
    // Sama seperti permohonan surat, kita menggunakan 'judul' untuk nama_lengkap
    // dan 'isi' untuk keperluan. Kolom 'foto' diasumsikan bisa NULL.
    $stmt = $conn->prepare("INSERT INTO pengaduan (user_id, judul, isi, status, created_at, updated_at) VALUES (?, ?, ?, 'terkirim', NOW(), NOW())");
    $stmt->bind_param("iss", $user_id, $nama_lengkap, $keperluan);

    if ($stmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = 'Data kependudukan berhasil dikirim!';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Gagal mengirim data kependudukan: ' . $stmt->error;
    }

    $stmt->close();
} else {
    $response['status'] = 'error';
    $response['message'] = 'Metode request tidak diizinkan.';
}

echo json_encode($response);
$conn->close();
?>