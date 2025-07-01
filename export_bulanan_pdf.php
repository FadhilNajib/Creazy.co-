<?php
require 'vendor/autoload.php'; // atau 'libs/dompdf/autoload.inc.php'
use Dompdf\Dompdf;
use Dompdf\Options;

include "koneksi.php";

$bulan = $_POST['bulan'] ?? date('n');
$tahun = $_POST['tahun'] ?? date('Y');

// Query laporan gabung tabel
$sql = "SELECT
            l.tanggal_laporan,
            l.id_pesanan,
            pr.nama_produk,
            ps.jumlah,
            pr.harga,
            (ps.jumlah * pr.harga) AS subtotal
        FROM laporan l
        JOIN pesanan ps ON l.id_pesanan = ps.id_pesanan
        JOIN produk pr ON ps.id_produk = pr.id_produk
        WHERE MONTH(l.tanggal_laporan) = ? AND YEAR(l.tanggal_laporan) = ?
        ORDER BY l.tanggal_laporan ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $bulan, $tahun);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
$total_bulan = 0;

while ($row = $result->fetch_assoc()) {
    $row['tanggal_laporan'] = date("d-m-Y", strtotime($row['tanggal_laporan']));
    $rows[] = $row;
    $total_bulan += $row['subtotal'];
}

// Nama bulan
$bulan_nama = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
$judul = "Laporan Bulanan - " . ($bulan_nama[$bulan] ?? $bulan) . " $tahun";

// HTML
$html = "<h2 style='text-align:center;'>$judul</h2>";

if (empty($rows)) {
    $html .= "<p style='text-align:center;'>Tidak ada data laporan untuk bulan ini.</p>";
} else {
    $html .= "<table width='100%' border='1' cellspacing='0' cellpadding='6'>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>ID Pesanan</th>
                        <th>Nama Produk</th>
                        <th>Jumlah</th>
                        <th>Harga Satuan</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>";

    foreach ($rows as $r) {
        $html .= "<tr>
                    <td>{$r['tanggal_laporan']}</td>
                    <td>{$r['id_pesanan']}</td>
                    <td>{$r['nama_produk']}</td>
                    <td>{$r['jumlah']}</td>
                    <td>Rp " . number_format($r['harga'], 0, ',', '.') . "</td>
                    <td>Rp " . number_format($r['subtotal'], 0, ',', '.') . "</td>
                  </tr>";
    }

    $html .= "<tr>
                <td colspan='5' align='right'><strong>Total Pemasukan Bulan Ini</strong></td>
                <td><strong>Rp " . number_format($total_bulan, 0, ',', '.') . "</strong></td>
              </tr>";

    $html .= "</tbody></table>";
}

// Generate PDF
$options = new Options();
$options->set('defaultFont', 'Arial');
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("laporan_bulan_{$bulan}_{$tahun}.pdf", ["Attachment" => false]);
exit;
?>
