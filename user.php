<?php
include 'koneksi.php';
include 'header.php';

$action  = isset($_GET['action']) ? $_GET['action'] : '';
$id      = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = ""; 
$msg_type = "";

// PROSES TAMBAH / UPDATE DATA USER
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $idrole   = intval($_POST['idrole']);
    
    if (isset($_POST['btn_save'])) {
        $sql = "INSERT INTO user (username, password, idrole) VALUES ('$username', '$password', $idrole)";
        if (mysqli_query($conn, $sql)) { 
            $message = "User berhasil disimpan!"; 
            $msg_type = "success"; 
        }
    } elseif (isset($_POST['btn_update'])) {
        $iduser = intval($_POST['iduser']);
        $sql    = "UPDATE user SET username='$username', password='$password', idrole=$idrole WHERE iduser=$iduser";
        if (mysqli_query($conn, $sql)) { 
            $message = "User berhasil diperbarui!"; 
            $msg_type = "success"; 
        }
    }
}

// PROSES HAPUS DATA USER
if ($action == 'delete' && $id > 0) {
    $sql = "DELETE FROM user WHERE iduser=$id";
    if (mysqli_query($conn, $sql)) { 
        $message = "User berhasil dihapus!"; 
        $msg_type = "danger"; 
    } else { 
        $message = "Gagal hapus! Data terikat pada transaksi."; 
        $msg_type = "warning"; 
    }
}

// AMBIL DATA UNTUK EDIT FORM
$edit_data = null;
if ($action == 'edit' && $id > 0) {
    $res       = mysqli_query($conn, "SELECT * FROM user WHERE iduser=$id");
    $edit_data = mysqli_fetch_assoc($res);
}
?>

<h4 class="fw-bold mb-1"><i class="fa-solid fa-users text-primary me-2"></i>Kelola Data User</h4>
<p class="text-muted small mb-4">Pengaturan akun kasir, manajer, dan petugas toko.</p>

<?php if ($message): ?>
    <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show">
        <?= $message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- FORM INPUT / EDIT USER -->
<div class="bg-light p-3 rounded-4 mb-4 border">
    <form method="POST" action="user.php">
        <?php if ($edit_data): ?>
            <input type="hidden" name="iduser" value="<?= $edit_data['iduser'] ?>">
        <?php endif; ?>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label small fw-bold">Username</label>
                <input type="text" name="username" class="form-control" value="<?= $edit_data['username'] ?? '' ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Password</label>
                <input type="password" name="password" class="form-control" value="<?= $edit_data['password'] ?? '' ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Role Hak Akses</label>
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
            <div class="col-12 text-end">
                <?php if ($edit_data): ?>
                    <a href="user.php" class="btn btn-light me-2">Batal</a>
                <?php endif; ?>
                <button type="submit" name="<?= $edit_data ? 'btn_update' : 'btn_save' ?>" class="btn btn-primary fw-bold">
                    <?= $edit_data ? 'Update User' : 'Simpan User' ?>
                </button>
            </div>
        </div>
    </form>
</div>

<!-- TABEL DATA USER DENGAN HASIL FUNCTION CEK AKSES -->
<div class="table-responsive">
    <table class="table align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Role Jabatan</th>
                <th>Status Akses (Hasil Function)</th>
                <th class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // QUERY DENGAN MEMANGGIL FUNCTION DATABASE (f_cek_akses_role)
            $query = "SELECT u.*, r.nama_role, f_cek_akses_role(u.iduser) AS hak_akses 
                      FROM user u 
                      JOIN role r ON u.idrole = r.idrole 
                      ORDER BY u.iduser DESC";
            $data  = mysqli_query($conn, $query);
            while ($row = mysqli_fetch_assoc($data)):
            ?>
            <tr>
                <td>#<?= $row['iduser'] ?></td>
                <td class="fw-bold"><?= $row['username'] ?></td>
                <td><span class="badge bg-secondary border"><?= $row['nama_role'] ?></span></td>
                <!-- MENAMPILKAN HASIL DARI FUNCTION f_cek_akses_role -->
                <td>
                    <span class="badge bg-info text-dark px-3 py-2 rounded-pill">
                        <i class="fa-solid fa-shield-halved me-1"></i> <?= $row['hak_akses'] ?>
                    </span>
                </td>
                <td class="text-center">
                    <a href="user.php?action=edit&id=<?= $row['iduser'] ?>" class="btn btn-action btn-warning text-white me-1"><i class="fa-solid fa-pen"></i></a>
                    <a href="user.php?action=delete&id=<?= $row['iduser'] ?>" class="btn btn-action btn-danger" onclick="return confirm('Hapus user ini?')"><i class="fa-solid fa-trash"></i></a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>