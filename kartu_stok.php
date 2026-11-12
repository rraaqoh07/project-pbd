<?php
include 'koneksi.php';
include 'header.php';
?>

<h4 class="fw-bold mb-1"><i class="fa-solid fa-clipboard-list text-pink-pastel me-2"></i>Kartu Stok Barang</h4>
<p class="text-muted small mb-4">Riwayat keluar-masuk mutasi stok barang otomatis dari penerimaan dan penjualan.</p>

<div class="table-responsive">
    <table class="table align-middle">
        <thead>
            <tr>
                <th>ID Mutasi</th>
                <th>Tanggal & Waktu</th>
                <th>Nama Barang</th>
                <th>Keterangan Transaksi</th>
                <th>Masuk</th>
                <th>Keluar</th>
                <th>Stok Akhir</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $ks = mysqli_query($conn, "SELECT * FROM v_kartu_stok");
            while ($row = mysqli_fetch_assoc($ks)):
            ?>
            <tr>
                <td class="fw-bold">#KS-<?= $row['idkartu_stok'] ?></td>
                <td class="small text-muted"><?= $row['tanggal'] ?></td>
                <td class="fw-semibold"><?= $row['nama_barang'] ?></td>
                <td>
                    <?php if ($row['masuk'] > 0): ?>
                        <span class="badge bg-success-subtle text-success"><?= $row['keterangan'] ?> (#TRM-<?= $row['idtransaksi'] ?>)</span>
                    <?php else: ?>
                        <span class="badge bg-danger-subtle text-danger"><?= $row['keterangan'] ?> (#JL-<?= $row['idtransaksi'] ?>)</span>
                    <?php endif; ?>
                </td>
                <td class="fw-bold text-success"><?= $row['masuk'] > 0 ? '+' . $row['masuk'] : '-' ?></td>
                <td class="fw-bold text-danger"><?= $row['keluar'] > 0 ? '-' . $row['keluar'] : '-' ?></td>
                <td class="fw-bold text-dark"><?= $row['stok_akhir'] ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>