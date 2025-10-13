<?php
session_start();
include "koneksi.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];
    
    // Query untuk mencari admin
    $query = "SELECT * FROM tb_admin WHERE username = '$username' AND status = 'active'";
    $result = mysqli_query($koneksi, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $admin = mysqli_fetch_assoc($result);
        
        // Cek password (support untuk hash dan plain text)
        $password_valid = false;
        
        // Jika password di database adalah hash (dimulai dengan $2y$)
        if (substr($admin['password'], 0, 4) === '$2y$') {
            $password_valid = password_verify($password, $admin['password']);
        } else {
            // Jika password plain text
            $password_valid = ($password === $admin['password']);
        }
        
        if ($password_valid) {
            // Login berhasil
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id_admin'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_nama'] = $admin['nama_lengkap'];
            $_SESSION['admin_role'] = $admin['role'];
            $_SESSION['admin_login_time'] = time();
            
            // Update last login
            mysqli_query($koneksi, "UPDATE tb_admin SET last_login = NOW() WHERE id_admin = {$admin['id_admin']}");
            
            // Redirect ke dashboard
            header("Location: admin_dashboard.php?message=Login berhasil! Selamat datang " . urlencode($admin['nama_lengkap']));
            exit();
        } else {
            // Password salah
            header("Location: admin_login.php?error=" . urlencode("Password salah!"));
            exit();
        }
    } else {
        // Username tidak ditemukan atau tidak aktif
        header("Location: admin_login.php?error=" . urlencode("Username tidak ditemukan atau akun nonaktif!"));
        exit();
    }
} else {
    // Akses langsung tanpa POST
    header("Location: admin_login.php");
    exit();
}
?>