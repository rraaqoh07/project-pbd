<?php
session_start();
include 'koneksi.php';

// Jika sudah login, arahkan sesuai role
if (isset($_SESSION['iduser'])) {
    if ($_SESSION['nama_role'] == 'Kasir') {
        header("Location: transaksi.php");
    } else {
        header("Location: barang.php");
    }
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $query = "SELECT u.*, r.nama_role 
              FROM user u 
              JOIN role r ON u.idrole = r.idrole 
              WHERE u.username = '$username' AND u.password = '$password' 
              LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($row = mysqli_fetch_assoc($result)) {
        $_SESSION['iduser']    = $row['iduser'];
        $_SESSION['username']  = $row['username'];
        $_SESSION['idrole']    = $row['idrole'];
        $_SESSION['nama_role'] = $row['nama_role'];

        if ($row['nama_role'] == 'Kasir') {
            header("Location: transaksi.php");
        } else {
            header("Location: barang.php");
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
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #fdf2f8; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { border: none; border-radius: 24px; background: #ffffff; box-shadow: 0 10px 30px rgba(244, 114, 182, 0.15); width: 100%; max-width: 420px; padding: 2.5rem; }
        .bg-pink-pastel { background-color: #f472b6 !important; }
        .text-pink-pastel { color: #db2777 !important; }
        .btn-primary { background-color: #f472b6 !important; border-color: #f472b6 !important; color: #fff !important; border-radius: 12px; padding: 12px; }
        .btn-primary:hover { background-color: #ec4899 !important; border-color: #ec4899 !important; }
        .form-control { border-radius: 12px; padding: 12px 15px; border: 1px solid #fbcfe8; }
        .form-control:focus { border-color: #f472b6; box-shadow: 0 0 0 0.25rem rgba(244, 114, 182, 0.25); }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <div class="bg-pink-pastel text-white rounded-4 d-inline-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px; font-size: 1.5rem;">
            <i class="fa-solid fa-store"></i>
        </div>
        <h4 class="fw-bold text-dark mb-1">Toko Kelontong Bakti</h4>
        <p class="text-muted small">Silakan masuk untuk melanjutkan ke sistem</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2 small rounded-3"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="mb-3">
            <label class="form-label small fw-bold">Username</label>
            <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
        </div>
        <div class="mb-4">
            <label class="form-label small fw-bold">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
        </div>
        <button type="submit" name="btn_login" class="btn btn-primary w-100 fw-bold mb-3">
            <i class="fa-solid fa-right-to-bracket me-2"></i> Masuk Sistem
        </button>
        <div class="text-center small text-muted">
            Belum punya akun? <a href="register.php" class="text-pink-pastel fw-bold text-decoration-none">Daftar Sekarang</a>
        </div>
    </form>
</div>

</body>
</html>