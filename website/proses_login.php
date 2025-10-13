<?php
session_start();
include "koneksi.php";

// Cek jika member sudah login, redirect ke index
if (isset($_SESSION['is_member']) && $_SESSION['is_member'] === true) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = $_POST['password'];
    
    // Query untuk mencari member berdasarkan email
    $query = "SELECT * FROM tb_member WHERE email = '$email'";
    $result = mysqli_query($koneksi, $query);
        if (mysqli_num_rows($result) == 1) {
        $member = mysqli_fetch_assoc($result);
        
        // Verifikasi password
        if (password_verify($password, $member['password'])) {
            
            // --- PERIKSA STATUS MEMBER SEBELUM IZINKAN LOGIN ---
            if ($member['status'] == 'active') {
                // Jika status aktif, simpan data member ke session dan izinkan login
                $_SESSION['member_id'] = $member['id_member'];
                $_SESSION['member_nama'] = $member['nama'];
                $_SESSION['member_email'] = $member['email'];
                $_SESSION['member_telepon'] = $member['telepon'];
                $_SESSION['member_alamat'] = $member['alamat'];
                $_SESSION['is_member'] = true;
                
                // Redirect ke halaman utama
                header("Location: index.php?message=Login berhasil! Selamat datang " . $member['nama'] . "&type=success");
                exit();

            } else {
                // Jika status tidak aktif, tampilkan pesan error dan cegah login
                header("Location: login.php?message=Akun Anda tidak aktif. Silakan hubungi admin.&type=error");
                exit();
            }

        } else {
            // Password salah
            header("Location: login.php?message=Email atau password salah!&type=error");
            exit();
        }
    } else {
        // Email tidak ditemukan
        header("Location: login.php?message=Email atau password salah!&type=error");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>