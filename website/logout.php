<?php
session_start();

// Hapus semua session member
unset($_SESSION['member_id']);
unset($_SESSION['member_nama']);
unset($_SESSION['member_email']);
unset($_SESSION['member_telepon']);
unset($_SESSION['member_alamat']);
unset($_SESSION['is_member']);

// Redirect ke halaman utama
header("Location: index.php?message=Anda telah logout dengan sukses&type=success");
exit();
?>