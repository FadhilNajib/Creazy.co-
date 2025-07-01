<?php
include "koneksi.php";

$error_message = "";

// Cek apakah form sudah disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validasi input dasar
    if (empty($username) || empty($password)) {
        $error_message = "Username dan password tidak boleh kosong.";
    } else {
        $found_user = false;
        $role = null;
        $user_id = null;
        $db_stored_password = null; // Nama variabel diubah untuk kejelasan

        // --- Coba login sebagai OWNER terlebih dahulu ---
        $stmt_owner = $conn->prepare("SELECT id_owner, username, password FROM owner WHERE username = ?");
        if ($stmt_owner) {
            $stmt_owner->bind_param("s", $username);
            $stmt_owner->execute();
            $result_owner = $stmt_owner->get_result();

            if ($result_owner->num_rows == 1) {
                $owner_data = $result_owner->fetch_assoc();
                $db_stored_password = $owner_data['password'];

                // Perbandingan password tanpa hashing (TIDAK AMAN!)
                if ($password === $db_stored_password) {
                    $found_user = true;
                    $role = 'owner';
                    $_SESSION['username'] = $owner_data['username'];
                    $_SESSION['user_id'] = $owner_data['id_owner']; // Simpan ID owner
                    $_SESSION['role'] = $role;
                }
            }
            $stmt_owner->close();
        } else {
            // Tangani error jika prepare gagal
            $error_message = "Kesalahan database saat mencoba login owner: " . $conn->error;
        }


        // --- Jika bukan owner, coba login sebagai ADMIN ---
        if (!$found_user) {
            $stmt_admin = $conn->prepare("SELECT id_admin, username, password FROM admin WHERE username = ?");
            if ($stmt_admin) {
                $stmt_admin->bind_param("s", $username);
                $stmt_admin->execute();
                $result_admin = $stmt_admin->get_result();

                if ($result_admin->num_rows == 1) {
                    $admin_data = $result_admin->fetch_assoc();
                    $db_stored_password = $admin_data['password'];

                    // Perbandingan password tanpa hashing (TIDAK AMAN!)
                    if ($password === $db_stored_password) {
                        $found_user = true;
                        $role = 'admin';
                        $_SESSION['username'] = $admin_data['username'];
                        $_SESSION['user_id'] = $admin_data['id_admin']; // Simpan ID admin
                        $_SESSION['role'] = $role;
                    }
                }
                $stmt_admin->close();
            } else {
                // Tangani error jika prepare gagal
                $error_message = "Kesalahan database saat mencoba login admin: " . $conn->error;
            }
        }

        // --- Redirect berdasarkan role jika login berhasil ---
        if ($found_user) {
            if ($role == 'owner') {
                header("Location: owner_dashboard.php");
                exit();
            } elseif ($role == 'admin') {
                header("Location: dashboard_admin.php"); // Pastikan file ini ada
                exit();
            }
        } else {
            // Login gagal untuk kedua peran
            $error_message = "Username atau password salah.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Creazy.co</title>
     <link rel="icon" href="logo.png" type="image/png">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #e9ecef;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            color: #343a40;
        }
        .login-container {
            background: #ffffff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        h2 {
            margin-bottom: 30px;
            color: #2f54eb;
            font-size: 2em;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #495057;
        }
        input[type="text"],
        input[type="password"] {
            width: calc(100% - 20px);
            padding: 12px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 1em;
            box-sizing: border-box;
        }
        button {
            background: #2f54eb;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1em;
            transition: background 0.3s ease;
            width: 100%;
            box-sizing: border-box;
            margin-top: 10px;
        }
        button:hover {
            background: #1a3eab;
        }
        .error-message {
            margin-top: 20px;
            padding: 12px;
            background-color: #f8d7da;
            color: #dc3545;
            border: 1px solid #dc3545;
            border-radius: 5px;
            font-weight: bold;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login Creazy.co</h2>

        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
<?php $conn->close(); ?>