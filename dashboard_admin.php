<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}


include "koneksi.php";

// Data utama
$total_pesanan     = $conn->query("SELECT COUNT(*) AS total FROM pesanan")->fetch_assoc()['total'];
$total_selesai     = $conn->query("SELECT COUNT(*) AS total FROM pesanan WHERE status = 'Selesai'")->fetch_assoc()['total'];
$total_menunggu    = $conn->query("SELECT COUNT(*) AS total FROM pesanan WHERE status = 'Menunggu'")->fetch_assoc()['total'];
$total_diproses    = $conn->query("SELECT COUNT(*) AS total FROM pesanan WHERE status = 'Diproses'")->fetch_assoc()['total'];
$total_pemasukan   = $conn->query("SELECT SUM(total_pemasukan) AS total FROM laporan")->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Admin - Creazy.co</title>
        <link rel="icon" href="logo.png" type="image/png">
    <style>
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: #f4f6fc; }
        .navbar {
            background: #2f54eb;
            color: white;
            padding: 16px;
            display: flex;
            justify-content: space-between;
        }
        .navbar a {
            color: white;
            margin-left: 20px;
            text-decoration: none;
        }
        .container {
            padding: 40px;
            max-width: 1100px;
            margin: auto;
        }
        h2 { margin-bottom: 20px; }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            text-align: center;
        }

        .card h3 {
            margin-bottom: 8px;
            font-size: 18px;
            color: #555;
        }

        .card p {
            font-size: 28px;
            font-weight: bold;
            color: #2f54eb;
        }

        .actions {
            margin-top: 30px;
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .actions a {
            background: #2f54eb;
            color: white;
            padding: 12px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
        }

        .actions a:hover {
            background: #1c39b3;
        }
    </style>
</head>
<body>

<?php include "navbar.php";?>

<div class="container">
    <h2>Dashboard Admin</h2>

    <div class="cards">
        <div class="card">
            <h3>Total Pesanan</h3>
            <p><?= $total_pesanan ?></p>
        </div>
        <div class="card">
            <h3>Menunggu Pembayaran</h3>
            <p><?= $total_menunggu ?></p>
        </div>
        <div class="card">
            <h3>Diproses</h3>
            <p><?= $total_diproses ?></p>
        </div>
        <div class="card">
            <h3>Selesai</h3>
            <p><?= $total_selesai ?></p>
        </div>
        <div class="card">
            <h3>Total Pemasukan</h3>
            <p>Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></p>
        </div>
    </div>

    <div class="actions">
        <a href="orderList2.php">Kelola Pesanan</a>
        <a href="laporan.php">Lihat Laporan</a>
        <a href="tambahan_pesanan.php">+ Tambah Pesanan</a>
        <a href="produk.php">Kelola Produk</a>
    </div>
</div>

</body>
</html>

<?php $conn->close(); ?>
