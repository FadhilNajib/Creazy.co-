<?php
require 'vendor/autoload.php'; // atau libs/dompdf/autoload.inc.php jika tanpa composer
use Dompdf\Dompdf;
use Dompdf\Options;

include "koneksi.php";

$tahun = $_POST['tahun'] ?? date("Y");

// Ambil semua laporan berdasarkan tahun
$query = "SELECT * FROM laporan WHERE YEAR(tanggal_laporan) = $tahun ORDER BY tanggal_laporan ASC";
$result = $conn->query($query);

// Siapkan array per bulan
$bulan_nama = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
    4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September',
    10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$laporan_bulanan = [];
$total_tahun = 0;

while ($row = $result->fetch_assoc()) {
    $bulan = (int)date('n', strtotime($row['tanggal_laporan']));
    $laporan_bulanan[$bulan][] = $row;
    $total_tahun += $row['total_pemasukan'];
}

// Buat HTML-nya
$html = "<h2 style='text-align:center;'>Laporan Tahunan CREAZY.CO - $tahun</h2>";

foreach ($bulan_nama as $bulan_num => $nama_bulan) {
    if (!isset($laporan_bulanan[$bulan_num])) continue; // Lewati jika bulan kosong

    $html .= "<h3 style='margin-top:20px;'> $nama_bulan $tahun </h3><table width='100%' border='1' cellspacing='0' cellpadding='6'>
                <thead>
                    <tr>
                        <th>ID Pesanan</th>
                        <th>Tanggal</th>
                        <th>Nama Laporan</th>
                        <th>Total Pemasukan</th>
                    </tr>
                </thead>
                <tbody>";

    $total_bulanan = 0;
    foreach ($laporan_bulanan[$bulan_num] as $laporan) {
        $tanggal = date("d-m-Y", strtotime($laporan['tanggal_laporan']));
        $html .= "<tr>
                    <td>{$laporan['id_pesanan']}</td>
                    <td>{$tanggal}</td>
                    <td>{$laporan['nama_laporan']}</td>
                    <td>Rp " . number_format($laporan['total_pemasukan'], 0, ',', '.') . "</td>
                  </tr>";
        $total_bulanan += $laporan['total_pemasukan'];
    }

    $html .= "<tr>
                <td colspan='3' align='right'><strong>Total Bulan $nama_bulan:</strong></td>
                <td><strong>Rp " . number_format($total_bulanan, 0, ',', '.') . "</strong></td>
              </tr>";

    $html .= "</tbody></table>";
}

// Total tahunan
$html .= "<h3 style='text-align:right; margin-top:30px;'>
            Total Pemasukan Tahun $tahun: <span style='color:green;'>Rp " . number_format($total_tahun, 0, ',', '.') . "</span>
          </h3>";

// Dompdf
$options = new Options();
$options->set('defaultFont', 'Arial');
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("laporan_tahunan_perbulan_$tahun.pdf", ["Attachment" => false]);
exit;
?>
