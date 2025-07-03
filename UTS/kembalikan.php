<?php
session_start();
include 'koneksi.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
// echo $_POST['id_peminjaman'];
// Validasi ID peminjaman
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_peminjaman'])) {
    $id_peminjaman = intval($_POST['id_peminjaman']);

    // Cek apakah peminjaman memang milik user ini dan statusnya masih dipinjam
    $id_user = intval($_SESSION['user_id']);
    $check = mysqli_query($conn, "SELECT * FROM peminjaman WHERE id_peminjaman = $id_peminjaman AND id_user = $id_user AND status = 'dipinjam'");
    echo $id_user;
    if (mysqli_num_rows($check) > 0) {
        // Update status ke dikembalikan
        $tanggal_kembali = date('Y-m-d');
        $update = mysqli_query($conn, "
            UPDATE peminjaman 
            SET status = 'dikembalikan', tanggal_kembali = '$tanggal_kembali'
            WHERE id_peminjaman = $id_peminjaman
        ");

        if ($update) {
            header("Location: status_peminjaman.php?msg=success");
            exit;
        } else {
            echo "Gagal mengupdate status peminjaman.";
        }
    } else {
        echo "Data peminjaman tidak ditemukan atau tidak valid.";
    }
} else {
    echo "Permintaan tidak valid.";
}
?>
