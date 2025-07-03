<?php
include 'koneksi.php';

// Pastikan ID valid dan ada di URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='alert alert-danger'>ID tidak valid.</div>";
    exit;
}

$id = (int) $_GET['id'];

// Cek apakah buku sedang dipinjam
$cekPinjam = mysqli_query($conn, "SELECT COUNT(*) AS total FROM peminjaman WHERE id_buku = $id");
$data = mysqli_fetch_assoc($cekPinjam);

if ($data['total'] > 0) {
    // Buku sedang dipinjam, tidak boleh dihapus
    echo "<div class='alert alert-warning'>Buku tidak dapat dihapus karena sedang dipinjam.</div>";
    echo "<a href='index.php' class='btn btn-primary mt-2'>Kembali ke Daftar Buku</a>";
    exit;
}

// Jika tidak sedang dipinjam, lanjutkan penghapusan
$query = "DELETE FROM buku_2401010595 WHERE id = $id";
$result = mysqli_query($conn, $query);

if ($result) {
    header("Location: index.php");
    exit;
} else {
    echo "<div class='alert alert-danger'>Gagal menghapus data: " . mysqli_error($conn) . "</div>";
    echo "<a href='index.php' class='btn btn-primary mt-2'>Kembali ke Daftar Buku</a>";
}
?>
