<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';

// 1. Cek apakah user sudah login
if (!isset($_SESSION['iduser'])) {
    header("Location: login.php");
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);
$user_role    = $_SESSION['nama_role'];
$is_kasir     = ($user_role == 'Kasir');

// 2. Proteksi URL: Jika Kasir mencoba buka halaman selain penjualan.php, arahkan kembali ke penjualan.php
if ($is_kasir && $current_page !== 'penjualan.php') {
    header("Location: penjualan.php");
    exit;
}

// Hitung Ringkasan Statistik
$total_barang    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM barang"))['total'] ?? 0;
$total_pengadaan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pengadaan"))['total'] ?? 0;
$total_penjualan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM penjualan"))['total'] ?? 0;
$margin_aktif    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT persen FROM margin_penjualan WHERE status=1 ORDER BY idmargin_penjualan DESC LIMIT 1"))['persen'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Kelontong Bakti - Sistem Kasir & Inventori</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #fdf2f8; color: #334155; }
        .navbar-top { background: #ffffff; border-bottom: 1px solid #fbcfe8; }
        .card-stat { border: none; border-radius: 16px; background: #ffffff; box-shadow: 0 4px 20px rgba(244, 114, 182, 0.08); }
        .icon-shape { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
        
        .bg-pink-pastel { background-color: #f472b6 !important; }
        .text-pink-pastel { color: #db2777 !important; }
        
        .nav-pills-custom .nav-link { color: #64748b; font-weight: 600; border-radius: 12px; padding: 9px 16px; margin-right: 6px; margin-bottom: 6px; background: #ffffff; border: 1px solid #fbcfe8; font-size: 0.9rem; }
        .nav-pills-custom .nav-link.active { background: #f472b6 !important; color: #ffffff !important; border-color: #f472b6 !important; box-shadow: 0 4px 12px rgba(244, 114, 182, 0.35) !important; }
        
        .btn-primary { background-color: #f472b6 !important; border-color: #f472b6 !important; color: #ffffff !important; }
        .btn-primary:hover { background-color: #ec4899 !important; border-color: #ec4899 !important; }
        .text-primary { color: #db2777 !important; }
        
        .main-card { border: none; border-radius: 20px; background: #ffffff; box-shadow: 0 4px 25px rgba(244, 114, 182, 0.08); }
        .table thead th { background: #fce7f3; color: #831843; font-weight: 600; border: none; padding: 13px 16px; }
        .table tbody td { padding: 13px 16px; vertical-align: middle; border-bottom: 1px solid #fdf2f8; }
        .btn-action { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-top sticky-top py-3">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center fw-bold text-pink-pastel fs-5" href="<?= $is_kasir ? 'penjualan.php' : 'pengadaan.php' ?>">
            <div class="bg-pink-pastel text-white p-2 rounded-3 me-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                <i class="fa-solid fa-store"></i>
            </div>
            TOKO KELONTONG BAKTI
        </a>
        <div class="d-flex align-items-center gap-3 ms-auto">
            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                <i class="fa-solid fa-user-circle text-pink-pastel me-1"></i> 
                <?= htmlspecialchars($_SESSION['username']) ?> 
                <span class="text-muted ms-1">(<?= htmlspecialchars($user_role) ?>)</span>
            </span>
            <a href="logout.php" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold" onclick="return confirm('Keluar dari sistem?')">
                <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
            </a>
        </div>
    </div>
</nav>

<div class="container-fluid px-4 py-4">

    <?php if (!$is_kasir): ?>
    <!-- RINGKASAN STATISTIK (Hanya tampil untuk Admin) -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-stat p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">Katalog Produk</span>
                        <h3 class="fw-bold mb-0 mt-1"><?= $total_barang ?> Item</h3>
                    </div>
                    <div class="icon-shape bg-danger bg-opacity-10 text-pink-pastel"><i class="fa-solid fa-box"></i></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-stat p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">Total Pengadaan</span>
                        <h3 class="fw-bold mb-0 mt-1"><?= $total_pengadaan ?> PO</h3>
                    </div>
                    <div class="icon-shape bg-warning bg-opacity-10 text-warning"><i class="fa-solid fa-file-invoice"></i></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-stat p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">Transaksi Penjualan</span>
                        <h3 class="fw-bold mb-0 mt-1"><?= $total_penjualan ?> Nota</h3>
                    </div>
                    <div class="icon-shape bg-success bg-opacity-10 text-success"><i class="fa-solid fa-cash-register"></i></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-stat p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">Margin Jual Aktif</span>
                        <h3 class="fw-bold mb-0 mt-1 text-pink-pastel">+<?= $margin_aktif ?>%</h3>
                    </div>
                    <div class="icon-shape bg-info bg-opacity-10 text-info"><i class="fa-solid fa-percent"></i></div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- NAVIGASI PROSES BISNIS & MASTER DATA BERDASARKAN ROLE -->
    <div class="d-flex flex-wrap nav-pills-custom mb-4">
        <?php if (!$is_kasir): ?>
            <a href="pengadaan.php" class="nav-link <?= ($current_page == 'pengadaan.php') ? 'active' : '' ?>"><i class="fa-solid fa-file-invoice me-1"></i>1. Pengadaan</a>
            <a href="penerimaan.php" class="nav-link <?= ($current_page == 'penerimaan.php') ? 'active' : '' ?>"><i class="fa-solid fa-truck-ramp-box me-1"></i>2. Penerimaan</a>
        <?php endif; ?>
        
        <!-- Menu Penjualan (Bisa diakses Admin & Kasir) -->
        <a href="penjualan.php" class="nav-link <?= ($current_page == 'penjualan.php') ? 'active' : '' ?>"><i class="fa-solid fa-cart-shopping me-1"></i>3. Penjualan (Kasir)</a>
        
        <?php if (!$is_kasir): ?>
            <a href="kartu_stok.php" class="nav-link <?= ($current_page == 'kartu_stok.php') ? 'active' : '' ?>"><i class="fa-solid fa-clipboard-list me-1"></i>Kartu Stok</a>
            <span class="mx-2 border-end"></span>
            <a href="barang.php" class="nav-link <?= ($current_page == 'barang.php') ? 'active' : '' ?>"><i class="fa-solid fa-box me-1"></i>Barang</a>
            <a href="margin.php" class="nav-link <?= ($current_page == 'margin.php') ? 'active' : '' ?>"><i class="fa-solid fa-percent me-1"></i>Margin</a>
            <a href="satuan.php" class="nav-link <?= ($current_page == 'satuan.php') ? 'active' : '' ?>"><i class="fa-solid fa-scale-balanced me-1"></i>Satuan</a>
            <a href="vendor.php" class="nav-link <?= ($current_page == 'vendor.php') ? 'active' : '' ?>"><i class="fa-solid fa-truck-field me-1"></i>Vendor</a>
            <a href="user.php" class="nav-link <?= ($current_page == 'user.php') ? 'active' : '' ?>"><i class="fa-solid fa-users me-1"></i>User</a>
            <a href="role.php" class="nav-link <?= ($current_page == 'role.php') ? 'active' : '' ?>"><i class="fa-solid fa-user-shield me-1"></i>Role</a>
        <?php endif; ?>
    </div>

    <div class="main-card p-4">