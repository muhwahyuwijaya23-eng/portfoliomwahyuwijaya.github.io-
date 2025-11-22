<?php
// submit_surat_permohonan.php
include 'config.php'; // Include file koneksi database Anda

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'] ?? '';
    $nama_pemohon = $_POST['nama_pemohon'] ?? ''; // Akan jadi 'judul' di aplikasi Android
    $keterangan_pemohon = $_POST['keterangan_pemohon'] ?? ''; // Akan jadi 'isi' di aplikasi Android

    if (empty($user_id) || empty($nama_pemohon) || empty($keterangan_pemohon)) {
        $response['status'] = 'error';
        $response['message'] = 'User ID, nama pemohon, dan keterangan tidak boleh kosong.';
        echo json_encode($response);
        exit();
    }

    // Masukkan data permohonan surat ke tabel 'pengaduan'
    // Kita bisa menggunakan 'judul' untuk nama_pemohon dan 'isi' untuk keterangan_pemohon
    // Anda bisa menambahkan kolom 'jenis_pengaduan' atau 'tipe' di tabel pengaduan
    // untuk membedakan antara keluhan dan permohonan surat jika diperlukan di masa depan.
    // Untuk saat ini, kita anggap 'foto' bisa null karena di permohonan surat tidak ada input gambar.
    $stmt = $conn->prepare("INSERT INTO pengaduan (user_id, judul, isi, status, created_at, updated_at) VALUES (?, ?, ?, 'terkirim', NOW(), NOW())");
    $stmt->bind_param("iss", $user_id, $nama_pemohon, $keterangan_pemohon);

    if ($stmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = 'Permohonan surat berhasil dikirim!';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Gagal mengirim permohonan surat: ' . $stmt->error;
    }

    $stmt->close();
} else {
    $response['status'] = 'error';
    $response['message'] = 'Metode request tidak diizinkan.';
}

echo json_encode($response);
$conn->close();
?>