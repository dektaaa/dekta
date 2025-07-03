<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
include 'koneksi.php'; // Pastikan file koneksi.php sudah terhubung dengan benar

// Ambil nama pengguna dari sesi
$loggedInUsername = $_SESSION['username'];

// Proses pencarian untuk daftar buku yang tersedia
$keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT * FROM buku_2401010595";
if ($keyword !== '') {
    $escaped = mysqli_real_escape_string($conn, $keyword);
    // Menambahkan pencarian berdasarkan kategori dan penerbit juga
    $sql .= " WHERE judul_buku LIKE '%$escaped%' OR kategori_buku LIKE '%$escaped%' OR penerbit LIKE '%$escaped%'";
}
$sql .= " ORDER BY tanggal_pembelian DESC"; // Tidak ada LIMIT di sini
$result = mysqli_query($conn, $sql);

// Hitung jumlah total buku dan jumlah penerbit unik
$total_buku = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM buku_2401010595"))['total'];
$total_penerbit = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT penerbit) AS total FROM buku_2401010595"))['total'];
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perpustakaan Digital - Katalog</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@3.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb; /* Light Gray Background */
        }
        /* Penyesuaian untuk search bar di navbar */
        .search-container-desktop {
            position: relative;
            margin-left: 1.5rem; /* Space from right side nav items */
        }
        .search-container-desktop .search-icon-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            color: #4b5563; /* text-gray-600 */
            transition: color 0.2s ease;
        }
        .search-container-desktop .search-icon-btn:hover {
            color: #6366f1; /* indigo-500 */
        }
        .search-container-desktop .search-input-wrapper {
            position: absolute;
            right: 0;
            top: 100%; /* Posisi di bawah ikon */
            width: 280px; /* Lebar search input */
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            z-index: 20;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: opacity 0.3s ease, transform 0.3s ease, visibility 0.3s ease;
        }
        .search-container-desktop .search-input-wrapper.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .search-container-desktop .search-input-wrapper form {
            display: flex;
            align-items: center;
            padding: 0.5rem;
        }
        .search-container-desktop .search-input-wrapper input {
            flex-grow: 1;
            border: 1px solid #d1d5db; /* gray-300 */
            border-radius: 0.375rem; /* rounded-md */
            padding: 0.5rem 0.75rem;
            margin-right: 0.5rem;
            font-size: 0.9rem;
        }
        .search-container-desktop .search-input-wrapper button {
            background-color: #6366f1; /* indigo-500 */
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem; /* rounded-md */
            font-size: 0.9rem;
        }

        /* Style untuk grid katalog buku */
        .katalog-section {
            /* display: grid;  Remove this for Swiper */
            /* grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); */
            gap: 2.25rem; /* Jarak antar kartu */
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }
        .katalog-section > div {
            border-radius: 0.75rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08); /* Shadow lebih halus */
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background-color: white;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            overflow: hidden; /* Pastikan gambar tidak keluar dari radius */
        }
        .katalog-section > div:hover {
            transform: translateY(-0.5rem);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15); /* Shadow lebih menonjol saat hover */
        }
        .katalog-section img {
            width: 100%;
            height: 320px; /* Tinggi gambar sedikit ditambah */
            object-fit: cover;
            border-top-left-radius: 0.75rem;
            border-top-right-radius: 0.75rem;
            transition: transform 0.3s ease;
        }
        .katalog-section img:hover {
            transform: scale(1.05); /* Zoom sedikit saat hover */
        }
        .katalog-section .keterangan {
            padding: 1.25rem; /* Padding sedikit lebih besar */
            text-align: left;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .katalog-section .keterangan h3 {
            font-size: 1.2rem; /* Ukuran font judul sedikit lebih besar */
            font-weight: 700; /* Lebih tebal */
            color: #1a202c; /* text-gray-900 */
            margin-bottom: 0.6rem;
        }
        .katalog-section .keterangan p {
            color: #4a5568; /* text-gray-700 */
            font-size: 0.9rem;
            line-height: 1.6; /* Line height sedikit lebih renggang */
            margin-bottom: 0.75rem;
        }
        .katalog-section .keterangan .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 2rem;
            border-radius: 0.75rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            width: 90%;
            max-width: 600px; /* Lebar maksimum modal kontak */
            position: relative;
            animation: fadeInScale 0.3s ease-out;
        }
        .modal-close {
            color: #aaa;
            position: absolute;
            top: 1rem;
            right: 1.5rem;
            font-size: 1.75rem;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.2s;
        }
        .modal-close:hover,
        .modal-close:focus {
            color: #333;
            text-decoration: none;
            cursor: pointer;
        }
        @keyframes fadeInScale {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .modal-open {
            overflow: hidden;
        }

        /* Style untuk gambar hero */
        #home .md:w-1/2 img {
            max-width: 450px; /* Batasi lebar maksimum gambar */
            height: auto; /* Biarkan tinggi menyesuaikan aspek rasio */
            display: block; /* Mencegah spasi ekstra di bawah gambar */
            margin-left: auto; /* Pusatkan gambar jika kurang dari lebar kontainer */
            margin-right: auto;
        }

        /* Custom Table Styles for Buku Dipinjam */
        .table-container {
            overflow-x: auto;
        }
        .table-responsive-custom {
            width: 100%;
            border-collapse: collapse;
        }
        .table-responsive-custom th,
        .table-responsive-custom td {
            padding: 0.85rem 1rem; /* Padding lebih proporsional */
            border: 1px solid #e2e8f0; /* border-gray-200 */
            text-align: left;
            vertical-align: middle;
        }
        .table-responsive-custom th {
            background-color: #e0e7ff; /* bg-indigo-100 */
            font-weight: 600;
            color: #312e81; /* text-indigo-900 */
            white-space: nowrap;
        }
        .table-responsive-custom tbody tr:nth-child(even) {
            background-color: #f1f5f9; /* bg-gray-100 */
        }
        .table-responsive-custom tbody tr:hover {
            background-color: #e2e8f0; /* hover:bg-gray-200 */
        }
        .badge {
            padding: 0.3em 0.6em;
            border-radius: 0.375rem; /* rounded-md */
            font-weight: 600;
            font-size: 0.8rem; /* Sedikit lebih besar */
            display: inline-block; /* Agar padding dan margin berfungsi baik */
            white-space: nowrap; /* Mencegah teks terpotong */
        }
        .badge-warning {
            background-color: #fcd34d; /* bg-yellow-400 */
            color: #78350f; /* text-yellow-900 */
        }
        .badge-success {
            background-color: #34d399; /* bg-green-400 */
            color: #064e3b; /* text-green-900 */
        }

        /* Custom Swiper styles for book catalog */
        .mySwiper {
            width: 100%;
            padding-bottom: 50px; /* Space for pagination */
        }

        .mySwiper .swiper-slide {
            display: flex; /* Ensure flex properties from .katalog-section are applied */
            flex-direction: column;
            height: auto; /* Allow height to adjust */
        }

        .mySwiper .swiper-pagination-bullet {
            background: #6366f1; /* Warna bullet aktif */
            opacity: 0.6;
        }

        .mySwiper .swiper-pagination-bullet-active {
            background: #4f46e5; /* Warna bullet aktif */
            opacity: 1;
        }

        .mySwiper .swiper-button-next,
        .mySwiper .swiper-button-prev {
            color: #4f46e5; /* Warna tombol navigasi */
            top: 50%; /* Tengah vertikal */
            transform: translateY(-50%);
            width: 40px; /* Ukuran tombol */
            height: 40px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.8);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: background-color 0.3s ease, color 0.3s ease;
            z-index: 10; /* Pastikan tombol di atas slide */
        }

        .mySwiper .swiper-button-next:hover,
        .mySwiper .swiper-button-prev:hover {
            background-color: rgba(255, 255, 255, 1);
            color: #312e81;
        }

        .mySwiper .swiper-button-next::after,
        .mySwiper .swiper-button-prev::after {
            font-size: 1.5rem; /* Ukuran ikon panah */
        }

        /* Responsive adjustments for slides per view */
        @media (max-width: 768px) {
            .mySwiper .swiper-button-next,
            .mySwiper .swiper-button-prev {
                display: none; /* Sembunyikan tombol navigasi di perangkat kecil */
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <header class="bg-white text-gray-800 py-4 shadow-md sticky top-0 z-10">
        <div class="container mx-auto px-6 flex justify-between items-center">
            <a href="#" class="text-2xl font-bold text-indigo-600 animated bounce">Perpustakaan Digital</a>
            <nav class="hidden md:flex items-center space-x-6">
                <ul class="flex space-x-6 items-center">
                    <li><a href="#home" class="hover:text-indigo-500 transition duration-300 font-medium animated fadeIn">Beranda</a></li>
                    <li><a href="#katalog" class="hover:text-indigo-500 transition duration-300 font-medium animated fadeIn">Katalog</a></li>
                    <li><a href="#buku-dipinjam" class="hover:text-indigo-500 transition duration-300 font-medium">Buku Dipinjam</a></li>
                    <li><a href="#layanan" class="hover:text-indigo-500 transition duration-300 font-medium">Layanan</a></li>
                    <li><a href="#" id="kontak-link" class="hover:text-indigo-500 transition duration-300 font-medium animated fadeIn">Kontak</a></li>
                </ul>
                <div class="search-container-desktop">
                    <button id="search-toggle-btn" class="search-icon-btn" aria-label="Toggle Search">
                        <i class="fas fa-search text-xl"></i>
                    </button>
                    <div id="search-input-wrapper" class="search-input-wrapper">
                        <form id="search-form-desktop" class="flex items-center">
                            <input
                                type="text"
                                id="search-box"
                                name="search"
                                placeholder="Cari buku..."
                                class="py-2 px-3 focus:outline-none focus:ring-1 focus:ring-indigo-400"
                                value="<?= htmlspecialchars($keyword) ?>"
                            />
                            <button type="submit" class="bg-indigo-500 text-white py-2 px-3 rounded-md hover:bg-indigo-600 transition duration-300">
                                Cari
                            </button>
                        </form>
                    </div>
                </div>
                <div class="flex items-center space-x-3 ml-4">
                    <span class="text-gray-700 font-medium">Halo, <strong class="text-indigo-600"><?= htmlspecialchars($loggedInUsername) ?></strong></span>
                    <a href="logout.php" class="bg-red-500 text-white py-1.5 px-4 rounded-full hover:bg-red-600 transition duration-300 text-sm font-semibold">Logout</a>
                </div>
            </nav>
            <button id="hamburger-btn" class="md:hidden text-gray-700 focus:outline-none" aria-label="Toggle Navigation">
                <i class="fas fa-bars text-xl"></i>
            </button>
        </div>
    </header>

    <div id="mobile-menu" class="hidden fixed inset-0 w-full h-full bg-gray-900 bg-opacity-90 z-20">
        <div class="bg-gray-800 w-72 h-full absolute right-0 p-6 shadow-xl animated slideInRight">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold text-white">Menu</h3>
                <button id="close-menu-btn" class="text-white focus:outline-none" aria-label="Close Menu">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <nav class="block">
                <ul class="space-y-5">
                    <li><a href="#home" class="block text-lg text-white hover:text-indigo-300 transition duration-300 font-medium">Beranda</a></li>
                    <li><a href="#katalog" class="block text-lg text-white hover:text-indigo-300 transition duration-300 font-medium">Katalog</a></li>
                    <li><a href="#buku-dipinjam" class="block text-lg text-white hover:text-indigo-300 transition duration-300 font-medium">Buku Dipinjam</a></li>
                    <li><a href="#layanan" class="block text-lg text-white hover:text-indigo-300 transition duration-300 font-medium">Layanan</a></li>
                    <li><a href="#" id="kontak-link-mobile" class="block text-lg text-white hover:text-indigo-300 transition duration-300 font-medium">Kontak</a></li>
                    <li class="mt-8">
                         <span class="block text-lg text-white font-medium">Halo, <strong class="text-indigo-300"><?= htmlspecialchars($loggedInUsername) ?></strong></span>
                    </li>
                    <li>
                        <a href="logout.php" class="block bg-red-500 text-white py-2 px-4 rounded-full hover:bg-red-600 transition duration-300 text-center font-semibold">Logout</a>
                    </li>
                    <li class="mt-8">
                        <form id="search-form-mobile" class="flex items-center">
                            <input
                                type="text"
                                id="search-box-mobile"
                                name="search"
                                placeholder="Cari buku..."
                                class="w-full py-2 px-3 rounded-l-full border border-gray-700 bg-gray-900 text-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-transparent"
                                value="<?= htmlspecialchars($keyword) ?>"
                            />
                            <button type="submit" class="bg-indigo-500 text-white py-2 px-3 rounded-r-full hover:bg-indigo-600 transition duration-300">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <div id="kontak-modal" class="modal">
        <div id="kontak-modal-content" class="modal-content animated zoomIn">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-semibold text-gray-800">Hubungi Kami</h2>
                <span id="kontak-modal-close" class="modal-close">&times;</span>
            </div>
            <form id="contact-form" class="space-y-6">
                <div>
                    <label for="nama" class="block text-gray-700 text-sm font-bold mb-2">Nama Anda</label>
                    <input type="text" id="nama" name="nama" placeholder="Nama Anda" required class="shadow appearance-none border rounded-md w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <div id="nama-error" class="text-red-500 text-xs italic" style="display: none;"></div>
                </div>
                <div>
                    <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Alamat Email</label>
                    <input type="email" id="email" name="email" placeholder="Alamat Email Anda" required class="shadow appearance-none border rounded-md w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <div id="email-error" class="text-red-500 text-xs italic" style="display: none;"></div>
                </div>
                <div>
                    <label for="pesan" class="block text-gray-700 text-sm font-bold mb-2">Pesan Anda</label>
                    <textarea id="pesan" name="pesan" placeholder="Tuliskan pesan Anda di sini..." required class="shadow appearance-none border rounded-md w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                    <div id="pesan-error" class="text-red-500 text-xs italic" style="display: none;"></div>
                </div>
                <button type="submit" class="bg-indigo-500 text-white py-4 px-8 rounded-full hover:bg-indigo-600 transition duration-300 w-full font-semibold text-lg">Kirim Pesan</button>
            </form>
        </div>
    </div>

    <main>
        <section id="home" class="container mx-auto px-6 py-20 flex flex-col md:flex-row items-center justify-between gap-12">
            <div class="md:w-1/2 text-center md:text-left animated fadeInLeft">
                <h1 class="text-5xl font-extrabold text-indigo-700 mb-6 leading-tight animated fadeIn">Selamat Datang, <span class="text-indigo-500"><?= htmlspecialchars($loggedInUsername) ?></span>!</h1>
                <p class="text-gray-700 mb-8 text-lg animated fadeIn">Jelajahi koleksi digital kami yang luas. Temukan buku, jurnal, dan sumber daya lainnya untuk mendukung pembelajaran dan penelitian Anda.</p>
                <a href="#katalog" class="bg-indigo-600 text-white py-4 px-10 rounded-full hover:bg-indigo-700 transition duration-300 font-semibold text-lg shadow-lg animated fadeInUp">Temukan Katalog Kami</a>
            </div>
            <div class="md:w-1/2 animated fadeInRight">
                <img src="image.png" alt="Ilustrasi Perpustakaan Digital" class="rounded-lg shadow-2xl transform hover:scale-102 transition duration-300">
            </div>
        </section>

        <section id="stats" class="bg-indigo-700 py-12">
            <div class="container mx-auto px-6 grid grid-cols-1 md:grid-cols-2 gap-8 text-center">
                <div class="bg-white text-indigo-800 p-8 rounded-lg shadow-xl animated fadeInUp">
                    <h6 class="text-xl font-medium mb-3">Jumlah Buku Tersedia</h6>
                    <h3 class="text-5xl font-bold"><?= $total_buku ?></h3>
                    <div class="text-6xl mt-4 opacity-80 text-indigo-600"><i class="fas fa-book"></i></div>
                </div>
                <div class="bg-white text-indigo-800 p-8 rounded-lg shadow-xl animated fadeInUp delay-200ms">
                    <h6 class="text-xl font-medium mb-3">Jumlah Penerbit Unik</h6>
                    <h3 class="text-5xl font-bold"><?= $total_penerbit ?></h3>
                    <div class="text-6xl mt-4 opacity-80 text-indigo-600"><i class="fas fa-building"></i></div>
                </div>
            </div>
        </section>

        <section id="katalog" class="bg-gray-50 py-16">
            <div class="container mx-auto px-6">
                <h2 class="text-4xl font-extrabold text-indigo-700 text-center mb-12 animated fadeIn">Katalog Buku Terbaru</h2>
                 <div class="flex justify-center mb-10 animated fadeInUp">
                    <form id="search-form-section" class="flex w-full max-w-2xl items-center bg-white rounded-full shadow-md">
                        <input
                            type="text"
                            id="search-box-section"
                            name="search"
                            placeholder="Cari buku berdasarkan judul, kategori, atau penerbit..."
                            class="w-full py-3.5 px-6 rounded-l-full border-none focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-lg"
                            value="<?= htmlspecialchars($keyword) ?>"
                        />
                        <button type="submit" class="bg-indigo-600 text-white py-3.5 px-6 rounded-r-full hover:bg-indigo-700 transition duration-300 text-lg font-semibold">
                            <i class="fas fa-search"></i> <span class="hidden md:inline">Cari</span>
                        </button>
                    </form>
                </div>

                <div class="swiper mySwiper">
                    <div id="book-container" class="swiper-wrapper katalog-section">
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <?php
                                $id_buku = $row['id'];
                                $user = $_SESSION['username'];
                                $cek_status = mysqli_query($conn, "SELECT * FROM peminjaman
                                    WHERE id_buku = $id_buku AND username = '$user' AND status = 'dipinjam'");
                                $dipinjam_user_ini = mysqli_fetch_assoc($cek_status);
                                // print_r( $dipinjam_user_ini);
                                ?>
                                <div class="swiper-slide animated fadeIn" data-book-id="<?= $row['id'] ?>">
                                    <img src="https://placehold.co/250x375/EEE/31343C?text=<?= urlencode(str_replace(' ', '+', $row['judul_buku'])) ?>" alt="<?= htmlspecialchars($row['judul_buku']) ?>">
                                    <div class="keterangan">
                                        <h3 class="text-lg font-semibold"><?= htmlspecialchars($row['judul_buku']) ?></h3>
                                        <p class="text-gray-600 text-sm mb-1">Kategori: <?= htmlspecialchars($row['kategori_buku']) ?></p>
                                        <p class="text-gray-600 text-sm mb-1">Penerbit: <?= htmlspecialchars($row['penerbit']) ?></p>
                                        <p class="text-gray-600 text-sm mb-1">Tahun: <?= htmlspecialchars($row['tahun']) ?></p>
                                        <p class="text-gray-600 text-sm mb-3">Harga: Rp <?= number_format($row['harga'], 0, ',', '.') ?></p>
                                        <p class="text-gray-700 text-sm mb-4 line-clamp-3"><?= htmlspecialchars($row['detail_buku'] ?: 'Tidak ada deskripsi tersedia.') ?></p>
                                        <?php if ($dipinjam_user_ini): ?>
                                            <form method="post" action="kembalikan.php" onsubmit="return confirm('Yakin ingin mengembalikan buku ini?')">
                                                <input type="hidden" name="id_peminjaman" value="<?= $dipinjam_user_ini['id_peminjaman']?>">
                                                <button type="submit" class="w-full bg-red-500 text-white py-2.5 px-4 rounded-md hover:bg-red-600 transition duration-300 font-semibold text-base">Kembalikan</button>
                                            </form>
                                        <?php else: ?>
                                            <form method="post" action="pinjam.php" onsubmit="return confirm('Yakin ingin meminjam buku ini?')">
                                                <input type="hidden" name="id_buku" value="<?= $row['id'] ?>">
                                                <button type="submit" class="w-full bg-green-500 text-white py-2.5 px-4 rounded-md hover:bg-green-600 transition duration-300 font-semibold text-base">Pinjam</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-center text-gray-600 w-full col-span-full text-lg py-10">Tidak ada buku ditemukan untuk pencarian "<?= htmlspecialchars($keyword) ?>".</p>
                        <?php endif; ?>
                    </div>
                    <div class="swiper-pagination"></div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                </div>
                </div>
        </section>

        <section id="buku-dipinjam" class="bg-white py-16">
            <div class="container mx-auto px-6">
                <h2 class="text-4xl font-extrabold text-indigo-700 text-center mb-12 animated fadeIn">Buku yang Sedang Dipinjam</h2>
                <div class="table-container rounded-lg shadow-lg overflow-hidden animated fadeInUp">
                    <table class="table-responsive-custom w-full">
                        <thead>
                            <tr>
                                <th>Judul Buku</th>
                                <th>Tanggal Pinjam</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $user = $_SESSION['username'];
                            $query_pinjam = mysqli_query($conn, "SELECT p.*, b.judul_buku FROM peminjaman p
                                                                 JOIN buku_2401010595 b ON b.id = p.id_buku
                                                                 WHERE p.username = '$user' ORDER BY p.tanggal_pinjam DESC");
                            if (mysqli_num_rows($query_pinjam) > 0):
                                while($pinjam = mysqli_fetch_assoc($query_pinjam)):
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($pinjam['judul_buku']) ?></td>
                                    <td><?= date('d-m-Y', strtotime($pinjam['tanggal_pinjam'])) ?></td>
                                    <td>
                                        <span class="badge <?= $pinjam['status']=='dipinjam' ? 'badge-warning' : 'badge-success' ?>">
                                            <?= ucfirst($pinjam['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; else: ?>
                                <tr><td colspan="3" class="text-center text-gray-500 py-6 text-base">Belum ada data peminjaman.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section id="layanan" class="bg-gray-50 py-16">
            <div class="container mx-auto px-6">
                <h2 class="text-4xl font-extrabold text-indigo-700 text-center mb-12 animated fadeIn">Layanan Kami</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                    <div class="bg-white rounded-lg shadow-lg p-8 hover:shadow-xl transition duration-300 animated fadeInUp">
                        <h3 class="text-2xl font-semibold text-indigo-600 mb-4"><i class="fas fa-book-open mr-3"></i> Akses Digital Tak Terbatas</h3>
                        <p class="text-gray-700 text-lg">Nikmati akses tak terbatas ke ribuan buku, jurnal, dan publikasi lainnya secara online dari mana saja.</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-lg p-8 hover:shadow-xl transition duration-300 animated fadeInUp delay-100ms">
                        <h3 class="text-2xl font-semibold text-indigo-600 mb-4"><i class="fas fa-search mr-3"></i> Dukungan Penelitian Profesional</h3>
                        <p class="text-gray-700 text-lg">Tim pustakawan kami siap membantu Anda dengan kebutuhan penelitian Anda. Konsultasi dan bantuan pencarian tersedia.</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-lg p-8 hover:shadow-xl transition duration-300 animated fadeInUp delay-200ms">
                        <h3 class="text-2xl font-semibold text-indigo-600 mb-4"><i class="fas fa-users mr-3"></i> Program Komunitas yang Aktif</h3>
                        <p class="text-gray-700 text-lg">Bergabunglah dengan komunitas kami melalui berbagai lokakarya, seminar, dan klub buku. Perluas pengetahuan Anda dan terhubung.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="kontak-section" class="bg-white py-16">
            <div class="container mx-auto px-6 text-center">
                <h2 class="text-4xl font-extrabold text-indigo-700 mb-10 animated fadeIn">Punya Pertanyaan? Kami Siap Membantu.</h2>
                <p class="text-gray-700 text-lg mb-10 max-w-3xl mx-auto animated fadeIn">Jangan ragu untuk menghubungi tim dukungan kami. Kami akan dengan senang hati membantu Anda.</p>
                <button id="kontak-btn" class="bg-indigo-600 text-white py-4 px-10 rounded-full hover:bg-indigo-700 transition duration-300 font-semibold text-lg shadow-lg animated fadeInUp">Hubungi Kami</button>
            </div>
        </section>
    </main>

    <footer class="bg-gray-900 text-white py-8 mt-16 rounded-md animated fadeIn">
        <div class="container mx-auto px-6 text-center">
            <p class="text-lg">&copy; 2024 Perpustakaan Digital. Semua Hak Cipta Dilindungi.</p>
            <div class="flex justify-center space-x-6 mt-4">
                <a href="#" class="text-gray-400 hover:text-white transition duration-300"><i class="fab fa-facebook-f text-xl"></i></a>
                <a href="#" class="text-gray-400 hover:text-white transition duration-300"><i class="fab fa-twitter text-xl"></i></a>
                <a href="#" class="text-gray-400 hover:text-white transition duration-300"><i class="fab fa-instagram text-xl"></i></a>
                <a href="#" class="text-gray-400 hover:text-white transition duration-300"><i class="fab fa-linkedin-in text-xl"></i></a>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        const hamburgerBtn = document.getElementById("hamburger-btn");
        const mobileMenu = document.getElementById("mobile-menu");
        const closeMenuBtn = document.getElementById("close-menu-btn");
        const mobileMenuLinks = mobileMenu.querySelectorAll("a");
        const navLinks = document.querySelectorAll('header nav ul li a'); // Mengambil link di header
        const sections = document.querySelectorAll('main section');

        // Form dan input pencarian
        const searchToggleBtn = document.getElementById("search-toggle-btn");
        const searchInputWrapper = document.getElementById("search-input-wrapper");
        const searchFormDesktop = document.getElementById("search-form-desktop");
        const searchBox = document.getElementById("search-box");
        const searchFormMobile = document.getElementById("search-box-mobile");
        const searchFormSection = document.getElementById("search-form-section");
        const searchBoxSection = document.getElementById("search-box-section");

        // Kontak Modal
        const kontakBtn = document.getElementById("kontak-btn");
        const kontakModal = document.getElementById("kontak-modal");
        const kontakModalClose = document.getElementById("kontak-modal-close");
        const kontakLink = document.getElementById("kontak-link");
        const kontakLinkMobile = document.getElementById("kontak-link-mobile");
        const contactForm = document.getElementById("contact-form");

        // Fungsi untuk toggle menu mobile
        function toggleMobileMenu() {
            mobileMenu.classList.toggle("hidden");
            if (!mobileMenu.classList.contains('hidden')) {
                document.body.classList.add('modal-open'); // Menambahkan kelas untuk overflow hidden
            } else {
                document.body.classList.remove('modal-open');
            }
        }

        hamburgerBtn.addEventListener("click", toggleMobileMenu);
        closeMenuBtn.addEventListener("click", toggleMobileMenu);
        mobileMenuLinks.forEach(link => {
            link.addEventListener("click", toggleMobileMenu);
        });

        // Event listener untuk menutup mobile menu jika klik di luar
        document.addEventListener('click', (event) => {
            if (!mobileMenu.classList.contains('hidden') && !mobileMenu.contains(event.target) && event.target !== hamburgerBtn) {
                toggleMobileMenu();
            }
        });

        // Fungsi untuk mengupdate kelas 'active' pada navigasi berdasarkan scroll
        function updateActiveNavLink() {
            let currentSectionId = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                // Menyesuaikan offset agar link aktif saat bagian terlihat di layar
                if (window.scrollY >= sectionTop - 150 && window.scrollY < sectionTop + sectionHeight - 150) {
                    currentSectionId = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                // Menghapus kelas aktif dari semua link
                link.classList.remove('text-indigo-600', 'font-bold');
                // Menambahkan kelas aktif ke link yang sesuai
                if (link.getAttribute('href').slice(1) === currentSectionId) {
                    link.classList.add('text-indigo-600', 'font-bold');
                }
            });
        }

        window.addEventListener('scroll', updateActiveNavLink);
        document.addEventListener('DOMContentLoaded', updateActiveNavLink); // Panggil saat DOM dimuat

        // Smooth scroll untuk navigasi
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);

                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Toggle search input di desktop
        searchToggleBtn.addEventListener("click", (event) => {
            event.stopPropagation(); // Mencegah event klik menyebar ke window
            searchInputWrapper.classList.toggle("active");
            if (searchInputWrapper.classList.contains('active')) {
                searchBox.focus(); // Fokuskan input saat dibuka
            }
        });

        // Tutup search input jika klik di luar area search
        window.addEventListener("click", (event) => {
            if (searchInputWrapper.classList.contains('active') && !searchInputWrapper.contains(event.target) && event.target !== searchToggleBtn) {
                searchInputWrapper.classList.remove("active");
            }
        });

        // Event listeners untuk SUBMIT form pencarian
        if (searchFormDesktop) {
            searchFormDesktop.addEventListener("submit", (event) => {
                event.preventDefault(); // Mencegah refresh halaman default form
                const keyword = searchBox.value;
                window.location.href = `user.php?search=${encodeURIComponent(keyword)}#katalog`;
            });
        }

        if (searchFormMobile) {
            searchFormMobile.addEventListener("submit", (event) => {
                event.preventDefault(); // Mencegah refresh halaman default form
                const keyword = searchBoxMobile.value;
                window.location.href = `user.php?search=${encodeURIComponent(keyword)}#katalog`;
            });
        }

        if (searchFormSection) {
            searchFormSection.addEventListener("submit", (event) => {
                event.preventDefault(); // Mencegah refresh halaman default form
                const keyword = searchBoxSection.value;
                window.location.href = `user.php?search=${encodeURIComponent(keyword)}#katalog`;
            });
        }

        // Fungsi dan event listener untuk modal kontak
        function openModal(modalElement) {
            modalElement.style.display = "flex"; // Menggunakan flex untuk centering
            document.body.classList.add('modal-open');
        }

        function closeModal(modalElement) {
            modalElement.style.display = "none";
            document.body.classList.remove('modal-open');
        }

        kontakBtn.addEventListener("click", () => openModal(kontakModal));
        kontakLink.addEventListener("click", (e) => { e.preventDefault(); openModal(kontakModal); });
        kontakLinkMobile.addEventListener("click", (e) => { e.preventDefault(); openModal(kontakModal); });
        kontakModalClose.addEventListener("click", () => closeModal(kontakModal));

        // Tutup modal jika klik di luar konten modal
        window.addEventListener('click', (event) => {
            if (event.target === kontakModal) {
                closeModal(kontakModal);
            }
        });

        // Validasi form kontak (dari kode lama)
        contactForm.addEventListener('submit', (event) => {
            event.preventDefault();

            let hasErrors = false;

            const namaInput = document.getElementById('nama');
            const emailInput = document.getElementById('email');
            const pesanInput = document.getElementById('pesan');
            const namaError = document.getElementById('nama-error');
            const emailError = document.getElementById('email-error');
            const pesanError = document.getElementById('pesan-error');

            if (!namaInput.value.trim()) {
                namaError.textContent = "Nama harus diisi";
                namaError.style.display = "block";
                hasErrors = true;
            } else {
                namaError.style.display = "none";
            }

            if (!emailInput.value.trim()) {
                emailError.textContent = "Email harus diisi";
                emailError.style.display = "block";
                hasErrors = true;
            } else if (!isValidEmail(emailInput.value.trim())) {
                emailError.textContent = "Email tidak valid";
                emailError.style.display = "block";
                hasErrors = true;
            } else {
                emailError.style.display = "none";
            }

            if (!pesanInput.value.trim()) {
                pesanError.textContent = "Pesan harus diisi";
                pesanError.style.display = "block";
                hasErrors = true;
            } else {
                pesanError.style.display = "none";
            }

            if (!hasErrors) {
                alert("Pesan Anda telah terkirim! (Simulasi)"); // Ganti dengan AJAX request sebenarnya jika ada backend untuk kontak
                closeModal(kontakModal);
                contactForm.reset();
            }
        });

        function isValidEmail(email) {
            const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(String(email).toLowerCase());
        }

        // Initialize Swiper
        document.addEventListener('DOMContentLoaded', function() {
            // Hanya inisialisasi Swiper jika ada lebih dari 4 buku
            const bookContainer = document.getElementById('book-container');
            const totalBooks = bookContainer ? bookContainer.children.length : 0;

            if (totalBooks > 4) {
                new Swiper(".mySwiper", {
                    slidesPerView: 1, // Default untuk mobile
                    spaceBetween: 30,
                    pagination: {
                        el: ".swiper-pagination",
                        clickable: true,
                    },
                    navigation: {
                        nextEl: ".swiper-button-next",
                        prevEl: ".swiper-button-prev",
                    },
                    breakpoints: {
                        // when window width is >= 640px
                        640: {
                            slidesPerView: 2,
                            spaceBetween: 20,
                        },
                        // when window width is >= 768px
                        768: {
                            slidesPerView: 3,
                            spaceBetween: 30,
                        },
                        // when window width is >= 1024px
                        1024: {
                            slidesPerView: 4, // Tampilkan 4 buku di layar lebar
                            spaceBetween: 40,
                        },
                    },
                });
            } else {
                // Jika buku kurang dari atau sama dengan 4, pastikan tampilan tetap sebagai grid
                // Hapus kelas swiper-wrapper dan berikan kembali properti grid ke book-container
                if (bookContainer) {
                    bookContainer.classList.remove('swiper-wrapper');
                    bookContainer.style.display = 'grid'; // Kembalikan ke grid
                    bookContainer.style.gridTemplateColumns = 'repeat(auto-fill, minmax(260px, 1fr))';
                    bookContainer.style.gap = '2.25rem';
                }
                // Sembunyikan pagination dan navigation jika tidak ada slide
                const pagination = document.querySelector('.mySwiper .swiper-pagination');
                const nextButton = document.querySelector('.mySwiper .swiper-button-next');
                const prevButton = document.querySelector('.mySwiper .swiper-button-prev');

                if (pagination) pagination.style.display = 'none';
                if (nextButton) nextButton.style.display = 'none';
                if (prevButton) prevButton.style.display = 'none';
            }
        });

    </script>
</body>
</html>