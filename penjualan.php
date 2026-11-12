<?php
include 'koneksi.php';
include 'header.php';

$message = ""; $msg_type = "";
$active_jl = isset($_GET['id']) ? intval($_GET['id']) : 0;

$margin_aktif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM margin_penjualan WHERE status = 1 ORDER BY idmargin_penjualan DESC LIMIT 1"));

// 1. Insert Header Penjualan (Tarik 1 Margin Aktif, Subtotal=0, Total=0, Tanpa Status)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_buat_nota'])) {
    $iduser = $_SESSION['iduser'];
    if (mysqli_query($conn, "CALL sp_buat_penjualan($iduser, @new_jl)")) {
        $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT @new_jl AS id"));
        header("Location: penjualan.php?id={$row['id']}");
        exit;
    } else {
        $message = "Gagal membuat nota: " . mysqli_error($conn);
        $msg_type = "danger";
    }
}

// 2. Tambah Detail Penjualan (Trigger otomatis update total penjualan & potong kartu stok)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_tambah_jual'])) {
    $idpenjualan = intval($_POST['idpenjualan']);
    $idbarang    = intval($_POST['idbarang']);
    $jumlah      = intval($_POST['jumlah']);
    
    $sql = "CALL sp_tambah_detail_penjualan($idpenjualan, $idbarang, $jumlah)";
    if (mysqli_query($conn, $sql)) {
        while (mysqli_more_results($conn) && mysqli_next_result($conn)) {;}
        $message = "Produk ditambahkan ke keranjang! Total nota & Kartu Stok otomatis terupdate.";
        $msg_type = "success";
        $active_jl = $idpenjualan;
    } else {
        $message = "Gagal: " . mysqli_error($conn);
        $msg_type = "danger";
    }
}

$jl_header = null;
if ($active_jl > 0) {
    $jl_header = mysqli_fetch_assoc(mysqli_query($conn, "SELECT p.*, m.persen FROM penjualan p JOIN margin_penjualan m ON p.idmargin_penjualan = m.idmargin_penjualan WHERE p.idpenjualan = $active_jl"));
}
?>

<h4 class="fw-bold mb-1"><i class="fa-solid fa-cart-shopping text-pink-pastel me-2"></i>3. Penjualan Kasir</h4>
<p class="text-muted small mb-4">Buat nota penjualan terlebih dahulu (otomatis menarik margin aktif), lalu input barang belanjaan.</p>

<?php if ($message): ?>
    <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show"><?= $message ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-md-5">
        <div class="bg-light p-4 rounded-4 border mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">A. Buat Nota Penjualan</h6>
                <?php if ($margin_aktif): ?>
                    <span class="badge bg-success-subtle text-success border border-success">Margin Aktif: +<?= $margin_aktif['persen'] ?>%</span>
                <?php else: ?>
                    <span class="badge bg-danger">Tidak Ada Margin Aktif!</span>
                <?php endif; ?>
            </div>
            <form method="POST" action="penjualan.php">
                <p class="small text-muted mb-3">Kasir: <strong><?= htmlspecialchars($_SESSION['username']) ?></strong> | PPN: <strong>11%</strong></p>
                <button type="submit" name="btn_buat_nota" class="btn btn-primary w-100 fw-bold" <?= !$margin_aktif ? 'disabled' : '' ?>>
                    <i class="fa-solid fa-receipt me-1"></i> Mulai Transaksi Kasir Baru
                </button>
            </form>
        </div>

        <?php if ($jl_header): ?>
        <div class="p-4 rounded-4 border border-2" style="border-color: #f472b6 !important; background: #fff;">
            <h6 class="fw-bold text-pink-pastel mb-3">B. Input Barang Belanja (#JL-<?= $jl_header['idpenjualan'] ?>)</h6>
            <form method="POST" action="penjualan.php?id=<?= $jl_header['idpenjualan'] ?>">
                <input type="hidden" name="idpenjualan" value="<?= $jl_header['idpenjualan'] ?>">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Pilih Barang (Harga Sudah +Margin <?= $jl_header['persen'] ?>%)</label>
                    <select name="idbarang" class="form-select" required>
                        <option value="">-- Pilih Produk --</option>
                        <?php
                        $b = mysqli_query($conn, "SELECT * FROM v_katalog_barang WHERE status = 1 AND stok_tersedia > 0");
                        while ($rb = mysqli_fetch_assoc($b)):
                        ?>
                            <option value="<?= $rb['idbarang'] ?>">
                                <?= $rb['nama_barang'] ?> — Rp <?= number_format($rb['harga_jual'], 0, ',', '.') ?> (Stok: <?= $rb['stok_tersedia'] ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Jumlah Beli (Qty)</label>
                    <input type="number" name="jumlah" class="form-control" value="1" min="1" required>
                </div>
                <button type="submit" name="btn_tambah_jual" class="btn btn-primary w-100 fw-bold">
                    <i class="fa-solid fa-plus me-1"></i> Tambah ke Nota
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-md-7">
        <?php if ($jl_header): ?>
        <div class="p-4 rounded-4 border bg-white mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Keranjang Belanja #JL-<?= $jl_header['idpenjualan'] ?></h6>
                <a href="penjualan.php" class="btn btn-sm btn-success rounded-pill px-3">Selesai Transaksi</a>
            </div>
            <table class="table table-sm align-middle mb-3">
                <thead>
                    <tr><th>Barang</th><th>Harga Jual</th><th>Qty</th><th>Subtotal</th></tr>
                </thead>
                <tbody>
                    <?php
                    $det = mysqli_query($conn, "SELECT dp.*, b.nama FROM detail_penjualan dp JOIN barang b ON dp.idbarang = b.idbarang WHERE dp.penjualan_idpenjualan = {$jl_header['idpenjualan']}");
                    while ($rd = mysqli_fetch_assoc($det)):
                    ?>
                    <tr>
                        <td class="fw-semibold"><?= $rd['nama'] ?></td>
                        <td>Rp <?= number_format($rd['harga_satuan'], 0, ',', '.') ?></td>
                        <td><?= $rd['jumlah'] ?></td>
                        <td class="fw-bold">Rp <?= number_format($rd['subtotal'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <div class="bg-light p-3 rounded-3 text-end small">
                <div>Subtotal: <strong>Rp <?= number_format($jl_header['subtotal_nilai'], 0, ',', '.') ?></strong></div>
                <div>PPN (<?= $jl_header['ppn'] ?>%): <strong>Rp <?= number_format($jl_header['total_nilai'] - $jl_header['subtotal_nilai'], 0, ',', '.') ?></strong></div>
                <div class="fs-6 text-pink-pastel mt-1">Total Bayar: <strong>Rp <?= number_format($jl_header['total_nilai'], 0, ',', '.') ?></strong></div>
            </div>
        </div>
        <?php endif; ?>

        <h6 class="fw-bold mb-3">Riwayat Nota Penjualan</h6>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr><th>No Nota</th><th>Waktu</th><th>Margin</th><th>Total (+PPN)</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php
                    $list = mysqli_query($conn, "SELECT p.*, m.persen FROM penjualan p JOIN margin_penjualan m ON p.idmargin_penjualan = m.idmargin_penjualan ORDER BY p.idpenjualan DESC");
                    while ($rl = mysqli_fetch_assoc($list)):
                    ?>
                    <tr>
                        <td class="fw-bold">#JL-<?= $rl['idpenjualan'] ?></td>
                        <td class="small text-muted"><?= $rl['created_at'] ?></td>
                        <td><span class="badge bg-info text-dark">+<?= $rl['persen'] ?>%</span></td>
                        <td class="fw-bold text-pink-pastel">Rp <?= number_format($rl['total_nilai'], 0, ',', '.') ?></td>
                        <td><a href="penjualan.php?id=<?= $rl['idpenjualan'] ?>" class="btn btn-sm btn-outline-primary rounded-pill">Rincian</a></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>