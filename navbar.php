<style>
    /* --- Gaya Navbar --- */
    .navbar {
        width: 100%;
        background-color: #0066cc;
        overflow: hidden;
        position: fixed; /* Membuat navbar tetap di atas */
        top: 0;
        left: 0;
        z-index: 1000; /* Pastikan navbar di atas elemen lain */
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 20px;
    }

    .navbar .brand {
        color: white;
        font-size: 24px;
        font-weight: bold;
        text-decoration: none;
    }

    .navbar ul {
        list-style-type: none;
        margin: 0;
        padding: 0;
        display: flex;
    }

    .navbar ul li {
        float: left;
    }

    .navbar ul li a {
        display: block;
        color: white;
        text-align: center;
        padding: 14px 16px;
        text-decoration: none;
        transition: background-color 0.3s ease;
    }

    .navbar ul li a:hover {
        background-color: #555;
    }
    /* --- Akhir Gaya Navbar --- */
</style>

<div class="navbar">
    <a href="index.php" class="brand">CREAZY.CO</a> 
    <ul>
        <li>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="dashboard_admin.php">Dashboard</a>
            <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'owner'): ?>
                <a href="owner_dashboard.php">Dashboard</a>
            <?php else: ?>
                <a href="#">Dashboard</a>
            <?php endif; ?>
        </li>
        <li><a href="orderList2.php">Transaksi</a></li>
        <li>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'owner'): ?>
                <a href="laporan.php">Laporan</a>
            <?php endif; ?>
        <!-- <li><a href="laporan.php">Laporan</a></li> -->
        <li><a href="produk.php">Produk</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>
