<?php
session_start();
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'owner'])) {
    header("Location: index.php");
    exit;
}

include "koneksi.php"; // Pastikan file koneksi.php sudah benar

// Periksa apakah ID produk diteruskan melalui URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_produk = (int)$_GET['id'];

    // Query untuk mengambil detail produk berdasarkan ID
    $sql = "SELECT * FROM produk WHERE id_produk = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_produk); // "i" menandakan parameter adalah integer
    $stmt->execute();
    $result = $stmt->get_result();
    $product_data = $result->fetch_assoc();

    // Jika produk tidak ditemukan, alihkan atau tampilkan pesan error
    if (!$product_data) {
        // Alihkan kembali ke halaman produk.php jika ID tidak valid
        header("Location: produk.php");
        exit();
    }

    $stmt->close();
} else {
    // Jika tidak ada ID yang diteruskan, alihkan kembali ke halaman produk.php
    header("Location: produk.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Detail Produk - <?= htmlspecialchars($product_data['nama_produk']) ?></title>
         <link rel="icon" href="logo.png" type="image/png">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f6fc;
            margin: 0;
            padding-top: 70px; /* Sesuaikan dengan tinggi navbar */
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            text-align: left; /* Teks konten di kiri */
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        .product-info {
            display: grid;
            grid-template-columns: 1fr 2fr; /* Kolom label dan kolom nilai */
            gap: 15px;
            margin-bottom: 30px;
            font-size: 1.1em;
        }

        .product-info strong {
            color: #555;
        }

        .product-info div {
            padding: 8px 0;
            border-bottom: 1px dashed #eee;
        }

        .product-info div:last-child {
            border-bottom: none;
        }

        .btn-group {
            text-align: center;
            margin-top: 30px;
        }

        .btn-edit, .btn-back {
            display: inline-block;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: background 0.3s ease;
            margin: 0 10px;
        }

        .btn-edit {
            background: #2f54eb; /* Biru */
            color: white;
        }

        .btn-edit:hover {
            background: #1a3eab;
        }

        .btn-back {
            background: #6c757d; /* Abu-abu */
            color: white;
        }

        .btn-back:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>

    <?php include "navbar.php"; // Memasukkan navbar ?>

    <div class="container">
        <h2>Detail Produk</h2>

        <div class="product-info">
            <div><strong>ID Produk:</strong></div>
            <div><?= htmlspecialchars($product_data['id_produk']) ?></div>

            <div><strong>Nama Produk:</strong></div>
            <div><?= htmlspecialchars($product_data['nama_produk']) ?></div>

            <div><strong>Harga:</strong></div>
            <div>Rp <?= number_format($product_data['harga'], 0, ',', '.') ?></div>

            <div><strong>Bahan:</strong></div>
            <div><?= htmlspecialchars($product_data['bahan']) ?></div>
        </div>

        <div class="btn-group">
            <a href="edit_produk.php?id=<?= $product_data['id_produk'] ?>" class="btn-edit">Edit Produk</a>
            <a href="produk.php" class="btn-back">Kembali ke Daftar Produk</a>
        </div>
    </div>

</body>
</html>