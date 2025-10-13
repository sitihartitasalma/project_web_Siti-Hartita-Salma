<?php
include "koneksi.php";

// Set timezone Indonesia
date_default_timezone_set('Asia/Jakarta');

// Cek apakah id_pesanan ada
if (!isset($_GET['id_pesanan']) || empty($_GET['id_pesanan'])) {
    echo "<script>alert('ID Pesanan tidak ditemukan!'); window.location.href='index.php';</script>";
    exit;
}

$id_pesanan = $_GET['id_pesanan'];

// Cek apakah kolom metode_pembayaran ada di tabel
$check_column = mysqli_query($koneksi, "SHOW COLUMNS FROM tb_pesanan LIKE 'metode_pembayaran'");
$column_exists = mysqli_num_rows($check_column) > 0;

// Ambil data pelanggan & pesanan
if ($column_exists) {
    $query = mysqli_query($koneksi, "
        SELECT p.tanggal, b.nama_pembeli, b.nomor_pembeli, b.alamat, p.catatan, p.metode_pembayaran, p.id_pembeli, p.is_member
        FROM tb_pesanan p
        JOIN tb_pembeli b ON p.id_pembeli = b.id_pembeli
        WHERE p.id_pesanan = '$id_pesanan'
    ");
} else {
    $query = mysqli_query($koneksi, "
        SELECT p.tanggal, b.nama_pembeli, b.nomor_pembeli, b.alamat, p.catatan, p.id_pembeli, p.is_member
        FROM tb_pesanan p
        JOIN tb_pembeli b ON p.id_pembeli = b.id_pembeli
        WHERE p.id_pesanan = '$id_pesanan'
    ");
}

$pesanan = mysqli_fetch_assoc($query);

// Cek apakah data pesanan ditemukan
if (!$pesanan) {
    echo "<script>alert('Data pesanan tidak ditemukan!'); window.location.href='index.php';</script>";
    exit;
}

// PERBAIKAN: Gunakan kolom is_member dari tb_pesanan
// Kolom ini disimpan saat admin/pelanggan membuat pesanan
$is_member_order = isset($pesanan['is_member']) ? (bool)$pesanan['is_member'] : false;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembelian - StarCoffee</title>
    <style>
        body { font-family: 'Courier New', monospace; background-color: #f5f5f5; margin: 0; padding: 20px; }
        .receipt-container { max-width: 400px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .receipt-header { text-align: center; border-bottom: 2px dashed #333; padding-bottom: 15px; margin-bottom: 15px; }
        .receipt-header h1 { margin: 0; font-size: 24px; font-weight: bold; }
        .receipt-header p { margin: 5px 0; font-size: 12px; }
        .customer-info { margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px dashed #ccc; }
        .customer-info h3 { margin: 0 0 8px 0; font-size: 14px; }
        .customer-info p { margin: 3px 0; font-size: 12px; }
        .member-badge { background: #4caf50; color: white; padding: 5px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; display: inline-block; margin-top: 5px; }
        .receipt-table { width: 100%; margin-bottom: 15px; border-collapse: collapse; }
        .receipt-table th { text-align: left; padding: 5px 2px; border-bottom: 1px solid #333; font-size: 12px; }
        .receipt-table td { padding: 8px 2px; font-size: 12px; border-bottom: 1px dotted #ccc; vertical-align: top; }
        .item-name { font-weight: bold; max-width: 120px; word-wrap: break-word; }
        .item-details { font-size: 10px; color: #666; }
        .total-section { border-top: 2px solid #333; padding-top: 10px; text-align: right; }
        .total-row { display: flex; justify-content: space-between; margin: 5px 0; }
        .grand-total { font-weight: bold; font-size: 16px; border-top: 1px solid #333; padding-top: 8px; margin-top: 8px; }
        .receipt-footer { text-align: center; margin-top: 20px; padding-top: 15px; border-top: 2px dashed #333; font-size: 12px; }
        .back-button, .print-button { text-align: center; margin-top: 20px; }
        .back-button a, .print-button button { background: #004d40; color: white; padding: 10px 20px; text-decoration: none; border: none; border-radius: 5px; font-size: 14px; cursor: pointer; margin: 0 5px; }
        .print-button button { background: #2e7d32; }
        .back-button a:hover, .print-button button:hover { background: #00695c; }
        .print-button button:hover { background: #388e3c; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .member-price { color: #888; text-decoration: line-through; font-size: 0.9em; }
        .discounted-price { color: #c0392b; font-weight: bold; }
        
        @media print {
            @page { size: auto; margin: 0; padding: 0; }
            body { margin: 0; padding: 0; background: white; font-family: 'Courier New', monospace; }
            * { visibility: hidden; }
            .thermal-receipt, .thermal-receipt * { visibility: visible; }
            .thermal-receipt { position: absolute; left: 50%; top: 0; transform: translateX(-50%); width: 80mm !important; max-width: 80mm !important; padding: 5mm !important; font-size: 10px !important; page-break-inside: avoid; box-shadow: none !important; border-radius: 0 !important; }
            .back-button, .print-button { display: none !important; }
            .thermal-receipt .receipt-header h1 { font-size: 16px !important; margin: 0 0 5px 0 !important; }
            .thermal-receipt .receipt-header p, .thermal-receipt .customer-info p, .thermal-receipt .receipt-footer p { font-size: 9px !important; margin: 2px 0 !important; }
            .thermal-receipt .customer-info h3 { font-size: 11px !important; margin: 5px 0 !important; }
            .thermal-receipt .receipt-table th, .thermal-receipt .receipt-table td { font-size: 9px !important; padding: 3px 2px !important; }
            .thermal-receipt .item-name { font-size: 10px !important; }
            .thermal-receipt .item-details { font-size: 8px !important; }
            .thermal-receipt .total-section { font-size: 10px !important; }
            .thermal-receipt .grand-total { font-size: 12px !important; }
        }
    </style>
</head>
<body>
    <div class="thermal-receipt receipt-container">
        <div class="receipt-header">
            <h1>☕ STAR COFFEE ☕</h1>
            <p>Pulosari-Banjarmlati-Kota Kediri</p>
            <p>Telp: +62 857-5507-4905</p>
            <p>================================</p>
            <p><strong>STRUK PEMBELIAN</strong></p>
        </div>

        <div class="customer-info">
            <h3>DATA PELANGGAN</h3>
            <p><strong>Nama:</strong> <?= htmlspecialchars($pesanan['nama_pembeli'] ?? '-') ?></p>
            <p><strong>No HP:</strong> <?= htmlspecialchars($pesanan['nomor_pembeli'] ?? '-') ?></p>
            <p><strong>Alamat:</strong> <?= htmlspecialchars($pesanan['alamat'] ?? '-') ?></p>
            <p><strong>Tanggal:</strong> <?= $pesanan['tanggal'] ? date('d/m/Y H:i', strtotime($pesanan['tanggal'])) : '-' ?> WIB</p>
            <?php if ($is_member_order): ?>
            <div class="member-badge">✨ MEMBER VIP - DISKON 10% ✨</div>
            <?php endif; ?>
            <?php if (!empty($pesanan['catatan'])): ?>
            <p><strong>Catatan:</strong> <?= htmlspecialchars($pesanan['catatan']) ?></p>
            <?php endif; ?>
        </div>

        <table class="receipt-table">
            <thead>
                <tr>
                    <th style="width: 45%;">Item</th>
                    <th class="text-center" style="width: 10%;">Qty</th>
                    <th class="text-right" style="width: 20%;">Harga</th>
                    <th class="text-right" style="width: 25%;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $detail = mysqli_query($koneksi, "
                    SELECT d.jumlah_pesanan, d.subtotal, d.harga_satuan, m.nama as nama_produk
                    FROM tb_detailpembayaran d
                    LEFT JOIN tb_menu m ON d.id_menu = m.id_menu
                    WHERE d.id_pesanan = '$id_pesanan'
                ");

                $total_belanja = 0;
                $no = 1;
                
                if ($detail && mysqli_num_rows($detail) > 0) {
                    while ($row = mysqli_fetch_assoc($detail)) {
                        $nama_produk = !empty($row['nama_produk']) ? $row['nama_produk'] : 'Produk #' . $no;
                        $jumlah = (int)$row['jumlah_pesanan'];
                        
                        // Harga satuan sudah termasuk diskon dari admin_buat_pesanan.php
                        $harga_akhir = (float)$row['harga_satuan'];
                        $subtotal = (float)$row['subtotal'];
                        $total_belanja += $subtotal;
                ?>
                <tr>
                    <td>
                        <div class="item-name"><?= htmlspecialchars($nama_produk) ?></div>
                        <div class="item-details">@ Rp <?= number_format($harga_akhir, 0, ',', '.') ?></div>
                    </td>
                    <td class="text-center"><?= $jumlah ?></td>
                    <td class="text-right">
                        <?php if ($is_member_order): ?>
                            <span class="discounted-price">Rp <?= number_format($harga_akhir, 0, ',', '.') ?></span>
                        <?php else: ?>
                            Rp <?= number_format($harga_akhir, 0, ',', '.') ?>
                        <?php endif; ?>
                    </td>
                    <td class="text-right"><strong>Rp <?= number_format($subtotal, 0, ',', '.') ?></strong></td>
                </tr>
                <?php 
                        $no++;
                    } 
                } else {
                ?>
                <tr>
                    <td colspan="4" class="text-center">Tidak ada item dalam pesanan</td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <div class="total-section">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>Rp <?= number_format($total_belanja, 0, ',', '.') ?></span>
            </div>
            <div class="total-row grand-total">
                <span><strong>TOTAL PEMBAYARAN:</strong></span>
                <span><strong>Rp <?= number_format($total_belanja, 0, ',', '.') ?></strong></span>
            </div>
        </div>

        <div class="receipt-footer">
            <p>================================</p>
            <p><strong>PEMBAYARAN BERHASIL</strong></p>
            <p>Transaksi telah berhasil diproses</p>
            <p>Terima kasih atas kepercayaan Anda</p>
            <p>================================</p>
            <p><strong>TERIMA KASIH ATAS KUNJUNGAN ANDA</strong></p>
            <p>Selamat menikmati kopi pilihan kami!</p>
            <p>Barang yang sudah dibeli tidak dapat dikembalikan</p>
            <p>================================</p>
            <p>Order ID: #<?= str_pad($id_pesanan, 6, '0', STR_PAD_LEFT) ?></p>
            <p>Kasir: Admin | <?= date('d/m/Y H:i') ?> WIB</p>
        </div>

        <div class="print-button">
            <button onclick="window.print()" id="printBtn">🖨️ Cetak Struk</button>
            <button onclick="downloadPDF()" id="downloadBtn">📄 Download PDF</button>
        </div>
        
        <div class="back-button">
            <a href="index.php">Kembali ke Beranda</a>
        </div>
    </div>

    <script>
        function downloadPDF() { 
            const originalTitle = document.title; 
            document.title = 'Struk_StarCoffee_<?= str_pad($id_pesanan, 6, '0', STR_PAD_LEFT) ?>'; 
            window.print(); 
            document.title = originalTitle; 
        }
    </script>
</body>
</html>