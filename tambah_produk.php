<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

include "koneksi.php"; // Pastikan file koneksi.php sudah benar

$pesan_sukses = "";
$pesan_error = "";

// Inisialisasi variabel untuk form agar tidak error saat pertama kali load
$nama_produk = '';
$harga = '';
$bahan = '';

// Proses form submission (ketika tombol 'Tambah Produk' diklik)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $nama_produk = isset($_POST['nama_produk']) ? htmlspecialchars($_POST['nama_produk']) : '';
    $harga = isset($_POST['harga']) ? (int)$_POST['harga'] : 0;
    $bahan = isset($_POST['bahan']) ? htmlspecialchars($_POST['bahan']) : '';

    // Inisialisasi data gambar biner
    $gambar_biner = null;

    // Validasi dasar untuk nama, harga, bahan
    if (empty($nama_produk) || empty($bahan)) { // Harga bisa 0
        $pesan_error = "Nama Produk dan Bahan harus diisi!";
    } elseif ($harga < 0) {
        $pesan_error = "Harga tidak boleh negatif!";
    } else {
        // --- LOGIKA PENGAMBILAN GAMBAR DARI REFERENSI ANDA ---
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $file_tmp_name = $_FILES['gambar']['tmp_name'];
            $file_size = $_FILES['gambar']['size'];
            $file_type = $_FILES['gambar']['type']; // Akan berguna saat menampilkan

            // Validasi tipe file sederhana (contoh)
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($file_type, $allowed_types)) {
                $pesan_error = "Tipe file gambar tidak diizinkan. Harap gunakan JPEG, PNG, atau GIF.";
            } elseif ($file_size > 5 * 1024 * 1024) { // Batas 5MB (sesuaikan jika perlu)
                $pesan_error = "Ukuran file gambar terlalu besar. Maksimal 5MB.";
            } else {
                // Membaca file gambar sebagai binary data
                // Menggunakan mysqli_real_escape_string TIDAK diperlukan dengan prepared statements
                // dan bahkan tidak direkomendasikan untuk BLOBs.
                // Prepared statement akan menangani escaping dengan benar.
                $gambar_biner = file_get_contents($file_tmp_name);

                if ($gambar_biner === false) {
                    $pesan_error = "Gagal membaca file gambar.";
                    $gambar_biner = null;
                }
            }
        } else {
            // Jika tidak ada file diunggah atau ada error upload
            $pesan_error = "Harap pilih file gambar untuk produk ini. Error code: " . ($_FILES['gambar']['error'] ?? 'N/A');
        }

        // Jika tidak ada error dari proses upload gambar dan gambar_biner berhasil didapatkan
        if (empty($pesan_error) && $gambar_biner !== null) {
            // Query untuk INSERT data produk baru beserta data biner gambar
            // Note: Untuk BLOB, bind_param menggunakan 's' (string)
            $sql_insert = "INSERT INTO produk (nama_produk, harga, bahan, gambar) VALUES (?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            // "siss" berarti parameter: String, Integer, String, String (untuk BLOB)
            $stmt_insert->bind_param("siss", $nama_produk, $harga, $bahan, $gambar_biner);

            if ($stmt_insert->execute()) {
                $pesan_sukses = "Produk '" . htmlspecialchars($nama_produk) . "' berhasil ditambahkan!";
                // Opsional: kosongkan form setelah berhasil ditambahkan
                $nama_produk = '';
                $harga = '';
                $bahan = '';
            } else {
                $pesan_error = "Gagal menambahkan produk ke database: " . $stmt_insert->error;
            }
            $stmt_insert->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Produk Baru - Creazy.co</title>
         <link rel="icon" href="logo.png" type="image/png">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f6fc;
            margin: 0;
            padding-top: 70px; /* Sesuaikan dengan tinggi navbar */
        }

        .container {
            max-width: 700px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            text-align: left;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="file"] {
            width: calc(100% - 24px);
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
            box-sizing: border-box;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="number"]:focus,
        .form-group input[type="file"]:focus {
            border-color: #2f54eb;
            outline: none;
            box-shadow: 0 0 0 2px rgba(47, 84, 235, 0.2);
        }
        .form-group input[type="file"] {
            padding: 10px;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .btn-submit {
            background: #2f54eb;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: bold;
            transition: background 0.3s ease;
            width: 100%;
        }

        .btn-submit:hover {
            background: #1a3eab;
        }

        .btn-back {
            display: block;
            width: fit-content;
            margin: 20px auto 0;
            padding: 10px 20px;
            background: #6c757d;
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

    <?php include "navbar.php"; // Memasukkan navbar ?>

    <div class="container">
        <h2>Tambah Produk Baru</h2>

        <?php if (!empty($pesan_sukses)): ?>
            <div class="alert alert-success"><?= $pesan_sukses ?></div>
        <?php endif; ?>

        <?php if (!empty($pesan_error)): ?>
            <div class="alert alert-error"><?= $pesan_error ?></div>
        <?php endif; ?>

        <form action="tambah_produk.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nama_produk">Nama Produk:</label>
                <input type="text" id="nama_produk" name="nama_produk" value="<?= htmlspecialchars($nama_produk) ?>" required>
            </div>

            <div class="form-group">
                <label for="harga">Harga:</label>
                <input type="number" id="harga" name="harga" value="<?= htmlspecialchars($harga) ?>" required min="0">
            </div>

            <div class="form-group">
                <label for="bahan">Bahan:</label>
                <input type="text" id="bahan" name="bahan" value="<?= htmlspecialchars($bahan) ?>" required>
            </div>

            <div class="form-group">
                <label for="gambar">Gambar Produk:</label>
                <input type="file" id="gambar" name="gambar" accept="image/jpeg, image/png, image/gif" required>
            </div>

            <button type="submit" class="btn-submit">Tambah Produk</button>
        </form>

        <a href="produk.php" class="btn-back">Kembali ke Daftar Produk</a>
    </div>

</body>
</html>