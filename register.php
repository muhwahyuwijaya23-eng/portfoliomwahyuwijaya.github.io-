<?php
// register.php
include 'config.php'; // Sertakan file konfigurasi database

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari request POST
    $nama = $_POST['nama'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $rt = $_POST['rt'] ?? '';
    $rw = $_POST['rw'] ?? '';
    $no_telepon = $_POST['no_telepon'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validasi data
    if (empty($nama) || empty($alamat) || empty($rt) || empty($rw) || empty($no_telepon) || empty($username) || empty($password)) {
        $response['status'] = 'error';
        $response['message'] = 'Semua field harus diisi.';
    } else {
        // Hash password sebelum menyimpan
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Mulai transaksi untuk memastikan integritas data
        $conn->begin_transaction();

        try {
            // 1. Masukkan data ke tabel `users`
            $stmt_user = $conn->prepare("INSERT INTO users (username, password, role, created_at, updated_at) VALUES (?, ?, 'warga', NOW(), NOW())");
            $stmt_user->bind_param("ss", $username, $hashed_password);

            if ($stmt_user->execute()) {
                $user_id = $conn->insert_id; // Ambil ID user yang baru dibuat

                // 2. Masukkan data ke tabel `warga` dengan user_id yang baru
                $stmt_warga = $conn->prepare("INSERT INTO warga (user_id, nama, alamat, no_telepon, rt, rw, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
                $stmt_warga->bind_param("isssss", $user_id, $nama, $alamat, $no_telepon, $rt, $rw);

                if ($stmt_warga->execute()) {
                    $conn->commit(); // Commit transaksi jika keduanya berhasil
                    $response['status'] = 'success';
                    $response['message'] = 'Registrasi berhasil!';
                    $response['user_id'] = $user_id;
                    $response['username'] = $username;
                } else {
                    $conn->rollback(); // Rollback jika ada error di tabel `warga`
                    $response['status'] = 'error';
                    $response['message'] = 'Gagal menyimpan data warga: ' . $stmt_warga->error;
                }
            } else {
                $conn->rollback(); // Rollback jika ada error di tabel `users`
                // Periksa jika username sudah ada
                if ($stmt_user->errno == 1062) { // MySQL error code for duplicate entry
                    $response['status'] = 'error';
                    $response['message'] = 'Username sudah terdaftar. Mohon gunakan username lain.';
                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'Gagal menyimpan data user: ' . $stmt_user->error;
                }
            }

            $stmt_user->close();
            if (isset($stmt_warga)) $stmt_warga->close();

        } catch (Exception $e) {
            $conn->rollback();
            $response['status'] = 'error';
            $response['message'] = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Metode request tidak diizinkan.';
}

echo json_encode($response);
$conn->close();
?>