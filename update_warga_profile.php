<?php
// update_warga_profile.php
include 'config.php'; // Sertakan file koneksi database Anda

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'] ?? '';
    $nama = $_POST['nama'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $no_telepon = $_POST['no_telepon'] ?? '';
    $rt = $_POST['rt'] ?? '';
    $rw = $_POST['rw'] ?? '';

    if (empty($user_id)) {
        $response['status'] = 'error';
        $response['message'] = 'User ID tidak boleh kosong.';
        echo json_encode($response);
        exit();
    }

    // Update data di tabel 'warga'
    // updated_at akan otomatis terupdate jika kolomnya diatur ON UPDATE CURRENT_TIMESTAMP
    $stmt = $conn->prepare("UPDATE warga SET nama = ?, alamat = ?, no_telepon = ?, rt = ?, rw = ? WHERE user_id = ?");
    $stmt->bind_param("sssisi", $nama, $alamat, $no_telepon, $rt, $rw, $user_id); // 'i' untuk int (rt, rw), 's' untuk string

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $response['status'] = 'success';
            $response['message'] = 'Profil berhasil diperbarui!';
        } else {
            $response['status'] = 'success'; // Tetap success jika data tidak berubah (affected_rows = 0)
            $response['message'] = 'Tidak ada perubahan pada profil.';
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Gagal memperbarui profil: ' . $stmt->error;
    }

    $stmt->close();
} else {
    $response['status'] = 'error';
    $response['message'] = 'Metode request tidak diizinkan.';
}

echo json_encode($response);
$conn->close();
?>