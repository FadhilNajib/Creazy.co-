<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header("Location: index.php");
    exit;
}
include "koneksi.php"; // Pastikan koneksi.php sudah benar


$total_pesanan = 0;
$total_selesai = 0;
$total_menunggu = 0;
$total_diproses = 0;
$jumlah_produk = 0;
$total_pemasukan = 0;

// 1. Total Pesanan Keseluruhan
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM pesanan");
$stmt->execute();
$result = $stmt->get_result();
$total_pesanan = $result->fetch_assoc()['total'];
$stmt->close();

// 2. Total Pesanan Selesai
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM pesanan WHERE status = 'Selesai'");
$stmt->execute();
$result = $stmt->get_result();
$total_selesai = $result->fetch_assoc()['total'];
$stmt->close();

// 3. Total Pesanan Menunggu Pembayaran ('Menunggu' sesuai dashboard Anda)
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM pesanan WHERE status = 'Menunggu'");
$stmt->execute();
$result = $stmt->get_result();
$total_menunggu = $result->fetch_assoc()['total'];
$stmt->close();

// 4. Total Pesanan Diproses
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM pesanan WHERE status = 'Diproses'");
$stmt->execute();
$result = $stmt->get_result();
$total_diproses = $result->fetch_assoc()['total'];
$stmt->close();

// 5. Jumlah Produk (tetap relevan untuk owner)
$stmt = $conn->prepare("SELECT COUNT(id_produk) AS jumlah_produk FROM produk");
$stmt->execute();
$result = $stmt->get_result();
$jumlah_produk = $result->fetch_assoc()['jumlah_produk'];
$stmt->close();

// 6. Total Pemasukan
$stmt = $conn->prepare("SELECT SUM(total_harga) AS total FROM pesanan WHERE status = 'Selesai'");
$stmt->execute();
$result = $stmt->get_result();
$total_pemasukan = $result->fetch_assoc()['total'] ?? 0; // Gunakan ?? 0 untuk handle NULL jika tidak ada data
$stmt->close();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Owner - Creazy.co</title>
         <link rel="icon" href="logo.png" type="image/png">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f6fc;
            margin: 0;
            padding-top: 70px; /* Sesuaikan dengan tinggi navbar */
            color: #333;
        }

        .navbar {
            background: #2f54eb;
            padding: 16px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .navbar a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            text-align: center;
        }

        h2 {
            margin-bottom: 30px;
            color: #333;
        }

        /* Gaya untuk kartu statistik */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); /* Kolom lebih kecil untuk statistik */
            gap: 20px;
            margin-bottom: 40px; /* Jarak antara statistik dan navigasi */
        }

        .stat-card {
            background: #ffffff; /* Putih untuk stat card */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border-left: 5px solid #2f54eb; /* Garis biru di kiri */
            text-align: left;
        }

        .stat-card .label {
            color: #777;
            font-size: 0.9em;
            margin-bottom: 5px;
        }

        .stat-card .value {
            font-size: 1.8em;
            font-weight: bold;
            color: #2f54eb; /* Biru gelap untuk nilai */
        }

        .stat-card.revenue .value {
            color: #52c41a; /* Hijau untuk pemasukan */
        }

        /* Gaya yang sudah ada untuk navigasi dashboard */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .dashboard-card {
            background: #e8f0fe;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            text-align: center;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        }

        .dashboard-card h3 {
            margin-top: 0;
            color: #2f54eb;
            margin-bottom: 15px;
            font-size: 1.4em;
        }

        .dashboard-card p {
            color: #555;
            line-height: 1.6;
        }

        .dashboard-card a {
            display: inline-block;
            background: #2f54eb;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 15px;
            transition: background 0.3s ease;
        }

        .dashboard-card a:hover {
            background: #1a3eab;
        }
    </style>
</head>
<body>

    <?php include "navbar.php"; // Memasukkan navbar ?>

    <div class="container">
        <h2>Selamat Datang, Owner!</h2>
        <p>Ringkasan kinerja bisnis Anda:</p>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Pesanan</h3>
                <div class="value"><?= $total_pesanan ?></div>
            </div>
            <div class="stat-card">
                <h3>Menunggu Pembayaran</h3>
                <div class="value"><?= $total_menunggu ?></div>
            </div>
            <div class="stat-card">
                <h3>Diproses</h3>
                <div class="value"><?= $total_diproses ?></div>
            </div>
            <div class="stat-card">
                <h3>Selesai</h3>
                <div class="value"><?= $total_selesai ?></div>
            </div>
            <div class="stat-card">
                <h3>Jumlah Produk</h3>
                <div class="value"><?= $jumlah_produk ?></div>
            </div>
            <div class="stat-card revenue">
                <h3>Total Pemasukan</h3>
                <div class="value">Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></div>
            </div>
        </div>

        <p>Gunakan link di bawah untuk mengelola lebih lanjut:</p>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h3>Kelola Pesanan</h3>
                <p>Lihat dan proses semua pesanan pelanggan.</p>
                <a href="orderList2.php">Lihat Pesanan</a>
            </div>

            <div class="dashboard-card">
                <h3>Kelola Produk</h3>
                <p>Tambah, edit, atau hapus produk Anda.</p>
                <a href="produk.php">Lihat Produk</a>
            </div>

            <div class="dashboard-card">
                <h3>Lihat Laporan</h3>
                <p>Akses laporan penjualan dan aktivitas lainnya.</p>
                <a href="laporan.php">Lihat Laporan</a>
            </div>

            <div class="dashboard-card">
                <h3>Buat Akun Admin</h3>
                <p>Daftarkan administrator baru untuk sistem.</p>
                <a href="create_admin.php">Buat Admin</a>
            </div>
        </div>
    </div>

</body>
</html>
<?php $conn->close(); ?>