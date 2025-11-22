<?php
// change_password.php
include 'config.php'; // Sertakan file koneksi database Anda

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'] ?? '';
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';

    if (empty($user_id) || empty($old_password) || empty($new_password)) {
        $response['status'] = 'error';
        $response['message'] = 'User ID, password lama, dan password baru tidak boleh kosong.';
        echo json_encode($response);
        exit();
    }

    // 1. Verifikasi password lama
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $hashed_password_from_db = $row['password'];

        // Asumsi password di DB di-hash menggunakan password_hash()
        if (password_verify($old_password, $hashed_password_from_db)) {
            // Password lama cocok, hash password baru dan update
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_stmt->bind_param("si", $new_hashed_password, $user_id);

            if ($update_stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'Password berhasil diubah!';
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Gagal mengubah password: ' . $update_stmt->error;
            }
            $update_stmt->close();
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Password lama salah.';
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'User tidak ditemukan.';
    }

    $stmt->close();
} else {
    $response['status'] = 'error';
    $response['message'] = 'Metode request tidak diizinkan.';
}

echo json_encode($response);
$conn->close();
?>