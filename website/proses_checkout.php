<?php
session_start();
include "koneksi.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Data pelanggan dari form
    $nama_pembeli   = mysqli_real_escape_string($koneksi, $_POST['nama_pembeli']);
    $nomor_pembeli  = mysqli_real_escape_string($koneksi, $_POST['nomor_pembeli']);
    $alamat         = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $catatan        = isset($_POST['catatan']) ? mysqli_real_escape_string($koneksi, $_POST['catatan']) : '';

    // CEK APAKAH USER ADALAH MEMBER
    $is_member = isset($_SESSION['is_member']) && $_SESSION['is_member'] ? 1 : 0;
    
    // DEBUG: Tampilkan info member
    echo "<!-- DEBUG: is_member = " . $is_member . " -->";
    echo "<!-- DEBUG: SESSION is_member = " . (isset($_SESSION['is_member']) ? $_SESSION['is_member'] : 'tidak ada') . " -->";

    // Simpan pelanggan
    mysqli_query($koneksi, "INSERT INTO tb_pembeli (nama_pembeli, nomor_pembeli, alamat) 
                            VALUES ('$nama_pembeli','$nomor_pembeli','$alamat')");
    $id_pembeli = mysqli_insert_id($koneksi);

    // Set timezone Indonesia dan tanggal otomatis
    date_default_timezone_set('Asia/Jakarta');
    $tanggal = date('Y-m-d H:i:s'); 

    // SIMPAN PESANAN DENGAN KOLOM is_member
    mysqli_query($koneksi, "INSERT INTO tb_pesanan (id_pembeli, tanggal, catatan, is_member) 
                            VALUES ('$id_pembeli', '$tanggal', '$catatan', '$is_member')");

    $id_pesanan = mysqli_insert_id($koneksi);
    
    echo "<!-- DEBUG: id_pesanan = " . $id_pesanan . " -->";

    // Simpan detail pesanan
    foreach ($_SESSION['keranjang'] as $id_menu => $jumlah_pesanan) {
        $query = mysqli_query($koneksi, "SELECT * FROM tb_menu WHERE id_menu='$id_menu'");
        $menu = mysqli_fetch_assoc($query);

        if (!$menu) {
            continue;
        }

        $harga_satuan = (float)$menu['harga_satuan'];
        $harga_awal = $harga_satuan;  // Simpan harga awal untuk debug
        
        // PENTING: TERAPKAN DISKON 10% JIKA MEMBER
        if ($is_member) {
            $harga_satuan = $harga_satuan * 0.9;  // Kurangi harga menjadi 90% (diskon 10%)
        }
        
        $jumlah_pesanan = (int)$jumlah_pesanan;
        $subtotal = $harga_satuan * $jumlah_pesanan;
        
        // DEBUG: Tampilkan info harga
        echo "<!-- DEBUG: id_menu = " . $id_menu . ", harga_awal = " . $harga_awal . ", harga_akhir = " . $harga_satuan . ", subtotal = " . $subtotal . " -->";

        // Insert ke database dengan harga yang sudah diskon
        $insert_result = mysqli_query($koneksi, "INSERT INTO tb_detailpembayaran 
            (id_pesanan, id_menu, jumlah_pesanan, harga_satuan, subtotal) 
            VALUES ('$id_pesanan', '$id_menu', '$jumlah_pesanan', '$harga_satuan', '$subtotal')");
        
        if (!$insert_result) {
            echo "<!-- ERROR: " . mysqli_error($koneksi) . " -->";
        }
    }

    // Kosongkan keranjang
    unset($_SESSION['keranjang']);

    echo "<script>alert('Pesanan berhasil disimpan!'); window.location.href='sukses.php?id_pesanan=$id_pesanan';</script>";
} else {
    echo "Akses tidak valid!";
}
?>