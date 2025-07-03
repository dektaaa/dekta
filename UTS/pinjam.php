<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['username']) || !isset($_POST['id_buku'])) {
    header("Location: user.php");
    exit;
}

$id_buku = intval($_POST['id_buku']);
$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];
$tanggal = date('Y-m-d');

// Cek apakah sudah dipinjam
$cek = mysqli_query($conn, "SELECT * FROM peminjaman WHERE id_buku=$id_buku AND status='dipinjam'");
if (mysqli_num_rows($cek) > 0) {
    header("Location: user.php?error=already_borrowed");
    exit;
}

// Lakukan peminjaman
mysqli_query($conn, "INSERT INTO peminjaman (id_buku, id_user, username, tanggal_pinjam, status) VALUES ($id_buku, $user_id, '$username', '$tanggal', 'dipinjam')");

header("Location: user.php?success=borrowed");
