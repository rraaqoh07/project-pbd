<?php
include 'koneksi.php';
include 'header.php';

$pesan = "";
$tipe_pesan = "success";

// 1. Tambah Barang Baru
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_simpan'])) {
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $jenis    = mysqli_real_escape_string($conn, $_POST['jenis']);
    $idsatuan = (int)$_POST['idsatuan'];
    $harga    = (int)$_POST['harga'];
    $status   = (int)$_POST['status'];

    $sql = "INSERT INTO barang (jenis, nama, idsatuan, status, harga) VALUES ('$jenis', '$nama', $idsatuan, $status, $harga)";
    if (mysqli_query($conn, $sql)) {
        $pesan = "Produk baru berhasil ditambahkan ke katalog!";
    } else {
        $pesan = "Gagal menambahkan produk: " . mysqli_error($conn);
        $tipe_pesan = "danger";
    }
}

// 2. Update Data Barang
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_update'])) {
    $idbarang = (int)$_POST['idbarang'];
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $jenis    = mysqli_real_escape_string($conn, $_POST['jenis']);
    $idsatuan = (int)$_POST['idsatuan'];
    $harga    = (int)$_POST['harga'];
    $status   = (int)$_POST['status'];

    $sql = "UPDATE barang SET jenis='$jenis', nama='$nama', idsatuan=$idsatuan, status=$status, harga=$harga WHERE idbarang=$idbarang";
    if (mysqli_query($conn, $sql)) {
        $pesan = "Data produk berhasil diperbarui!";
    } else {
        $pesan = "Gagal memperbarui produk: " . mysqli_error($conn);
        $tipe_pesan = "danger";
    }
}

// 3. Hapus Barang
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    if (mysqli_query($conn, "DELETE FROM barang WHERE idbarang=$id")) {
        $pesan = "Produk berhasil dihapus dari katalog!";
    } else {
        $pesan = "Produk tidak dapat dihapus karena sudah tercatat dalam riwayat transaksi.";
        $tipe_pesan = "danger";
    }
}

// Ambil data jika sedang mode Edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id_edit = (int)$_GET['edit'];
    $edit_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM barang WHERE idbarang=$id_edit"));
}
?>

<h4 class="fw-bold mb-1"><i class="fa-solid fa-box text-pink-pastel me-2"></i>Kelola Data Barang</h4>
<p class="text-muted small mb-4">Katalog produk yang dijual di Toko Kelontong Bakti.</p>

<?php if (!empty($pesan)): ?>
    <div class="alert alert-<?= $tipe_pesan ?> alert-dismissible fade show rounded-3 small">
        <i class="fa-solid <?= $tipe_pesan == 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?> me-1"></i> <?= $pesan ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- FORM INPUT / EDIT BARANG -->
<div class="card border rounded-4 p-3 mb-4 bg-light bg-opacity-50">
    <form method="POST">
        <?php if ($edit_data): ?>
            <input type="hidden" name="idbarang" value="<?= $edit_data['idbarang'] ?>">
        <?php endif; ?>

        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Nama Produk</label>
                <input type="text" name="nama" class="form-control rounded-3" placeholder="Contoh: Beras Premium 5Kg" value="<?= htmlspecialchars($edit_data['nama'] ?? '') ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Kategori / Jenis Produk</label>
                <select name="jenis" class="form-select rounded-3" required>
                    <option value="A" <?= (isset($edit_data['jenis']) && $edit_data['jenis'] == 'A') ? 'selected' : '' ?>>A - Sembako / Kebutuhan Pokok</option>
                    <option value="B" <?= (isset($edit_data['jenis']) && $edit_data['jenis'] == 'B') ? 'selected' : '' ?>>B - Produk Kemasan / Rumah Tangga</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Satuan</label>
                <select name="idsatuan" class="form-select rounded-3" required>
                    <option value="">-- Pilih Satuan --</option>
                    <?php
                    $q_sat = mysqli_query($conn, "SELECT * FROM satuan WHERE status=1 ORDER BY nama_satuan ASC");
                    while ($s = mysqli_fetch_assoc($q_sat)):
                        $sel = (isset($edit_data['idsatuan']) && $edit_data['idsatuan'] == $s['idsatuan']) ? 'selected' : '';
                    ?>
                    <option value="<?= $s['idsatuan'] ?>" <?= $sel ?>><?= $s['nama_satuan'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Harga Beli Dasar (Rp)</label>
                <input type="number" name="harga" class="form-control rounded-3" min="0" value="<?= $edit_data['harga'] ?? 0 ?>" required>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Status</label>
                <select name="status" class="form-select rounded-3">
                    <option value="1" <?= (!isset($edit_data['status']) || $edit_data['status'] == 1) ? 'selected' : '' ?>>Aktif</option>
                    <option value="0" <?= (isset($edit_data['status']) && $edit_data['status'] == 0) ? 'selected' : '' ?>>Nonaktif</option>
                </select>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <?php if ($edit_data): ?>
                <a href="barang.php" class="btn btn-outline-secondary rounded-3 px-4 fw-semibold">Batal</a>
                <button type="submit" name="btn_update" class="btn btn-primary rounded-3 px-4 fw-semibold">
                    <i class="fa-solid fa-save me-1"></i> Simpan Perubahan
                </button>
            <?php else: ?>
                <button type="submit" name="btn_simpan" class="btn btn-primary rounded-3 px-4 fw-semibold">
                    Simpan Barang
                </button>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- TABEL DAFTAR BARANG -->
<div class="table-responsive">
    <table class="table align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Barang</th>
                <th>Kategori / Jenis</th>
                <th>Satuan</th>
                <th>Harga Dasar</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $q_brg = mysqli_query($conn, "SELECT b.*, s.nama_satuan FROM barang b JOIN satuan s ON b.idsatuan = s.idsatuan ORDER BY b.idbarang DESC");
            while ($row = mysqli_fetch_assoc($q_brg)):
            ?>
            <tr>
                <td class="fw-bold text-muted">#<?= $row['idbarang'] ?></td>
                <td class="fw-semibold"><?= htmlspecialchars($row['nama']) ?></td>
                <td>
                    <?php if ($row['jenis'] == 'A'): ?>
                        <span class="badge bg-secondary-subtle text-dark border">A - Sembako / Pokok</span>
                    <?php else: ?>
                        <span class="badge bg-light text-secondary border">B - Kemasan / Rumah Tangga</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($row['nama_satuan']) ?></td>
                <td class="fw-bold text-pink-pastel">Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                <td>
                    <?php if ($row['status'] == 1): ?>
                        <span class="badge bg-success-subtle text-success rounded-pill px-3">Aktif</span>
                    <?php else: ?>
                        <span class="badge bg-danger-subtle text-danger rounded-pill px-3">Nonaktif</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="barang.php?edit=<?= $row['idbarang'] ?>" class="btn btn-warning btn-action text-white me-1" title="Edit">
                        <i class="fa-solid fa-pen"></i>
                    </a>
                    <a href="barang.php?hapus=<?= $row['idbarang'] ?>" class="btn btn-danger btn-action" title="Hapus" onclick="return confirm('Hapus produk ini?')">
                        <i class="fa-solid fa-trash"></i>
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>