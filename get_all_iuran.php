<?php
// get_all_iuran.php
include 'config.php'; // Sertakan file koneksi database Anda

$response = array();
$iuran_list = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') { // <--- UBAH DARI GET MENJADI POST
    $user_id = $_POST['user_id'] ?? ''; // <--- Ambil user_id

    if (empty($user_id)) {
        $response['status'] = 'error';
        $response['message'] = 'User ID tidak boleh kosong.';
        echo json_encode($response);
        exit();
    }

    // Mengambil semua iuran, lalu melakukan LEFT JOIN dengan tabel pembayaran_iuran
    // untuk mengecek apakah user_id ini sudah membayar iuran tersebut.
    $stmt = $conn->prepare("
        SELECT
            i.id,
            i.judul,
            i.deskripsi,
            i.nominal,
            i.jatuh_tempo,
            i.created_at,
            pi.status AS payment_status,
            pi.tanggal_bayar AS paid_date
        FROM
            iuran i
        LEFT JOIN
            pembayaran_iuran pi ON i.id = pi.iuran_id AND pi.user_id = ?
        ORDER BY
            i.jatuh_tempo ASC
    ");
    $stmt->bind_param("i", $user_id); // <--- Bind user_id

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Tentukan status pembayaran
                $is_paid = ($row['payment_status'] === 'Dikonfirmasi'); // Asumsi 'Dikonfirmasi' berarti sudah dibayar
                $paid_date = $row['paid_date'] ?? null; // Tanggal bayar, bisa null jika belum bayar

                $iuran_item = [
                    'id' => $row['id'],
                    'judul' => $row['judul'],
                    'deskripsi' => $row['deskripsi'],
                    'nominal' => (int)$row['nominal'], // Pastikan nominal adalah integer
                    'jatuh_tempo' => $row['jatuh_tempo'],
                    'created_at' => $row['created_at'],
                    'is_paid' => $is_paid, // <--- Kirim status pembayaran
                    'paid_date' => $paid_date // <--- Kirim tanggal bayar
                ];
                $iuran_list[] = $iuran_item;
            }
            $response['status'] = 'success';
            $response['message'] = 'Daftar iuran ditemukan.';
            $response['data'] = $iuran_list;
        } else {
            $response['status'] = 'success';
            $response['message'] = 'Belum ada iuran yang tersedia.';
            $response['data'] = [];
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Gagal mengambil daftar iuran: ' . $stmt->error;
    }

    $stmt->close();
} else {
    $response['status'] = 'error';
    $response['message'] = 'Metode request tidak diizinkan.';
}

echo json_encode($response);
$conn->close();
?>