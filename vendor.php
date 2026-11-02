<?php
include 'header.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$id     = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = ""; $msg_type = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_vendor = mysqli_real_escape_string($conn, $_POST['nama_vendor']);
    $badan_hukum = mysqli_real_escape_string($conn, $_POST['badan_hukum']);
    $status      = mysqli_real_escape_string($conn, $_POST['status']);
    
    if (isset($_POST['btn_save'])) {
        $sql = "INSERT INTO vendor (nama_vendor, badan_hukum, status) VALUES ('$nama_vendor', '$badan_hukum', '$status')";
        if (mysqli_query($conn, $sql)) { $message = "Vendor disimpan!"; $msg_type = "success"; }
    } elseif (isset($_POST['btn_update'])) {
        $idvendor = intval($_POST['idvendor']);
        $sql = "UPDATE vendor SET nama_vendor='$nama_vendor', badan_hukum='$badan_hukum', status='$status' WHERE idvendor=$idvendor";
        if (mysqli_query($conn, $sql)) { $message = "Vendor diperbarui!"; $msg_type = "success"; }
    }
}

if ($action == 'delete' && $id > 0) {
    $sql = "DELETE FROM vendor WHERE idvendor=$id";
    if (mysqli_query($conn, $sql)) { $message = "Vendor dihapus!"; $msg_type = "danger"; }
    else { $message = "Gagal hapus! Terikat transaksi pengadaan."; $msg_type = "warning"; }
}

$edit_data = null;
if ($action == 'edit' && $id > 0) {
    $res = mysqli_query($conn, "SELECT * FROM vendor WHERE idvendor=$id");
    $edit_data = mysqli_fetch_assoc($res);
}
?>

<h4 class="fw-bold mb-1"><i class="fa-solid fa-truck-field text-primary me-2"></i>Kelola Data Vendor</h4>
<p class="text-muted small mb-4">Daftar distributor / pemasok stok Toko Kelontong Bakti.</p>

<?php if ($message): ?>
    <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show"><?= $message ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="bg-light p-3 rounded-4 mb-4 border">
    <form method="POST" action="vendor.php">
        <?php if ($edit_data): ?><input type="hidden" name="idvendor" value="<?= $edit_data['idvendor'] ?>"><?php endif; ?>
        <div class="row g-3">
            <div class="col-md-5">
                <label class="form-label small fw-bold">Nama Vendor</label>
                <input type="text" name="nama_vendor" class="form-control" value="<?= $edit_data['nama_vendor'] ?? '' ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Badan Hukum</label>
                <select name="badan_hukum" class="form-select">
                    <option value="Y" <?= ($edit_data && $edit_data['badan_hukum'] == 'Y') ? 'selected' : '' ?>>Ya (PT/CV)</option>
                    <option value="N" <?= ($edit_data && $edit_data['badan_hukum'] == 'N') ? 'selected' : '' ?>>Tidak (Perorangan)</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Status</label>
                <select name="status" class="form-select">
                    <option value="A" <?= ($edit_data && $edit_data['status'] == 'A') ? 'selected' : '' ?>>Aktif (A)</option>
                    <option value="N" <?= ($edit_data && $edit_data['status'] == 'N') ? 'selected' : '' ?>>Inaktif (N)</option>
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
            <tr><th>ID</th><th>Nama Vendor</th><th>Badan Hukum</th><th>Status</th><th class="text-center">Aksi</th></tr>
        </thead>
        <tbody>
            <?php
            $data = mysqli_query($conn, "SELECT * FROM vendor ORDER BY idvendor DESC");
            while ($row = mysqli_fetch_assoc($data)):
            ?>
            <tr>
                <td>#<?= $row['idvendor'] ?></td>
                <td class="fw-bold"><?= $row['nama_vendor'] ?></td>
                <td><span class="badge bg-secondary border"><?= $row['badan_hukum'] == 'Y' ? 'Berbadan Hukum' : 'Perorangan' ?></span></td>
                <td><span class="badge bg-<?= $row['status'] == 'A' ? 'success' : 'danger' ?>-subtle text-<?= $row['status'] == 'A' ? 'success' : 'danger' ?> px-3 py-2 rounded-pill"><?= $row['status'] == 'A' ? 'Aktif' : 'Inaktif' ?></span></td>
                <td class="text-center">
                    <a href="vendor.php?action=edit&id=<?= $row['idvendor'] ?>" class="btn btn-action btn-warning text-white me-1"><i class="fa-solid fa-pen"></i></a>
                    <a href="vendor.php?action=delete&id=<?= $row['idvendor'] ?>" class="btn btn-action btn-danger" onclick="return confirm('Hapus vendor ini?')"><i class="fa-solid fa-trash"></i></a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>