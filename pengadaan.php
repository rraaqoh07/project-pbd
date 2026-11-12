<?php
include 'koneksi.php';
include 'header.php';

$message = ""; $msg_type = "";
$active_po = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 1. Proses Buat Header Pengadaan Baru (Subtotal=0, Total=0, PPN=11)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_buat_po'])) {
    $iduser   = $_SESSION['iduser'];
    $idvendor = intval($_POST['idvendor']);
    
    mysqli_query($conn, "CALL sp_buat_pengadaan($iduser, $idvendor, @new_id)");
    $res = mysqli_query($conn, "SELECT @new_id AS id");
    $row = mysqli_fetch_assoc($res);
    $new_id = $row['id'];
    
    header("Location: pengadaan.php?id=$new_id");
    exit;
}

// 2. Proses Tambah Banyak Item ke Detail Pengadaan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_tambah_item'])) {
    $idpengadaan = intval($_POST['idpengadaan']);
    $idbarang    = intval($_POST['idbarang']);
    $jumlah      = intval($_POST['jumlah']);
    
    $sql = "CALL sp_tambah_detail_pengadaan($idpengadaan, $idbarang, $jumlah)";
    if (mysqli_query($conn, $sql)) {
        while (mysqli_more_results($conn) && mysqli_next_result($conn)) {;}
        $message = "Item barang ditambahkan! Subtotal & Total Pengadaan otomatis diperbarui.";
        $msg_type = "success";
        $active_po = $idpengadaan;
    } else {
        $message = "Gagal menambah item: " . mysqli_error($conn);
        $msg_type = "danger";
    }
}

$po_header = null;
if ($active_po > 0) {
    $po_header = mysqli_fetch_assoc(mysqli_query($conn, "SELECT p.*, v.nama_vendor FROM pengadaan p JOIN vendor v ON p.vendor_idvendor = v.idvendor WHERE p.idpengadaan = $active_po"));
}
?>

<h4 class="fw-bold mb-1"><i class="fa-solid fa-file-invoice text-pink-pastel me-2"></i>1. Pengadaan Barang (Master - Detail)</h4>
<p class="text-muted small mb-4">Buat nomor pengadaan baru terlebih dahulu, lalu tambahkan beberapa item barang sekaligus.</p>

<?php if ($message): ?>
    <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show"><?= $message ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="row g-4">
    <!-- KOLOM KIRI: BUAT HEADER ATAU ISI DETAIL -->
    <div class="col-md-5">
        <!-- Form Buat Header Pengadaan -->
        <div class="bg-light p-4 rounded-4 border mb-4">
            <h6 class="fw-bold mb-3">A. Buat Pengadaan Baru</h6>
            <form method="POST" action="pengadaan.php">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Pilih Mitra Vendor</label>
                    <select name="idvendor" class="form-select" required>
                        <option value="">-- Pilih Vendor --</option>
                        <?php
                        $v = mysqli_query($conn, "SELECT * FROM vendor WHERE status='A'");
                        while ($rv = mysqli_fetch_assoc($v)) echo "<option value='{$rv['idvendor']}'>{$rv['nama_vendor']}</option>";
                        ?>
                    </select>
                </div>
                <div class="row g-2 mb-3 small text-muted">
                    <div class="col-4">PPN: <strong>11%</strong></div>
                    <div class="col-4">Subtotal Awal: <strong>Rp 0</strong></div>
                    <div class="col-4">Total Awal: <strong>Rp 0</strong></div>
                </div>
                <button type="submit" name="btn_buat_po" class="btn btn-primary w-100 fw-bold">
                    <i class="fa-solid fa-plus me-1"></i> Buat Nomor Pengadaan
                </button>
            </form>
        </div>

        <!-- Form Tambah Item Detail (Muncul langsung setelah Header dibuat/dipilih) -->
        <?php if ($po_header && $po_header['status'] != 'S'): ?>
        <div class="p-4 rounded-4 border border-2" style="border-color: #f472b6 !important; background: #fff;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-pink-pastel mb-0">B. Isi Detail Pengadaan #PO-<?= $po_header['idpengadaan'] ?></h6>
                <span class="badge bg-light text-dark border"><?= $po_header['nama_vendor'] ?></span>
            </div>
            <form method="POST" action="pengadaan.php?id=<?= $po_header['idpengadaan'] ?>">
                <input type="hidden" name="idpengadaan" value="<?= $po_header['idpengadaan'] ?>">
                
                <div class="mb-3">
                    <label class="form-label small fw-bold">Pilih Barang (Harga Otomatis dari Master)</label>
                    <select name="idbarang" id="po_barang" class="form-select" required onchange="hitungPreviewPO()">
                        <option value="" data-harga="0">-- Pilih Barang --</option>
                        <?php
                        $b = mysqli_query($conn, "SELECT * FROM barang WHERE status=1");
                        while ($rb = mysqli_fetch_assoc($b)):
                        ?>
                            <option value="<?= $rb['idbarang'] ?>" data-harga="<?= $rb['harga'] ?>">
                                <?= $rb['nama'] ?> (Harga: Rp <?= number_format($rb['harga'], 0, ',', '.') ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Jumlah Pesan</label>
                        <input type="number" name="jumlah" id="po_jumlah" class="form-control" value="1" min="1" required oninput="hitungPreviewPO()">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Estimasi Subtotal</label>
                        <input type="text" id="po_subtotal_preview" class="form-control bg-light fw-bold" value="Rp 0" readonly>
                    </div>
                </div>

                <button type="submit" name="btn_tambah_item" class="btn btn-primary w-100 fw-bold">
                    <i class="fa-solid fa-cart-plus me-1"></i> Tambah Item ke Pengadaan
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <!-- KOLOM KANAN: DAFTAR PENGADAAN & DETAIL ITEM -->
    <div class="col-md-7">
        <?php if ($po_header): ?>
        <div class="p-4 rounded-4 border bg-white mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Rincian Item #PO-<?= $po_header['idpengadaan'] ?> (<?= $po_header['nama_vendor'] ?>)</h6>
                <a href="pengadaan.php" class="btn btn-sm btn-outline-secondary">Selesai / Tutup Rincian</a>
            </div>
            <table class="table table-sm align-middle mb-3">
                <thead>
                    <tr><th>Barang</th><th>Harga Satuan</th><th>Qty</th><th>Subtotal</th></tr>
                </thead>
                <tbody>
                    <?php
                    $det = mysqli_query($conn, "SELECT dp.*, b.nama FROM detail_pengadaan dp JOIN barang b ON dp.idbarang = b.idbarang WHERE dp.idpengadaan = {$po_header['idpengadaan']}");
                    while ($rd = mysqli_fetch_assoc($det)):
                    ?>
                    <tr>
                        <td class="fw-semibold"><?= $rd['nama'] ?></td>
                        <td>Rp <?= number_format($rd['harga_satuan'], 0, ',', '.') ?></td>
                        <td><?= $rd['jumlah'] ?></td>
                        <td class="fw-bold">Rp <?= number_format($rd['sub_total'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <div class="bg-light p-3 rounded-3 text-end small">
                <div>Subtotal Pengadaan: <strong>Rp <?= number_format($po_header['subtotal_nilai'], 0, ',', '.') ?></strong></div>
                <div>PPN (<?= $po_header['ppn'] ?>%): <strong>Rp <?= number_format($po_header['total_nilai'] - $po_header['subtotal_nilai'], 0, ',', '.') ?></strong></div>
                <div class="fs-6 text-pink-pastel mt-1">Total Nilai Pengadaan: <strong>Rp <?= number_format($po_header['total_nilai'], 0, ',', '.') ?></strong></div>
            </div>
        </div>
        <?php endif; ?>

        <h6 class="fw-bold mb-3">Daftar Semua Pengadaan</h6>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr><th>No PO</th><th>Vendor</th><th>Total (+PPN)</th><th>Status</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php
                    $list = mysqli_query($conn, "SELECT p.*, v.nama_vendor FROM pengadaan p JOIN vendor v ON p.vendor_idvendor = v.idvendor ORDER BY p.idpengadaan DESC");
                    while ($rl = mysqli_fetch_assoc($list)):
                    ?>
                    <tr>
                        <td class="fw-bold">#PO-<?= $rl['idpengadaan'] ?></td>
                        <td><?= $rl['nama_vendor'] ?></td>
                        <td class="fw-bold text-pink-pastel">Rp <?= number_format($rl['total_nilai'], 0, ',', '.') ?></td>
                        <td>
                            <?php if ($rl['status'] == 'P'): ?>
                                <span class="badge bg-warning text-dark">Proses (Belum Diterima)</span>
                            <?php elseif ($rl['status'] == 'B'): ?>
                                <span class="badge bg-info text-dark">Diterima Sebagian</span>
                            <?php else: ?>
                                <span class="badge bg-success">Selesai / Tuntas</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="pengadaan.php?id=<?= $rl['idpengadaan'] ?>" class="btn btn-sm btn-outline-primary rounded-pill">Detail Item</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function hitungPreviewPO() {
    const sel = document.getElementById('po_barang');
    const harga = parseInt(sel.options[sel.selectedIndex].getAttribute('data-harga')) || 0;
    const qty = parseInt(document.getElementById('po_jumlah').value) || 0;
    document.getElementById('po_subtotal_preview').value = 'Rp ' + (harga * qty).toLocaleString('id-ID');
}
</script>

<?php include 'footer.php'; ?>