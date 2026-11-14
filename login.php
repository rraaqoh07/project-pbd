<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include 'koneksi.php';

// Jika sudah login, langsung arahkan sesuai role
if (isset($_SESSION['iduser'])) {
    if ($_SESSION['nama_role'] == 'Kasir') {
        header("Location: penjualan.php");
    } else {
        header("Location: pengadaan.php");
    }
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_login'])) {
    $username     = mysqli_real_escape_string($conn, $_POST['username']);
    $password     = mysqli_real_escape_string($conn, $_POST['password']);
    $password_md5 = md5($password);

    // Mendukung password MD5 (data awal UTS) maupun teks biasa (register baru)
    $query = "SELECT u.*, r.nama_role 
              FROM user u 
              JOIN role r ON u.idrole = r.idrole 
              WHERE u.username = '$username' 
              AND (u.password = '$password' OR u.password = '$password_md5') 
              LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($row = mysqli_fetch_assoc($result)) {
        $_SESSION['iduser']    = $row['iduser'];
        $_SESSION['username']  = $row['username'];
        $_SESSION['idrole']    = $row['idrole'];
        $_SESSION['nama_role'] = $row['nama_role'];

        if ($row['nama_role'] == 'Kasir') {
            header("Location: penjualan.php");
        } else {
            header("Location: pengadaan.php");
        }
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Toko Kelontong Bakti</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #fdf2f8; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { border: none; border-radius: 24px; background: #ffffff; box-shadow: 0 10px 35px rgba(244, 114, 182, 0.15); max-width: 420px; width: 100%; }
        .bg-pink-pastel { background-color: #f472b6 !important; }
        .text-pink-pastel { color: #db2777 !important; }
        .btn-primary { background-color: #f472b6 !important; border-color: #f472b6 !important; color: #ffffff !important; border-radius: 12px; padding: 11px; font-weight: 600; }
        .btn-primary:hover { background-color: #ec4899 !important; border-color: #ec4899 !important; }
        .form-control { border-radius: 12px; padding: 11px 14px; border-color: #fbcfe8; }
        .form-control:focus { border-color: #f472b6; box-shadow: 0 0 0 0.25rem rgba(244, 114, 182, 0.2); }
    </style>
</head>
<body>

<div class="login-card p-4 p-md-5 mx-3">
    <div class="text-center mb-4">
        <div class="bg-pink-pastel text-white rounded-4 d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 56px; height: 56px; font-size: 1.5rem;">
            <i class="fa-solid fa-store"></i>
        </div>
        <h4 class="fw-bold text-dark mb-1">Toko Kelontong Bakti</h4>
        <p class="text-muted small">Silakan login untuk mengakses sistem</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger py-2 small rounded-3 text-center">
            <i class="fa-solid fa-circle-exclamation me-1"></i> <?= $error ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label class="form-label small fw-semibold text-secondary">Username</label>
            <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
        </div>
        <div class="mb-4">
            <label class="form-label small fw-semibold text-secondary">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
        </div>
        <button type="submit" name="btn_login" class="btn btn-primary w-100 mb-3">
            <i class="fa-solid fa-right-to-bracket me-1"></i> Masuk Sistem
        </button>
    </form>

    <div class="text-center mt-2">
        <span class="text-muted small">Belum punya akun?</span>
        <a href="register.php" class="text-pink-pastel small fw-bold text-decoration-none ms-1">Daftar Akun</a>
    </div>
</div>

</body>
</html>