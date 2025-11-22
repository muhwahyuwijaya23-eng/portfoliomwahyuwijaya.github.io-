<?php
// get_all_informasi.php
include 'config.php'; // Include file koneksi database Anda

$response = array();
$informasi_list = array();

if ($_SERVER['REQUEST_METHOD'] == 'GET') { // Menggunakan GET untuk mengambil semua data
    // Ambil semua informasi dari tabel 'informasi'
    // Urutkan berdasarkan tanggal dibuat terbaru
    $stmt = $conn->prepare("SELECT id, judul, isi, tanggal, created_at FROM informasi ORDER BY created_at DESC");

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $informasi_list[] = $row; // Tambahkan setiap baris ke array
            }
            $response['status'] = 'success';
            $response['message'] = 'Daftar informasi ditemukan.';
            $response['data'] = $informasi_list; // Kirim daftar informasi
        } else {
            $response['status'] = 'success'; // Tetap success, hanya tidak ada data
            $response['message'] = 'Belum ada informasi.';
            $response['data'] = []; // Kirim array kosong
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Gagal mengambil daftar informasi: ' . $stmt->error;
    }

    $stmt->close();
} else {
    $response['status'] = 'error';
    $response['message'] = 'Metode request tidak diizinkan.';
}

echo json_encode($response);
$conn->close();
?>