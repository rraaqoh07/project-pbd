<?php
include 'koneksi.php';
include 'header.php';

$pesan = "";
$tipe_pesan = "success";

// 1. Buat Struk Baru (Memanggil SP sp_buat_penjualan)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_buat_nota'])) {
    $iduser = $_SESSION['iduser'];
    mysqli_query($conn, "CALL sp_buat_penjualan($iduser, @new_id_jl)");
    $res = mysqli_query($conn, "SELECT @new_id_jl AS id_baru");
    $row = mysqli_fetch_assoc($res);
    $id_baru = $row['id_baru'];
    header("Location: penjualan.php?idnota=" . $id_baru);
    exit;
}

// 2. Tambah Produk ke Struk (Memanggil SP sp_tambah_detail_penjualan)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_tambah_item'])) {
    $idpenjualan = (int)$_POST['idpenjualan'];
    $idbarang    = (int)$_POST['idbarang'];
    $jumlah      = (int)$_POST['jumlah'];

    // Cek stok tersedia sebelum dijual
    $cek_stok = mysqli_fetch_assoc(mysqli_query($conn, "SELECT stok_tersedia FROM v_katalog_barang WHERE idbarang = $idbarang"))['stok_tersedia'] ?? 0;

    if ($jumlah > $cek_stok) {
        $pesan = "Stok tidak mencukupi! Sisa stok tersedia saat ini hanya $cek_stok unit.";
        $tipe_pesan = "danger";
    } else {
        if (mysqli_query($conn, "CALL sp_tambah_detail_penjualan($idpenjualan, $idbarang, $jumlah)")) {
            $pesan = "Produk berhasil ditambahkan ke struk belanja!";
        } else {
            $pesan = "Gagal menambahkan produk: " . mysqli_error($conn);
            $tipe_pesan = "danger";
        }
    }
}

$active_id = isset($_GET['idnota']) ? (int)$_GET['idnota'] : 0;
?>

<h4 class="fw-bold mb-1"><i class="fa-solid fa-cash-register text-pink-pastel me-2"></i>Transaksi Kasir (POS)</h4>
<p class="text-muted small mb-4">Layani transaksi pembelian pelanggan, hitung total belanja otomatis, dan pantau riwayat penjualan.</p>

<?php if (!empty($pesan)): ?>
    <div class="alert alert-<?= $tipe_pesan ?> alert-dismissible fade show rounded-3 small">
        <i class="fa-solid <?= $tipe_pesan == 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?> me-1"></i> <?= $pesan ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- KOLOM KIRI: STRUK BELANJA PELANGGAN -->
    <div class="col-lg-5">
        <div class="card border rounded-4 p-3 mb-3 bg-light bg-opacity-50">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold mb-0"><i class="fa-solid fa-receipt text-pink-pastel me-1"></i> Struk Belanja Pelanggan</h6>
                <span class="badge bg-success-subtle text-success border border-success-subtle">Harga Jual Aktif (+<?= $margin_aktif ?>%)</span>
            </div>
            <p class="small text-muted mb-3">Kasir Bertugas: <strong><?= htmlspecialchars($_SESSION['username']) ?></strong> &bull; Pajak PPN: <strong>11%</strong></p>
            <form method="POST">
                <button type="submit" name="btn_buat_nota" class="btn btn-primary w-100 fw-semibold rounded-3">
                    <i class="fa-solid fa-plus-circle me-1"></i> Buat Struk Baru
                </button>
            </form>
        </div>

        <?php if ($active_id > 0): 
            $nota_aktif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM penjualan WHERE idpenjualan = $active_id"));
        ?>
        <div class="card border-2 border-pink rounded-4 p-3 shadow-sm" style="border-color: #f472b6 !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-pink-pastel mb-0">Pilih Produk Belanjaan (Struk #JL-<?= $active_id ?>)</h6>
                <a href="penjualan.php" class="btn btn-sm btn-success rounded-pill px-3 fw-semibold">
                    <i class="fa-solid fa-check me-1"></i> Selesai Bayar
                </a>
            </div>

            <form method="POST">
                <input type="hidden" name="idpenjualan" value="<?= $active_id ?>">
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Nama Produk</label>
                    <select name="idbarang" class="form-select rounded-3" required>
                        <option value="">-- Pilih Produk yang Dibeli --</option>
                        <?php
                        $q_brg = mysqli_query($conn, "SELECT * FROM v_katalog_barang WHERE status = 1 AND stok_tersedia > 0 ORDER BY nama_barang ASC");
                        while ($b = mysqli_fetch_assoc($q_brg)):
                        ?>
                        <option value="<?= $b['idbarang'] ?>">
                            <?= $b['nama_barang'] ?> - Rp <?= number_format($b['harga_jual'], 0, ',', '.') ?> (Stok: <?= $b['stok_tersedia'] ?> <?= $b['nama_satuan'] ?>)
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Jumlah Beli</label>
                    <input type="number" name="jumlah" class="form-control rounded-3" min="1" value="1" required>
                </div>
                <button type="submit" name="btn_tambah_item" class="btn btn-primary w-100 fw-semibold rounded-3">
                    <i class="fa-solid fa-cart-plus me-1"></i> Tambahkan ke Struk
                </button>
            </form>

            <!-- Daftar Item di Struk Aktif -->
            <hr class="my-3">
            <h6 class="small fw-bold text-muted mb-2">Rincian Belanja Struk #JL-<?= $active_id ?>:</h6>
            <div class="table-responsive">
                <table class="table table-sm small mb-2">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Qty</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $q_det = mysqli_query($conn, "SELECT dp.*, b.nama FROM detail_penjualan dp JOIN barang b ON dp.idbarang = b.idbarang WHERE dp.penjualan_idpenjualan = $active_id");
                        while ($d = mysqli_fetch_assoc($q_det)):
                        ?>
                        <tr>
                            <td><?= $d['nama'] ?> <br><span class="text-muted">@Rp <?= number_format($d['harga_satuan'], 0, ',', '.') ?></span></td>
                            <td><?= $d['jumlah'] ?></td>
                            <td class="text-end fw-semibold">Rp <?= number_format($d['subtotal'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="bg-light p-2 rounded-3 small">
                <div class="d-flex justify-content-between">
                    <span>Subtotal Belanja:</span>
                    <strong>Rp <?= number_format($nota_aktif['subtotal_nilai'] ?? 0, 0, ',', '.') ?></strong>
                </div>
                <div class="d-flex justify-content-between text-pink-pastel fs-6 mt-1">
                    <span class="fw-bold">Total Bayar (+PPN 11%):</span>
                    <strong class="fw-bold">Rp <?= number_format($nota_aktif['total_nilai'] ?? 0, 0, ',', '.') ?></strong>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- KOLOM KANAN: DAFTAR TRANSAKSI PELANGGAN -->
    <div class="col-lg-7">
        <h6 class="fw-bold mb-3"><i class="fa-solid fa-list-check text-pink-pastel me-1"></i> Daftar Transaksi Pelanggan</h6>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>No Struk</th>
                        <th>Waktu Transaksi</th>
                        <th>Kasir</th>
                        <th>Total Bayar (+PPN)</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $q_list = mysqli_query($conn, "SELECT p.*, u.username FROM penjualan p JOIN user u ON p.iduser = u.iduser ORDER BY p.idpenjualan DESC");
                    if (mysqli_num_rows($q_list) == 0):
                    ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4 small">Belum ada transaksi penjualan hari ini.</td>
                        </tr>
                    <?php
                    endif;
                    while ($row = mysqli_fetch_assoc($q_list)):
                    ?>
                    <tr>
                        <td class="fw-bold text-pink-pastel">#JL-<?= $row['idpenjualan'] ?></td>
                        <td class="small text-muted"><?= $row['created_at'] ?></td>
                        <td class="small"><?= htmlspecialchars($row['username']) ?></td>
                        <td class="fw-bold">Rp <?= number_format($row['total_nilai'], 0, ',', '.') ?></td>
                        <td>
                            <a href="penjualan.php?idnota=<?= $row['idpenjualan'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
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