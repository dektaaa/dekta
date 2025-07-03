<?php
include 'koneksi.php';

$judul_buku = $_POST['judul_buku'];
$kategori_buku = $_POST['kategori_buku'];
$penerbit = $_POST['penerbit'];
$tahun = $_POST['tahun'];
$tanggal_pembelian = $_POST['tanggal_pembelian'];
$harga = $_POST['harga'];
$detail_buku = $_POST['detail_buku'] ?? '';


$query = "INSERT INTO buku_2401010595 (judul_buku, kategori_buku, penerbit, tahun, tanggal_pembelian, harga) 
          VALUES ('$judul_buku', '$kategori_buku', '$penerbit', '$tahun', '$tanggal_pembelian', '$harga')";

mysqli_query($conn, $query);

header("Location: index.php");
?>
