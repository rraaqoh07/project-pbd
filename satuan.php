<?php
include 'header.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$id     = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = ""; $msg_type = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_satuan = mysqli_real_escape_string($conn, $_POST['nama_satuan']);
    $status      = intval($_POST['status']);
    
    if (isset($_POST['btn_save'])) {
        $sql = "INSERT INTO satuan (nama_satuan, status) VALUES ('$nama_satuan', $status)";
        if (mysqli_query($conn, $sql)) { $message = "Satuan disimpan!"; $msg_type = "success"; }
    } elseif (isset($_POST['btn_update'])) {
        $idsatuan = intval($_POST['idsatuan']);
        $sql = "UPDATE satuan SET nama_satuan='$nama_satuan', status=$status WHERE idsatuan=$idsatuan";
        if (mysqli_query($conn, $sql)) { $message = "Satuan diperbarui!"; $msg_type = "success"; }
    }
}

if ($action == 'delete' && $id > 0) {
    $sql = "DELETE FROM satuan WHERE idsatuan=$id";
    if (mysqli_query($conn, $sql)) { $message = "Satuan dihapus!"; $msg_type = "danger"; }
    else { $message = "Gagal hapus! Terikat barang."; $msg_type = "warning"; }
}

$edit_data = null;
if ($action == 'edit' && $id > 0) {
    $res = mysqli_query($conn, "SELECT * FROM satuan WHERE idsatuan=$id");
    $edit_data = mysqli_fetch_assoc($res);
}
?>

<h4 class="fw-bold mb-1"><i class="fa-solid fa-scale-balanced text-primary me-2"></i>Kelola Data Satuan</h4>
<p class="text-muted small mb-4">Master takaran unit barang (Kg, Pcs, Liter, Bungkus, Dus).</p>

<?php if ($message): ?>
    <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show"><?= $message ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="bg-light p-3 rounded-4 mb-4 border">
    <form method="POST" action="satuan.php">
        <?php if ($edit_data): ?><input type="hidden" name="idsatuan" value="<?= $edit_data['idsatuan'] ?>"><?php endif; ?>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label small fw-bold">Nama Satuan</label>
                <input type="text" name="nama_satuan" class="form-control" value="<?= $edit_data['nama_satuan'] ?? '' ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Status</label>
                <select name="status" class="form-select">
                    <option value="1" <?= ($edit_data && $edit_data['status'] == 1) ? 'selected' : '' ?>>Aktif</option>
                    <option value="0" <?= ($edit_data && $edit_data['status'] == 0) ? 'selected' : '' ?>>Non-Aktif</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" name="<?= $edit_data ? 'btn_update' : 'btn_save' ?>" class="btn btn-primary w-100 fw-bold"><?= $edit_data ? 'Update' : 'Simpan' ?></button>
            </div>
        </div>
    </form>
</div>

<div class="table-responsive">
    <table class="table align-middle">
        <thead>
            <tr><th>ID</th><th>Nama Satuan</th><th>Status</th><th class="text-center">Aksi</th></tr>
        </thead>
        <tbody>
            <?php
            $data = mysqli_query($conn, "SELECT * FROM satuan ORDER BY idsatuan DESC");
            while ($row = mysqli_fetch_assoc($data)):
            ?>
            <tr>
                <td>#<?= $row['idsatuan'] ?></td>
                <td class="fw-bold"><?= $row['nama_satuan'] ?></td>
                <td><span class="badge bg-<?= $row['status'] == 1 ? 'success' : 'danger' ?>-subtle text-<?= $row['status'] == 1 ? 'success' : 'danger' ?> px-3 py-2 rounded-pill"><?= $row['status'] == 1 ? 'Aktif' : 'Non-Aktif' ?></span></td>
                <td class="text-center">
                    <a href="satuan.php?action=edit&id=<?= $row['idsatuan'] ?>" class="btn btn-action btn-warning text-white me-1"><i class="fa-solid fa-pen"></i></a>
                    <a href="satuan.php?action=delete&id=<?= $row['idsatuan'] ?>" class="btn btn-action btn-danger" onclick="return confirm('Hapus satuan ini?')"><i class="fa-solid fa-trash"></i></a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>