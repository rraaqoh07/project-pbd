<?php
include 'koneksi.php';
include 'header.php';

$pesan = "";
$tipe_pesan = "success";

// 1. Buat Surat Pesanan / PO Baru (Memanggil SP sp_buat_pengadaan)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_buat_po'])) {
    $iduser   = $_SESSION['iduser'];
    $idvendor = (int)$_POST['idvendor'];

    if ($idvendor > 0) {
        mysqli_query($conn, "CALL sp_buat_pengadaan($iduser, $idvendor, @new_id_po)");
        $res = mysqli_query($conn, "SELECT @new_id_po AS id_baru");
        $row = mysqli_fetch_assoc($res);
        $id_baru = $row['id_baru'];
        header("Location: pengadaan.php?idpo=" . $id_baru);
        exit;
    } else {
        $pesan = "Silakan pilih pemasok (vendor) terlebih dahulu!";
        $tipe_pesan = "danger";
    }
}

// 2. Tambah Barang ke Daftar Pesanan PO (Memanggil SP sp_tambah_detail_pengadaan)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_tambah_item'])) {
    $idpengadaan = (int)$_POST['idpengadaan'];
    $idbarang    = (int)$_POST['idbarang'];
    $jumlah      = (int)$_POST['jumlah'];

    if ($jumlah > 0) {
        if (mysqli_query($conn, "CALL sp_tambah_detail_pengadaan($idpengadaan, $idbarang, $jumlah)")) {
            $pesan = "Barang berhasil ditambahkan ke daftar pesanan!";
        } else {
            $pesan = "Gagal menambahkan barang: " . mysqli_error($conn);
            $tipe_pesan = "danger";
        }
    }
}

$active_po = isset($_GET['idpo']) ? (int)$_GET['idpo'] : 0;
?>

<h4 class="fw-bold mb-1"><i class="fa-solid fa-file-invoice text-pink-pastel me-2"></i>Pengadaan & Pemesanan Stok</h4>
<p class="text-muted small mb-4">Buat surat pesanan (PO) ke pemasok untuk menambah persediaan stok barang toko.</p>

<?php if (!empty($pesan)): ?>
    <div class="alert alert-<?= $tipe_pesan ?> alert-dismissible fade show rounded-3 small">
        <i class="fa-solid <?= $tipe_pesan == 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?> me-1"></i> <?= $pesan ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- KOLOM KIRI: FORM PESANAN BARU & INPUT BARANG -->
    <div class="col-lg-5">
        <div class="card border rounded-4 p-3 mb-3 bg-light bg-opacity-50">
            <h6 class="fw-bold mb-3"><i class="fa-solid fa-truck-field text-pink-pastel me-1"></i> Pesanan Baru ke Vendor</h6>
            <form method="POST">
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Pilih Pemasok (Vendor)</label>
                    <select name="idvendor" class="form-select rounded-3" required>
                        <option value="">-- Pilih Vendor --</option>
                        <?php
                        $q_v = mysqli_query($conn, "SELECT * FROM vendor WHERE status = 'A' ORDER BY nama_vendor ASC");
                        while ($v = mysqli_fetch_assoc($q_v)):
                        ?>
                        <option value="<?= $v['idvendor'] ?>"><?= $v['nama_vendor'] ?> (<?= $v['badan_hukum'] == 'Y' ? 'PT/CV' : 'Perorangan' ?>)</option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <p class="small text-muted mb-3">Ketentuan Pajak Pembelian: <strong>PPN 11%</strong></p>
                <button type="submit" name="btn_buat_po" class="btn btn-primary w-100 fw-semibold rounded-3">
                    <i class="fa-solid fa-plus me-1"></i> Buat Surat Pesanan (PO)
                </button>
            </form>
        </div>

        <?php if ($active_po > 0): 
            $po_aktif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT p.*, v.nama_vendor FROM pengadaan p JOIN vendor v ON p.vendor_idvendor = v.idvendor WHERE p.idpengadaan = $active_po"));
        ?>
        <div class="card border-2 rounded-4 p-3 shadow-sm" style="border-color: #f472b6 !important;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <h6 class="fw-bold text-pink-pastel mb-0">Daftar Barang yang Dipesan (#PO-<?= $active_po ?>)</h6>
                    <span class="small text-muted">Vendor: <strong><?= $po_aktif['nama_vendor'] ?></strong></span>
                </div>
                <a href="pengadaan.php" class="btn btn-sm btn-success rounded-pill px-3 fw-semibold">
                    <i class="fa-solid fa-check me-1"></i> Selesai
                </a>
            </div>

            <?php if ($po_aktif['status'] == 'P'): ?>
            <form method="POST" class="mt-2">
                <input type="hidden" name="idpengadaan" value="<?= $active_po ?>">
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Pilih Produk</label>
                    <select name="idbarang" class="form-select rounded-3" required>
                        <option value="">-- Pilih Barang --</option>
                        <?php
                        $q_b = mysqli_query($conn, "SELECT b.*, s.nama_satuan FROM barang b JOIN satuan s ON b.idsatuan = s.idsatuan WHERE b.status = 1 ORDER BY b.nama ASC");
                        while ($b = mysqli_fetch_assoc($q_b)):
                        ?>
                        <option value="<?= $b['idbarang'] ?>">
                            <?= $b['nama'] ?> - Rp <?= number_format($b['harga'], 0, ',', '.') ?> / <?= $b['nama_satuan'] ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Jumlah Pesan</label>
                    <input type="number" name="jumlah" class="form-control rounded-3" min="1" value="1" required>
                </div>
                <button type="submit" name="btn_tambah_item" class="btn btn-primary w-100 fw-semibold rounded-3">
                    <i class="fa-solid fa-plus-circle me-1"></i> Tambahkan ke Pesanan
                </button>
            </form>
            <?php else: ?>
                <div class="alert alert-info py-2 small rounded-3 mt-2 mb-0">
                    Pesanan ini sudah diproses penerimaan barang, sehingga daftar item tidak dapat diubah lagi.
                </div>
            <?php endif; ?>

            <hr class="my-3">
            <h6 class="small fw-bold text-muted mb-2">Rincian Item Pesanan:</h6>
            <div class="table-responsive">
                <table class="table table-sm small mb-2">
                    <thead>
                        <tr>
                            <th>Barang</th>
                            <th>Qty</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $q_det = mysqli_query($conn, "SELECT dp.*, b.nama FROM detail_pengadaan dp JOIN barang b ON dp.idbarang = b.idbarang WHERE dp.idpengadaan = $active_po");
                        while ($d = mysqli_fetch_assoc($q_det)):
                        ?>
                        <tr>
                            <td><?= $d['nama'] ?><br><span class="text-muted">@Rp <?= number_format($d['harga_satuan'], 0, ',', '.') ?></span></td>
                            <td><?= $d['jumlah'] ?></td>
                            <td class="text-end fw-semibold">Rp <?= number_format($d['sub_total'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="bg-light p-2 rounded-3 small">
                <div class="d-flex justify-content-between">
                    <span>Subtotal Pesanan:</span>
                    <strong>Rp <?= number_format($po_aktif['subtotal_nilai'] ?? 0, 0, ',', '.') ?></strong>
                </div>
                <div class="d-flex justify-content-between text-pink-pastel fs-6 mt-1">
                    <span class="fw-bold">Total Tagihan (+PPN 11%):</span>
                    <strong class="fw-bold">Rp <?= number_format($po_aktif['total_nilai'] ?? 0, 0, ',', '.') ?></strong>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- KOLOM KANAN: RIWAYAT PESANAN KE VENDOR -->
    <div class="col-lg-7">
        <h6 class="fw-bold mb-3"><i class="fa-solid fa-clock-rotate-left text-pink-pastel me-1"></i> Riwayat Pesanan ke Vendor</h6>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>No PO</th>
                        <th>Vendor</th>
                        <th>Total (+PPN)</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $q_list = mysqli_query($conn, "SELECT p.*, v.nama_vendor FROM pengadaan p JOIN vendor v ON p.vendor_idvendor = v.idvendor ORDER BY p.idpengadaan DESC");
                    if (mysqli_num_rows($q_list) == 0):
                    ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4 small">Belum ada surat pesanan ke vendor.</td>
                        </tr>
                    <?php
                    endif;
                    while ($row = mysqli_fetch_assoc($q_list)):
                        if ($row['status'] == 'P') {
                            $badge = '<span class="badge bg-warning-subtle text-warning border border-warning-subtle">Menunggu Kirim</span>';
                        } elseif ($row['status'] == 'B') {
                            $badge = '<span class="badge bg-info-subtle text-info border border-info-subtle">Diterima Sebagian</span>';
                        } else {
                            $badge = '<span class="badge bg-success-subtle text-success border border-success-subtle">Selesai</span>';
                        }
                    ?>
                    <tr>
                        <td class="fw-bold text-pink-pastel">#PO-<?= $row['idpengadaan'] ?></td>
                        <td class="fw-semibold"><?= $row['nama_vendor'] ?><br><span class="small text-muted fw-normal"><?= $row['timestamp'] ?></span></td>
                        <td class="fw-bold">Rp <?= number_format($row['total_nilai'], 0, ',', '.') ?></td>
                        <td><?= $badge ?></td>
                        <td>
                            <a href="pengadaan.php?idpo=<?= $row['idpengadaan'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                <i class="fa-solid fa-eye me-1"></i> Detail
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>