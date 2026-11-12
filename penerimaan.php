<?php
include 'koneksi.php';
include 'header.php';

$message = ""; $msg_type = "";
$selected_po = isset($_GET['idpo']) ? intval($_GET['idpo']) : 0;
$active_trm  = isset($_GET['idtrm']) ? intval($_GET['idtrm']) : 0;

// 1. Buat Header Penerimaan untuk PO tertentu (Call SP)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_buat_penerimaan'])) {
    $idpengadaan = intval($_POST['idpengadaan']);
    $iduser      = $_SESSION['iduser'];
    
    mysqli_query($conn, "CALL sp_buat_penerimaan($idpengadaan, $iduser, @new_trm)");
    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT @new_trm AS id"));
    header("Location: penerimaan.php?idpo=$idpengadaan&idtrm={$row['id']}");
    exit;
}

// 2. Simpan Detail Penerimaan Barang (Call SP + Trigger Kartu Stok & Status PO)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_simpan_terima'])) {
    $idpenerimaan = intval($_POST['idpenerimaan']);
    $idpengadaan  = intval($_POST['idpengadaan']);
    $idbarang     = intval($_POST['idbarang']);
    $jumlah       = intval($_POST['jumlah_terima']);
    $harga        = intval($_POST['harga_satuan_terima']);
    
    $sql = "CALL sp_tambah_detail_penerimaan($idpenerimaan, $idbarang, $jumlah, $harga)";
    if (mysqli_query($conn, $sql)) {
        while (mysqli_more_results($conn) && mysqli_next_result($conn)) {;}
        $message = "Penerimaan barang tercatat! Stok & Status Pengadaan otomatis diperbarui.";
        $msg_type = "success";
        $selected_po = $idpengadaan;
        $active_trm  = $idpenerimaan;
    } else {
        $message = "Peringatan: " . mysqli_error($conn);
        $msg_type = "danger";
    }
}
?>

<h4 class="fw-bold mb-1"><i class="fa-solid fa-truck-ramp-box text-pink-pastel me-2"></i>2. Penerimaan Barang (Utuh / Sebagian)</h4>
<p class="text-muted small mb-4">Hanya pengadaan yang belum tuntas yang dapat diproses. Jumlah terima otomatis dibatasi sesuai sisa pesanan.</p>

<?php if ($message): ?>
    <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show"><?= $message ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-md-5">
        <!-- Pilih Pengadaan yang Belum Tuntas (Status P atau B) -->
        <div class="bg-light p-4 rounded-4 border mb-4">
            <h6 class="fw-bold mb-3">A. Mulai Dokumen Penerimaan</h6>
            <form method="POST" action="penerimaan.php">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Pilih Pengadaan (Belum Tuntas)</label>
                    <select name="idpengadaan" class="form-select" required>
                        <option value="">-- Pilih Pengadaan --</option>
                        <?php
                        $q_po = mysqli_query($conn, "SELECT p.idpengadaan, v.nama_vendor, p.status FROM pengadaan p JOIN vendor v ON p.vendor_idvendor = v.idvendor WHERE p.status IN ('P', 'B') AND p.total_nilai > 0 ORDER BY p.idpengadaan DESC");
                        while ($po = mysqli_fetch_assoc($q_po)):
                            $ket = ($po['status'] == 'B') ? 'Diterima Sebagian' : 'Belum Diterima';
                        ?>
                            <option value="<?= $po['idpengadaan'] ?>" <?= ($selected_po == $po['idpengadaan']) ? 'selected' : '' ?>>
                                #PO-<?= $po['idpengadaan'] ?> | <?= $po['nama_vendor'] ?> (<?= $ket ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" name="btn_buat_penerimaan" class="btn btn-primary w-100 fw-bold">
                    Buat Dokumen Penerimaan
                </button>
            </form>
        </div>

        <!-- Form Input Barang Diterima -->
        <?php if ($active_trm > 0 && $selected_po > 0): ?>
        <div class="p-4 rounded-4 border border-2" style="border-color: #f472b6 !important; background: #fff;">
            <h6 class="fw-bold text-pink-pastel mb-3">B. Input Barang Diterima (#TRM-<?= $active_trm ?> | Ref #PO-<?= $selected_po ?>)</h6>
            <form method="POST" action="penerimaan.php?idpo=<?= $selected_po ?>&idtrm=<?= $active_trm ?>">
                <input type="hidden" name="idpenerimaan" value="<?= $active_trm ?>">
                <input type="hidden" name="idpengadaan" value="<?= $selected_po ?>">

                <div class="mb-3">
                    <label class="form-label small fw-bold">Barang dalam Pengadaan (Menampilkan Sisa)</label>
                    <select name="idbarang" id="trm_barang" class="form-select" required onchange="isiSisaTerima()">
                        <option value="">-- Pilih Barang --</option>
                        <?php
                        $q_det = mysqli_query($conn, "SELECT dp.idbarang, b.nama, dp.harga_satuan, dp.jumlah AS pesan, f_sisa_penerimaan($selected_po, dp.idbarang) AS sisa 
                                                      FROM detail_pengadaan dp 
                                                      JOIN barang b ON dp.idbarang = b.idbarang 
                                                      WHERE dp.idpengadaan = $selected_po HAVING sisa > 0");
                        while ($rd = mysqli_fetch_assoc($q_det)):
                        ?>
                            <option value="<?= $rd['idbarang'] ?>" data-sisa="<?= $rd['sisa'] ?>" data-harga="<?= $rd['harga_satuan'] ?>">
                                <?= $rd['nama'] ?> (Pesan: <?= $rd['pesan'] ?> | Sisa Belum Terima: <?= $rd['sisa'] ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Jumlah Diterima Sekarang</label>
                        <input type="number" name="jumlah_terima" id="trm_jumlah" class="form-control" min="1" required>
                        <small class="text-muted" id="label_maks">Maksimal sesuai sisa</small>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold text-pink-pastel">Harga Terima (Bisa Diedit)</label>
                        <input type="number" name="harga_satuan_terima" id="trm_harga" class="form-control border-danger" required>
                    </div>
                </div>

                <button type="submit" name="btn_simpan_terima" class="btn btn-primary w-100 fw-bold">
                    Simpan Penerimaan Barang
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-md-7">
        <h6 class="fw-bold mb-3">Riwayat Penerimaan Barang (Status Otomatis Selesai)</h6>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr><th>ID Terima</th><th>Ref PO</th><th>Produk</th><th>Diterima</th><th>Harga Final</th><th>Subtotal</th></tr>
                </thead>
                <tbody>
                    <?php
                    $q = "SELECT pn.idpenerimaan, pn.idpengadaan, b.nama, dp.jumlah_terima, dp.harga_satuan_terima, dp.sub_total_terima 
                          FROM penerimaan pn 
                          JOIN detail_penerimaan dp ON pn.idpenerimaan = dp.idpenerimaan
                          JOIN barang b ON dp.barang_idbarang = b.idbarang
                          ORDER BY pn.idpenerimaan DESC";
                    $res = mysqli_query($conn, $q);
                    while ($row = mysqli_fetch_assoc($res)):
                    ?>
                    <tr>
                        <td class="fw-bold">#TRM-<?= $row['idpenerimaan'] ?> <span class="badge bg-success ms-1">Selesai</span></td>
                        <td>#PO-<?= $row['idpengadaan'] ?></td>
                        <td class="fw-semibold"><?= $row['nama'] ?></td>
                        <td class="text-success fw-bold">+<?= $row['jumlah_terima'] ?></td>
                        <td>Rp <?= number_format($row['harga_satuan_terima'], 0, ',', '.') ?></td>
                        <td class="fw-bold text-pink-pastel">Rp <?= number_format($row['sub_total_terima'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function isiSisaTerima() {
    const sel = document.getElementById('trm_barang');
    const opt = sel.options[sel.selectedIndex];
    if (opt && opt.value !== "") {
        const sisa = opt.getAttribute('data-sisa');
        const harga = opt.getAttribute('data-harga');
        const inpJml = document.getElementById('trm_jumlah');
        inpJml.value = sisa;
        inpJml.max = sisa;
        document.getElementById('label_maks').innerText = 'Maksimal terima: ' + sisa;
        document.getElementById('trm_harga').value = harga;
    }
}
</script>

<?php include 'footer.php'; ?>