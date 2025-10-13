<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['admin_logged_in']) || !isset($_GET['id'])) {
    exit('Unauthorized');
}

$id_pesanan = (int) $_GET['id'];

// Cek apakah kolom status_pembayaran ada
$columns_check = mysqli_query($koneksi, "SHOW COLUMNS FROM tb_pesanan LIKE 'status_pembayaran'");
$has_payment_status = mysqli_num_rows($columns_check) > 0;

// Cek apakah kolom metode_pembayaran ada
$method_check = mysqli_query($koneksi, "SHOW COLUMNS FROM tb_pesanan LIKE 'metode_pembayaran'");
$has_payment_method = mysqli_num_rows($method_check) > 0;

// Get pesanan info
$query = "
    SELECT p.*, b.nama_pembeli, b.nomor_pembeli, b.alamat" . 
    ($has_payment_status ? ", p.status_pembayaran" : "") . 
    ($has_payment_method ? ", p.metode_pembayaran" : "") . "
    FROM tb_pesanan p
    JOIN tb_pembeli b ON p.id_pembeli = b.id_pembeli
    WHERE p.id_pesanan = $id_pesanan
";
$result = mysqli_query($koneksi, $query);
$pesanan = mysqli_fetch_assoc($result);

if (!$pesanan) {
    exit('Pesanan tidak ditemukan');
}

// Format metode pembayaran
$metode_display = [
    'gopay' => '泙 GoPay',
    'dana' => '鳩 DANA',
    'ovo' => '泪 OVO',
    'shopeepay' => '泛 ShopeePay',
    'qris' => '諜 QRIS',
    'transfer_bank' => '嘗 Transfer Bank'
];

$metode_pembayaran = $pesanan['metode_pembayaran'] ?? null;

// Get detail items
$items_query = "
    SELECT d.*, m.nama as nama_produk, m.harga_satuan 
    FROM tb_detailpembayaran d
    LEFT JOIN tb_menu m ON d.id_menu = m.id_menu
    WHERE d.id_pesanan = $id_pesanan
";
$items = mysqli_query($koneksi, $items_query);
$total = 0;
?>

<div class="detail-item">
    <span class="detail-label">ID Pesanan</span>
    <span class="detail-value"><strong>#<?= str_pad($pesanan['id_pesanan'] ?? 0, 4, '0', STR_PAD_LEFT) ?></strong></span>
</div>

<div class="detail-item">
    <span class="detail-label">Nama Pelanggan</span>
    <span class="detail-value"><?= htmlspecialchars($pesanan['nama_pembeli'] ?? '-') ?></span>
</div>

<div class="detail-item">
    <span class="detail-label">No HP</span>
    <span class="detail-value"><?= htmlspecialchars($pesanan['nomor_pembeli'] ?? '-') ?></span>
</div>

<div class="detail-item">
    <span class="detail-label">Alamat</span>
    <span class="detail-value"><?= htmlspecialchars($pesanan['alamat'] ?? '-') ?></span>
</div>

<div class="detail-item">
    <span class="detail-label">Tanggal Pesan</span>
    <span class="detail-value"><?= date('d/m/Y H:i', strtotime($pesanan['tanggal'] ?? 'now')) ?> WIB</span>
</div>

<div class="detail-item">
    <span class="detail-label">Status Pesanan</span>
    <span class="detail-value">
        <span class="status-badge status-<?= $pesanan['status_pesanan'] ?? 'pending' ?>">
            <?= ucfirst($pesanan['status_pesanan'] ?? 'pending') ?>
        </span>
    </span>
</div>

<?php if ($has_payment_status): ?>
<div class="detail-item">
    <span class="detail-label">Status Pembayaran</span>
    <span class="detail-value">
        <span class="status-badge status-<?= ($pesanan['status_pembayaran'] ?? 'unpaid') ?>">
            <?= ($pesanan['status_pembayaran'] ?? 'unpaid')=='paid'?'Sudah Dibayar':'Belum Dibayar' ?>
        </span>
    </span>
</div>
<?php endif; ?>

<?php if ($has_payment_method && ($pesanan['status_pembayaran'] ?? 'unpaid') == 'paid'): ?>
<div class="detail-item">
    <span class="detail-label">Metode Pembayaran</span>
    <span class="detail-value">
        <strong><?= $metode_display[$metode_pembayaran] ?? $metode_pembayaran ?></strong>
    </span>
</div>
<?php endif; ?>

<?php if (!empty($pesanan['catatan'])): ?>
<div class="detail-item">
    <span class="detail-label">Catatan</span>
    <span class="detail-value"><?= htmlspecialchars($pesanan['catatan']) ?></span>
</div>
<?php endif; ?>

<div class="order-items">
    <h4><i class="ri-shopping-bag-line"></i> Item Pesanan</h4>
    <?php 
    if (mysqli_num_rows($items) > 0):
        while($item = mysqli_fetch_assoc($items)): 
            $subtotal = $item['subtotal'] ?? 0;
            $total += $subtotal;
    ?>
    <div class="order-item">
        <div>
            <strong><?= htmlspecialchars($item['nama_produk'] ?? 'Produk #'.($item['id_menu'] ?? '?')) ?></strong><br>
            <small><?= ($item['jumlah'] ?? 0) ?> x Rp <?= number_format($item['harga_satuan'] ?? 0, 0, ',', '.') ?></small>
        </div>
        <div>
            <strong>Rp <?= number_format($subtotal, 0, ',', '.') ?></strong>
        </div>
    </div>
    <?php 
        endwhile;
    else:
    ?>
    <div style="text-align: center; padding: 20px; color: #999;">
        <i class="ri-inbox-line" style="font-size: 36px; display: block; margin-bottom: 10px;"></i>
        Tidak ada item pesanan
    </div>
    <?php endif; ?>
    
    <div class="detail-item" style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #333;">
        <span class="detail-label"><strong>TOTAL PEMBAYARAN</strong></span>
        <span class="detail-value"><strong style="font-size: 20px; color: #28a745;">Rp <?= number_format($total, 0, ',', '.') ?></strong></span>
    </div>
</div>

<div style="text-align: right; margin-top: 20px;">
    <a href="print_struk.php?id=<?= $id_pesanan ?>" target="_blank" class="btn btn-primary">
        <i class="ri-printer-line"></i> Cetak Struk
    </a>
</div>
</div>
