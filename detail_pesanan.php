<?php
session_start();
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'owner'])) {
    header("Location: index.php");
    exit;
}

include "koneksi.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "SELECT 
            p.*, pr.nama_produk, pr.harga, pr.bahan
        FROM pesanan p
        JOIN produk pr ON p.id_produk = pr.id_produk
        WHERE p.id_pesanan = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Detail Pesanan</title>
         <link rel="icon" href="logo.png" type="image/png">
    <style>
        body { font-family: Arial; background: #f9f9f9; padding: 40px; }
        .detail-box { width: 600px; margin: auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; }
        table { width: 100%; }
        td { padding: 10px; vertical-align: top; }
        td:first-child { font-weight: bold; width: 40%; }
        .btn-back { display: inline-block; margin-top: 20px; padding: 10px 15px; background: #333; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
<? include "navbar.php";?>
<div class="detail-box">
    <h2>Detail Pesanan</h2>
    <?php if ($data): ?>
    <table>
        <tr><td>Nama Pemesan</td><td><?= $data['nama_pemesan'] ?></td></tr>
        <tr><td>Nomor Pemesan</td><td><?= $data['nomor_pemesan'] ?></td></tr>
        <tr><td>Tanggal Pesan</td><td><?= $data['tanggal_pesanan'] ?></td></tr>
        <tr><td>Estimasi Selesai</td><td><?= $data['estimasi'] ?></td></tr>
        <tr><td>Produk</td><td><?= $data['nama_produk'] ?></td></tr>
        <tr><td>Bahan</td><td><?= $data['bahan'] ?></td></tr>
        <tr><td>Harga Produk</td><td><?= number_format($data['harga']) ?></td></tr>
        <tr><td>Jumlah</td><td><?= $data['jumlah'] ?></td></tr>
        <tr><td>Tambahan Harga</td><td><?= number_format($data['tambahan_harga']) ?></td></tr>
        <tr><td>Total Harga</td><td><strong><?= number_format(($data['harga'] * $data['jumlah']) + $data['tambahan_harga']) ?></strong></td></tr>
        <tr><td>Status</td><td><?= $data['status'] ?></td></tr>
        <tr><td>Catatan</td><td><?= nl2br($data['catatan']) ?></td></tr>
    </table>
    <a class="btn-back" href="orderList2.php">← Kembali ke Daftar</a>
    <?php else: ?>
    <p>Data pesanan tidak ditemukan.</p>
    <?php endif; ?>
</div>

</body>
</html>
