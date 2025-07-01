<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header("Location: index.php");
    exit;
}

include "koneksi.php"; // Pastikan file koneksi.php sudah benar

// Ambil bulan dan tahun dari parameter GET, default ke bulan/tahun saat ini
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('n'); // 'n' untuk bulan tanpa leading zero
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

// Query data laporan untuk bulan dan tahun tersebut
// Menggabungkan tabel 'laporan' dengan 'pesanan' dan 'produk'
$sql = "SELECT
            l.id_laporan,
            l.tanggal_laporan,
            l.total_pemasukan,
            l.id_pesanan,
            pr.nama_produk,
            ps.jumlah,
            pr.harga
        FROM
            laporan l
        JOIN
            pesanan ps ON l.id_pesanan = ps.id_pesanan
        JOIN
            produk pr ON ps.id_produk = pr.id_produk
        WHERE
            MONTH(l.tanggal_laporan) = ? AND YEAR(l.tanggal_laporan) = ?
        ORDER BY
            l.tanggal_laporan ASC, l.id_pesanan ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $bulan, $tahun);
$stmt->execute();
$result = $stmt->get_result();

// Siapkan array untuk mengelompokkan detail berdasarkan ID Pesanan
$laporan_detail = [];

while ($row = $result->fetch_assoc()) {
    $id_laporan = $row['id_laporan'];

    if (!isset($laporan_detail[$id_laporan])) {
        $laporan_detail[$id_laporan] = [
            'tanggal_laporan' => $row['tanggal_laporan'],
            'id_pesanan'      => $row['id_pesanan'],
            'total_pesanan_ini' => $row['total_pemasukan'],
            'produk'          => []
        ];
    }

    $laporan_detail[$id_laporan]['produk'][] = [
        'nama_produk'   => $row['nama_produk'],
        'jumlah_produk' => $row['jumlah'],
        'harga_satuan'  => $row['harga'],
        'subtotal_produk' => $row['jumlah'] * $row['harga']
    ];
}

// Nama bulan dalam bahasa Indonesia (membutuhkan PHP intl extension)
if (class_exists('IntlDateFormatter')) {
    $formatter = new IntlDateFormatter('id_ID', IntlDateFormatter::LONG, IntlDateFormatter::NONE);
    $formatter->setPattern('MMMM');
    $nama_bulan_id = $formatter->format(mktime(0, 0, 0, $bulan, 10));
} else {
    $nama_bulan_array = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    $nama_bulan_id = $nama_bulan_array[$bulan] ?? 'Bulan Tidak Dikenal';
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Laporan - <?= $nama_bulan_id ?> <?= $tahun ?></title>
         <link rel="icon" href="logo.png" type="image/png">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0; /* Penting untuk navbar */
            background: #f2f2f2;
            padding-top: 60px; /* Sesuaikan dengan tinggi navbar */
            text-align: center;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        h2 {
            margin-bottom: 25px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #0066cc;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .order-header {
            background-color: #e0e0e0;
            font-weight: bold;
            text-align: center;
        }

        .total-order-row td {
            background-color: #d9edf7; /* Light blue for total per order */
            font-weight: bold;
            text-align: right;
        }
        .total-order-row td:first-child {
            text-align: left;
        }

        .grand-total {
            margin-top: 30px;
            padding: 15px;
            background: #d4edda; /* Light green for grand total */
            color: #155724; /* Dark green text */
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            font-size: 1.3em;
            font-weight: bold;
            text-align: right;
        }

        .btn-back {
            margin-top: 30px;
            display: inline-block;
            padding: 12px 20px;
            background: #6c757d; /* Dark grey */
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.3s ease;
        }

        .btn-back:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>

    <?php include "navbar.php"; // Ini akan menyertakan konten dari navbar.php ?>

    <div class="container">
        <h2>Detail Laporan Bulan <?= $nama_bulan_id ?> <?= $tahun ?></h2>

        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>ID Pesanan</th>
                    <th>Produk</th>
                    <th>Jumlah</th>
                    <th>Nominal Satuan</th>
                    <th>Subtotal Produk</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total_pemasukan_bulan_ini = 0;

                if (!empty($laporan_detail)):
                    foreach ($laporan_detail as $id_laporan_key => $data_laporan):
                        $total_pemasukan_bulan_ini += $data_laporan['total_pesanan_ini'];
                ?>
                        <tr class="order-header">
                            <td colspan="6">
                                Tanggal: <?= date("d-m-Y", strtotime($data_laporan['tanggal_laporan'])) ?> |
                                ID Pesanan: <?= htmlspecialchars($data_laporan['id_pesanan']) ?>
                            </td>
                        </tr>
                        <?php foreach ($data_laporan['produk'] as $produk): ?>
                            <tr>
                                <td></td> <td></td> <td><?= htmlspecialchars($produk['nama_produk']) ?></td>
                                <td><?= number_format($produk['jumlah_produk'], 0, ',', '.') ?></td>
                                <td>Rp <?= number_format($produk['harga_satuan'], 0, ',', '.') ?></td>
                                <td>Rp <?= number_format($produk['subtotal_produk'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="total-order-row">
                            <td colspan="5">Total Nominal Pesanan Ini:</td>
                            <td>Rp <?= number_format($data_laporan['total_pesanan_ini'], 0, ',', '.') ?></td>
                        </tr>
                <?php
                    endforeach;
                else:
                ?>
                    <tr>
                        <td colspan="6">Tidak ada laporan untuk bulan ini.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if (!empty($laporan_detail)): ?>
            <div class="grand-total">
                Total Pemasukan Bulan Ini: Rp <?= number_format($total_pemasukan_bulan_ini, 0, ',', '.') ?>
            </div>
        <?php endif; ?>

        <a class="btn-back" href="laporan.php?tahun=<?= $tahun ?>">← Kembali ke Laporan Tahunan</a>

        <h1>  </h1>
        <form method="POST" action="export_bulanan_pdf.php" target="_blank">
            <input type="hidden" name="bulan" value="<?= $bulan ?>">
            <input type="hidden" name="tahun" value="<?= $tahun ?>">
            <button type="submit" style="
                background-color: #2f54eb; 
                color: white; 
                border: none; 
                padding: 10px 20px; 
                border-radius: 5px; 
                cursor: pointer;">
                Convert to PDF
            </button>
        </form>

    </div>

</body>
</html>