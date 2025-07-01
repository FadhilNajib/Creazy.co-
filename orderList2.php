<?php
session_start();
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'owner'])) {
    header("Location: index.php");
    exit;
}

include "koneksi.php";

// Proses ubah status dan buat laporan harusnya

if (isset($_POST['ubah_status'])) {
    $id = $_POST['id_pesanan'];
    $status = $_POST['status'];

    // Update status pesanan
    $stmt = $conn->prepare("UPDATE pesanan SET status=? WHERE id_pesanan=?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();

    // Jika status berubah ke "Selesai", buat atau update laporan
    if ($status === "Selesai") {
        // Ambil data pesanan
        $q = $conn->prepare("SELECT p.*, pr.harga, pr.nama_produk 
                            FROM pesanan p 
                            JOIN produk pr ON p.id_produk = pr.id_produk 
                            WHERE p.id_pesanan=?");
        $q->bind_param("i", $id);
        $q->execute();
        $res = $q->get_result();
        $data = $res->fetch_assoc();

        $tanggal = $data['tanggal_pesanan'];
        $total = ($data['harga'] * $data['jumlah']) + $data['tambahan_harga'];
        $nama_laporan = "Laporan " . date("d M Y", strtotime($tanggal));

        // Cek apakah laporan untuk id_pesanan ini sudah ada
        $cek = $conn->prepare("SELECT * FROM laporan WHERE id_pesanan=?");
        $cek->bind_param("i", $id);
        $cek->execute();
        $hasil = $cek->get_result();  // <<<<< Tambahan penting


            if ($hasil->num_rows > 0) {
            // Jika sudah ada, update data laporan
            $update = $conn->prepare("UPDATE laporan 
                                    SET nama_laporan=?, tanggal_laporan=?, total_pemasukan=? 
                                    WHERE id_pesanan=?");
            $update->bind_param("ssii", $nama_laporan, $tanggal, $total, $id);
            $update->execute();
        } else {
            // Jika belum ada, insert laporan baru
            $insert = $conn->prepare("INSERT INTO laporan 
                (nama_laporan, tanggal_laporan, total_pemasukan, id_pesanan) 
                VALUES (?, ?, ?, ?)");
            $insert->bind_param("ssii", $nama_laporan, $tanggal, $total, $id);
            $insert->execute();
        }

    }
}



// Handle filter status
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : 'Semua';


$sql = "SELECT 
            p.id_pesanan,
            p.nama_pemesan,
            pr.nama_produk,
            p.tanggal_pesanan,
            p.estimasi,
            pr.harga,
            p.jumlah,
            p.tambahan_harga,
            p.status
        FROM pesanan p
        JOIN produk pr ON p.id_produk = pr.id_produk";

if ($filter_status !== 'Semua') {
    $sql .= " WHERE p.status = '" . $conn->real_escape_string($filter_status) . "'";
}

$sql .= " ORDER BY p.tanggal_pesanan DESC";


$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order List</title>
         <link rel="icon" href="logo.png" type="image/png">
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 0; margin: 0; }
        .container {
            max-width: 900px;
            margin: 30px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        h1 {
            text-align: center;
            margin-top: 20px;
        }

        form {
            text-align: center;
            margin: 20px 0;
        }

        table {
            width: 90%;
            margin: auto;
            border-collapse: collapse;
            background: #fff;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: center;
        }

        th {
            background: #0066cc;
            color: #fff;
        }

        .btn-detail {
            padding: 6px 12px;
            background: #009688;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        select {
            padding: 5px;
        }
    </style>
</head>
<body>

<?php include "navbar.php"; // Ini akan menyertakan konten dari navbar.php ?>
<div class="container">
        <h1>Order List</h1>

<form method="GET" action="">
    <label for="filter_status">Filter Status:</label>
    <select name="filter_status" onchange="this.form.submit()">
        <option <?= $filter_status == 'Semua' ? 'selected' : '' ?>>Semua</option>
        <option <?= $filter_status == 'Menunggu' ? 'selected' : '' ?>>Menunggu</option>
        <option <?= $filter_status == 'Diproses' ? 'selected' : '' ?>>Diproses</option>
        <option <?= $filter_status == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
    </select>
</form>

<table>
    <tr>
        <th>Nama Pemesan</th>
        <th>Produk</th>
        <th>Tanggal</th>
        <th>Estimasi</th>
        <th>Total Harga</th>
        <th>Status</th>
        <th>Aksi</th>
    </tr>
    <?php while($row = $result->fetch_assoc()): 
        $total = ($row['harga'] * $row['jumlah']) + $row['tambahan_harga'];
    ?>
        <tr>
            <td><?= htmlspecialchars($row['nama_pemesan']) ?></td>
            <td><?= htmlspecialchars($row['nama_produk']) ?></td>
            <td><?= $row['tanggal_pesanan'] ?></td>
            <td><?= $row['estimasi'] ?></td>
            <td><?= number_format($total) ?></td>
            <td>
                <form method="POST" action="" style="margin:0;">
                    <input type="hidden" name="id_pesanan" value="<?= $row['id_pesanan'] ?>">
                    <select name="status" onchange="this.form.submit()">
                        <option value="Menunggu" <?= $row['status'] == 'Menunggu' ? 'selected' : '' ?>>Menunggu</option>
                        <option value="Diproses" <?= $row['status'] == 'Diproses' ? 'selected' : '' ?>>Diproses</option>
                        <option value="Selesai" <?= $row['status'] == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                    </select>
                    <input type="hidden" name="ubah_status" value="1">
                </form>
            </td>
            <td>
                <a class="btn-detail" href="detail_pesanan.php?id=<?= $row['id_pesanan'] ?>">Detail</a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

</div>
</body>
</html>


<?php $conn->close(); ?>
