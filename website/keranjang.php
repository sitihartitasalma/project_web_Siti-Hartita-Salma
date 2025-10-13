<?php
session_start();
include "koneksi.php";

$id_menu = $_GET['id_menu'];
$jumlah  = 1; // default 1 kalau tidak ada input jumlah

if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

if (isset($_SESSION['keranjang'][$id_menu])) {
    $_SESSION['keranjang'][$id_menu] += $jumlah;
} else {
    $_SESSION['keranjang'][$id_menu] = $jumlah;
}

header("Location: tampil_keranjang.php");
?>
