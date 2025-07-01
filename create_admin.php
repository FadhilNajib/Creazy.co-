<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header("Location: index.php");
    exit;
}
include "koneksi.php";
include "koneksi.php"; 

$pesan_sukses = "";
$pesan_error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars($_POST['username']);
    $password = $_POST['password']; // Ambil password mentah

    // Validasi sederhana
    if (empty($username) || empty($password)) {
        $pesan_error = "Username dan Password harus diisi!";
    } elseif (strlen($password) < 6) { // Contoh: password minimal 6 karakter
        $pesan_error = "Password minimal 6 karakter.";
    } else {
        // --- KEAMANAN PENTING: Hash Password ---
        // PASTIKAN KOLOM 'password' di tabel 'admin' adalah VARCHAR(255)
        // Jika tidak, password akan terpotong dan admin tidak bisa login.
        // $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Cek apakah username sudah ada
        $sql_check_username = "SELECT id_admin FROM admin WHERE username = ?";
        $stmt_check = $conn->prepare($sql_check_username);
        $stmt_check->bind_param("s", $username);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $pesan_error = "Username sudah ada. Harap gunakan username lain.";
        } else {
            // Query untuk INSERT admin baru
            $sql_insert = "INSERT INTO admin (username, password) VALUES (?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ss", $username, $password); // Bind hashed password

            if ($stmt_insert->execute()) {
                $pesan_sukses = "Admin '" . $username . "' berhasil ditambahkan!";
                // Kosongkan form setelah berhasil
                $username = '';
            } else {
                $pesan_error = "Gagal menambahkan admin: " . $stmt_insert->error;
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Buat Admin Baru - Creazy.co</title>
        <link rel="icon" href="logo.png" type="image/png">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f6fc;
            margin: 0;
            padding-top: 70px;
            color: #333;
        }

        .container {
            max-width: 500px;
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
        .form-group input[type="password"] {
            width: calc(100% - 24px);
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
            box-sizing: border-box;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="password"]:focus {
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
        <h2>Buat Akun Admin Baru</h2>

        <?php if (!empty($pesan_sukses)): ?>
            <div class="alert alert-success"><?= $pesan_sukses ?></div>
        <?php endif; ?>

        <?php if (!empty($pesan_error)): ?>
            <div class="alert alert-error"><?= $pesan_error ?></div>
        <?php endif; ?>

        <form action="create_admin.php" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($username ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn-submit">Buat Admin</button>
        </form>

        <a href="owner_dashboard.php" class="btn-back">Kembali ke Dashboard Owner</a>
    </div>

</body>
</html>