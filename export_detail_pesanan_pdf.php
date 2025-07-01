<?php
require 'vendor/autoload.php'; // Atau libs/dompdf/autoload.inc.php jika tanpa composer
use Dompdf\Dompdf;
use Dompdf\Options;

include "koneksi.php";

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

$sql = "SELECT 
            p.*, pr.nama_produk, pr.harga, pr.bahan
        FROM pesanan p
        JOIN produk pr ON p.id_produk = pr.id_produk
        WHERE p.id_pesanan = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    die("Data tidak ditemukan.");
}

// Hitung total
$total = ($data['harga'] * $data['jumlah']) + $data['tambahan_harga'];

// HTML content
$html = "
<h2 style='text-align:center;'>Detail Pesanan</h2>
<table width='100%' border='1' cellspacing='0' cellpadding='8'>
    <tr><th>Nama Pemesan</th><td>{$data['nama_pemesan']}</td></tr>
    <tr><th>Nomor Pemesan</th><td>{$data['nomor_pemesan']}</td></tr>
    <tr><th>Tanggal Pesan</th><td>{$data['tanggal_pesanan']}</td></tr>
    <tr><th>Estimasi Selesai</th><td>{$data['estimasi']}</td></tr>
    <tr><th>Produk</th><td>{$data['nama_produk']}</td></tr>
    <tr><th>Bahan</th><td>{$data['bahan']}</td></tr>
    <tr><th>Harga Produk</th><td>Rp " . number_format($data['harga'], 0, ',', '.') . "</td></tr>
    <tr><th>Jumlah</th><td>{$data['jumlah']}</td></tr>
    <tr><th>Tambahan Harga</th><td>Rp " . number_format($data['tambahan_harga'], 0, ',', '.') . "</td></tr>
    <tr><th>Total Harga</th><td><strong>Rp " . number_format($total, 0, ',', '.') . "</strong></td></tr>
    <tr><th>Catatan</th><td>" . nl2br($data['catatan']) . "</td></tr>
</table>
";

// Generate PDF
$options = new Options();
$options->set('defaultFont', 'Arial');
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("detail_pesanan_{$data['id_pesanan']}.pdf", ["Attachment" => false]);
exit;
?>
