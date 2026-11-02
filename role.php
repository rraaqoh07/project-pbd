<?php
include 'header.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$id     = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = ""; $msg_type = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_role = mysqli_real_escape_string($conn, $_POST['nama_role']);
    
    if (isset($_POST['btn_save'])) {
        $sql = "INSERT INTO role (nama_role) VALUES ('$nama_role')";
        if (mysqli_query($conn, $sql)) { $message = "Role disimpan!"; $msg_type = "success"; }
    } elseif (isset($_POST['btn_update'])) {
        $idrole = intval($_POST['idrole']);
        $sql = "UPDATE role SET nama_role='$nama_role' WHERE idrole=$idrole";
        if (mysqli_query($conn, $sql)) { $message = "Role diperbarui!"; $msg_type = "success"; }
    }
}

if ($action == 'delete' && $id > 0) {
    $sql = "DELETE FROM role WHERE idrole=$id";
    if (mysqli_query($conn, $sql)) { $message = "Role dihapus!"; $msg_type = "danger"; }
    else { $message = "Gagal hapus! Masih digunakan user."; $msg_type = "warning"; }
}

$edit_data = null;
if ($action == 'edit' && $id > 0) {
    $res = mysqli_query($conn, "SELECT * FROM role WHERE idrole=$id");
    $edit_data = mysqli_fetch_assoc($res);
}
?>

<h4 class="fw-bold mb-1"><i class="fa-solid fa-user-shield text-primary me-2"></i>Kelola Data Role</h4>
<p class="text-muted small mb-4">Pengelompokan jabatan hak akses staf toko.</p>

<?php if ($message): ?>
    <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show"><?= $message ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="bg-light p-3 rounded-4 mb-4 border">
    <form method="POST" action="role.php">
        <?php if ($edit_data): ?><input type="hidden" name="idrole" value="<?= $edit_data['idrole'] ?>"><?php endif; ?>
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label small fw-bold">Nama Role / Jabatan</label>
                <input type="text" name="nama_role" class="form-control" value="<?= $edit_data['nama_role'] ?? '' ?>" required>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" name="<?= $edit_data ? 'btn_update' : 'btn_save' ?>" class="btn btn-primary w-100 fw-bold"><?= $edit_data ? 'Update' : 'Simpan' ?></button>
            </div>
        </div>
    </form>
</div>

<div class="table-responsive">
    <table class="table align-middle">
        <thead>
            <tr><th>ID</th><th>Nama Role</th><th class="text-center">Aksi</th></tr>
        </thead>
        <tbody>
            <?php
            $data = mysqli_query($conn, "SELECT * FROM role ORDER BY idrole DESC");
            while ($row = mysqli_fetch_assoc($data)):
            ?>
            <tr>
                <td>#<?= $row['idrole'] ?></td>
                <td class="fw-bold"><?= $row['nama_role'] ?></td>
                <td class="text-center">
                    <a href="role.php?action=edit&id=<?= $row['idrole'] ?>" class="btn btn-action btn-warning text-white me-1"><i class="fa-solid fa-pen"></i></a>
                    <a href="role.php?action=delete&id=<?= $row['idrole'] ?>" class="btn btn-action btn-danger" onclick="return confirm('Hapus role ini?')"><i class="fa-solid fa-trash"></i></a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>