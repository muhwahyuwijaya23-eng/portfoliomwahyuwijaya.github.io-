<?php
// submit_pembayaran_iuran.php
include 'config.php'; // Sertakan file koneksi database Anda

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $iuran_id = $_POST['iuran_id'] ?? '';
    $user_id = $_POST['user_id'] ?? '';
    $jumlah = $_POST['jumlah'] ?? '';
    $bukti_pembayaran_type = $_POST['bukti_type'] ?? ''; // 'virtual' atau 'cash'
    $foto_base64 = $_POST['foto'] ?? ''; // Hanya jika bukti_type adalah 'virtual'

    if (empty($iuran_id) || empty($user_id) || empty($jumlah) || empty($bukti_pembayaran_type)) {
        $response['status'] = 'error';
        $response['message'] = 'Data pembayaran tidak lengkap.';
        echo json_encode($response);
        exit();
    }

    $bukti_pembayaran_value = null; // Default null untuk kolom bukti_pembayaran

    if ($bukti_pembayaran_type === 'virtual') {
        if (empty($foto_base64)) {
            $response['status'] = 'error';
            $response['message'] = 'Bukti pembayaran gambar harus diunggah untuk Virtual Transfer.';
            echo json_encode($response);
            exit();
        }

        // Dekode gambar Base64
        $data_parts = explode(',', $foto_base64);
        $encoded_data = count($data_parts) > 1 ? $data_parts[1] : $data_parts[0];
        $decoded_image = base64_decode($encoded_data);

        // Tentukan folder penyimpanan gambar
        $upload_dir = 'uploads/pembayaran_iuran/'; // Sesuaikan dengan struktur folder Anda
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true); // Buat folder jika belum ada
        }

        // Buat nama file unik
        $file_extension = 'jpg'; // Asumsi JPEG, Anda bisa deteksi dari MIME type jika perlu
        $bukti_pembayaran_filename = uniqid() . '_' . time() . '.' . $file_extension;
        $file_path = $upload_dir . $bukti_pembayaran_filename;

        // Simpan gambar ke server
        if (file_put_contents($file_path, $decoded_image)) {
            $bukti_pembayaran_value = $bukti_pembayaran_filename; // Simpan nama file di DB
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Gagal menyimpan bukti pembayaran gambar di server.';
            echo json_encode($response);
            exit();
        }
    } else if ($bukti_pembayaran_type === 'cash') {
        $bukti_pembayaran_value = 'dibayar cash'; // Set string "dibayar cash"
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Metode pembayaran tidak valid.';
        echo json_encode($response);
        exit();
    }

    // Status pembayaran awal selalu 'Menunggu'
    $status_pembayaran = 'Menunggu';

    // Masukkan data ke tabel pembayaran_iuran
    $stmt = $conn->prepare("INSERT INTO pembayaran_iuran (iuran_id, user_id, jumlah, bukti_pembayaran, status, tanggal_bayar) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("iisss", $iuran_id, $user_id, $jumlah, $bukti_pembayaran_value, $status_pembayaran);

    if ($stmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = 'Pembayaran berhasil dikirim dan menunggu konfirmasi!';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Gagal menyimpan pembayaran: ' . $stmt->error;
        // Jika gambar sudah disimpan tapi insert ke DB gagal, hapus gambar yang sudah diupload
        if ($bukti_pembayaran_type === 'virtual' && $bukti_pembayaran_value && file_exists($file_path)) {
            unlink($file_path);
        }
    }

    $stmt->close();
} else {
    $response['status'] = 'error';
    $response['message'] = 'Metode request tidak diizinkan.';
}

echo json_encode($response);
$conn->close();
?>