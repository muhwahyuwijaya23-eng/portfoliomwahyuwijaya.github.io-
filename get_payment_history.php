<?php
// get_payment_history.php
include 'config.php'; // Sertakan file koneksi database Anda

$response = array();
$history_list = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'] ?? '';

    if (empty($user_id)) {
        $response['status'] = 'error';
        $response['message'] = 'User ID tidak boleh kosong.';
        echo json_encode($response);
        exit();
    }

    // Mengambil riwayat pembayaran untuk user_id tertentu
    // Melakukan JOIN dengan tabel 'iuran' untuk mendapatkan judul iuran
    $stmt = $conn->prepare("
        SELECT
            pi.id,
            i.judul AS iuran_judul,
            pi.status AS payment_status,
            pi.tanggal_bayar AS paid_date,
            pi.jumlah AS paid_amount
        FROM
            pembayaran_iuran pi
        JOIN
            iuran i ON pi.iuran_id = i.id
        WHERE
            pi.user_id = ?
        ORDER BY
            pi.tanggal_bayar DESC
    ");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $history_list[] = $row;
            }
            $response['status'] = 'success';
            $response['message'] = 'Riwayat pembayaran ditemukan.';
            $response['data'] = $history_list;
        } else {
            $response['status'] = 'success';
            $response['message'] = 'Belum ada riwayat pembayaran.';
            $response['data'] = [];
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Gagal mengambil riwayat pembayaran: ' . $stmt->error;
    }

    $stmt->close();
} else {
    $response['status'] = 'error';
    $response['message'] = 'Metode request tidak diizinkan.';
}

echo json_encode($response);
$conn->close();
?>