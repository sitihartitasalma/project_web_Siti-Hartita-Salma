<?php
session_start();
include "koneksi.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $telepon = mysqli_real_escape_string($koneksi, $_POST['telepon']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi password
    if ($password !== $confirm_password) {
        header("Location: login.php?message=Password dan konfirmasi password tidak sama!&type=error");
        exit();
    }
    
    // Cek apakah email sudah terdaftar
    $check_email = "SELECT * FROM tb_member WHERE email = '$email'";
    $result = mysqli_query($koneksi, $check_email);
    
    if (mysqli_num_rows($result) > 0) {
        header("Location: login.php?message=Email sudah terdaftar! Silakan login atau gunakan email lain.&type=error");
        exit();
    }
    
    // Hash password untuk keamanan
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Set timezone dan tanggal daftar
    date_default_timezone_set('Asia/Jakarta');
    $tanggal_daftar = date('Y-m-d H:i:s');
    
    // Insert data member baru
    $insert_query = "INSERT INTO tb_member (nama, email, telepon, alamat, password, tanggal_daftar, status) 
                     VALUES ('$nama', '$email', '$telepon', '$alamat', '$hashed_password', '$tanggal_daftar', 'active')";
    
    if (mysqli_query($koneksi, $insert_query)) {
        // Registrasi berhasil - langsung login
        $member_id = mysqli_insert_id($koneksi);
        
        $_SESSION['member_id'] = $member_id;
        $_SESSION['member_nama'] = $nama;
        $_SESSION['member_email'] = $email;
        $_SESSION['member_telepon'] = $telepon;
        $_SESSION['member_alamat'] = $alamat;
        $_SESSION['is_member'] = true;
        
        header("Location: index.php?message=Registrasi berhasil! Selamat datang $nama, Anda mendapat diskon 10% sebagai member!&type=success");
        exit();
    } else {
        header("Location: login.php?message=Registrasi gagal! Silakan coba lagi.&type=error");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>