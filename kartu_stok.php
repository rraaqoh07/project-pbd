<?php
include 'koneksi.php';
include 'header.php';
?>

<h4 class="fw-bold mb-1"><i class="fa-solid fa-clipboard-list text-pink-pastel me-2"></i>Laporan Pergerakan Stok</h4>
<p class="text-muted small mb-4">Catatan keluar-masuk persediaan barang toko dari aktivitas barang masuk dan penjualan kasir.</p>

<div class="table-responsive">
    <table class="table align-middle">
        <thead>
            <tr>
                <th>No Catatan</th>
                <th>Tanggal & Waktu</th>
                <th>Nama Produk</th>
                <th>Aktivitas Transaksi</th>
                <th>Barang Masuk</th>
                <th>Barang Keluar</th>
                <th>Sisa Stok</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $ks = mysqli_query($conn, "SELECT * FROM v_kartu_stok");
            if (mysqli_num_rows($ks) == 0):
            ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-4 small">Belum ada riwayat pergerakan stok barang.</td>
                </tr>
            <?php
            endif;
            while ($row = mysqli_fetch_assoc($ks)):
            ?>
            <tr>
                <td class="fw-bold text-pink-pastel">#STK-<?= $row['idkartu_stok'] ?></td>
                <td class="small text-muted"><?= $row['tanggal'] ?></td>
                <td class="fw-semibold"><?= $row['nama_barang'] ?></td>
                <td>
                    <?php if ($row['masuk'] > 0): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                            <i class="fa-solid fa-arrow-down me-1"></i> Barang Masuk Gudang (#TRM-<?= $row['idtransaksi'] ?>)
                        </span>
                    <?php else: ?>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                            <i class="fa-solid fa-arrow-up me-1"></i> Terjual di Kasir (#JL-<?= $row['idtransaksi'] ?>)
                        </span>
                    <?php endif; ?>
                </td>
                <td class="fw-bold text-success"><?= $row['masuk'] > 0 ? '+' . $row['masuk'] : '-' ?></td>
                <td class="fw-bold text-danger"><?= $row['keluar'] > 0 ? '-' . $row['keluar'] : '-' ?></td>
                <td class="fw-bold text-dark"><?= $row['stok_akhir'] ?> unit</td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>