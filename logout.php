<?php
session_start(); // Mulai session

// Hapus semua variabel session
$_SESSION = array();

// Hancurkan session
session_destroy();

// Arahkan ke halaman login (atau halaman lain sesuai kebutuhan)
header("Location: index.php"); 
exit();
?>
