<?php include 'koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Buku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Define consistent theme colors */
        :root {
            --color-main: #17a2b8; /* Biru cerah/teal */
            --main-accent: #e0f2f7; /* Biru sangat muda */
            --bg: #f0f8ff; /* AliceBlue */
            --bg-2: #e3f2fd; /* Light Blue */
            --main: #283593; /* Biru tua/indigo */
            --shadow: rgba(17, 17, 26, 0.1) 2px 0px 16px;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .container {
            max-width: 700px; /* Max width for the form */
            margin-top: 30px;
            margin-bottom: 30px;
        }

        .card {
            border: 1px solid #e0e0e0;
            border-radius: 15px;
            overflow: hidden; /* Ensures child elements respect border-radius */
            box-shadow: rgba(17, 17, 26, 0.08) 0px 4px 16px, rgba(17, 17, 26, 0.05) 0px 8px 32px;
        }

        .card-header {
            background-color: var(--color-main) !important; /* Override Bootstrap primary */
            color: white !important;
            padding: 1.5rem 2rem;
            font-weight: 600;
            font-size: 1.5rem;
            border-bottom: none; /* Remove default border */
        }

        .card-body {
            padding: 2.5rem; /* Increased padding */
        }

        .form-label {
            font-weight: 500;
            color: var(--main); /* Dark blue for labels */
            margin-bottom: 0.5rem;
        }

        .form-control,
        .form-select {
            border-radius: 8px; /* More rounded inputs */
            padding: 0.8rem 1rem;
            border: 1px solid #ced4da;
            transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--color-main);
            box-shadow: 0 0 0 0.25rem rgba(23, 162, 184, 0.25); /* Custom focus shadow */
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 500;
            transition: background-color 0.2s ease-in-out, transform 0.1s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .btn-success {
            background-color: var(--color-main); /* Match main theme color */
            border-color: var(--color-main);
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-success:hover {
            background-color: #148ea1; /* Slightly darker teal */
            border-color: #148ea1;
            transform: translateY(-2px);
            box-shadow: rgba(17, 17, 26, 0.1) 0px 4px 8px;
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #5a6268;
            transform: translateY(-2px);
            box-shadow: rgba(17, 17, 26, 0.1) 0px 4px 8px;
        }

        /* Adjust button group spacing */
        form button, form a.btn {
            margin-right: 10px; /* Space between buttons */
        }

        @media (max-width: 576px) {
            .card-body {
                padding: 1.5rem;
            }
            .card-header {
                font-size: 1.3rem;
            }
            .btn {
                width: 100%;
                margin-right: 0;
                margin-bottom: 10px; /* Stack buttons on small screens */
            }
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header">
                <h4 class="mb-0">Tambah Data Buku</h4>
            </div>
            <div class="card-body">
                <form action="store.php" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="judul_buku" class="form-label">Judul Buku</label>
                        <input type="text" name="judul_buku" id="judul_buku" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="kategori_buku" class="form-label">Kategori Buku</label>
                        <select name="kategori_buku" id="kategori_buku" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="Novel">Novel</option>
                            <option value="Cerpen">Cerpen</option>
                            <option value="Komik">Komik</option>
                            <option value="Biografi">Biografi</option>
                            </select>
                    </div>
                    <div class="mb-3">
                        <label for="penerbit" class="form-label">Penerbit</label>
                        <input type="text" name="penerbit" id="penerbit" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="tahun" class="form-label">Tahun Terbit</label>
                        <input type="text" name="tahun" id="tahun" maxlength="4" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="tanggal_pembelian" class="form-label">Tanggal Pembelian</label>
                        <input type="date" name="tanggal_pembelian" id="tanggal_pembelian" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="harga" class="form-label">Harga</label>
                        <input type="number" name="harga" id="harga" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="detail_buku" class="form-label">Detail Buku</label>
                        <textarea name="detail_buku" id="detail_buku" class="form-control" rows="4"></textarea>
                    </div>
                    <div class="d-flex justify-content-start">
                        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan</button>
                        <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// This part should be in store.php, not in the same file as the form.
// For demonstration, I'll include it here, but best practice is separation.
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul      = $_POST['judul_buku'];
    $kategori   = $_POST['kategori_buku'];
    $penerbit   = $_POST['penerbit'];
    $tahun      = $_POST['tahun'];
    $tanggal    = $_POST['tanggal_pembelian'];
    $harga      = $_POST['harga'];
    $detail     = $_POST['detail_buku'];

    // Using prepared statements to prevent SQL injection
    $query = "INSERT INTO buku_2401010595 (judul_buku, kategori_buku, penerbit, tahun, tanggal_pembelian, harga, detail_buku) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);

    // 'sssssis' corresponds to string, string, string, string, string, integer, string
    mysqli_stmt_bind_param($stmt, 'sssssis', $judul, $kategori, $penerbit, $tahun, $tanggal, $harga, $detail);

    if (mysqli_stmt_execute($stmt)) {
        // Redirect with a success message
        header('Location: index.php?success=1');
        exit;
    } else {
        // Handle error (e.g., display error message, log error)
        echo "<script>alert('Gagal menyimpan data: " . mysqli_error($conn) . "'); window.location.href='create.php';</script>";
    }
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>