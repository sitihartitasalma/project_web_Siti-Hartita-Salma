<?php
session_start();

// Simpan nama admin untuk pesan
$admin_nama = isset($_SESSION['admin_nama']) ? $_SESSION['admin_nama'] : (isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin');

// Hapus semua session admin
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_nama']);
unset($_SESSION['admin_role']);
unset($_SESSION['admin_login_time']);

// Redirect ke halaman login admin dengan pesan
header("Location: admin_login.php?message=" . urlencode("Logout berhasil! Terima kasih " . $admin_nama . ".") . "&type=success");
exit();
?>