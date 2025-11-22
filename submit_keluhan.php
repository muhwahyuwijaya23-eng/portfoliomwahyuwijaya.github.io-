<?php
// submit_keluhan.php
include 'config.php'; // Include file koneksi database Anda

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'] ?? '';
    $judul = $_POST['judul'] ?? '';
    $isi = $_POST['isi'] ?? '';
    $foto_base64 = $_POST['foto'] ?? ''; // Menerima data gambar Base64

    if (empty($user_id) || empty($judul) || empty($isi)) {
        $response['status'] = 'error';
        $response['message'] = 'User ID, judul, dan isi keluhan tidak boleh kosong.';
        echo json_encode($response);
        exit();
    }

    $foto_filename = null; // Default null jika tidak ada gambar

    if (!empty($foto_base64)) {
        // Hapus "data:image/jpeg;base64," atau bagian MIME type lainnya jika ada
        $data_parts = explode(',', $foto_base64);
        $encoded_data = count($data_parts) > 1 ? $data_parts[1] : $data_parts[0];

        $decoded_image = base64_decode($encoded_data);

        // Tentukan folder penyimpanan gambar
        $upload_dir = 'uploads/pengaduan/'; // Sesuaikan dengan struktur folder Anda
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true); // Buat folder jika belum ada
        }

        // Buat nama file unik
        $foto_filename = uniqid() . '_' . time() . '.jpg'; // Contoh: 60a12345_1678901234.jpg
        $file_path = $upload_dir . $foto_filename;

        // Simpan gambar ke server
        if (file_put_contents($file_path, $decoded_image)) {
            // Gambar berhasil disimpan
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Gagal menyimpan gambar di server.';
            echo json_encode($response);
            exit();
        }
    }

    // Masukkan data keluhan ke tabel 'pengaduan'
    $stmt = $conn->prepare("INSERT INTO pengaduan (user_id, judul, isi, foto, status, created_at, updated_at) VALUES (?, ?, ?, ?, 'terkirim', NOW(), NOW())");
    // Perhatikan: 's' untuk foto_filename yang bisa null (varchar), atau 'b' jika Anda ingin bind BLOB data (tidak disarankan untuk gambar besar)
    $stmt->bind_param("isss", $user_id, $judul, $isi, $foto_filename);

    if ($stmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = 'Keluhan berhasil dikirim!';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Gagal mengirim keluhan: ' . $stmt->error;
        // Jika gambar sudah disimpan tapi insert ke DB gagal, hapus gambar yang sudah diupload
        if ($foto_filename && file_exists($file_path)) {
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