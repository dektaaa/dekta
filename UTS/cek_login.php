<?php
session_start();
include 'koneksi.php';

$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = mysqli_real_escape_string($conn, $_POST['password']);

$query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) === 1) {
    $data = mysqli_fetch_assoc($result);

    $_SESSION['username'] = $data['username'];
    $_SESSION['user_id']  = $data['id']; // ✅ Ini yang dibutuhkan untuk fitur pinjam/kembalikan

    // Redirect sesuai role (jika ada)
    if ($data['role'] === 'admin') {
        header("Location: index.php");
    } else {
        header("Location: user.php");
    }
    exit;
} else {
    header("Location: login.php?error=Username atau password salah!");
    exit;
}
