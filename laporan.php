<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header("Location: index.php");
    exit;
}
include "koneksi.php";
include "koneksi.php";

// Ambil tahun-tahun unik dari tabel laporan
$tahun_list = [];
$tahun_query = "SELECT DISTINCT YEAR(tanggal_laporan) as tahun FROM laporan ORDER BY tahun ASC";
$tahun_result = $conn->query($tahun_query);
while ($row = $tahun_result->fetch_assoc()) {
    $tahun_list[] = $row['tahun'];
}

// Ambil tahun terpilih dari GET, default: tahun sekarang
$tahun_terpilih = isset($_GET['tahun']) ? $_GET['tahun'] : date("Y");


// Ambil semua data laporan untuk tahun yang dipilih
$query = "SELECT * FROM laporan WHERE YEAR(tanggal_laporan) = $tahun_terpilih ORDER BY tanggal_laporan ASC";

$result = $conn->query($query);

// Siapkan array bulan kosong (Januari sampai Desember)
// Kunci array akan menjadi 'YYYY-MM'
$laporan_per_bulan = [];
for ($i = 1; $i <= 12; $i++) {
    $key = $tahun_terpilih . "-" . str_pad($i, 2, "0", STR_PAD_LEFT);
    $laporan_per_bulan[$key] = []; // inisialisasi dengan array kosong
}

// Masukkan data laporan ke bulan masing-masing
$total_semua = 0; // total akumulasi semua bulan untuk tahun terpilih
while ($row = $result->fetch_assoc()) {
    $bulan_key = date("Y-m", strtotime($row['tanggal_laporan']));
    // Pastikan kunci bulan ada di array yang sudah diinisialisasi
    if (isset($laporan_per_bulan[$bulan_key])) {
        $laporan_per_bulan[$bulan_key][] = $row;
        $total_semua += $row['total_pemasukan']; // tambahkan ke total keseluruhan
    }
}

// Tutup koneksi database
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report - CREAZY.CO</title>
         <link rel="icon" href="logo.png" type="image/png">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f9f9f9;
            padding: 20px;
            text-align: center;
        }
        .logo {
            background: #2f54eb;
            color: white;
            font-weight: bold;
            padding: 10px;
            font-size: 20px;
            display: inline-block;
            border-radius: 5px;
        }
        .container {
            max-width: 800px;
            margin: 30px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            margin-top: 10px;
            color: #333;
        }
        .bulan {
            background: #ddd;
            margin-top: 20px;
            padding: 10px;
            text-align: left;
            cursor: pointer;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: bold;
            color: #555;
        }
        .tabel-wrapper {
            display: none; /* Defaultnya tersembunyi */
            background: #eee;
            padding: 10px;
            border-radius: 5px;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        .arrow {
            font-size: 1.2em;
            transition: transform 0.3s ease;
        }
        .bulan.active .arrow {
            transform: rotate(180deg);
        }
        .total-semua {
            margin-top: 40px;
            font-size: 20px;
            font-weight: bold;
            color: green;
            background: #d9f7be;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        form {
            margin-bottom: 20px;
        }
        select {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        a {
            text-decoration: none;
        }
    </style>
    <script>
        function toggleTable(id, element) {
            const tabel = document.getElementById(id);
            const arrow = element.querySelector('.arrow');

            if (tabel.style.display === 'block') {
                tabel.style.display = 'none';
                arrow.style.transform = 'rotate(0deg)';
                element.classList.remove('active');
            } else {
                tabel.style.display = 'block';
                arrow.style.transform = 'rotate(180deg)';
                element.classList.add('active');
            }
        }
    </script>
</head>
<body>

    <?php include "navbar.php"; // Ini akan menyertakan konten dari navbar.php ?>
    <div class="logo">CREAZY.CO</div>
    <div class="container">
        <h2>Report Tahunan</h2>

        <form method="GET" action="">
            <label for="tahun">Pilih Tahun:</label>
            <select name="tahun" id="tahun" onchange="this.form.submit()">
                <?php foreach ($tahun_list as $tahun): ?>
                    <option value="<?= $tahun ?>" <?= ($tahun == $tahun_terpilih) ? 'selected' : '' ?>>
                        <?= $tahun ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <?php
        // Loop melalui setiap bulan dari Januari hingga Desember
        foreach ($laporan_per_bulan as $bulan_key => $laporans):
            // $bulan_key contohnya "2023-01"
            $total_bulanan = 0;
            foreach ($laporans as $laporan) {
                $total_bulanan += $laporan['total_pemasukan'];
            }
            // Ubah format bulan_key menjadi nama bulan dan tahun (misal: "January 2023")
            $nama_bulan = date("F Y", strtotime($bulan_key . "-01"));
            // Ambil nomor bulan dari bulan_key (misal: "01" dari "2023-01")
            $bulan_num = date("m", strtotime($bulan_key . "-01"));
        ?>
            <div class="bulan" onclick="toggleTable('<?= $bulan_key ?>_table', this)">
                <?= $nama_bulan ?> <span class="arrow">▼</span>
            </div>
            <div class="tabel-wrapper" id="<?= $bulan_key ?>_table">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Laporan</th>
                            <th>Tanggal</th>
                            <th>Total Pemasukan</th>
                            <th>ID Pesanan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($laporans) > 0): ?>
                            <?php foreach ($laporans as $laporan): ?>
                                <tr>
                                    <td><?= htmlspecialchars($laporan['nama_laporan']) ?></td>
                                    <td><?= date("d-m-Y", strtotime($laporan['tanggal_laporan'])) ?></td>
                                    <td>Rp <?= number_format($laporan['total_pemasukan'], 0, ',', '.') ?></td>
                                    <td><?= htmlspecialchars($laporan['id_pesanan']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">Tidak ada transaksi pada bulan ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <h2 style="text-align:center; margin-bottom: 10px;">
                    Total Pemasukan Bulan <?= $nama_bulan ?>:
                    <span style="color: green;">Rp <?= number_format($total_bulanan, 0, ',', '.') ?></span>
                </h2>
                <div style="text-align:center; margin-bottom: 20px;">
                    <a href="laporan_bulan.php?bulan=<?= (int)$bulan_num ?>&tahun=<?= $tahun_terpilih ?>"
                    style="background:#2f54eb; color:white; padding:8px 16px; border-radius:6px; text-decoration:none;">
                        Lihat Detail Bulan Ini
                    </a>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="total-semua">
            Total Keseluruhan Pemasukan Tahun <?= $tahun_terpilih ?>: Rp <?= number_format($total_semua, 0, ',', '.') ?>
        </div>
        <div style="margin-top: 20px;">
        <form method="POST" action="export_tahunan_pdf.php" target="_blank">
            <input type="hidden" name="tahun" value="<?= $tahun_terpilih ?>">
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

    </div>

</body>
</html>