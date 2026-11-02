<?php
include 'header.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$id     = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = ""; $msg_type = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $persen = floatval($_POST['persen']);
    $status = intval($_POST['status']);
    $iduser = intval($_POST['iduser']);
    
    if (isset($_POST['btn_save'])) {
        $sql = "INSERT INTO margin_penjualan (persen, status, iduser) VALUES ($persen, $status, $iduser)";
        if (mysqli_query($conn, $sql)) { $message = "Margin disimpan!"; $msg_type = "success"; }
    } elseif (isset($_POST['btn_update'])) {
        $idmargin = intval($_POST['idmargin_penjualan']);
        $sql = "UPDATE margin_penjualan SET persen=$persen, status=$status, iduser=$iduser WHERE idmargin_penjualan=$idmargin";
        if (mysqli_query($conn, $sql)) { $message = "Margin diperbarui!"; $msg_type = "success"; }
    }
}

if ($action == 'delete' && $id > 0) {
    $sql = "DELETE FROM margin_penjualan WHERE idmargin_penjualan=$id";
    if (mysqli_query($conn, $sql)) { $message = "Margin dihapus!"; $msg_type = "danger"; }
    else { $message = "Gagal hapus! Terikat transaksi penjualan."; $msg_type = "warning"; }
}

$edit_data = null;
if ($action == 'edit' && $id > 0) {
    $res = mysqli_query($conn, "SELECT * FROM margin_penjualan WHERE idmargin_penjualan=$id");
    $edit_data = mysqli_fetch_assoc($res);
}
?>

<h4 class="fw-bold mb-1"><i class="fa-solid fa-percent text-primary me-2"></i>Margin Penjualan</h4>
<p class="text-muted small mb-4">Pengaturan persentase margin keuntungan toko.</p>

<?php if ($message): ?>
    <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show"><?= $message ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="bg-light p-3 rounded-4 mb-4 border">
    <form method="POST" action="margin.php">
        <?php if ($edit_data): ?><input type="hidden" name="idmargin_penjualan" value="<?= $edit_data['idmargin_penjualan'] ?>"><?php endif; ?>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label small fw-bold">Margin (%)</label>
                <input type="number" step="0.1" name="persen" class="form-control" value="<?= $edit_data['persen'] ?? 10.0 ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Ditetapkan Oleh User</label>
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
                <label class="form-label small fw-bold">Status Margin</label>
                <select name="status" class="form-select">
                    <option value="1" <?= ($edit_data && $edit_data['status'] == 1) ? 'selected' : '' ?>>Aktif</option>
                    <option value="0" <?= ($edit_data && $edit_data['status'] == 0) ? 'selected' : '' ?>>Inaktif</option>
                </select>
            </div>
            <div class="col-12 text-end">
                <button type="submit" name="<?= $edit_data ? 'btn_update' : 'btn_save' ?>" class="btn btn-primary fw-bold"><?= $edit_data ? 'Update Margin' : 'Simpan Margin' ?></button>
            </div>
        </div>
    </form>
</div>

<div class="table-responsive">
    <table class="table align-middle">
        <thead>
            <tr><th>ID</th><th>Persen Profit</th><th>Status</th><th>User Pengatur</th><th>Tgl Update</th><th class="text-center">Aksi</th></tr>
        </thead>
        <tbody>
            <?php
            $data = mysqli_query($conn, "SELECT m.*, u.username FROM margin_penjualan m JOIN user u ON m.iduser = u.iduser ORDER BY m.idmargin_penjualan DESC");
            while ($row = mysqli_fetch_assoc($data)):
            ?>
            <tr>
                <td>#<?= $row['idmargin_penjualan'] ?></td>
                <td><span class="fs-6 fw-bold text-success">+<?= $row['persen'] ?>%</span></td>
                <td><span class="badge bg-<?= $row['status'] == 1 ? 'success' : 'danger' ?>-subtle text-<?= $row['status'] == 1 ? 'success' : 'danger' ?> px-3 py-2 rounded-pill"><?= $row['status'] == 1 ? 'Aktif' : 'Inaktif' ?></span></td>
                <td class="fw-semibold"><?= $row['username'] ?></td>
                <td class="text-muted small"><?= $row['created_at'] ?></td>
                <td class="text-center">
                    <a href="margin.php?action=edit&id=<?= $row['idmargin_penjualan'] ?>" class="btn btn-action btn-warning text-white me-1"><i class="fa-solid fa-pen"></i></a>
                    <a href="margin.php?action=delete&id=<?= $row['idmargin_penjualan'] ?>" class="btn btn-action btn-danger" onclick="return confirm('Hapus margin ini?')"><i class="fa-solid fa-trash"></i></a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>