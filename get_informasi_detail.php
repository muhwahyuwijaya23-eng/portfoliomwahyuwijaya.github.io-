<?php
// get_informasi_detail.php
include 'config.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $informasi_id = $_POST['informasi_id'] ?? '';

    if (empty($informasi_id)) {
        $response['status'] = 'error';
        $response['message'] = 'ID Informasi tidak boleh kosong.';
        echo json_encode($response);
        exit();
    }

    // Pastikan SELECT statement menyertakan 'gambar' dan 'created_at'
    $stmt = $conn->prepare("SELECT id, judul, isi, tanggal, created_at, gambar FROM informasi WHERE id = ?");
    $stmt->bind_param("i", $informasi_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $informasi_data = $result->fetch_assoc();
            $response['status'] = 'success';
            $response['message'] = 'Detail informasi ditemukan.';
            $response['data'] = $informasi_data;
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Informasi tidak ditemukan.';
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Gagal mengambil detail informasi: ' . $stmt->error;
    }

    $stmt->close();
} else {
    $response['status'] = 'error';
    $response['message'] = 'Metode request tidak diizinkan.';
}

echo json_encode($response);
$conn->close();
?>