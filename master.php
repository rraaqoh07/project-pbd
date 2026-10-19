<?php
// ==============================================================================
// KONEKSI DATABASE (MySQLi Native - Tanpa Query Builder / ORM)
// ==============================================================================
$host = "localhost";
$user = "root";
$pass = "";
$db   = "uts_pbd_2026"; // Sesuaikan nama database Laragon Anda

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// Parameter Halaman & Aksi
$page   = isset($_GET['page']) ? $_GET['page'] : 'barang';
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id     = isset($_GET['id']) ? intval($_GET['id']) : 0;

$message  = "";
$msg_type = "";

// ==============================================================================
// PROSES LOGIKA CRUD (NATIVE SQL)
// ==============================================================================

// 1. TABEL ROLE
if ($page == 'role') {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nama_role = mysqli_real_escape_string($conn, $_POST['nama_role']);
        
        if (isset($_POST['btn_save'])) {
            $sql = "INSERT INTO role (nama_role) VALUES ('$nama_role')";
            if (mysqli_query($conn, $sql)) {
                $message  = "Data Role berhasil ditambahkan!";
                $msg_type = "success";
            }
        } elseif (isset($_POST['btn_update'])) {
            $idrole = intval($_POST['idrole']);
            $sql    = "UPDATE role SET nama_role='$nama_role' WHERE idrole=$idrole";
            if (mysqli_query($conn, $sql)) {
                $message  = "Data Role berhasil diperbarui!";
                $msg_type = "success";
            }
        }
    }
    if ($action == 'delete' && $id > 0) {
        $sql = "DELETE FROM role WHERE idrole=$id";
        if (mysqli_query($conn, $sql)) {
            $message  = "Data Role berhasil dihapus!";
            $msg_type = "danger";
        } else {
            $message  = "Gagal menghapus! Data masih terikat pada tabel user.";
            $msg_type = "warning";
        }
    }
}

// 2. TABEL SATUAN
if ($page == 'satuan') {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nama_satuan = mysqli_real_escape_string($conn, $_POST['nama_satuan']);
        $status      = intval($_POST['status']);
        
        if (isset($_POST['btn_save'])) {
            $sql = "INSERT INTO satuan (nama_satuan, status) VALUES ('$nama_satuan', $status)";
            if (mysqli_query($conn, $sql)) {
                $message  = "Data Satuan berhasil ditambahkan!";
                $msg_type = "success";
            }
        } elseif (isset($_POST['btn_update'])) {
            $idsatuan = intval($_POST['idsatuan']);
            $sql      = "UPDATE satuan SET nama_satuan='$nama_satuan', status=$status WHERE idsatuan=$idsatuan";
            if (mysqli_query($conn, $sql)) {
                $message  = "Data Satuan berhasil diperbarui!";
                $msg_type = "success";
            }
        }
    }
    if ($action == 'delete' && $id > 0) {
        $sql = "DELETE FROM satuan WHERE idsatuan=$id";
        if (mysqli_query($conn, $sql)) {
            $message  = "Data Satuan berhasil dihapus!";
            $msg_type = "danger";
        } else {
            $message  = "Gagal menghapus! Data terikat pada tabel barang.";
            $msg_type = "warning";
        }
    }
}

// 3. TABEL VENDOR
if ($page == 'vendor') {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nama_vendor = mysqli_real_escape_string($conn, $_POST['nama_vendor']);
        $badan_hukum = mysqli_real_escape_string($conn, $_POST['badan_hukum']);
        $status      = mysqli_real_escape_string($conn, $_POST['status']);
        
        if (isset($_POST['btn_save'])) {
            $sql = "INSERT INTO vendor (nama_vendor, badan_hukum, status) VALUES ('$nama_vendor', '$badan_hukum', '$status')";
            if (mysqli_query($conn, $sql)) {
                $message  = "Data Vendor berhasil ditambahkan!";
                $msg_type = "success";
            }
        } elseif (isset($_POST['btn_update'])) {
            $idvendor = intval($_POST['idvendor']);
            $sql      = "UPDATE vendor SET nama_vendor='$nama_vendor', badan_hukum='$badan_hukum', status='$status' WHERE idvendor=$idvendor";
            if (mysqli_query($conn, $sql)) {
                $message  = "Data Vendor berhasil diperbarui!";
                $msg_type = "success";
            }
        }
    }
    if ($action == 'delete' && $id > 0) {
        $sql = "DELETE FROM vendor WHERE idvendor=$id";
        if (mysqli_query($conn, $sql)) {
            $message  = "Data Vendor berhasil dihapus!";
            $msg_type = "danger";
        } else {
            $message  = "Gagal menghapus! Data terikat pada transaksi pengadaan.";
            $msg_type = "warning";
        }
    }
}

// 4. TABEL USER
if ($page == 'user') {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $password = mysqli_real_escape_string($conn, $_POST['password']);
        $idrole   = intval($_POST['idrole']);
        
        if (isset($_POST['btn_save'])) {
            $sql = "INSERT INTO user (username, password, idrole) VALUES ('$username', '$password', $idrole)";
            if (mysqli_query($conn, $sql)) {
                $message  = "Data User berhasil ditambahkan!";
                $msg_type = "success";
            }
        } elseif (isset($_POST['btn_update'])) {
            $iduser = intval($_POST['iduser']);
            $sql    = "UPDATE user SET username='$username', password='$password', idrole=$idrole WHERE iduser=$iduser";
            if (mysqli_query($conn, $sql)) {
                $message  = "Data User berhasil diperbarui!";
                $msg_type = "success";
            }
        }
    }
    if ($action == 'delete' && $id > 0) {
        $sql = "DELETE FROM user WHERE iduser=$id";
        if (mysqli_query($conn, $sql)) {
            $message  = "Data User berhasil dihapus!";
            $msg_type = "danger";
        } else {
            $message  = "Gagal menghapus! Data terikat pada tabel lain.";
            $msg_type = "warning";
        }
    }
}

// 5. TABEL MARGIN PENJUALAN
if ($page == 'margin') {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $persen = floatval($_POST['persen']);
        $status = intval($_POST['status']);
        $iduser = intval($_POST['iduser']);
        
        if (isset($_POST['btn_save'])) {
            $sql = "INSERT INTO margin_penjualan (persen, status, iduser) VALUES ($persen, $status, $iduser)";
            if (mysqli_query($conn, $sql)) {
                $message  = "Data Margin Penjualan berhasil ditambahkan!";
                $msg_type = "success";
            }
        } elseif (isset($_POST['btn_update'])) {
            $idmargin = intval($_POST['idmargin_penjualan']);
            $sql      = "UPDATE margin_penjualan SET persen=$persen, status=$status, iduser=$iduser WHERE idmargin_penjualan=$idmargin";
            if (mysqli_query($conn, $sql)) {
                $message  = "Data Margin Penjualan berhasil diperbarui!";
                $msg_type = "success";
            }
        }
    }
    if ($action == 'delete' && $id > 0) {
        $sql = "DELETE FROM margin_penjualan WHERE idmargin_penjualan=$id";
        if (mysqli_query($conn, $sql)) {
            $message  = "Data Margin Penjualan berhasil dihapus!";
            $msg_type = "danger";
        } else {
            $message  = "Gagal menghapus! Data terikat pada transaksi penjualan.";
            $msg_type = "warning";
        }
    }
}

// 6. TABEL BARANG
if ($page == 'barang') {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $jenis    = mysqli_real_escape_string($conn, $_POST['jenis']);
        $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
        $idsatuan = intval($_POST['idsatuan']);
        $status   = intval($_POST['status']);
        $harga    = intval($_POST['harga']);
        
        if (isset($_POST['btn_save'])) {
            $sql = "INSERT INTO barang (jenis, nama, idsatuan, status, harga) VALUES ('$jenis', '$nama', $idsatuan, $status, $harga)";
            if (mysqli_query($conn, $sql)) {
                $message  = "Data Barang berhasil ditambahkan!";
                $msg_type = "success";
            }
        } elseif (isset($_POST['btn_update'])) {
            $idbarang = intval($_POST['idbarang']);
            $sql      = "UPDATE barang SET jenis='$jenis', nama='$nama', idsatuan=$idsatuan, status=$status, harga=$harga WHERE idbarang=$idbarang";
            if (mysqli_query($conn, $sql)) {
                $message  = "Data Barang berhasil diperbarui!";
                $msg_type = "success";
            }
        }
    }
    if ($action == 'delete' && $id > 0) {
        $sql = "DELETE FROM barang WHERE idbarang=$id";
        if (mysqli_query($conn, $sql)) {
            $message  = "Data Barang berhasil dihapus!";
            $msg_type = "danger";
        } else {
            $message  = "Gagal menghapus! Data terikat pada detail transaksi.";
            $msg_type = "warning";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Kelontong Jaya</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { min-height: 100vh; background: #1e293b; color: #fff; }
        .sidebar .nav-link { color: #94a3b8; font-weight: 500; margin-bottom: 4px; padding: 12px 20px; border-radius: 8px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: #0f172a; color: #38bdf8; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .table thead { background-color: #0f172a; color: #fff; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- SIDEBAR NAVIGASI -->
        <div class="col-md-3 col-lg-2 sidebar p-3">
            <h5 class="text-center text-info mb-4 fw-bold"><i class="fa-solid fa-database me-2"></i>TOKO KELONTONG JAYA</h5>
            <div class="nav flex-column nav-pills">
                <a href="?page=barang" class="nav-link <?= ($page == 'barang') ? 'active' : '' ?>">
                    <i class="fa-solid fa-box me-2"></i> Data Barang
                </a>
                <a href="?page=satuan" class="nav-link <?= ($page == 'satuan') ? 'active' : '' ?>">
                    <i class="fa-solid fa-scale-balanced me-2"></i> Data Satuan
                </a>
                <a href="?page=vendor" class="nav-link <?= ($page == 'vendor') ? 'active' : '' ?>">
                    <i class="fa-solid fa-truck-field me-2"></i> Data Vendor
                </a>
                <a href="?page=user" class="nav-link <?= ($page == 'user') ? 'active' : '' ?>">
                    <i class="fa-solid fa-users me-2"></i> Data User
                </a>
                <a href="?page=role" class="nav-link <?= ($page == 'role') ? 'active' : '' ?>">
                    <i class="fa-solid fa-user-shield me-2"></i> Data Role
                </a>
                <a href="?page=margin" class="nav-link <?= ($page == 'margin') ? 'active' : '' ?>">
                    <i class="fa-solid fa-percent me-2"></i> Margin Penjualan
                </a>
            </div>
        </div>

        <!-- KONTEN UTAMA -->
        <div class="col-md-9 col-lg-10 p-4">
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show" role="alert">
                    <?= $message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- ==================================================================== -->
            <!-- 1. HALAMAN TABEL BARANG -->
            <!-- ==================================================================== -->
            <?php if ($page == 'barang'): ?>
                <?php
                $edit_data = null;
                if ($action == 'edit' && $id > 0) {
                    $res = mysqli_query($conn, "SELECT * FROM barang WHERE idbarang=$id");
                    $edit_data = mysqli_fetch_assoc($res);
                }
                ?>
                <h3 class="fw-bold text-dark mb-4"><i class="fa-solid fa-box text-primary me-2"></i>Kelola Data Barang</h3>
                
                <!-- Form Insert/Update -->
                <div class="card p-4 mb-4">
                    <h5 class="fw-bold mb-3"><?= $edit_data ? 'Edit Barang' : 'Tambah Barang Baru' ?></h5>
                    <form method="POST" action="?page=barang">
                        <?php if ($edit_data): ?>
                            <input type="hidden" name="idbarang" value="<?= $edit_data['idbarang'] ?>">
                        <?php endif; ?>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Nama Barang</label>
                                <input type="text" name="nama" class="form-control" value="<?= $edit_data['nama'] ?? '' ?>" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Jenis</label>
                                <input type="text" name="jenis" class="form-control" maxlength="1" value="<?= $edit_data['jenis'] ?? 'A' ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Satuan</label>
                                <select name="idsatuan" class="form-select" required>
                                    <option value="">-- Pilih Satuan --</option>
                                    <?php
                                    $sat = mysqli_query($conn, "SELECT * FROM satuan");
                                    while ($s = mysqli_fetch_assoc($sat)) {
                                        $sel = ($edit_data && $edit_data['idsatuan'] == $s['idsatuan']) ? 'selected' : '';
                                        echo "<option value='{$s['idsatuan']}' $sel>{$s['nama_satuan']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Harga (Rp)</label>
                                <input type="number" name="harga" class="form-control" value="<?= $edit_data['harga'] ?? 0 ?>" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="1" <?= ($edit_data && $edit_data['status'] == 1) ? 'selected' : '' ?>>Aktif</option>
                                    <option value="0" <?= ($edit_data && $edit_data['status'] == 0) ? 'selected' : '' ?>>Non-Aktif</option>
                                </select>
                            </div>
                            <div class="col-12 mt-3">
                                <button type="submit" name="<?= $edit_data ? 'btn_update' : 'btn_save' ?>" class="btn btn-<?= $edit_data ? 'warning' : 'primary' ?> px-4">
                                    <i class="fa-solid fa-save me-1"></i> <?= $edit_data ? 'Update' : 'Simpan' ?>
                                </button>
                                <?php if ($edit_data): ?>
                                    <a href="?page=barang" class="btn btn-secondary ms-2">Batal</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Read Table -->
                <div class="card p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama Barang</th>
                                    <th>Jenis</th>
                                    <th>Satuan</th>
                                    <th>Harga</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT b.*, s.nama_satuan FROM barang b JOIN satuan s ON b.idsatuan = s.idsatuan ORDER BY b.idbarang DESC";
                                $data  = mysqli_query($conn, $query);
                                while ($row = mysqli_fetch_assoc($data)):
                                ?>
                                <tr>
                                    <td><?= $row['idbarang'] ?></td>
                                    <td class="fw-bold"><?= $row['nama'] ?></td>
                                    <td><span class="badge bg-info text-dark"><?= $row['jenis'] ?></span></td>
                                    <td><?= $row['nama_satuan'] ?></td>
                                    <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                                    <td>
                                        <span class="badge bg-<?= $row['status'] == 1 ? 'success' : 'danger' ?>">
                                            <?= $row['status'] == 1 ? 'Aktif' : 'Non-Aktif' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="?page=barang&action=edit&id=<?= $row['idbarang'] ?>" class="btn btn-sm btn-warning me-1"><i class="fa-solid fa-pen"></i> Edit</a>
                                        <a href="?page=barang&action=delete&id=<?= $row['idbarang'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus data ini?')"><i class="fa-solid fa-trash"></i> Hapus</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <!-- ==================================================================== -->
            <!-- 2. HALAMAN TABEL SATUAN -->
            <!-- ==================================================================== -->
            <?php elseif ($page == 'satuan'): ?>
                <?php
                $edit_data = null;
                if ($action == 'edit' && $id > 0) {
                    $res = mysqli_query($conn, "SELECT * FROM satuan WHERE idsatuan=$id");
                    $edit_data = mysqli_fetch_assoc($res);
                }
                ?>
                <h3 class="fw-bold text-dark mb-4"><i class="fa-solid fa-scale-balanced text-primary me-2"></i>Kelola Data Satuan</h3>
                
                <div class="card p-4 mb-4">
                    <h5 class="fw-bold mb-3"><?= $edit_data ? 'Edit Satuan' : 'Tambah Satuan' ?></h5>
                    <form method="POST" action="?page=satuan">
                        <?php if ($edit_data): ?>
                            <input type="hidden" name="idsatuan" value="<?= $edit_data['idsatuan'] ?>">
                        <?php endif; ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Satuan</label>
                                <input type="text" name="nama_satuan" class="form-control" value="<?= $edit_data['nama_satuan'] ?? '' ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="1" <?= ($edit_data && $edit_data['status'] == 1) ? 'selected' : '' ?>>Aktif</option>
                                    <option value="0" <?= ($edit_data && $edit_data['status'] == 0) ? 'selected' : '' ?>>Non-Aktif</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" name="<?= $edit_data ? 'btn_update' : 'btn_save' ?>" class="btn btn-<?= $edit_data ? 'warning' : 'primary' ?> w-100">
                                    <?= $edit_data ? 'Update' : 'Simpan' ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card p-4">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Satuan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $data = mysqli_query($conn, "SELECT * FROM satuan ORDER BY idsatuan DESC");
                            while ($row = mysqli_fetch_assoc($data)):
                            ?>
                            <tr>
                                <td><?= $row['idsatuan'] ?></td>
                                <td class="fw-bold"><?= $row['nama_satuan'] ?></td>
                                <td><span class="badge bg-<?= $row['status'] == 1 ? 'success' : 'danger' ?>"><?= $row['status'] == 1 ? 'Aktif' : 'Non-Aktif' ?></span></td>
                                <td>
                                    <a href="?page=satuan&action=edit&id=<?= $row['idsatuan'] ?>" class="btn btn-sm btn-warning"><i class="fa-solid fa-pen"></i> Edit</a>
                                    <a href="?page=satuan&action=delete&id=<?= $row['idsatuan'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus data satuan ini?')"><i class="fa-solid fa-trash"></i> Hapus</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

            <!-- ==================================================================== -->
            <!-- 3. HALAMAN TABEL VENDOR -->
            <!-- ==================================================================== -->
            <?php elseif ($page == 'vendor'): ?>
                <?php
                $edit_data = null;
                if ($action == 'edit' && $id > 0) {
                    $res = mysqli_query($conn, "SELECT * FROM vendor WHERE idvendor=$id");
                    $edit_data = mysqli_fetch_assoc($res);
                }
                ?>
                <h3 class="fw-bold text-dark mb-4"><i class="fa-solid fa-truck-field text-primary me-2"></i>Kelola Data Vendor</h3>
                
                <div class="card p-4 mb-4">
                    <h5 class="fw-bold mb-3"><?= $edit_data ? 'Edit Vendor' : 'Tambah Vendor Baru' ?></h5>
                    <form method="POST" action="?page=vendor">
                        <?php if ($edit_data): ?>
                            <input type="hidden" name="idvendor" value="<?= $edit_data['idvendor'] ?>">
                        <?php endif; ?>
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label">Nama Vendor</label>
                                <input type="text" name="nama_vendor" class="form-control" value="<?= $edit_data['nama_vendor'] ?? '' ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Badan Hukum</label>
                                <select name="badan_hukum" class="form-select">
                                    <option value="Y" <?= ($edit_data && $edit_data['badan_hukum'] == 'Y') ? 'selected' : '' ?>>Ya (PT/CV)</option>
                                    <option value="N" <?= ($edit_data && $edit_data['badan_hukum'] == 'N') ? 'selected' : '' ?>>Tidak (Perorangan)</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="A" <?= ($edit_data && $edit_data['status'] == 'A') ? 'selected' : '' ?>>Aktif (A)</option>
                                    <option value="N" <?= ($edit_data && $edit_data['status'] == 'N') ? 'selected' : '' ?>>Non-Aktif (N)</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" name="<?= $edit_data ? 'btn_update' : 'btn_save' ?>" class="btn btn-<?= $edit_data ? 'warning' : 'primary' ?> w-100">
                                    <?= $edit_data ? 'Update' : 'Simpan' ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card p-4">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Vendor</th>
                                <th>Badan Hukum</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $data = mysqli_query($conn, "SELECT * FROM vendor ORDER BY idvendor DESC");
                            while ($row = mysqli_fetch_assoc($data)):
                            ?>
                            <tr>
                                <td><?= $row['idvendor'] ?></td>
                                <td class="fw-bold"><?= $row['nama_vendor'] ?></td>
                                <td><span class="badge bg-secondary"><?= $row['badan_hukum'] == 'Y' ? 'Berbadan Hukum' : 'Non-Badan Hukum' ?></span></td>
                                <td><span class="badge bg-<?= $row['status'] == 'A' ? 'success' : 'danger' ?>"><?= $row['status'] == 'A' ? 'Aktif' : 'Inaktif' ?></span></td>
                                <td>
                                    <a href="?page=vendor&action=edit&id=<?= $row['idvendor'] ?>" class="btn btn-sm btn-warning"><i class="fa-solid fa-pen"></i> Edit</a>
                                    <a href="?page=vendor&action=delete&id=<?= $row['idvendor'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus vendor ini?')"><i class="fa-solid fa-trash"></i> Hapus</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

            <!-- ==================================================================== -->
            <!-- 4. HALAMAN TABEL USER -->
            <!-- ==================================================================== -->
            <?php elseif ($page == 'user'): ?>
                <?php
                $edit_data = null;
                if ($action == 'edit' && $id > 0) {
                    $res = mysqli_query($conn, "SELECT * FROM user WHERE iduser=$id");
                    $edit_data = mysqli_fetch_assoc($res);
                }
                ?>
                <h3 class="fw-bold text-dark mb-4"><i class="fa-solid fa-users text-primary me-2"></i>Kelola Data User</h3>
                
                <div class="card p-4 mb-4">
                    <h5 class="fw-bold mb-3"><?= $edit_data ? 'Edit User' : 'Tambah User Baru' ?></h5>
                    <form method="POST" action="?page=user">
                        <?php if ($edit_data): ?>
                            <input type="hidden" name="iduser" value="<?= $edit_data['iduser'] ?>">
                        <?php endif; ?>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" value="<?= $edit_data['username'] ?? '' ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" value="<?= $edit_data['password'] ?? '' ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Role</label>
                                <select name="idrole" class="form-select" required>
                                    <option value="">-- Pilih Role --</option>
                                    <?php
                                    $roles = mysqli_query($conn, "SELECT * FROM role");
                                    while ($r = mysqli_fetch_assoc($roles)) {
                                        $sel = ($edit_data && $edit_data['idrole'] == $r['idrole']) ? 'selected' : '';
                                        echo "<option value='{$r['idrole']}' $sel>{$r['nama_role']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-12 mt-3">
                                <button type="submit" name="<?= $edit_data ? 'btn_update' : 'btn_save' ?>" class="btn btn-<?= $edit_data ? 'warning' : 'primary' ?>">
                                    <?= $edit_data ? 'Update User' : 'Simpan User' ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card p-4">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $data = mysqli_query($conn, "SELECT u.*, r.nama_role FROM user u JOIN role r ON u.idrole = r.idrole ORDER BY u.iduser DESC");
                            while ($row = mysqli_fetch_assoc($data)):
                            ?>
                            <tr>
                                <td><?= $row['iduser'] ?></td>
                                <td class="fw-bold"><?= $row['username'] ?></td>
                                <td><span class="badge bg-primary"><?= $row['nama_role'] ?></span></td>
                                <td>
                                    <a href="?page=user&action=edit&id=<?= $row['iduser'] ?>" class="btn btn-sm btn-warning"><i class="fa-solid fa-pen"></i> Edit</a>
                                    <a href="?page=user&action=delete&id=<?= $row['iduser'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus user ini?')"><i class="fa-solid fa-trash"></i> Hapus</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

            <!-- ==================================================================== -->
            <!-- 5. HALAMAN TABEL ROLE -->
            <!-- ==================================================================== -->
            <?php elseif ($page == 'role'): ?>
                <?php
                $edit_data = null;
                if ($action == 'edit' && $id > 0) {
                    $res = mysqli_query($conn, "SELECT * FROM role WHERE idrole=$id");
                    $edit_data = mysqli_fetch_assoc($res);
                }
                ?>
                <h3 class="fw-bold text-dark mb-4"><i class="fa-solid fa-user-shield text-primary me-2"></i>Kelola Data Role</h3>
                
                <div class="card p-4 mb-4">
                    <h5 class="fw-bold mb-3"><?= $edit_data ? 'Edit Role' : 'Tambah Role' ?></h5>
                    <form method="POST" action="?page=role">
                        <?php if ($edit_data): ?>
                            <input type="hidden" name="idrole" value="<?= $edit_data['idrole'] ?>">
                        <?php endif; ?>
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Nama Role</label>
                                <input type="text" name="nama_role" class="form-control" value="<?= $edit_data['nama_role'] ?? '' ?>" required>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" name="<?= $edit_data ? 'btn_update' : 'btn_save' ?>" class="btn btn-<?= $edit_data ? 'warning' : 'primary' ?> w-100">
                                    <?= $edit_data ? 'Update' : 'Simpan' ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card p-4">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Role</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $data = mysqli_query($conn, "SELECT * FROM role ORDER BY idrole DESC");
                            while ($row = mysqli_fetch_assoc($data)):
                            ?>
                            <tr>
                                <td><?= $row['idrole'] ?></td>
                                <td class="fw-bold"><?= $row['nama_role'] ?></td>
                                <td>
                                    <a href="?page=role&action=edit&id=<?= $row['idrole'] ?>" class="btn btn-sm btn-warning"><i class="fa-solid fa-pen"></i> Edit</a>
                                    <a href="?page=role&action=delete&id=<?= $row['idrole'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus role ini?')"><i class="fa-solid fa-trash"></i> Hapus</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

            <!-- ==================================================================== -->
            <!-- 6. HALAMAN TABEL MARGIN PENJUALAN -->
            <!-- ==================================================================== -->
            <?php elseif ($page == 'margin'): ?>
                <?php
                $edit_data = null;
                if ($action == 'edit' && $id > 0) {
                    $res = mysqli_query($conn, "SELECT * FROM margin_penjualan WHERE idmargin_penjualan=$id");
                    $edit_data = mysqli_fetch_assoc($res);
                }
                ?>
                <h3 class="fw-bold text-dark mb-4"><i class="fa-solid fa-percent text-primary me-2"></i>Kelola Margin Penjualan</h3>
                
                <div class="card p-4 mb-4">
                    <h5 class="fw-bold mb-3"><?= $edit_data ? 'Edit Margin' : 'Tambah Margin Penjualan' ?></h5>
                    <form method="POST" action="?page=margin">
                        <?php if ($edit_data): ?>
                            <input type="hidden" name="idmargin_penjualan" value="<?= $edit_data['idmargin_penjualan'] ?>">
                        <?php endif; ?>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Persen Margin (%)</label>
                                <input type="number" step="0.1" name="persen" class="form-control" value="<?= $edit_data['persen'] ?? 10.0 ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">User Pengatur</label>
                                <select name="iduser" class="form-select" required>
                                    <option value="">-- Pilih User --</option>
                                    <?php
                                    $users = mysqli_query($conn, "SELECT * FROM user");
                                    while ($u = mysqli_fetch_assoc($users)) {
                                        $sel = ($edit_data && $edit_data['iduser'] == $u['iduser']) ? 'selected' : '';
                                        echo "<option value='{$u['iduser']}' $sel>{$u['username']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="1" <?= ($edit_data && $edit_data['status'] == 1) ? 'selected' : '' ?>>Aktif</option>
                                    <option value="0" <?= ($edit_data && $edit_data['status'] == 0) ? 'selected' : '' ?>>Non-Aktif</option>
                                </select>
                            </div>
                            <div class="col-12 mt-3">
                                <button type="submit" name="<?= $edit_data ? 'btn_update' : 'btn_save' ?>" class="btn btn-<?= $edit_data ? 'warning' : 'primary' ?>">
                                    <?= $edit_data ? 'Update Margin' : 'Simpan Margin' ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card p-4">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Persen</th>
                                <th>Status</th>
                                <th>User Pengatur</th>
                                <th>Tanggal Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $data = mysqli_query($conn, "SELECT m.*, u.username FROM margin_penjualan m JOIN user u ON m.iduser = u.iduser ORDER BY m.idmargin_penjualan DESC");
                            while ($row = mysqli_fetch_assoc($data)):
                            ?>
                            <tr>
                                <td><?= $row['idmargin_penjualan'] ?></td>
                                <td class="fw-bold text-success"><?= $row['persen'] ?> %</td>
                                <td><span class="badge bg-<?= $row['status'] == 1 ? 'success' : 'danger' ?>"><?= $row['status'] == 1 ? 'Aktif' : 'Inaktif' ?></span></td>
                                <td><?= $row['username'] ?></td>
                                <td><?= $row['created_at'] ?></td>
                                <td>
                                    <a href="?page=margin&action=edit&id=<?= $row['idmargin_penjualan'] ?>" class="btn btn-sm btn-warning"><i class="fa-solid fa-pen"></i> Edit</a>
                                    <a href="?page=margin&action=delete&id=<?= $row['idmargin_penjualan'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus margin ini?')"><i class="fa-solid fa-trash"></i> Hapus</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>