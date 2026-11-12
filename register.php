<?php
session_start();
include 'koneksi.php';

$message = "";
$msg_type = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_register'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $idrole   = intval($_POST['idrole']);

    $cek = mysqli_query($conn, "SELECT iduser FROM user WHERE username = '$username'");
    if (mysqli_num_rows($cek) > 0) {
        $message  = "Username sudah terdaftar, gunakan username lain!";
        $msg_type = "danger";
    } else {
        $sql = "INSERT INTO user (username, password, idrole) VALUES ('$username', '$password', $idrole)";
        if (mysqli_query($conn, $sql)) {
            $message  = "Registrasi berhasil! Silakan login menggunakan akun Anda.";
            $msg_type = "success";
        } else {
            $message  = "Gagal mendaftar: " . mysqli_error($conn);
            $msg_type = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Toko Kelontong Bakti</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #fdf2f8; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .reg-card { border: none; border-radius: 24px; background: #ffffff; box-shadow: 0 10px 30px rgba(244, 114, 182, 0.15); width: 100%; max-width: 420px; padding: 2.5rem; }
        .bg-pink-pastel { background-color: #f472b6 !important; }
        .text-pink-pastel { color: #db2777 !important; }
        .btn-primary { background-color: #f472b6 !important; border-color: #f472b6 !important; color: #fff !important; border-radius: 12px; padding: 12px; }
        .btn-primary:hover { background-color: #ec4899 !important; border-color: #ec4899 !important; }
        .form-control, .form-select { border-radius: 12px; padding: 12px 15px; border: 1px solid #fbcfe8; }
        .form-control:focus, .form-select:focus { border-color: #f472b6; box-shadow: 0 0 0 0.25rem rgba(244, 114, 182, 0.25); }
    </style>
</head>
<body>

<div class="reg-card">
    <div class="text-center mb-4">
        <div class="bg-pink-pastel text-white rounded-4 d-inline-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px; font-size: 1.5rem;">
            <i class="fa-solid fa-user-plus"></i>
        </div>
        <h4 class="fw-bold text-dark mb-1">Buat Akun Baru</h4>
        <p class="text-muted small">Daftarkan akun pegawai Toko Kelontong Bakti</p>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $msg_type ?> py-2 small rounded-3"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <div class="mb-3">
            <label class="form-label small fw-bold">Username</label>
            <input type="text" name="username" class="form-control" placeholder="Buat username" required>
        </div>
        <div class="mb-3">
            <label class="form-label small fw-bold">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Buat password" required>
        </div>
        <div class="mb-4">
            <label class="form-label small fw-bold">Pilih Role / Jabatan</label>
            <select name="idrole" class="form-select" required>
                <option value="">-- Pilih Role Akses --</option>
                <?php
                $roles = mysqli_query($conn, "SELECT * FROM role WHERE nama_role IN ('Admin', 'Kasir')");
                while ($r = mysqli_fetch_assoc($roles)) {
                    echo "<option value='{$r['idrole']}'>{$r['nama_role']}</option>";
                }
                ?>
            </select>
        </div>
        <button type="submit" name="btn_register" class="btn btn-primary w-100 fw-bold mb-3">
            Daftar Akun
        </button>
        <div class="text-center small text-muted">
            Sudah punya akun? <a href="login.php" class="text-pink-pastel fw-bold text-decoration-none">Masuk di sini</a>
        </div>
    </form>
</div>

</body>
</html>