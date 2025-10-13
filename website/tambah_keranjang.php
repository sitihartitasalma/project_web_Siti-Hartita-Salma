<?php
session_start();
include "koneksi.php";

// jika session keranjang belum ada, buat dulu
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

// ambil id_menu, pastikan integer
$id_menu = isset($_POST['id_menu']) ? (int) $_POST['id_menu'] : 0;

if ($id_menu > 0) {
    if (isset($_SESSION['keranjang'][$id_menu])) {
        $_SESSION['keranjang'][$id_menu] += 1;
    } else {
        $_SESSION['keranjang'][$id_menu] = 1;
    }
}

// total item untuk icon keranjang
$total_item = array_sum($_SESSION['keranjang']);

// response JSON
header('Content-Type: application/json');
echo json_encode([
    "success" => true,
    "total_item" => $total_item
]);
