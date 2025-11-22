<?php
// login.php
include 'config.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $response['status'] = 'error';
        $response['message'] = 'Username dan password harus diisi!';
        echo json_encode($response);
        exit();
    }

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = $row['id'];
        $hashed_password_from_db = $row['password'];

        if (password_verify($password, $hashed_password_from_db)) {
            $response['status'] = 'success';
            $response['message'] = 'Login berhasil!';
            $response['user_id'] = $user_id; // <--- PENTING: TAMBAHKAN INI
            $response['username'] = $username;
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Password salah!';
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Username tidak ditemukan!';
    }

    $stmt->close();
} else {
    $response['status'] = 'error';
    $response['message'] = 'Metode request tidak diizinkan.';
}

echo json_encode($response);
$conn->close();
?>