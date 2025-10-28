<?php
session_start();
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'owner'])) {
    header("Location: index.php");
    exit;
}
include "koneksi.php";

// Ambil semua data produk, termasuk kolom 'gambar'
$result = $conn->query("SELECT id_produk, nama_produk, harga, bahan, gambar FROM produk ORDER BY nama_produk ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola Produk - Creazy.co</title>
         <link rel="icon" href="logo.png" type="image/png">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f6fc;
            margin: 0;
            padding-top: 70px; /* Sesuaikan dengan tinggi navbar */
        }

        .navbar {
            background: #2f54eb;
            padding: 16px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center; /* Rata tengah vertikal */
            position: fixed; /* Membuat navbar tetap di atas */
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
            max-width: 1000px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        h2 {
            margin-bottom: 20px;
            text-align: center; /* Pusatkan judul */
        }

        .btn-add {
            background: #2f54eb;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 6px;
            float: right;
            margin-bottom: 20px;
        }

        .btn-add:hover {
            background: #1a3eab;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px; /* Jarak dari tombol tambah */
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
            vertical-align: middle; /* Rata tengah vertikal di sel tabel */
        }

        th {
            background: #2f54eb;
            color: white;
        }

        .btn-detail {
            background: #52c41a;
            color: white;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 4px;
        }

        .btn-detail:hover {
            background: #389e0d;
        }
        
        /* Gaya untuk gambar thumbnail */
        .product-thumbnail {
            width: 80px; /* Atur lebar thumbnail */
            height: 80px; /* Atur tinggi thumbnail */
            object-fit: cover; /* Pastikan gambar memenuhi area tanpa terdistorsi */
            border-radius: 4px; /* Sudut sedikit membulat */
            border: 1px solid #eee;
        }
    </style>
</head>
<body>

<?php include "navbar.php"; ?>

<div class="container">
    <h2>Kelola Produk</h2>

    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="tambah_produk.php" class="btn-add">+ Tambah Produk</a>
    <?php endif; ?>
    

    <div style="clear: both;"></div> <table>
        <thead>
            <tr>
                <th>Gambar</th> <th>Nama Produk</th>
                <th>Harga</th>
                <th>Bahan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td>
                    <?php if (!empty($row['gambar'])): ?>
                        <img src="data:image/jpeg;base64,<?= base64_encode($row['gambar']) ?>" alt="<?= htmlspecialchars($row['nama_produk']) ?>" class="product-thumbnail">
                    <?php else: ?>
                        Tidak ada gambar
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($row['nama_produk']) ?></td>
                <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                <td><?= htmlspecialchars($row['bahan']) ?></td>
                <td>
                    <a class="btn-detail" href="detail_produk.php?id=<?= $row['id_produk'] ?>">Detail</a>
                </td>
            </tr>
            <?php endwhile; ?>
            <?php if ($result->num_rows == 0): ?>
                <tr>
                    <td colspan="5">Tidak ada produk ditemukan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>

<?php $conn->close(); ?>