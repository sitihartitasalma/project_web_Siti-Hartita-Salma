<?php
session_start();
include "koneksi.php";

// Penting: Beri tahu browser bahwa ini adalah data JSON
header('Content-Type: application/json');

 $id_menu = $_POST['id_menu'] ?? 0;
 $action  = $_POST['action'] ?? "";

if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

if ($id_menu && isset($_SESSION['keranjang'][$id_menu])) {
    if ($action == "plus") {
        $_SESSION['keranjang'][$id_menu]++;
    } elseif ($action == "minus") {
        $_SESSION['keranjang'][$id_menu]--;
        if ($_SESSION['keranjang'][$id_menu] <= 0) {
            unset($_SESSION['keranjang'][$id_menu]);
        }
    }
}

// Siapkan array untuk response
 $response = [
    "jumlah" => 0,
    "subtotal" => 0,
    "total_belanja" => 0,
    "total_item" => 0
];

// Hitung ulang total belanja dan siapkan data untuk dikembalikan
 $total_belanja = 0;
 $total_item = 0;

if (!empty($_SESSION['keranjang'])) {
    foreach ($_SESSION['keranjang'] as $id => $jumlah) {
        // Ambil data produk dari database
        $query = mysqli_query($koneksi, "SELECT * FROM tb_menu WHERE id_menu = $id");
        $produk = mysqli_fetch_assoc($query);

        if ($produk) {
            // --- PERUBAHAN LOGIKA HARGA ---
            // Ambil harga normal dari database
            $harga_normal = (float) $produk['harga_satuan'];
            
            // Cek apakah user adalah member yang sudah login
            if (isset($_SESSION['is_member']) && $_SESSION['is_member'] === true) {
                // Jika member, berikan diskon 10%
                $harga_akhir = $harga_normal * 0.9;
            } else {
                // Jika bukan member, gunakan harga normal
                $harga_akhir = $harga_normal;
            }

            $subtotal = $harga_akhir * $jumlah;
            $total_belanja += $subtotal;
            $total_item += $jumlah;

            // Jika ini adalah item yang sedang di-update, kirimkan detailnya
            if ($id == $id_menu) {
                $response['jumlah'] = $jumlah;
                $response['subtotal'] = $subtotal;
            }
        }
    }
}

 $response['total_belanja'] = $total_belanja;
 $response['total_item'] = $total_item;

// Kembalikan data dalam format JSON
echo json_encode($response);
?>