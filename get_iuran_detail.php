<?php
// get_iuran_detail.php
include 'config.php'; // Sertakan file koneksi database Anda

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $iuran_id = $_POST['iuran_id'] ?? '';

    if (empty($iuran_id)) {
        $response['status'] = 'error';
        $response['message'] = 'ID Iuran tidak boleh kosong.';
        echo json_encode($response);
        exit();
    }

    $stmt = $conn->prepare("SELECT id, judul, deskripsi, nominal, jatuh_tempo, created_at FROM iuran WHERE id = ?");
    $stmt->bind_param("i", $iuran_id); // 'i' untuk integer

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $iuran_data = $result->fetch_assoc();
            $response['status'] = 'success';
            $response['message'] = 'Detail iuran ditemukan.';
            $response['data'] = $iuran_data;
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Iuran tidak ditemukan.';
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Gagal mengambil detail iuran: ' . $stmt->error;
    }

    $stmt->close();
} else {
    $response['status'] = 'error';
    $response['message'] = 'Metode request tidak diizinkan.';
}

echo json_encode($response);
$conn->close();
?>