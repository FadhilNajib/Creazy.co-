<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

include "koneksi.php";

// Ambil data produk untuk dropdown
$produk_result = $conn->query("SELECT id_produk, nama_produk FROM produk");

// Handle submit form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['nama_pemesan'];
    $nomor = $_POST['nomor_pemesan'];
    $tanggal = $_POST['tanggal_pesanan'];
    $estimasi = $_POST['estimasi'];

    $jumlah = $_POST['jumlah'];
    $tambahan = $_POST['tambahan_harga'];
    $status = $_POST['status'];
    $catatan = $_POST['catatan'];
    $id_produk = $_POST['id_produk'];
    $id_adminr = 1; // Asumsi: ID Admin default atau ambil dari session nantinya

    $sql = "INSERT INTO pesanan 
    (nama_pemesan, nomor_pemesan, tanggal_pesanan, estimasi, jumlah, tambahan_harga, status, catatan, id_adminr, id_produk) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sississsii", $nama, $nomor, $tanggal, $estimasi, $jumlah, $tambahan, $status, $catatan, $id_adminr, $id_produk);

    if ($stmt->execute()) {
        echo "<script>alert('Pesanan berhasil ditambahkan!')</script>";
    } else {
        echo "Gagal: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Pesanan</title>
         <link rel="icon" href="logo.png" type="image/png">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            padding: 30px;
        }

        .form-container {
            width: 600px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
        }

        input[type="text"],
        input[type="number"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #0066cc;
            color: white;
            border: none;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background: #004c99;
        }

    </style>
</head>
<body>

<div class="form-container">
    <h2>Tambah Pesanan Baru</h2>
    <form method="POST" action="">
        <label>Nama Pemesan</label>
        <input type="text" name="nama_pemesan" required>

        <label>Nomor Pemesan</label>
        <input type="text" name="nomor_pemesan" required>

        <label>Tanggal Pesanan</label>
        <input type="date" name="tanggal_pesanan" required>

        <label>Estimasi Selesai</label>
        <input type="date" name="estimasi" required>

        <label>Produk</label>
        <select name="id_produk" required>
            <option value="">-- Pilih Produk --</option>
            <?php while($row = $produk_result->fetch_assoc()): ?>
                <option value="<?= $row['id_produk'] ?>"><?= $row['nama_produk'] ?></option>
            <?php endwhile; ?>
        </select>

        <label>Jumlah</label>
        <input type="number" name="jumlah" min="1" required>

        <label>Tambahan Harga</label>
        <input type="number" name="tambahan_harga" value="0" required>

        <label>Status</label>
        <select name="status" required>
            <option value="Menunggu">Menunggu</option>
            <option value="Diproses">Diproses</option>
            <option value="Selesai">Selesai</option>
        </select>

        <label>Catatan</label>
        <textarea name="catatan" rows="3"></textarea>

        <button type="submit">Tambah Pesanan</button>
    </form>
</div>

</body>
</html>
