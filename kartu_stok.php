<?php
include 'koneksi.php';
include 'header.php';

// Ambil data kartu stok dengan JOIN ke tabel barang untuk menghindari Undefined array key "nama_barang"
$query = mysqli_query($conn, "
    SELECT * FROM v_kartu_stok 
    ORDER BY idkartu_stok DESC
");
?>

<div class="container-fluid px-4">
    <h4 class="fw-bold mt-4 mb-3"><i class="fa-solid fa-boxes-stacked text-pink-pastel me-2"></i>Kartu Stok Barang</h4>
    <p class="text-muted small mb-4">Pantau keluar masuk barang serta riwayat mutasi stok secara real-time.</p>

    <div class="card border-0 shadow-sm rounded-4 p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No Kartu</th>
                        <th>Tanggal</th>
                        <th>Nama Barang</th>
                        <th>Jenis Mutasi</th>
                        <th>Masuk</th>
                        <th>Keluar</th>
                        <th>Stok Akhir</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($query) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($query)): ?>
                            <tr>
                                <td class="fw-bold text-pink-pastel">#STK-<?php echo $row['idkartu_stok'] ?? ''; ?></td>
                                <td><?php echo $row['created_at'] ?? '-'; ?></td>
                                <td>
                                    <div class="fw-semibold"><?php echo $row['nama_barang'] ?? 'Barang Tidak Ditemukan'; ?></div>
                                    <small class="text-muted">
                                        <?php 
                                        $idtransaksi = $row['idtransaksi'] ?? null;
                                        if (!empty($idtransaksi)) {
                                            echo "Ref Transaksi: #" . $idtransaksi;
                                        } else {
                                            echo "Mutasi Sistem";
                                        }
                                        ?>
                                    </small>
                                </td>
                                <td>
                                    <?php if (($row['jenis_transaksi'] ?? '') == 'M'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Masuk</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Keluar</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-success fw-bold">+<?php echo $row['masuk'] ?? 0; ?></td>
                                <td class="text-danger fw-bold">-<?php echo $row['keluar'] ?? 0; ?></td>
                                <td class="fw-bold"><?php echo $row['stock'] ?? 0; ?> unit</td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Belum ada riwayat kartu stok.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>