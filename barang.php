<?php
include 'header.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$id     = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = ""; $msg_type = "";

// Tambah / Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jenis    = mysqli_real_escape_string($conn, $_POST['jenis']);
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $idsatuan = intval($_POST['idsatuan']);
    $status   = intval($_POST['status']);
    $harga    = intval($_POST['harga']);
    
    if (isset($_POST['btn_save'])) {
        $sql = "INSERT INTO barang (jenis, nama, idsatuan, status, harga) VALUES ('$jenis', '$nama', $idsatuan, $status, $harga)";
        if (mysqli_query($conn, $sql)) { $message = "Barang berhasil disimpan!"; $msg_type = "success"; }
    } elseif (isset($_POST['btn_update'])) {
        $idbarang = intval($_POST['idbarang']);
        $sql = "UPDATE barang SET jenis='$jenis', nama='$nama', idsatuan=$idsatuan, status=$status, harga=$harga WHERE idbarang=$idbarang";
        if (mysqli_query($conn, $sql)) { $message = "Barang berhasil diperbarui!"; $msg_type = "success"; }
    }
}

// Hapus
if ($action == 'delete' && $id > 0) {
    $sql = "DELETE FROM barang WHERE idbarang=$id";
    if (mysqli_query($conn, $sql)) { $message = "Barang berhasil dihapus!"; $msg_type = "danger"; }
    else { $message = "Gagal hapus! Data terikat transaksi."; $msg_type = "warning"; }
}

// Ambil Data Edit
$edit_data = null;
if ($action == 'edit' && $id > 0) {
    $res = mysqli_query($conn, "SELECT * FROM barang WHERE idbarang=$id");
    $edit_data = mysqli_fetch_assoc($res);
}
?>

<h4 class="fw-bold mb-1"><i class="fa-solid fa-box text-primary me-2"></i>Kelola Data Barang</h4>
<p class="text-muted small mb-4">Katalog produk yang dijual di Toko Kelontong Bakti.</p>

<?php if ($message): ?>
    <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show"><?= $message ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="bg-light p-3 rounded-4 mb-4 border">
    <form method="POST" action="barang.php">
        <?php if ($edit_data): ?><input type="hidden" name="idbarang" value="<?= $edit_data['idbarang'] ?>"><?php endif; ?>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label small fw-bold">Nama Produk</label>
                <input type="text" name="nama" class="form-control" value="<?= $edit_data['nama'] ?? '' ?>" required>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Jenis</label>
                <input type="text" name="jenis" class="form-control" maxlength="1" value="<?= $edit_data['jenis'] ?? 'A' ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Satuan</label>
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
                <label class="form-label small fw-bold">Harga (Rp)</label>
                <input type="number" name="harga" class="form-control" value="<?= $edit_data['harga'] ?? 0 ?>" required>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Status</label>
                <select name="status" class="form-select">
                    <option value="1" <?= ($edit_data && $edit_data['status'] == 1) ? 'selected' : '' ?>>Aktif</option>
                    <option value="0" <?= ($edit_data && $edit_data['status'] == 0) ? 'selected' : '' ?>>Non-Aktif</option>
                </select>
            </div>
            <div class="col-12 text-end">
                <?php if ($edit_data): ?><a href="barang.php" class="btn btn-light me-2">Batal</a><?php endif; ?>
                <button type="submit" name="<?= $edit_data ? 'btn_update' : 'btn_save' ?>" class="btn btn-primary fw-bold"><?= $edit_data ? 'Update Barang' : 'Simpan Barang' ?></button>
            </div>
        </div>
    </form>
</div>

<div class="table-responsive">
    <table class="table align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Barang</th>
                <th>Jenis</th>
                <th>Satuan</th>
                <th>Harga Jual</th>
                <th>Status</th>
                <th class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = "SELECT b.*, s.nama_satuan FROM barang b JOIN satuan s ON b.idsatuan = s.idsatuan ORDER BY b.idbarang DESC";
            $data  = mysqli_query($conn, $query);
            while ($row = mysqli_fetch_assoc($data)):
            ?>
            <tr>
                <td>#<?= $row['idbarang'] ?></td>
                <td class="fw-bold"><?= $row['nama'] ?></td>
                <td><span class="badge bg-secondary border"><?= $row['jenis'] ?></span></td>
                <td><?= $row['nama_satuan'] ?></td>
                <td class="fw-bold text-primary">Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                <td><span class="badge bg-<?= $row['status'] == 1 ? 'success' : 'danger' ?>-subtle text-<?= $row['status'] == 1 ? 'success' : 'danger' ?> px-3 py-2 rounded-pill"><?= $row['status'] == 1 ? 'Aktif' : 'Non-Aktif' ?></span></td>
                <td class="text-center">
                    <a href="barang.php?action=edit&id=<?= $row['idbarang'] ?>" class="btn btn-action btn-warning text-white me-1"><i class="fa-solid fa-pen"></i></a>
                    <a href="barang.php?action=delete&id=<?= $row['idbarang'] ?>" class="btn btn-action btn-danger" onclick="return confirm('Hapus barang ini?')"><i class="fa-solid fa-trash"></i></a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>