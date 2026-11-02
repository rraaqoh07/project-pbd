<?php
include 'koneksi.php';
include 'header.php';

$message  = "";
$msg_type = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_transaksi'])) {
    $iduser       = intval($_POST['iduser']);
    $idbarang     = intval($_POST['idbarang']);
    $jumlah       = intval($_POST['jumlah']);
    $harga_satuan = intval($_POST['harga_satuan']);
    
    // MEMANGGIL STORED PROCEDURE TRANSAKSI PENJUALAN
    $sql = "CALL sp_transaksi_penjualan($iduser, $idbarang, $jumlah, $harga_satuan)";
    
    if (mysqli_query($conn, $sql)) {
        while (mysqli_more_results($conn) && mysqli_next_result($conn)) {;}
        // TRIGGER trg_update_kartu_stok_penjualan OTOMATIS BERJALAN DI BACKGROUND DATABASE
        $message  = "Transaksi Penjualan Berhasil! Trigger otomatis memotong stok barang.";
        $msg_type = "success";
    } else {
        $message  = "Gagal Transaksi: " . mysqli_error($conn);
        $msg_type = "danger";
    }
}
?>

<h4 class="fw-bold mb-1"><i class="fa-solid fa-cart-shopping text-pink-pastel me-2"></i>Input Transaksi Penjualan</h4>
<p class="text-muted small mb-4">Pengujian Stored Procedure Penjualan & Trigger Otomatisasi Kartu Stok.</p>

<?php if ($message): ?>
    <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show"><?= $message ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-md-5">
        <div class="bg-light p-4 rounded-4 border">
            <h6 class="fw-bold mb-3">Kasir / Form Penjualan</h6>
            <form method="POST" action="transaksi.php">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Kasir (User)</label>
                    <select name="iduser" class="form-select" required>
                        <option value="">-- Pilih User --</option>
                        <?php
                        $u = mysqli_query($conn, "SELECT * FROM user");
                        while ($row_u = mysqli_fetch_assoc($u)) {
                            echo "<option value='{$row_u['iduser']}'>{$row_u['username']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Barang Yang Dibeli</label>
                    <select name="idbarang" class="form-select" required>
                        <option value="">-- Pilih Barang --</option>
                        <?php
                        $b = mysqli_query($conn, "SELECT * FROM barang WHERE status=1");
                        while ($row_b = mysqli_fetch_assoc($b)) {
                            echo "<option value='{$row_b['idbarang']}'>{$row_b['nama']} (Rp {$row_b['harga']})</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Jumlah (Qty)</label>
                    <input type="number" name="jumlah" class="form-control" value="1" min="1" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Harga Satuan (Rp)</label>
                    <input type="number" name="harga_satuan" class="form-control" placeholder="15000" required>
                </div>
                <button type="submit" name="btn_transaksi" class="btn btn-primary w-100 fw-bold py-2">
                    <i class="fa-solid fa-paper-plane me-1"></i> Proses Penjualan (SP + Trigger)
                </button>
            </form>
        </div>
    </div>

    <div class="col-md-7">
        <div class="border p-4 rounded-4 bg-white">
            <h6 class="fw-bold mb-3"><i class="fa-solid fa-clock-rotate-left me-2 text-pink-pastel"></i>Riwayat Kartu Stok (Hasil Otomatis Trigger)</h6>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Tgl / Waktu</th>
                            <th>Barang</th>
                            <th>Jenis Mutasi</th>
                            <th>Keluar</th>
                            <th>Sisa Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $ks = mysqli_query($conn, "SELECT k.*, b.nama FROM kartu_stok k JOIN barang b ON k.idbarang = b.idbarang ORDER BY k.idkartu_stok DESC LIMIT 10");
                        while ($row_k = mysqli_fetch_assoc($ks)):
                        ?>
                        <tr>
                            <td class="small text-muted"><?= $row_k['created_at'] ?></td>
                            <td class="fw-bold"><?= $row_k['nama'] ?></td>
                            <td><span class="badge bg-danger-subtle text-danger"><?= $row_k['jenis_transaksi'] ?></span></td>
                            <td class="fw-bold text-danger">-<?= $row_k['keluar'] ?></td>
                            <td class="fw-bold text-success"><?= $row_k['stok_akhir'] ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>