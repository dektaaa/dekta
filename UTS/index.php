<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
include 'koneksi.php';

$search   = $_GET['search']   ?? '';
$sort     = $_GET['sort']     ?? 'judul_buku';
$order    = strtoupper($_GET['order'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
$page     = max(1, intval($_GET['page'] ?? 1));
$perPage  = 10;
$offset   = ($page - 1) * $perPage;

$sortable = ['judul_buku','kategori_buku','penerbit','tahun','harga'];
if (!in_array($sort, $sortable)) {
    $sort = 'judul_buku';
}

$where = '';
if ($search !== '') {
    $s = mysqli_real_escape_string($conn, $search);
    $where = "WHERE judul_buku LIKE '%{$s}%'
              OR kategori_buku LIKE '%{$s}%'
              OR penerbit LIKE '%{$s}%'";
}

$totalRes = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM buku_2401010595 $where");
$totalRow = mysqli_fetch_assoc($totalRes)['cnt'];
$totalPage = ceil($totalRow / $perPage);

$sql = "SELECT * FROM buku_2401010595
        $where
        ORDER BY `$sort` $order
        LIMIT $perPage OFFSET $offset";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Data Buku</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Base styles */
        :root {
            --color-main: #17a2b8; /* Biru cerah/teal */
            --main-accent: #e0f2f7; /* Biru sangat muda */
            --bg: #f0f8ff; /* AliceBlue */
            --bg-2: #e3f2fd; /* Light Blue */
            --main: #283593; /* Biru tua/indigo */
            --shadow: rgba(17, 17, 26, 0.1) 2px 0px 16px;
        }
        * {
            padding: 0;
            margin: 0;
            text-decoration: none;
            font-family: "Poppins", sans-serif;
            list-style-type: none;
            box-sizing: border-box;
        }
        body {
            background-color: var(--bg);
            overflow-x: hidden;
            color: #333; /* Default text color */
        }
        img {
            max-width: 100%;
            height: auto;
        }
        #menu-toggle {
            display: none;
        }
        #menu-toggle:checked ~ .sidebar {
            left: -345px;
        }
        #menu-toggle:checked ~ .main-content {
            margin-left: 0;
            width: 100vw;
        }
        .overlay {
            position: fixed;
            width: 100%;
            height: 100%;
            left: 0;
            top: 0;
            z-index: 999; /* Higher z-index for overlay */
            background-color: rgba(0, 0, 0, 0.6); /* Darker overlay */
            display: none;
            cursor: pointer;
        }
        .overlay label {
            display: block;
            height: 100%;
            width: 100%;
        }

        /* helper */
        .text-danger {
            color: #dc3545;
        }
        .text-success {
            color: #2ec3a3;
        }
        .text-main {
            color: var(--color-main);
        }

        /* Sortable table headers */
        .sortable {
            color: inherit;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            font-weight: 600; /* Make sortable headers bolder */
        }
        .sortable:hover {
            text-decoration: underline;
        }
        .sort-icon {
            font-size: 0.8em;
            vertical-align: middle;
            transition: transform 0.2s ease-in-out;
        }
        /* Rotates icon when sorting DESC */
        .sortable .sort-icon.asc { transform: rotate(0deg); }
        .sortable .sort-icon.desc { transform: rotate(180deg); }


        /* Sidebar */
        .sidebar {
            width: 345px;
            height: 100%;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            padding: 1rem 1.2rem;
            transition: left 300ms;
            z-index: 1000; /* Ensure sidebar is above other content */
        }
        .sidebar-container {
            height: 100%;
            width: 100%;
            background-color: #fff;
            border-radius: 20px;
            box-shadow: var(--shadow);
            padding: 1.2rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }
        .sidebar-container::-webkit-scrollbar {
            width: 5px;
        }
        .sidebar-container::-webkit-scrollbar-track {
            box-shadow: var(--shadow);
        }
        .sidebar-container::-webkit-scrollbar-thumb {
            background-color: var(--main-accent);
            outline: 1px solid #ccc;
            border-radius: 2px;
        }
        .brand {
            padding-top: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
        }
        .brand h3 {
            color: var(--main);
            font-size: 2rem;
            font-weight: 700;
        }
        .brand h3 span {
            color: var(--color-main);
            font-size: 2rem;
            display: inline-block;
            margin-right: 0.5rem;
        }
        .sidebar-avatar {
            display: grid;
            grid-template-columns: 70px auto;
            align-items: center;
            border: 2px solid var(--main-accent);
            background-color: var(--bg-2); /* Light blue background */
            padding: 0.1rem 0.7rem;
            border-radius: 7px;
            margin-bottom: 2rem;
        }
        .sidebar-avatar img {
            width: 40px;
            border-radius: 10px;
            margin: 5px 0;
            object-fit: cover;
        }
        .avatar-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-left: 0.5rem;
        }
        .avatar-info h4 {
            font-weight: 600;
            color: var(--main);
            font-size: 1rem;
        }
        .avatar-info small {
            font-size: 0.8rem;
            color: #666;
        }
        .sidebar-menu {
            flex-grow: 1; /* Allows menu to take available space */
        }
        .sidebar-menu li {
            margin-bottom: 1rem;
        }
        .sidebar-menu a {
            color: var(--main);
            display: flex;
            align-items: center;
            padding: 0.7rem 1.2rem;
            border-radius: 7px;
            transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out;
            font-weight: 500;
        }
        .sidebar-menu a.active, .sidebar-menu a:hover {
            background-color: var(--main-accent);
            color: var(--color-main);
        }
        .sidebar-menu a span:first-child {
            display: inline-block;
            margin-right: 0.8rem;
            font-size: 1.5rem;
        }
        .sidebar-card {
            background-color: var(--main-accent);
            padding: 1rem;
            margin-top: auto; /* Pushes card to the bottom */
            box-shadow: var(--shadow);
            text-align: center;
            border-radius: 15px;
            border: 1px solid var(--color-main);
        }
        .side-card-icon span {
            font-size: 5rem; /* Smaller icon for better fit */
            color: var(--color-main);
            display: inline-block;
            margin-bottom: 0.5rem;
        }
        .sidebar-card h4 {
            font-size: 1.1rem;
            color: var(--main);
            margin-bottom: 0.5rem;
        }
        .sidebar-card p {
            font-size: 0.8rem;
            color: #555;
            margin-bottom: 1rem;
        }
        .btn {
            padding: 0.7rem 1.2rem;
            border: none;
            border-radius: 10px;
            display: inline-flex; /* Use inline-flex for button groups */
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        .btn span {
            font-size: 1rem; /* Slightly smaller icon for buttons */
            display: inline-block;
            margin-right: 0.5rem;
        }
        .btn-block {
            display: block;
            width: 100%;
        }
        .btn-main {
            background-color: var(--color-main);
            color: #fff;
        }
        .btn-main:hover {
            background-color: #148ea1; /* Slightly darker teal */
            box-shadow: rgba(17, 17, 26, 0.2) 0px 4px 8px;
        }
        .btn-danger {
            background-color: #dc3545; /* Red */
            color: #fff;
            margin-left: 0.5rem;
        }
        .btn-danger:hover {
            background-color: #c82333; /* Darker red */
            box-shadow: rgba(17, 17, 26, 0.2) 0px 4px 8px;
        }
        .btn-warning {
            background-color: #ffc107; /* Orange */
            color: #212529; /* Dark text */
        }
        .btn-warning:hover {
            background-color: #e0a800; /* Darker orange */
            box-shadow: rgba(17, 17, 26, 0.2) 0px 4px 8px;
        }

        /* Main Content */
        .main-content {
            margin-left: 345px;
            width: calc(100vw - 345px);
            padding: 1.5rem 2.5rem; /* Increased padding */
            transition: margin-left 300ms, width 300ms;
        }
        /* Header */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem; /* More space below header */
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        .header-wrapper {
            display: flex;
            align-items: center;
        }
        .header-wrapper label {
            display: inline-block;
            color: var(--color-main);
            margin-right: 1.5rem; /* Adjusted margin */
            font-size: 2rem; /* Larger icon */
            cursor: pointer;
            transition: color 0.2s ease-in-out;
        }
        .header-wrapper label:hover {
            color: #148ea1;
        }
        .header-title h1 {
            color: var(--main);
            font-weight: 600;
            font-size: 1.8rem;
            margin-bottom: 0.2rem;
        }
        .header-title p {
            color: #666;
            font-size: 0.9rem;
        }
        .header-title p span {
            color: var(--color-main); /* Changed to main color for consistency */
            font-size: 1.1rem;
            display: inline-block;
            margin-left: 0.5rem;
        }
        .header-action .btn {
            padding: 0.75rem 1.5rem;
            font-size: 0.95rem;
        }

        /* Main content sections */
        main {
            padding-top: 1rem; /* Adjust main padding */
        }
        .section-head {
            font-size: 1.6rem; /* Larger section heading */
            color: var(--main);
            font-weight: 600;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid var(--main-accent);
            padding-bottom: 0.5rem;
        }

        /* Analytics/Summary Cards */
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); /* Default responsive grid */
            gap: 2rem;
            margin-bottom: 3rem;
        }

        /* ** NEW: Force 2 columns on larger screens ** */
        @media only screen and (min-width: 768px) {
            .analytics-grid {
                grid-template-columns: repeat(2, 1fr); /* 2 columns, equal width */
            }
        }
        /* ** END NEW ** */

        .analytic-card {
            background-color: #fff;
            box-shadow: var(--shadow);
            padding: 1.8rem; /* Increased padding */
            border-radius: 15px; /* More rounded corners */
            display: flex;
            align-items: center;
            border: 1px solid #eee;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        .analytic-card:hover {
            transform: translateY(-5px);
            box-shadow: rgba(17, 17, 26, 0.15) 0px 8px 24px;
        }
        .analytic-icon {
            width: 50px; /* Larger icon container */
            height: 50px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            margin-right: 1rem;
            font-size: 1.8rem; /* Larger icon size */
            color: #fff; /* White icon color */
        }
        /* Adjusted colors for blue theme */
        .analytic-card:nth-child(1) .analytic-icon { background-color: #2196f3; } /* Material Blue 500 */
        .analytic-card:nth-child(2) .analytic-icon { background-color: #4caf50; } /* Green for variety */
        .analytic-card:nth-child(3) .analytic-icon { background-color: #03a9f4; } /* Light Blue 500 */
        .analytic-card:nth-child(4) .analytic-icon { background-color: #f44336; } /* Red for error/warning */

        .analytic-info h4 {
            font-weight: 500;
            color: #555;
            font-size: 0.9rem;
            margin-bottom: 0.2rem;
        }
        .analytic-info h1 {
            color: var(--main);
            font-weight: 700;
            font-size: 1.8rem;
        }

        /* Search Form */
        .search-form-container {
            margin-bottom: 2.5rem;
            background-color: #fff;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: var(--shadow);
            border: 1px solid #eee;
        }
        .search-form {
            display: flex;
            gap: 15px; /* Increased gap */
            flex-wrap: wrap; /* Allow wrapping on small screens */
            align-items: center;
        }
        .search-form input[type="search"] {
            flex-grow: 1;
            padding: 0.8rem 1.2rem; /* Increased padding */
            border: 1px solid #ddd;
            border-radius: 8px; /* More rounded */
            font-size: 1rem;
            outline: none;
            transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            max-width: 350px; /* Limit search input width */
        }
        .search-form input[type="search"]:focus {
            border-color: var(--color-main);
            box-shadow: 0 0 0 3px rgba(23, 162, 184, 0.2); /* Adjust shadow color to main */
        }
        .search-form button {
            background-color: var(--main); /* Dark blue for search button */
            color: #fff;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        .search-form button:hover {
            background-color: #1a237e; /* Darker blue */
            box-shadow: rgba(17, 17, 26, 0.2) 0px 4px 8px;
        }

        /* Table Specific Styles */
        .card {
            background-color: #fff;
            box-shadow: var(--shadow);
            border-radius: 15px;
            margin-bottom: 2rem;
            overflow: hidden; /* For rounded corners on table container */
            border: 1px solid #eee;
        }
        .card-header {
            background-color: var(--color-main);
            color: #fff;
            padding: 1.2rem 1.8rem; /* Increased padding */
            font-size: 1.3rem; /* Larger font size */
            font-weight: 700;
            border-bottom: 1px solid rgba(0,0,0,.125);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .card-header .icon {
            font-size: 1.5rem;
        }
        .card-body {
            padding: 0;
        }
        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
        }
        .data-table thead {
            background-color: #f8f9fa;
            border-bottom: 2px solid #ddd;
        }
        .data-table th, .data-table td {
            padding: 1rem 0.8rem; /* Increased padding */
            border: 1px solid #eee; /* Lighter borders */
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
            max-width: 150px; /* Increased max-width for better readability */
            font-size: 0.9rem;
            color: #444;
        }
        .data-table th {
            font-weight: 600;
            background-color: #f0f0f0;
            color: var(--main);
        }
        /* Specific column widths adjusted */
        .data-table td:nth-child(1) { max-width: 180px; } /* Judul */
        .data-table td:nth-child(2), .data-table td:nth-child(3) { max-width: 160px; } /* Kategori, Penerbit */
        .data-table td:nth-child(4), .data-table td:nth-child(5) { max-width: 100px; } /* Tahun, Harga */
        .data-table td:nth-child(6) { max-width: 120px; } /* Tanggal */
        .data-table td:nth-child(7) { max-width: 200px; } /* Detail Buku */
        .data-table td:last-child { max-width: 160px; } /* Aksi */


        .data-table tbody tr:hover {
            background-color: #fefefe;
            box-shadow: inset 0 0 0 1px #e0e0e0;
        }
        .data-table tbody tr:nth-child(even) {
            background-color: #fcfcfc; /* Light stripe effect */
        }
        .data-table .action-buttons {
            display: flex;
            gap: 8px; /* Space between action buttons */
            justify-content: center;
        }
        .data-table .action-buttons .btn {
            padding: 0.5rem 0.8rem;
            font-size: 0.8rem;
            border-radius: 7px;
        }

        /* Pagination */
        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
        }
        .pagination-list {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: var(--shadow);
            border: 1px solid #eee;
            overflow: hidden; /* For rounded ends */
        }
        .page-item {
            margin: 0; /* Remove horizontal margin */
        }
        .page-link {
            display: block;
            padding: 0.8rem 1.2rem; /* Increased padding */
            border: none; /* No individual borders */
            border-right: 1px solid #eee; /* Add vertical dividers */
            color: var(--main);
            text-decoration: none;
            transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out;
            font-weight: 500;
        }
        .page-item:last-child .page-link {
            border-right: none; /* No border on the last item */
        }
        .page-link:hover {
            background-color: var(--main-accent);
            color: var(--color-main);
        }
        .page-item.active .page-link {
            background-color: var(--color-main);
            color: #fff;
            border-color: var(--color-main);
            cursor: default;
        }
        .page-item.active .page-link:hover {
             background-color: var(--color-main); /* Keep active background on hover */
             color: #fff;
        }

        /* Custom Confirmation Modal */
        .custom-modal {
            display: none; /* Hidden by default */
            position: fixed;
            z-index: 1001; /* Above overlay */
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4); /* Dark semi-transparent background */
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 30px;
            border: 1px solid #888;
            border-radius: 15px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            text-align: center;
            animation: fadeIn 0.3s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
        .modal-content h3 {
            margin-top: 0;
            color: var(--main);
            font-size: 1.5rem;
            margin-bottom: 15px;
        }
        .modal-content p {
            margin-bottom: 30px;
            font-size: 1rem;
            color: #555;
        }
        .modal-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        .modal-buttons .btn {
            min-width: 100px;
            font-size: 1rem;
        }
        .modal-buttons .btn.cancel {
            background-color: #6c757d;
            color: #fff;
        }
        .modal-buttons .btn.cancel:hover {
            background-color: #5a6268;
        }


        /* Media Queries for Responsiveness */
        @media only screen and (max-width: 1224px) {
            .sidebar {
                left: -345px;
                z-index: 30; /* Ensure sidebar is still above content but below overlay */
            }
            .main-content {
                width: 100vw;
                margin-left: 0;
                padding: 1rem 1.5rem; /* Adjust padding for smaller screens */
            }
            #menu-toggle:checked ~ .sidebar {
                left: 0;
            }
            #menu-toggle:checked ~ .overlay {
                display: block;
            }
        }
        @media only screen and (max-width: 860px) {
            .analytics-grid {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); /* Revert to flexible on smaller wide screens */
            }
            .analytic-card {
                padding: 1.2rem;
            }
            .analytic-icon {
                width: 45px;
                height: 45px;
                font-size: 1.6rem;
                margin-right: 0.8rem;
            }
            .analytic-info h1 {
                font-size: 1.5rem;
            }
            .search-form input[type="search"] {
                max-width: none; /* Allow full width on smaller screens */
            }
        }
        @media only screen and (max-width: 600px) {
            .header-wrapper label {
                font-size: 1.5rem;
                margin-right: 1rem;
            }
            .header-title h1 {
                font-size: 1.4rem;
            }
            .header-title p {
                display: none; /* Hide subtitle on very small screens */
            }
            .header-action .btn {
                padding: 0.6rem 1rem;
                font-size: 0.85rem;
            }
            .sidebar {
                width: 280px; /* Make sidebar slightly smaller on mobile */
                padding: 0.8rem 1rem;
            }
            #menu-toggle:checked ~ .sidebar {
                left: -280px;
            }
            .main-content {
                padding: 1rem;
            }
            .section-head {
                font-size: 1.4rem;
            }
            .analytics-grid {
                grid-template-columns: 1fr; /* Stack cards on very small screens */
            }
            .analytic-card {
                flex-direction: column; /* Stack icon and info vertically */
                text-align: center;
                padding: 1.5rem;
            }
            .analytic-icon {
                margin-right: 0;
                margin-bottom: 0.8rem;
            }
            .search-form {
                flex-direction: column;
                gap: 10px;
            }
            .search-form input[type="search"],
            .search-form button {
                width: 100%;
            }
            .data-table th, .data-table td {
                padding: 0.7rem 0.5rem;
                font-size: 0.8rem;
                max-width: 120px; /* Re-adjust max-width for columns */
            }
             .data-table td:nth-child(1) { max-width: 140px; }
             .data-table td:last-child { max-width: 120px; }
            .data-table .action-buttons .btn {
                padding: 0.4rem 0.6rem;
                font-size: 0.75rem;
                margin-left: 0.3rem;
            }
        }
    </style>
</head>
<body class="bg-light">

    <input type="checkbox" name="menu-toggle" id="menu-toggle">

    <div class="overlay"><label for="menu-toggle"></label></div>

    <div class="sidebar">
        <div class="sidebar-container">
            <div class="brand">
                <h3>
                    <span class="las la-book"></span>
                    Sistem Buku
                </h3>
            </div>
            <div class="sidebar-avatar">
                <div>
                    <img src="https://ui-avatars.com/api/?name=<?=urlencode($_SESSION['username'])?>&background=17a2b8&color=fff&size=40" alt="avatar">
                </div>
                <div class="avatar-info">
                    <div class="avatar-text">
                        <h4><?=htmlspecialchars($_SESSION['username'])?></h4>
                        <small>Admin</small>
                    </div>
                </div>
            </div>
            <div class="sidebar-menu">
                <ul>
                    <li><a href="index.php" class="active"><span class="las la-adjust"></span><span>Dashboard</span></a></li>
                    <li><a href="create.php"><span class="las la-plus-circle"></span><span>Tambah Buku</span></a></li>
                    <li><a href="logout.php"><span class="las la-sign-out-alt"></span><span>Logout</span></a></li>
                </ul>
            </div>
            <div class="sidebar-card">
                <div class="side-card-icon">
                    <span class="lab la-codiepie"></span>
                </div>
                <div>
                    <h4>Aplikasi Buku</h4>
                    <p>Kelola data buku Anda dengan mudah</p>
                </div>
                <a href="create.php" class="btn btn-main btn-block">Tambah Buku Sekarang</a>
            </div>
        </div>
    </div>

    <div class="main-content">
        <header>
            <div class="header-wrapper">
                <label for="menu-toggle">
                    <span class="las la-bars"></span>
                </label>
                <div class="header-title">
                    <h1>Admin Dashboard</h1>
                    <p>Overview of your book collection <span class="las la-book-open"></span></p>
                </div>
            </div>
            <div class="header-action">
                <a href="create.php" class="btn btn-main">
                    <span class="las la-plus-circle"></span>
                    Tambah Buku
                </a>
            </div>
        </header>

        <main>
            <section>
                <h3 class="section-head">Overview Data</h3>
                <div class="analytics-grid">
                    <div class="analytic-card">
                        <div class="analytic-icon" style="background-color: #2196f3;"><i class="fas fa-book"></i></div>
                        <div class="analytic-info">
                            <h4>Total Buku</h4>
                            <h1><?= $totalRow ?></h1>
                        </div>
                    </div>
                    <div class="analytic-card">
                        <div class="analytic-icon" style="background-color: #4caf50;"><i class="fas fa-layer-group"></i></div>
                        <div class="analytic-info">
                            <h4>Kategori Unik</h4>
                            <h1><?php
                                $categoryRes = mysqli_query($conn, "SELECT COUNT(DISTINCT kategori_buku) AS cnt FROM buku_2401010595");
                                echo mysqli_fetch_assoc($categoryRes)['cnt'];
                            ?></h1>
                        </div>
                    </div>
                    <div class="analytic-card">
                        <div class="analytic-icon" style="background-color: #03a9f4;"><i class="fas fa-users"></i></div>
                        <div class="analytic-info">
                            <h4>Total Penerbit</h4>
                            <h1><?php
                                $publisherRes = mysqli_query($conn, "SELECT COUNT(DISTINCT penerbit) AS cnt FROM buku_2401010595");
                                echo mysqli_fetch_assoc($publisherRes)['cnt'];
                            ?></h1>
                        </div>
                    </div>
                    <div class="analytic-card">
                        <div class="analytic-icon" style="background-color: #f44336;"><i class="fas fa-dollar-sign"></i></div>
                        <div class="analytic-info">
                            <h4>Total Harga (Est.)</h4>
                            <h1>Rp <?php
                                $sumPriceRes = mysqli_query($conn, "SELECT SUM(harga) AS total_harga FROM buku_2401010595");
                                echo number_format(mysqli_fetch_assoc($sumPriceRes)['total_harga'], 0, ',', '.');
                            ?></h1>
                        </div>
                    </div>
                </div>
            </section>

            <section>
                <div class="search-form-container">
                    <h3 class="section-head">Cari Buku</h3>
                    <form class="search-form" method="get">
                        <input name="search" type="search" placeholder="Cari judul, kategori, atau penerbit..." value="<?=htmlspecialchars($search)?>">
                        <button type="submit"><i class="fas fa-search"></i> Search</button>
                    </form>
                </div>

                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-list-alt icon"></i>
                        Daftar Buku (<?= $totalRow ?>)
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <?php
                                        function th($label, $col, $current_sort, $current_order, $current_search, $current_page) {
                                            $ord   = ($current_sort===$col && $current_order==='ASC') ? 'DESC' : 'ASC';
                                            $icon_class = '';
                                            if ($current_sort === $col) {
                                                $icon_class = $current_order === 'ASC' ? 'fa-solid fa-arrow-up' : 'fa-solid fa-arrow-down';
                                            }
                                            $qs    = http_build_query([
                                                'search'=>$current_search,'sort'=>$col,'order'=>$ord,'page'=>1 // Always reset page to 1 on new sort/search
                                            ]);
                                            echo "<th><a class='sortable' href='?{$qs}'>{$label} <i class='sort-icon {$icon_class}'></i></a></th>";
                                        }
                                        th('Judul','judul_buku', $sort, $order, $search, $page);
                                        th('Kategori','kategori_buku', $sort, $order, $search, $page);
                                        th('Penerbit','penerbit', $sort, $order, $search, $page);
                                        th('Tahun','tahun', $sort, $order, $search, $page);
                                        th('Harga','harga', $sort, $order, $search, $page);
                                        echo "<th>Tanggal</th><th>Detail Buku</th><th>Aksi</th>";
                                        ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($result && mysqli_num_rows($result)>0):
                                        while ($row = mysqli_fetch_assoc($result)):
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['judul_buku']) ?></td>
                                        <td><?= htmlspecialchars($row['kategori_buku']) ?></td>
                                        <td><?= htmlspecialchars($row['penerbit']) ?></td>
                                        <td><?= $row['tahun'] ?></td>
                                        <td>Rp <?= number_format($row['harga'],0,',','.') ?></td>
                                        <td><?= $row['tanggal_pembelian'] ?></td>
                                        <td><?= nl2br(htmlspecialchars($row['detail_buku'])) ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Edit</a>
                                                <button type="button" class="btn btn-danger" onclick="showConfirmModal('Yakin ingin menghapus buku \'<?= addslashes(htmlspecialchars($row['judul_buku'])) ?>\'?', 'delete.php?id=<?= $row['id'] ?>')"><i class="fas fa-trash-alt"></i> Hapus</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                        endwhile;
                                    else:
                                    ?>
                                    <tr><td colspan="8" style="text-align: center; padding: 1.5rem; color: #777; font-style: italic;">Data tidak ditemukan.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <nav class="pagination-container">
                    <ul class="pagination-list">
                        <?php for($i=1; $i<=$totalPage; $i++):
                            $qs = http_build_query([
                                'search'=>$search,'sort'=>$sort,'order'=>$order,'page'=>$i
                            ]); ?>
                            <li class="page-item <?= $i==$page?'active':'' ?>">
                                <a class="page-link" href="?<?= $qs ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </section>
        </main>
    </div>

    <div id="confirmModal" class="custom-modal">
        <div class="modal-content">
            <h3>Konfirmasi Aksi</h3>
            <p id="modalMessage"></p>
            <div class="modal-buttons">
                <button class="btn cancel" onclick="hideConfirmModal()">Batal</button>
                <button class="btn btn-danger" id="confirmActionBtn">Ya, Hapus</button>
            </div>
        </div>
    </div>

    <script>
        // JavaScript for custom confirmation modal
        const confirmModal = document.getElementById('confirmModal');
        const modalMessage = document.getElementById('modalMessage');
        const confirmActionBtn = document.getElementById('confirmActionBtn');
        let currentCallback = null;

        function showConfirmModal(message, callback) {
            modalMessage.textContent = message;
            currentCallback = callback;
            confirmModal.style.display = 'flex'; // Use flex to center
        }

        function hideConfirmModal() {
            confirmModal.style.display = 'none';
            currentCallback = null;
        }

        confirmActionBtn.onclick = function() {
            if (currentCallback) {
                window.location.href = currentCallback; // Redirect if confirmed
            }
            hideConfirmModal();
        };

        // Close modal if overlay is clicked
        confirmModal.addEventListener('click', function(event) {
            if (event.target === confirmModal) {
                hideConfirmModal();
            }
        });

        // Close modal if escape key is pressed
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && confirmModal.style.display === 'flex') {
                hideConfirmModal();
            }
        });

        // Handle menu toggle on mobile (if needed, though CSS handles most)
        document.getElementById('menu-toggle').addEventListener('change', function() {
            const overlay = document.querySelector('.overlay');
            if (this.checked) {
                overlay.style.display = 'block';
            } else {
                overlay.style.display = 'none';
            }
        });

        // Close sidebar and overlay if a menu item is clicked on mobile
        document.querySelectorAll('.sidebar-menu a').forEach(item => {
            item.addEventListener('click', () => {
                const menuToggle = document.getElementById('menu-toggle');
                if (menuToggle.checked) {
                    menuToggle.checked = false;
                    document.querySelector('.overlay').style.display = 'none';
                }
            });
        });

    </script>
</body>
</html>