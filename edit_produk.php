<?php
session_start();
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'owner'])) {
    header("Location: index.php");
    exit;
}

include "koneksi.php"; // Pastikan file koneksi.php sudah benar

$product_data = null; // Inisialisasi variabel untuk data produk
$pesan_sukses = "";
$pesan_error = "";

// 1. Ambil ID produk dari URL dan data produk yang sudah ada
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_produk = (int)$_GET['id'];

    // Query untuk mengambil detail produk yang akan diedit, termasuk gambar
    $sql = "SELECT id_produk, nama_produk, harga, bahan, gambar FROM produk WHERE id_produk = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_produk);
    $stmt->execute();
    $result = $stmt->get_result();
    $product_data = $result->fetch_assoc();

    // Jika produk tidak ditemukan, alihkan kembali ke halaman produk.php
    if (!$product_data) {
        header("Location: produk.php?pesan=produk_tidak_ditemukan");
        exit();
    }
    $stmt->close(); // Tutup statement setelah mengambil data awal
} else {
    // Jika tidak ada ID yang diteruskan, alihkan kembali ke halaman produk.php
    header("Location: produk.php?pesan=id_tidak_ada");
    exit();
}

// 2. Proses form submission (ketika tombol 'Simpan Perubahan' diklik)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_produk_post = (int)$_POST['id_produk']; // Pastikan ID yang sama
    $nama_produk_baru = htmlspecialchars($_POST['nama_produk']);
    $harga_baru = (int)$_POST['harga'];
    $bahan_baru = htmlspecialchars($_POST['bahan']);

    // Default: gunakan gambar yang sudah ada
    $gambar_untuk_update = $product_data['gambar'];

    // Validasi sederhana (Anda bisa menambahkan validasi yang lebih kuat)
    if (empty($nama_produk_baru) || empty($harga_baru) || empty($bahan_baru)) {
        $pesan_error = "Semua field (Nama Produk, Harga, Bahan) harus diisi!";
    } elseif ($harga_baru < 0) {
        $pesan_error = "Harga tidak boleh negatif!";
    } else {
        // --- LOGIKA PENGOLAHAN GAMBAR BARU (JIKA ADA) ---
        if (isset($_FILES['gambar_baru']) && $_FILES['gambar_baru']['error'] === UPLOAD_ERR_OK) {
            $file_tmp_name = $_FILES['gambar_baru']['tmp_name'];
            $file_size = $_FILES['gambar_baru']['size'];
            $file_type = $_FILES['gambar_baru']['type'];

            // Validasi tipe file dan ukuran (sama seperti tambah_produk.php)
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($file_type, $allowed_types)) {
                $pesan_error = "Tipe file gambar tidak diizinkan. Harap gunakan JPEG, PNG, atau GIF.";
            } elseif ($file_size > 5 * 1024 * 1024) { // Batas 5MB (sesuaikan jika perlu)
                $pesan_error = "Ukuran file gambar terlalu besar. Maksimal 5MB.";
            } else {
                // Membaca file gambar baru sebagai binary data
                $new_gambar_binary = file_get_contents($file_tmp_name);
                if ($new_gambar_binary !== false) {
                    $gambar_untuk_update = $new_gambar_binary; // Gunakan data gambar yang baru
                } else {
                    $pesan_error = "Gagal membaca file gambar baru.";
                }
            }
        } // Jika tidak ada file baru diunggah, $gambar_untuk_update tetap menggunakan gambar lama

        // Hanya lanjutkan update jika tidak ada error validasi atau error gambar
        if (empty($pesan_error)) {
            // Query untuk update data produk, termasuk kolom gambar
            $sql_update = "UPDATE produk SET nama_produk = ?, harga = ?, bahan = ?, gambar = ? WHERE id_produk = ?";
            $stmt_update = $conn->prepare($sql_update);
            // "sissi" berarti parameter: String, Integer, String, String (untuk BLOB), Integer
            $stmt_update->bind_param("sissi", $nama_produk_baru, $harga_baru, $bahan_baru, $gambar_untuk_update, $id_produk_post);

            if ($stmt_update->execute()) {
                $pesan_sukses = "Data produk berhasil diperbarui!";
                // Perbarui $product_data agar form menampilkan data terbaru
                $product_data['nama_produk'] = $nama_produk_baru;
                $product_data['harga'] = $harga_baru;
                $product_data['bahan'] = $bahan_baru;
                $product_data['gambar'] = $gambar_untuk_update; // Penting: Perbarui data gambar juga!
            } else {
                $pesan_error = "Gagal memperbarui data produk: " . $stmt_update->error;
            }
            $stmt_update->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Produk - <?= htmlspecialchars($product_data['nama_produk'] ?? 'Produk') ?></title>
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
        .form-group input[type="file"] { /* Tambahkan gaya untuk input file */
            width: calc(100% - 24px); /* Kurangi padding */
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
            box-sizing: border-box; /* Penting agar padding tidak menambah lebar */
        }
        .form-group input[type="file"] {
            padding: 10px;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="number"]:focus,
        .form-group input[type="file"]:focus {
            border-color: #2f54eb;
            outline: none;
            box-shadow: 0 0 0 2px rgba(47, 84, 235, 0.2);
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

        .current-image-preview {
            text-align: center;
            margin-bottom: 15px;
        }
        .current-image-preview img {
            max-width: 200px;
            max-height: 200px;
            border: 1px solid #ddd;
            border-radius: 5px;
            object-fit: contain;
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
        <h2>Edit Produk</h2>

        <?php if (!empty($pesan_sukses)): ?>
            <div class="alert alert-success"><?= $pesan_sukses ?></div>
        <?php endif; ?>

        <?php if (!empty($pesan_error)): ?>
            <div class="alert alert-error"><?= $pesan_error ?></div>
        <?php endif; ?>

        <form action="edit_produk.php?id=<?= $id_produk ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_produk" value="<?= htmlspecialchars($product_data['id_produk']) ?>">

            <div class="form-group">
                <label for="nama_produk">Nama Produk:</label>
                <input type="text" id="nama_produk" name="nama_produk" value="<?= htmlspecialchars($product_data['nama_produk']) ?>" required>
            </div>

            <div class="form-group">
                <label for="harga">Harga:</label>
                <input type="number" id="harga" name="harga" value="<?= htmlspecialchars($product_data['harga']) ?>" required min="0">
            </div>

            <div class="form-group">
                <label for="bahan">Bahan:</label>
                <input type="text" id="bahan" name="bahan" value="<?= htmlspecialchars($product_data['bahan']) ?>" required>
            </div>

            <div class="form-group">
                <label>Gambar Saat Ini:</label>
                <div class="current-image-preview">
                    <?php if (!empty($product_data['gambar'])): ?>
                        <img src="data:image/jpeg;base64,<?= base64_encode($product_data['gambar']) ?>" alt="Gambar <?= htmlspecialchars($product_data['nama_produk']) ?>">
                    <?php else: ?>
                        <span>Tidak ada gambar saat ini.</span>
                    <?php endif; ?>
                </div>
                <label for="gambar_baru">Pilih Gambar Baru (opsional):</label>
                <input type="file" id="gambar_baru" name="gambar_baru" accept="image/jpeg, image/png, image/gif">
                <small style="color: #666;">Biarkan kosong jika tidak ingin mengubah gambar.</small>
            </div>

            <button type="submit" class="btn-submit">Simpan Perubahan</button>
        </form>

        <a href="produk.php" class="btn-back">Kembali ke Daftar Produk</a>
    </div>

</body>
</html>