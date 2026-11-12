<?php
session_start();
include 'koneksi.php';

// Jika sudah login, arahkan sesuai role
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
    $password_md5 = md5($password); // Ubah inputan password menjadi MD5 juga

    // Cek apakah password cocok dengan teks biasa ATAU versi MD5-nya
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