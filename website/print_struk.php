<?php
session_start();
// PERBAIKAN 1: Tetapkan timezone ke WIB (Asia/Jakarta) agar tanggal dan waktu sesuai
date_default_timezone_set('Asia/Jakarta');

include "koneksi.php";

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

 $id_pesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_pesanan == 0) {
    header("Location: admin_pesanan.php?error=ID pesanan tidak valid");
    exit();
}

// Ambil data pesanan
 $query = "
    SELECT p.*, b.nama_pembeli, b.nomor_pembeli, b.alamat,
           (SELECT SUM(subtotal) FROM tb_detailpembayaran WHERE id_pesanan = p.id_pesanan) as total
    FROM tb_pesanan p
    JOIN tb_pembeli b ON p.id_pembeli = b.id_pembeli
    WHERE p.id_pesanan = $id_pesanan
";
 $result = mysqli_query($koneksi, $query);

if (mysqli_num_rows($result) == 0) {
    header("Location: admin_pesanan.php?error=Pesanan tidak ditemukan");
    exit();
}

 $pesanan = mysqli_fetch_assoc($result);

// Ambil detail item pesanan
 $items_query = "
    SELECT d.*, m.nama 
    FROM tb_detailpembayaran d
    JOIN tb_menu m ON d.id_menu = m.id_menu
    WHERE d.id_pesanan = $id_pesanan
    ORDER BY m.nama
";
 $items = mysqli_query($koneksi, $items_query);

// Format data
 $id_pesanan_str = str_pad($id_pesanan, 4, '0', STR_PAD_LEFT);

// PERBAIKAN 2: Perbaiki logika pengambilan tanggal
// Jika tanggal dari database kosong atau tidak valid, gunakan waktu saat ini
 $timestamp_pesanan = !empty($pesanan['tanggal']) ? strtotime($pesanan['tanggal']) : time();
 $tanggal = date('d/m/Y H:i', $timestamp_pesanan);

 $nama_pembeli = $pesanan['nama_pembeli'];
 $nomor_pembeli = $pesanan['nomor_pembeli'];
 $alamat = $pesanan['alamat'];
 $total = $pesanan['total'];

// PERBAIKAN 3: Tambahkan '?? ''' untuk mencegah error jika status_pesanan bernilai null
 $status_pesanan = ucfirst($pesanan['status_pesanan'] ?? '');

 $status_pembayaran = isset($pesanan['status_pembayaran']) ? $pesanan['status_pembayaran'] : 'unpaid';
 $metode_pembayaran = isset($pesanan['metode_pembayaran']) ? $pesanan['metode_pembayaran'] : '-';
 $catatan = $pesanan['catatan'] ?? '-';

 $metode_display = [
    'cash' => '💵 Tunai',
    'gopay' => '🟢 GoPay',
    'dana' => '🔵 DANA',
    'ovo' => '🟣 OVO',
    'shopeepay' => '🟠 ShopeePay',
    'qris' => '💳 QRIS',
    'transfer_bank' => '🏦 Transfer Bank',
];
// PERBAIKAN 4: Tambahkan '?? ''' pada metode_pembayaran juga untuk pencegahan
 $metode_text = $metode_display[$metode_pembayaran] ?? ucfirst($metode_pembayaran ?? '');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pesanan #<?= $id_pesanan_str ?> - StarCoffee</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: #f5f5f5;
            padding: 20px;
        }
        
        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }
        .btn {
            padding: 12px 30px;
            margin: 0 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: 'Segoe UI', Arial;
        }
        .btn-print { background: #4caf50; color: white; }
        .btn-print:hover { background: #45a049; }
        .btn-back { background: #6c757d; color: white; }
        .btn-back:hover { background: #5a6268; }
        
        .receipt-container {
            max-width: 400px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        
        .receipt-header {
            text-align: center;
            border-bottom: 2px dashed #333;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        .receipt-header h1 {
            font-size: 28px;
            margin-bottom: 5px;
            letter-spacing: 2px;
        }
        .receipt-header p {
            font-size: 12px;
            margin: 3px 0;
        }
        
        .receipt-info {
            margin-bottom: 15px;
            font-size: 14px;
        }
        .receipt-info div {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }
        .receipt-info .label {
            font-weight: bold;
        }
        
        .receipt-items {
            border-top: 2px dashed #333;
            border-bottom: 2px dashed #333;
            padding: 15px 0;
            margin: 15px 0;
        }
        .item {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            font-size: 14px;
        }
        .item-name {
            flex: 1;
        }
        .item-qty {
            margin: 0 10px;
            text-align: right;
            width: 30px;
        }
        .item-price {
            text-align: right;
            width: 100px;
        }
        
        .receipt-total {
            margin-top: 15px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            font-size: 14px;
        }
        .total-row.grand {
            font-size: 18px;
            font-weight: bold;
            border-top: 2px solid #333;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        .receipt-payment {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px dashed #333;
        }
        .payment-status {
            text-align: center;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            font-weight: bold;
        }
        .payment-status.paid {
            background: #d4edda;
            color: #155724;
        }
        .payment-status.unpaid {
            background: #f8d7da;
            color: #721c24;
        }
        
        .receipt-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            border-top: 2px dashed #333;
            padding-top: 15px;
        }
        .receipt-footer p {
            margin: 5px 0;
        }
        
        /* ======================================================= */
        /*                      CSS UNTUK PRINT                     */
        /* ======================================================= */
        /* CSS untuk cetak di printer thermal */
        @media print {
            /* Mengatur ukuran halaman menjadi 80mm (lebar kertas thermal) */
            @page {
                size: 80mm auto;
                margin: 0;
            }

            /* Sembunyikan elemen yang tidak perlu dicetak */
            .no-print {
                display: none !important;
            }

            body {
                background: white;
                padding: 0;
                margin: 0;
                font-family: 'Courier New', monospace; /* Font standar untuk struk */
            }

            /* Gaya utama untuk kontainer struk */
            .receipt-container {
                box-shadow: none !important; /* Hapus bayangan */
                border-radius: 0 !important; /* Hapus sudut melengkung */
                max-width: 100% !important;
                width: 80mm !important; /* Lebar tetap untuk kertas thermal */
                margin: 0 auto !important;
                padding: 5mm !important; /* Padding di dalam struk */
                font-size: 10px !important; /* Ukuran font dasar */
                background: white !important;
            }

            /* Gaya untuk header struk (nama toko, dll) */
            .receipt-header h1 {
                font-size: 16px !important; /* Perbesar judul toko */
                margin-bottom: 5px !important;
            }
            .receipt-header p {
                font-size: 9px !important; /* Perkecil info alamat/telp */
            }

            /* Gaya untuk info pesanan dan pelanggan */
            .receipt-info div {
                font-size: 10px !important;
                margin: 3px 0 !important;
            }

            /* Gaya untuk daftar item */
            .receipt-items {
                border-top: 1px dashed #000 !important;
                border-bottom: 1px dashed #000 !important;
                padding: 8px 0 !important;
                margin: 10px 0 !important;
            }
            .item {
                font-size: 10px !important;
                margin: 5px 0 !important;
            }
            .item-name {
                font-size: 11px !important; /* Nama item sedikit lebih besar */
            }

            /* Gaya untuk total pembayaran */
            .receipt-total {
                margin-top: 10px !important;
            }
            .total-row {
                font-size: 10px !important;
                margin: 5px 0 !important;
            }
            .total-row.grand {
                font-size: 14px !important; /* Total besar dibuat lebih menonjol */
                font-weight: bold !important;
                border-top: 1px solid #000 !important;
                padding-top: 5px !important;
                margin-top: 8px !important;
            }

            /* Gaya untuk status pembayaran */
            .payment-status {
                padding: 5px !important;
                border: 1px solid #000 !important; /* Beri border agar jelas */
            }
            .payment-status.paid, .payment-status.unpaid {
                background: white !important; /* Latar belakang putih */
                color: black !important; /* Teks hitam */
            }

            /* Gaya untuk footer struk */
            .receipt-footer {
                margin-top: 15px !important;
                border-top: 1px dashed #000 !important;
                padding-top: 10px !important;
                text-align: center !important;
                font-size: 9px !important;
            }

            /* Memastikan semua garis tercetak dengan warna hitam */
            .receipt-header, .receipt-items, .receipt-footer, .total-row.grand {
                border-color: black !important;
            }
            
            /* Memastikan semua teks berwarna hitam dan latar belakang transparan */
            * {
                -webkit-print-color-adjust: exact !important; /* Untuk browser berbasis Webkit */
                color-adjust: exact !important;
                color: black !important;
                background: transparent !important;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn btn-print">
            <i class="ri-printer-line"></i> Cetak Struk
        </button>
        <a href="admin_pesanan.php" class="btn btn-back">
            <i class="ri-arrow-left-line"></i> Kembali ke Pesanan
        </a>
    </div>

    <div class="receipt-container">
        <div class="receipt-header">
            <h1>☕ STARCOFFEE</h1>
            <p>Jl.Raung Pulosari Banjarmlati</p>
            <p>Telp: +62 857 5507 4905</p>
            <p>www.starcoffee.com</p>
        </div>

        <div class="receipt-info">
            <div>
                <span class="label">No. Pesanan:</span>
                <span>#<?= $id_pesanan_str ?></span>
            </div>
            <div>
                <span class="label">Tanggal:</span>
                <span><?= $tanggal ?></span>
            </div>
            <div>
                <span class="label">Kasir:</span>
                <span><?= htmlspecialchars($_SESSION['admin_nama'] ?? 'Admin') ?></span>
            </div>
        </div>

        <div class="receipt-info">
            <div>
                <span class="label">Pelanggan:</span>
                <span><?= htmlspecialchars($nama_pembeli) ?></span>
            </div>
            <?php if ($nomor_pembeli): ?>
            <div>
                <span class="label">No. HP:</span>
                <span><?= htmlspecialchars($nomor_pembeli) ?></span>
            </div>
            <?php endif; ?>
            <?php if ($alamat): ?>
            <div>
                <span class="label">Alamat:</span>
                <span><?= htmlspecialchars($alamat) ?></span>
            </div>
            <?php endif; ?>
        </div>

        <div class="receipt-items">
            <div class="item" style="font-weight: bold; border-bottom: 1px solid #ccc; padding-bottom: 5px;">
                <span class="item-name">Item</span>
                <span class="item-qty">Qty</span>
                <span class="item-price">Harga</span>
            </div>
            
            <?php 
            $subtotal_all = 0;
            while ($item = mysqli_fetch_assoc($items)): 
                $subtotal_all += $item['subtotal'];
            ?>
            <div class="item">
                <span class="item-name"><?= htmlspecialchars($item['nama']) ?></span>
                <span class="item-qty">x<?= $item['jumlah_pesanan'] ?></span>
                <span class="item-price">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></span>
            </div>
            <?php endwhile; ?>
        </div>

        <div class="receipt-total">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>Rp <?= number_format($subtotal_all, 0, ',', '.') ?></span>
            </div>
            
            <?php 
            // Cek apakah ada diskon member (jika harga satuan sudah dikurangi 10%)
            $is_member_discount = ($subtotal_all < $total * 1.1); // Asumsi diskon jika ada perbedaan
            if ($is_member_discount && false): // Disable untuk sekarang
            ?>
            <div class="total-row" style="color: #28a745;">
                <span>Diskon Member (10%):</span>
                <span>- Rp <?= number_format($subtotal_all * 0.1, 0, ',', '.') ?></span>
            </div>
            <?php endif; ?>
            
            <div class="total-row grand">
                <span>TOTAL:</span>
                <span>Rp <?= number_format($total, 0, ',', '.') ?></span>
            </div>
        </div>

        <div class="receipt-payment">
            <div class="payment-status <?= $status_pembayaran ?>">
                <?= $status_pembayaran == 'paid' ? '✅ LUNAS' : '⚠️ BELUM DIBAYAR' ?>
            </div>
            
            <?php if ($status_pembayaran == 'paid'): ?>
            <div class="receipt-info">
                <div>
                    <span class="label">Metode:</span>
                    <span><?= $metode_text ?></span>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="receipt-info">
                <div>
                    <span class="label">Status Pesanan:</span>
                    <span><?= $status_pesanan ?></span>
                </div>
            </div>
            
            <?php if ($catatan && $catatan != '-'): ?>
            <div class="receipt-info">
                <div style="display: block;">
                    <span class="label">Catatan:</span><br>
                    <span style="font-style: italic;"><?= htmlspecialchars($catatan) ?></span>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="receipt-footer">
            <p>════════════════════════════</p>
            <p><strong>Terima Kasih!</strong></p>
            <p>Selamat Menikmati ☕</p>
            <p>════════════════════════════</p>
            <p style="font-size: 10px; margin-top: 10px;">
                Dicetak: <?= date('d/m/Y H:i:s') ?>
            </p>
        </div>
    </div>

    <script>
        /*
        // FITUR AUTO PRINT DINONAKTIFKAN
        // Auto print saat halaman dimuat (opsional)
        <?php if (isset($_GET['auto_print'])): ?>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
        <?php endif; ?>
        */
    </script>
</body>
</html>