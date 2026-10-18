<?php
include 'koneksi.php';

// Hapus Data
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $idsatuan = $_GET['id'];
    mysqli_query($conn, "DELETE FROM satuan WHERE idsatuan = $idsatuan");
    header("Location: satuan.php");
    exit();
}

// Tambah Data
if (isset($_POST['simpan'])) {
    $nama_satuan = $_POST['nama_satuan'];
    $status      = $_POST['status'];
    mysqli_query($conn, "INSERT INTO satuan (nama_satuan, status) VALUES ('$nama_satuan', '$status')");
    header("Location: satuan.php");
    exit();
}

// Update Data
if (isset($_POST['update'])) {
    $idsatuan    = $_POST['idsatuan'];
    $nama_satuan = $_POST['nama_satuan'];
    $status      = $_POST['status'];
    mysqli_query($conn, "UPDATE satuan SET nama_satuan='$nama_satuan', status='$status' WHERE idsatuan=$idsatuan");
    header("Location: satuan.php");
    exit();
}

$edit_data = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit') {
    $idsatuan = $_GET['id'];
    $res = mysqli_query($conn, "SELECT * FROM satuan WHERE idsatuan = $idsatuan");
    $edit_data = mysqli_fetch_assoc($res);
}

$result_satuan = mysqli_query($conn, "SELECT * FROM satuan ORDER BY idsatuan DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Data Satuan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
<div class="container bg-white p-4 rounded shadow-sm" style="max-width: 800px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Kelola Data Satuan</h2>
        <a href="barang.php" class="btn btn-outline-primary">Kembali ke Data Barang</a>
    </div>

    <!-- Form Input -->
    <div class="card mb-4">
        <div class="card-header bg-dark text-white"><?= $edit_data ? 'Edit Satuan' : 'Tambah Satuan' ?></div>
        <div class="card-body">
            <form action="satuan.php" method="POST" class="row g-3">
                <?php if ($edit_data): ?>
                    <input type="hidden" name="idsatuan" value="<?= $edit_data['idsatuan'] ?>">
                <?php endif; ?>
                
                <div class="col-md-6">
                    <label class="form-label">Nama Satuan</label>
                    <input type="text" name="nama_satuan" class="form-control" value="<?= $edit_data['nama_satuan'] ?? '' ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="1" <?= (isset($edit_data) && $edit_data['status'] == 1) ? 'selected' : '' ?>>Aktif</option>
                        <option value="0" <?= (isset($edit_data) && $edit_data['status'] == 0) ? 'selected' : '' ?>>Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <?php if ($edit_data): ?>
                        <button type="submit" name="update" class="btn btn-warning w-100">Update</button>
                    <?php else: ?>
                        <button type="submit" name="simpan" class="btn btn-success w-100">Simpan</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Data -->
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nama Satuan</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result_satuan)): ?>
            <tr>
                <td><?= $row['idsatuan'] ?></td>
                <td><?= htmlspecialchars($row['nama_satuan']) ?></td>
                <td><span class="badge <?= $row['status'] == 1 ? 'bg-success' : 'bg-danger' ?>"><?= $row['status'] == 1 ? 'Aktif' : 'Nonaktif' ?></span></td>
                <td>
                    <a href="satuan.php?action=edit&id=<?= $row['idsatuan'] ?>" class="btn btn-sm btn-warning">Edit</a>
                    <a href="satuan.php?action=delete&id=<?= $row['idsatuan'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus data?')">Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>