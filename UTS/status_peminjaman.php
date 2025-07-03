<?php
session_start();
include 'koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$id_user = intval($_SESSION['user_id']);

// Ambil data peminjaman untuk user yang login
$sql = "
    SELECT p.id_peminjaman AS id_peminjaman, p.tanggal_pinjam, p.tanggal_kembali, p.status,
           b.judul_buku, b.penerbit, b.harga
    FROM peminjaman p
    JOIN buku_2401010595 b ON p.id_buku = b.id
    WHERE p.id_user = $id_user
    ORDER BY p.tanggal_pinjam DESC
";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Status Peminjaman</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --sidebar-width: 250px; }
        @media (min-width: 992px) {
            #staticSidebar {
                position: fixed;
                top: 0; left: 0;
                height: 100vh;
                width: var(--sidebar-width);
                background-color: #0d6efd;
                padding: 1rem;
                color: #fff;
            }
            #mainContent { margin-left: var(--sidebar-width); }
        }
    </style>
</head>
<body class="bg-light">

<!-- Navbar Mobile -->
<nav class="navbar navbar-dark bg-primary d-lg-none">
    <div class="container-fluid">
        <button class="btn btn-outline-light" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar">☰</button>
        <span class="navbar-text text-white ms-auto">
            Halo, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
        </span>
    </div>
</nav>

<!-- Sidebar Mobile -->
<div class="offcanvas offcanvas-start text-bg-primary d-lg-none" id="offcanvasSidebar">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">📚 Menu</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <ul class="nav flex-column">
            <li class="nav-item"><a href="user.php" class="nav-link text-white">📖 Lihat Buku</a></li>
            <li class="nav-item"><a href="status_peminjaman.php" class="nav-link text-white">📌 Status Peminjaman</a></li>
            <li class="nav-item mt-3"><a href="logout.php" class="btn btn-outline-light w-100">🚪 Logout</a></li>
        </ul>
    </div>
</div>

<!-- Sidebar Desktop -->
<aside id="staticSidebar" class="d-none d-lg-block">
    <h5>📚 Sistem Buku</h5>
    <p>👋 Halo, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></p>
    <hr class="border-light">
    <ul class="nav flex-column">
        <li class="nav-item"><a href="user.php" class="nav-link text-white">📖 Lihat Buku</a></li>
        <li class="nav-item"><a href="status_peminjaman.php" class="nav-link text-white">📌 Status Peminjaman</a></li>
        <li class="nav-item mt-3"><a href="logout.php" class="btn btn-outline-light">🚪 Logout</a></li>
    </ul>
</aside>

<!-- Main Content -->
<div id="mainContent" class="container py-4">
    <h3>Status Peminjaman Buku</h3>
    <div class="card shadow mt-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Judul Buku</th>
                            <th>Penerbit</th>
                            <th>Harga</th>
                            <th>Tanggal Pinjam</th>
                            <th>Tanggal Kembali</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['judul_buku']) ?></td>
                                    <td><?= htmlspecialchars($row['penerbit']) ?></td>
                                    <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                                    <td><?= $row['tanggal_pinjam'] ?></td>
                                    <td><?= $row['tanggal_kembali'] ?? '-' ?></td>
                                    <td><?= ucfirst($row['status']) ?></td>
                                    <td>
                                        <?php if ($row['status'] === 'dipinjam'): ?>
                                            <form method="post" action="kembalikan.php" onsubmit="return confirm('Yakin ingin mengembalikan buku ini?')">
                                                <input type="hidden" name="id_peminjaman" value="<?= $row['id_peminjaman'] ?>">
                                                <button class="btn btn-sm btn-danger">Kembalikan</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted">Selesai</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center py-3 text-muted">Belum ada peminjaman</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
