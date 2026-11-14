<?php
include 'koneksi.php';
include 'header.php';

$pesan = "";
$tipe_pesan = "success";

// 1. Proses Dokumen Penerimaan Baru (Memanggil SP sp_buat_penerimaan)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_buat_trm'])) {
    $idpengadaan = (int)$_POST['idpengadaan'];
    $iduser      = $_SESSION['iduser'];

    if ($idpengadaan > 0) {
        mysqli_query($conn, "CALL sp_buat_penerimaan($idpengadaan, $iduser, @new_id_trm)");
        $res = mysqli_query($conn, "SELECT @new_id_trm AS id_baru");
        $row = mysqli_fetch_assoc($res);
        $id_baru = $row['id_baru'];
        header("Location: penerimaan.php?idtrm=" . $id_baru);
        exit;
    } else {
        $pesan = "Silakan pilih surat pesanan (PO) terlebih dahulu!";
        $tipe_pesan = "danger";
    }
}

// 2. Catat Jumlah Barang yang Diterima (Memanggil SP sp_tambah_detail_penerimaan)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_terima_item'])) {
    $idpenerimaan  = (int)$_POST['idpenerimaan'];
    $idbarang      = (int)$_POST['idbarang'];
    $jumlah_terima = (int)$_POST['jumlah_terima'];
    $harga_terima  = (int)$_POST['harga_terima'];

    // Cek sisa pesanan menggunakan function f_sisa_penerimaan
    $trm_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT idpengadaan FROM penerimaan WHERE idpenerimaan = $idpenerimaan"));
    $idpo_ref = $trm_info['idpengadaan'];
    $sisa = mysqli_fetch_assoc(mysqli_query($conn, "SELECT f_sisa_penerimaan($idpo_ref, $idbarang) AS sisa"))['sisa'] ?? 0;

    if ($jumlah_terima <= 0 || $jumlah_terima > $sisa) {
        $pesan = "Jumlah diterima tidak valid! Maksimal sisa pesanan yang belum datang adalah $sisa unit.";
        $tipe_pesan = "danger";
    } else {
        if (mysqli_query($conn, "CALL sp_tambah_detail_penerimaan($idpenerimaan, $idbarang, $jumlah_terima, $harga_terima)")) {
            $pesan = "Barang masuk berhasil dicatat dan stok gudang otomatis bertambah!";
        } else {
            $pesan = "Gagal mencatat penerimaan: " . mysqli_error($conn);
            $tipe_pesan = "danger";
        }
    }
}

$active_trm = isset($_GET['idtrm']) ? (int)$_GET['idtrm'] : 0;
?>

<h4 class="fw-bold mb-1"><i class="fa-solid fa-truck-ramp-box text-pink-pastel me-2"></i>Penerimaan Barang Masuk</h4>
<p class="text-muted small mb-4">Catat kedatangan barang dari vendor untuk memperbarui stok gudang secara otomatis.</p>

<?php if (!empty($pesan)): ?>
    <div class="alert alert-<?= $tipe_pesan ?> alert-dismissible fade show rounded-3 small">
        <i class="fa-solid <?= $tipe_pesan == 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?> me-1"></i> <?= $pesan ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- KOLOM KIRI: FORM CATAT KEDATANGAN BARANG -->
    <div class="col-lg-5">
        <div class="card border rounded-4 p-3 mb-3 bg-light bg-opacity-50">
            <h6 class="fw-bold mb-3"><i class="fa-solid fa-clipboard-check text-pink-pastel me-1"></i> Catat Kedatangan Barang</h6>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Pilih Surat Pesanan (PO)</label>
                    <select name="idpengadaan" class="form-select rounded-3" required>
                        <option value="">-- Pilih Nomor Pesanan --</option>
                        <?php
                        $q_po = mysqli_query($conn, "SELECT p.*, v.nama_vendor FROM pengadaan p JOIN vendor v ON p.vendor_idvendor = v.idvendor WHERE p.status IN ('P', 'B') ORDER BY p.idpengadaan DESC");
                        while ($po = mysqli_fetch_assoc($q_po)):
                            $ket_st = ($po['status'] == 'P') ? 'Belum Dikirim' : 'Diterima Sebagian';
                        ?>
                        <option value="<?= $po['idpengadaan'] ?>">#PO-<?= $po['idpengadaan'] ?> - <?= $po['nama_vendor'] ?> (<?= $ket_st ?>)</option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" name="btn_buat_trm" class="btn btn-primary w-100 fw-semibold rounded-3">
                    <i class="fa-solid fa-box-open me-1"></i> Proses Penerimaan Barang
                </button>
            </form>
        </div>

        <?php if ($active_trm > 0): 
            $trm_aktif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT t.*, p.vendor_idvendor, v.nama_vendor FROM penerimaan t JOIN pengadaan p ON t.idpengadaan = p.idpengadaan JOIN vendor v ON p.vendor_idvendor = v.idvendor WHERE t.idpenerimaan = $active_trm"));
            $idpo_aktif = $trm_aktif['idpengadaan'];
        ?>
        <div class="card border-2 rounded-4 p-3 shadow-sm" style="border-color: #f472b6 !important;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <h6 class="fw-bold text-pink-pastel mb-0">Input Barang Datang (#TRM-<?= $active_trm ?>)</h6>
                    <span class="small text-muted">Ref: <strong>#PO-<?= $idpo_aktif ?></strong> &bull; <?= $trm_aktif['nama_vendor'] ?></span>
                </div>
                <a href="penerimaan.php" class="btn btn-sm btn-success rounded-pill px-3 fw-semibold">
                    <i class="fa-solid fa-check me-1"></i> Selesai
                </a>
            </div>

            <form method="POST" class="mt-2">
                <input type="hidden" name="idpenerimaan" value="<?= $active_trm ?>">
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Barang yang Diterima</label>
                    <select name="idbarang" id="select_barang_trm" class="form-select rounded-3" onchange="updateHargaDefault()" required>
                        <option value="">-- Pilih Barang dari Pesanan --</option>
                        <?php
                        $q_item_po = mysqli_query($conn, "SELECT dp.*, b.nama, f_sisa_penerimaan(dp.idpengadaan, dp.idbarang) AS sisa_qty FROM detail_pengadaan dp JOIN barang b ON dp.idbarang = b.idbarang WHERE dp.idpengadaan = $idpo_aktif HAVING sisa_qty > 0");
                        while ($it = mysqli_fetch_assoc($q_item_po)):
                        ?>
                        <option value="<?= $it['idbarang'] ?>" data-harga="<?= $it['harga_satuan'] ?>" data-sisa="<?= $it['sisa_qty'] ?>">
                            <?= $it['nama'] ?> (Sisa Pesanan: <?= $it['sisa_qty'] ?> unit)
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Jumlah Diterima</label>
                        <input type="number" name="jumlah_terima" id="input_qty_trm" class="form-control rounded-3" min="1" value="1" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Harga Beli Satuan (Rp)</label>
                        <input type="number" name="harga_terima" id="input_harga_trm" class="form-control rounded-3" min="0" required>
                    </div>
                </div>
                <button type="submit" name="btn_terima_item" class="btn btn-primary w-100 fw-semibold rounded-3">
                    <i class="fa-solid fa-check-circle me-1"></i> Simpan ke Stok Gudang
                </button>
            </form>

            <script>
            function updateHargaDefault() {
                var sel = document.getElementById('select_barang_trm');
                var opt = sel.options[sel.selectedIndex];
                if (opt.value !== "") {
                    document.getElementById('input_harga_trm').value = opt.getAttribute('data-harga');
                    document.getElementById('input_qty_trm').max = opt.getAttribute('data-sisa');
                    document.getElementById('input_qty_trm').value = opt.getAttribute('data-sisa');
                }
            }
            </script>
        </div>
        <?php endif; ?>
    </div>

    <!-- KOLOM KANAN: RIWAYAT BARANG MASUK GUDANG -->
    <div class="col-lg-7">
        <h6 class="fw-bold mb-3"><i class="fa-solid fa-warehouse text-pink-pastel me-1"></i> Riwayat Barang Masuk Gudang</h6>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>No Terima</th>
                        <th>Ref PO</th>
                        <th>Produk</th>
                        <th>Diterima</th>
                        <th>Harga Satuan</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $q_list = mysqli_query($conn, "SELECT dt.*, t.idpengadaan, t.created_at, b.nama FROM detail_penerimaan dt JOIN penerimaan t ON dt.idpenerimaan = t.idpenerimaan JOIN barang b ON dt.barang_idbarang = b.idbarang ORDER BY dt.iddetail_penerimaan DESC");
                    if (mysqli_num_rows($q_list) == 0):
                    ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4 small">Belum ada riwayat penerimaan barang masuk.</td>
                        </tr>
                    <?php
                    endif;
                    while ($row = mysqli_fetch_assoc($q_list)):
                    ?>
                    <tr>
                        <td class="fw-bold text-pink-pastel">#TRM-<?= $row['idpenerimaan'] ?><br><span class="small text-muted fw-normal"><?= $row['created_at'] ?></span></td>
                        <td class="fw-semibold">#PO-<?= $row['idpengadaan'] ?></td>
                        <td class="fw-semibold"><?= $row['nama'] ?></td>
                        <td><span class="badge bg-success-subtle text-success">+<?= $row['jumlah_terima'] ?></span></td>
                        <td class="small">Rp <?= number_format($row['harga_satuan_terima'], 0, ',', '.') ?></td>
                        <td class="fw-bold">Rp <?= number_format($row['sub_total_terima'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>