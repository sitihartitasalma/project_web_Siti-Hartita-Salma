<?php
include "koneksi.php";

echo "<h1>🔍 DEBUG SISTEM MEMBER</h1>";
echo "<style>
    body { font-family: Arial; padding: 20px; background: #f5f5f5; }
    .box { background: white; padding: 20px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: #28a745; font-weight: bold; }
    .error { color: #dc3545; font-weight: bold; }
    .warning { color: #ffc107; font-weight: bold; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
    th { background: #f8f9fa; }
    pre { background: #f5f5f5; padding: 10px; border-left: 3px solid #007bff; overflow-x: auto; }
</style>";

// 1. CEK KOLOM IS_MEMBER
echo "<div class='box'>";
echo "<h2>1️⃣ Cek Kolom is_member di tb_pesanan</h2>";
$check = mysqli_query($koneksi, "SHOW COLUMNS FROM tb_pesanan LIKE 'is_member'");
if (mysqli_num_rows($check) > 0) {
    $col = mysqli_fetch_assoc($check);
    echo "<p class='success'>✅ Kolom is_member SUDAH ADA</p>";
    echo "<pre>" . print_r($col, true) . "</pre>";
} else {
    echo "<p class='error'>❌ Kolom is_member BELUM ADA!</p>";
    echo "<p>Jalankan query ini:</p>";
    echo "<pre>ALTER TABLE tb_pesanan ADD COLUMN is_member TINYINT(1) DEFAULT 0 AFTER id_pembeli;</pre>";
}
echo "</div>";

// 2. CEK STRUKTUR TABEL
echo "<div class='box'>";
echo "<h2>2️⃣ Struktur Tabel tb_pesanan</h2>";
$columns = mysqli_query($koneksi, "SHOW COLUMNS FROM tb_pesanan");
echo "<table>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Default</th></tr>";
while ($row = mysqli_fetch_assoc($columns)) {
    $highlight = $row['Field'] == 'is_member' ? "style='background: #d4edda;'" : "";
    echo "<tr $highlight>";
    echo "<td><strong>" . $row['Field'] . "</strong></td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

// 3. CEK DATA PESANAN TERAKHIR
echo "<div class='box'>";
echo "<h2>3️⃣ Data Pesanan Terakhir</h2>";
$pesanan = mysqli_query($koneksi, "
    SELECT 
        p.id_pesanan,
        p.is_member,
        p.tanggal,
        b.nama_pembeli,
        (SELECT SUM(subtotal) FROM tb_detailpembayaran WHERE id_pesanan = p.id_pesanan) as total
    FROM tb_pesanan p
    JOIN tb_pembeli b ON p.id_pembeli = b.id_pembeli
    ORDER BY p.id_pesanan DESC
    LIMIT 5
");

if (mysqli_num_rows($pesanan) > 0) {
    echo "<table>";
    echo "<tr><th>ID Pesanan</th><th>Is Member</th><th>Nama Pembeli</th><th>Total</th><th>Tanggal</th></tr>";
    while ($row = mysqli_fetch_assoc($pesanan)) {
        $member_badge = $row['is_member'] == 1 ? 
            "<span style='background: #28a745; color: white; padding: 2px 8px; border-radius: 3px;'>MEMBER</span>" : 
            "<span style='background: #6c757d; color: white; padding: 2px 8px; border-radius: 3px;'>REGULER</span>";
        
        echo "<tr>";
        echo "<td><strong>#" . str_pad($row['id_pesanan'], 4, '0', STR_PAD_LEFT) . "</strong></td>";
        echo "<td>" . $member_badge . "</td>";
        echo "<td>" . htmlspecialchars($row['nama_pembeli']) . "</td>";
        echo "<td>Rp " . number_format($row['total'], 0, ',', '.') . "</td>";
        echo "<td>" . date('d/m/Y H:i', strtotime($row['tanggal'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='warning'>⚠️ Belum ada pesanan</p>";
}
echo "</div>";

// 4. CEK DETAIL ITEM PESANAN TERAKHIR
echo "<div class='box'>";
echo "<h2>4️⃣ Detail Item Pesanan Terakhir</h2>";
$last_order = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT MAX(id_pesanan) as id FROM tb_pesanan"));
if ($last_order && $last_order['id']) {
    $id = $last_order['id'];
    
    // Get order info
    $order = mysqli_fetch_assoc(mysqli_query($koneksi, "
        SELECT p.*, b.nama_pembeli 
        FROM tb_pesanan p 
        JOIN tb_pembeli b ON p.id_pembeli = b.id_pembeli 
        WHERE p.id_pesanan = $id
    "));
    
    echo "<p><strong>Pesanan #" . str_pad($id, 4, '0', STR_PAD_LEFT) . "</strong></p>";
    echo "<p>Pembeli: " . htmlspecialchars($order['nama_pembeli']) . "</p>";
    echo "<p>Is Member: <strong>" . ($order['is_member'] == 1 ? "✅ YA" : "❌ TIDAK") . "</strong></p>";
    
    $items = mysqli_query($koneksi, "
        SELECT d.*, m.nama, m.harga_satuan as harga_menu_asli
        FROM tb_detailpembayaran d
        JOIN tb_menu m ON d.id_menu = m.id_menu
        WHERE d.id_pesanan = $id
    ");
    
    echo "<table>";
    echo "<tr><th>Menu</th><th>Qty</th><th>Harga Menu Asli</th><th>Harga Tersimpan</th><th>Subtotal</th><th>Diskon?</th></tr>";
    
    while ($item = mysqli_fetch_assoc($items)) {
        $harga_asli = $item['harga_menu_asli'];
        $harga_tersimpan = $item['harga_satuan'];
        $ada_diskon = ($harga_tersimpan < $harga_asli);
        $persen_diskon = $ada_diskon ? round((1 - ($harga_tersimpan / $harga_asli)) * 100) : 0;
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($item['nama']) . "</td>";
        echo "<td>" . $item['jumlah_pesanan'] . "</td>";
        echo "<td>Rp " . number_format($harga_asli, 0, ',', '.') . "</td>";
        echo "<td>Rp " . number_format($harga_tersimpan, 0, ',', '.') . "</td>";
        echo "<td>Rp " . number_format($item['subtotal'], 0, ',', '.') . "</td>";
        echo "<td>" . ($ada_diskon ? 
            "<span class='success'>✅ Diskon $persen_diskon%</span>" : 
            "<span class='error'>❌ Tidak ada diskon</span>") . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // ANALISIS
    echo "<div style='margin-top: 20px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107;'>";
    echo "<h3>📊 ANALISIS:</h3>";
    if ($order['is_member'] == 1 && !$ada_diskon) {
        echo "<p class='error'>❌ MASALAH DITEMUKAN: Pesanan adalah MEMBER tapi harga TIDAK dikurangi 10%!</p>";
        echo "<p><strong>Kemungkinan penyebab:</strong></p>";
        echo "<ul>";
        echo "<li>File admin_buat_pesanan.php belum terupdate dengan benar</li>";
        echo "<li>Checkbox member tidak tercentang saat buat pesanan</li>";
        echo "<li>Ada error di kode PHP yang tidak terlihat</li>";
        echo "</ul>";
    } else if ($order['is_member'] == 1 && $ada_diskon) {
        echo "<p class='success'>✅ SISTEM BERJALAN NORMAL: Member mendapat diskon $persen_diskon%</p>";
    } else {
        echo "<p>ℹ️ Pesanan ini bukan member, tidak ada diskon (NORMAL)</p>";
    }
    echo "</div>";
    
} else {
    echo "<p class='warning'>⚠️ Belum ada pesanan</p>";
}
echo "</div>";

// 5. TEST PERHITUNGAN
echo "<div class='box'>";
echo "<h2>5️⃣ Test Perhitungan Diskon</h2>";
$harga_asli = 13000;
$harga_member = $harga_asli * 0.9;
$selisih = $harga_asli - $harga_member;

echo "<table>";
echo "<tr><th>Keterangan</th><th>Nilai</th></tr>";
echo "<tr><td>Harga Asli</td><td>Rp " . number_format($harga_asli, 0, ',', '.') . "</td></tr>";
echo "<tr><td>Diskon 10%</td><td>Rp " . number_format($selisih, 0, ',', '.') . "</td></tr>";
echo "<tr><td>Harga Member (90%)</td><td>Rp " . number_format($harga_member, 0, ',', '.') . "</td></tr>";
echo "</table>";
echo "<p style='margin-top: 10px;'><strong>Formula:</strong> <code>harga_member = harga_asli * 0.9</code></p>";
echo "</div>";

// 6. INSTRUKSI PERBAIKAN
echo "<div class='box' style='background: #e3f2fd;'>";
echo "<h2>🔧 Langkah Perbaikan</h2>";
echo "<ol>";
echo "<li><strong>Pastikan file admin_buat_pesanan.php sudah terupdate</strong> dengan kode terbaru</li>";
echo "<li><strong>Buat pesanan baru</strong> dengan:
    <ul>
        <li>✅ Centang checkbox 'Pelanggan adalah Member VIP'</li>
        <li>✅ Pilih member dari dropdown</li>
        <li>✅ Pilih menu dan qty</li>
        <li>✅ Klik 'Buat Pesanan'</li>
    </ul>
</li>";
echo "<li><strong>Refresh halaman ini</strong> (test_member.php) untuk cek hasilnya</li>";
echo "<li><strong>Cek struk</strong> - apakah diskon sudah muncul?</li>";
echo "</ol>";
echo "</div>";

?>

<div class="box">
    <h2>📁 File Yang Perlu Diupdate</h2>
    <ol>
        <li><strong>admin_buat_pesanan.php</strong> - Form buat pesanan (UTAMA)</li>
        <li><strong>print_struk.php</strong> - Tampilan struk thermal</li>
    </ol>
    <p><a href="admin_buat_pesanan.php" style="display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin-top: 10px;">➡️ Buat Pesanan Baru</a></p>
</div>