<?php
include 'koneksi.php';

// Tangkap semua data dari form
$id                = (int) $_POST['id'];
$judul             = $_POST['judul'];
$kategori_buku     = $_POST['kategori_buku'];
$penerbit          = $_POST['penerbit'];
$tahun             = $_POST['tahun'];
$tanggal_pembelian = $_POST['tanggal_pembelian'];
$harga             = $_POST['harga'];

// Validasi sederhana
// if (!$id || !$judul || !$kategori_buku || !$penerbit || !$tahun || !$tanggal_pembelian || !$harga) {
//     die("Data tidak lengkap.");
// }

// Update data
$query = "UPDATE buku_2401010595 SET
    judul_buku = '$judul',
    kategori_buku = '$kategori_buku',
    penerbit = '$penerbit',
    tahun = '$tahun',
    tanggal_pembelian = '$tanggal_pembelian',
    harga = '$harga'
    WHERE id = $id";

if (mysqli_query($conn, $query)) {
    echo "Data berhasil diperbarui. <a href='index.php'>Kembali</a>";
} else {
    echo "Gagal memperbarui data: " . mysqli_error($conn);
}
?>
