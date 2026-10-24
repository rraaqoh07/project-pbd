<?php
include 'koneksi.php';

// Hitung Ringkasan Data
$total_barang = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM barang"))['total'] ?? 0;
$total_satuan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM satuan"))['total'] ?? 0;
$total_vendor = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM vendor"))['total'] ?? 0;
$total_user   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM user"))['total'] ?? 0;

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Kelontong Bakti - Master Data</title>
    <!-- Google Fonts & FontAwesome -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #f8fafc; 
            color: #334155; 
        }
        .navbar-top { 
            background: #ffffff; 
            border-bottom: 1px solid #e2e8f0; 
        }
        .card-stat { 
            border: none; 
            border-radius: 16px; 
            background: #ffffff; 
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03); 
        }
        .icon-shape { 
            width: 48px; 
            height: 48px; 
            border-radius: 12px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-size: 1.25rem; 
        }
        
        /* ==========================================
           WARNA PINK PASTEL KUSTOM
           ========================================== */
        .bg-pink-pastel { 
            background-color: #f472b6 !important; 
        }
        .text-pink-pastel { 
            color: #db2777 !important; 
        }

        .nav-pills-custom .nav-link { 
            color: #64748b; 
            font-weight: 600; 
            border-radius: 12px; 
            padding: 10px 18px; 
            margin-right: 8px; 
            background: #ffffff; 
            border: 1px solid #e2e8f0; 
        }
        
        /* Menu Tab Aktif -> Pink Pastel */
        .nav-pills-custom .nav-link.active { 
            background: #f472b6 !important; 
            color: #ffffff !important; 
            border-color: #f472b6 !important; 
            box-shadow: 0 4px 12px rgba(244, 114, 182, 0.35) !important; 
        }

        /* Tombol Simpan / Utama -> Pink Pastel */
        .btn-primary {
            background-color: #f472b6 !important;
            border-color: #f472b6 !important;
            color: #ffffff !important;
        }
        .btn-primary:hover {
            background-color: #ec4899 !important;
            border-color: #ec4899 !important;
        }
        
        /* Ikon Judul Halaman -> Pink Pastel */
        .text-primary {
            color: #db2777 !important;
        }

        .main-card { 
            border: none; 
            border-radius: 20px; 
            background: #ffffff; 
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.04); 
        }
        .table thead th { 
            background: #f1f5f9; 
            color: #475569; 
            font-weight: 600; 
            border: none; 
            padding: 14px 16px; 
        }
        .table tbody td { 
            padding: 14px 16px; 
            vertical-align: middle; 
            border-bottom: 1px solid #f1f5f9; 
        }
        .btn-action { 
            width: 34px; 
            height: 34px; 
            padding: 0; 
            display: inline-flex; 
            align-items: center; 
            justify-content: center; 
            border-radius: 8px; 
        }
    </style>
</head>
<body>

<!-- TOP NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-top sticky-top py-3">
    <div class="container-fluid px-4">
        <!-- Logo & Judul Toko Pink Pastel -->
        <a class="navbar-brand d-flex align-items-center fw-bold text-pink-pastel fs-5" href="barang.php">
            <div class="bg-pink-pastel text-white p-2 rounded-3 me-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                <i class="fa-solid fa-store"></i>
            </div>
            TOKO KELONTONG BAKTI
        </a>
        <div class="d-flex align-items-center gap-3 ms-auto">
            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill">
                <i class="fa-solid fa-circle text-success me-1 fs-6"></i> Database Connected
            </span>
        </div>
    </div>
</nav>

<div class="container-fluid px-4 py-4">

    <!-- RINGKASAN STATISTIK -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-stat p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 font-semibold">Total Barang</span>
                        <h3 class="fw-bold mb-0 mt-1"><?= $total_barang ?></h3>
                    </div>
                    <div class="icon-shape bg-danger bg-opacity-10 text-pink-pastel"><i class="fa-solid fa-box"></i></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-stat p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 font-semibold">Jenis Satuan</span>
                        <h3 class="fw-bold mb-0 mt-1"><?= $total_satuan ?></h3>
                    </div>
                    <div class="icon-shape bg-success bg-opacity-10 text-success"><i class="fa-solid fa-scale-balanced"></i></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-stat p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 font-semibold">Mitra Vendor</span>
                        <h3 class="fw-bold mb-0 mt-1"><?= $total_vendor ?></h3>
                    </div>
                    <div class="icon-shape bg-warning bg-opacity-10 text-warning"><i class="fa-solid fa-truck-field"></i></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-stat p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 font-semibold">Pengguna System</span>
                        <h3 class="fw-bold mb-0 mt-1"><?= $total_user ?></h3>
                    </div>
                    <div class="icon-shape bg-info bg-opacity-10 text-info"><i class="fa-solid fa-users"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- NAVIGASI MENU MASTER DATA (PINK PASTEL AKTIF) -->
    <div class="d-flex flex-wrap nav-pills-custom mb-4">
        <a href="barang.php" class="nav-link <?= ($current_page == 'barang.php') ? 'active' : '' ?>"><i class="fa-solid fa-box me-2"></i>Data Barang</a>
        <a href="satuan.php" class="nav-link <?= ($current_page == 'satuan.php') ? 'active' : '' ?>"><i class="fa-solid fa-scale-balanced me-2"></i>Data Satuan</a>
        <a href="vendor.php" class="nav-link <?= ($current_page == 'vendor.php') ? 'active' : '' ?>"><i class="fa-solid fa-truck-field me-2"></i>Data Vendor</a>
        <a href="user.php" class="nav-link <?= ($current_page == 'user.php') ? 'active' : '' ?>"><i class="fa-solid fa-users me-2"></i>Data User</a>
        <a href="role.php" class="nav-link <?= ($current_page == 'role.php') ? 'active' : '' ?>"><i class="fa-solid fa-user-shield me-2"></i>Data Role</a>
        <a href="margin.php" class="nav-link <?= ($current_page == 'margin.php') ? 'active' : '' ?>"><i class="fa-solid fa-percent me-2"></i>Margin Penjualan</a>
    </div>

    <div class="main-card p-4">