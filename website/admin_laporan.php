<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Filter
$filter_periode = isset($_GET['periode']) ? $_GET['periode'] : 'hari_ini';
$tanggal_mulai = isset($_GET['tanggal_mulai']) ? $_GET['tanggal_mulai'] : date('Y-m-d');
$tanggal_akhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : date('Y-m-d');

// Set tanggal berdasarkan periode
switch($filter_periode) {
    case 'hari_ini':
        $tanggal_mulai = date('Y-m-d');
        $tanggal_akhir = date('Y-m-d');
        break;
    case 'minggu_ini':
        $tanggal_mulai = date('Y-m-d', strtotime('monday this week'));
        $tanggal_akhir = date('Y-m-d');
        break;
    case 'bulan_ini':
        $tanggal_mulai = date('Y-m-01');
        $tanggal_akhir = date('Y-m-d');
        break;
    case 'tahun_ini':
        $tanggal_mulai = date('Y-01-01');
        $tanggal_akhir = date('Y-m-d');
        break;
}

// Query untuk laporan penjualan
// Dihapus: 'kategori' tidak digunakan di sini
$laporan_query = "
    SELECT 
        DATE(p.tanggal) as tanggal,
        COUNT(DISTINCT p.id_pesanan) as total_transaksi,
        SUM(d.jumlah_pesanan) as total_item_terjual,
        SUM(d.subtotal) as total_pendapatan,
        COUNT(DISTINCT p.id_pembeli) as total_pelanggan
    FROM tb_pesanan p
    JOIN tb_detailpembayaran d ON p.id_pesanan = d.id_pesanan
    WHERE DATE(p.tanggal) BETWEEN '$tanggal_mulai' AND '$tanggal_akhir'
    GROUP BY DATE(p.tanggal)
    ORDER BY tanggal DESC
";
$laporan = mysqli_query($koneksi, $laporan_query);

// Produk terlaris
// PERBAIKAN: Hapus m.kategori dari SELECT dan GROUP BY
$produk_terlaris = mysqli_query($koneksi, "
    SELECT 
        m.nama,
        m.foto,
        SUM(d.jumlah_pesanan) as total_terjual,
        SUM(d.subtotal) as total_pendapatan
    FROM tb_detailpembayaran d
    JOIN tb_menu m ON d.id_menu = m.id_menu
    JOIN tb_pesanan p ON d.id_pesanan = p.id_pesanan
    WHERE DATE(p.tanggal) BETWEEN '$tanggal_mulai' AND '$tanggal_akhir'
    GROUP BY m.id_menu, m.nama, m.foto /* Tambahkan kolom non-agregat ke GROUP BY */
    ORDER BY total_terjual DESC
    LIMIT 10
");

// Summary statistics
$summary = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT 
        COUNT(DISTINCT p.id_pesanan) as total_transaksi,
        SUM(d.jumlah_pesanan) as total_item,
        COALESCE(SUM(d.subtotal), 0) as total_pendapatan,
        COALESCE(AVG(d.subtotal), 0) as rata_rata_transaksi
    FROM tb_pesanan p
    LEFT JOIN tb_detailpembayaran d ON p.id_pesanan = d.id_pesanan
    WHERE DATE(p.tanggal) BETWEEN '$tanggal_mulai' AND '$tanggal_akhir'
"));

$total_transaksi = $summary['total_transaksi'] ?? 0;
$total_item = $summary['total_item'] ?? 0;
$total_pendapatan = $summary['total_pendapatan'] ?? 0;
$rata_rata = $summary['rata_rata_transaksi'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Admin StarCoffee</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f5f7fa; }
        
        .header { 
            background: linear-gradient(135deg, #004d40 0%, #00695c 100%);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header a { 
            color: white; 
            text-decoration: none; 
            padding: 8px 15px; 
            border-radius: 5px; 
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .header a:hover { background: rgba(255,255,255,0.1); }
        
        .container { max-width: 1400px; margin: 30px auto; padding: 0 20px; }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .summary-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.3s;
        }
        .summary-card:hover {
            transform: translateY(-5px);
        }
        .summary-icon {
            width: 70px;
            height: 70px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: white;
        }
        .summary-icon.revenue { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .summary-icon.transactions { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .summary-icon.items { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .summary-icon.average { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
        
        .summary-info h3 {
            font-size: 28px;
            margin-bottom: 5px;
            color: #333;
        }
        .summary-info p {
            color: #666;
            font-size: 14px;
        }
        
        .filters {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .filter-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .filter-header h3 {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #333;
        }
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        input, select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #004d40;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-primary { background: #4caf50; color: white; }
        .btn-primary:hover { background: #45a049; transform: translateY(-2px); }
        .btn-secondary { background: #6c757d; color: white; margin-left: 10px; }
        .btn-secondary:hover { background: #5a6268; }
        .btn-export { background: #2196f3; color: white; margin-left: 10px; }
        .btn-export:hover { background: #1976d2; }
        
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 25px;
        }
        .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 20px 25px;
            border-bottom: 2px solid #dee2e6;
        }
        .card-header h2 {
            margin: 0;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e1e1e1;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
            font-size: 13px;
            text-transform: uppercase;
        }
        tbody tr:hover {
            background: #f8f9fa;
        }
        
        .product-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        /* PERBAIKAN: Hapus style kategori-badge dan badge terkait */
        /* .kategori-badge {
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
        }
        .kategori-hot { background: #ffe0e0; color: #c62828; }
        .kategori-cold { background: #e0f2ff; color: #0277bd; }
        .kategori-food { background: #fff3cd; color: #856404; } */
        
        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        .no-data i {
            font-size: 64px;
            display: block;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        @media print {
            .header, .filters, .btn { display: none; }
            body { background: white; }
            .card { box-shadow: none; }
        }
        
        @media (max-width: 768px) {
            .summary-grid {
                grid-template-columns: 1fr;
            }
            .filter-grid {
                grid-template-columns: 1fr;
            }
            table {
                font-size: 12px;
            }
            th, td {
                padding: 10px 5px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <a href="admin_dashboard.php">
                <i class="ri-arrow-left-line"></i> Dashboard
            </a>
            <span style="font-size: 20px; font-weight: bold; margin-left: 15px;">
                <i class="ri-file-chart-line"></i> Laporan Penjualan
            </span>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="admin_produk.php">
                <i class="ri-cup-line"></i> Produk
            </a>
            <a href="admin_pesanan.php">
                <i class="ri-shopping-bag-line"></i> Pesanan
            </a>
            <a href="admin_logout.php">
                <i class="ri-logout-box-line"></i> Logout
            </a>
        </div>
    </div>

    <div class="container">
        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-icon revenue">
                    <i class="ri-money-dollar-circle-line"></i>
                </div>
                <div class="summary-info">
                    <h3>Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></h3>
                    <p>Total Pendapatan</p>
                </div>
            </div>
            
            <div class="summary-card">
                <div class="summary-icon transactions">
                    <i class="ri-shopping-cart-line"></i>
                </div>
                <div class="summary-info">
                    <h3><?= $total_transaksi ?></h3>
                    <p>Total Transaksi</p>
                </div>
            </div>
            
            <div class="summary-card">
                <div class="summary-icon items">
                    <i class="ri-stack-line"></i>
                </div>
                <div class="summary-info">
                    <h3><?= $total_item ?></h3>
                    <p>Item Terjual</p>
                </div>
            </div>
            
            <div class="summary-card">
                <div class="summary-icon average">
                    <i class="ri-line-chart-line"></i>
                </div>
                <div class="summary-info">
                    <h3>Rp <?= number_format($rata_rata, 0, ',', '.') ?></h3>
                    <p>Rata-rata per Transaksi</p>
                </div>
            </div>
        </div>

        <form class="filters" method="GET">
            <div class="filter-header">
                <h3>
                    <i class="ri-filter-3-line"></i>
                    Filter Laporan
                </h3>
                <div>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-search-line"></i> Tampilkan Laporan
                    </button>
                    <a href="admin_laporan.php" class="btn btn-secondary">
                        <i class="ri-refresh-line"></i> Reset
                    </a>
                    <button type="button" onclick="window.print()" class="btn btn-export">
                        <i class="ri-printer-line"></i> Cetak
                    </button>
                </div>
            </div>
            
            <div class="filter-grid">
                <div class="filter-group">
                    <label>Periode</label>
                    <select name="periode" id="periode" onchange="toggleCustomDate()">
                        <option value="hari_ini" <?= $filter_periode=='hari_ini'?'selected':'' ?>>Hari Ini</option>
                        <option value="minggu_ini" <?= $filter_periode=='minggu_ini'?'selected':'' ?>>Minggu Ini</option>
                        <option value="bulan_ini" <?= $filter_periode=='bulan_ini'?'selected':'' ?>>Bulan Ini</option>
                        <option value="tahun_ini" <?= $filter_periode=='tahun_ini'?'selected':'' ?>>Tahun Ini</option>
                        <option value="custom" <?= $filter_periode=='custom'?'selected':'' ?>>Pilih Tanggal</option>
                    </select>
                </div>
                
                <div class="filter-group" id="tanggal_mulai_group">
                    <label>Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" value="<?= $tanggal_mulai ?>">
                </div>
                
                <div class="filter-group" id="tanggal_akhir_group">
                    <label>Tanggal Akhir</label>
                    <input type="date" name="tanggal_akhir" value="<?= $tanggal_akhir ?>">
                </div>
            </div>
        </form>

        <div class="card">
            <div class="card-header">
                <h2>
                    <i class="ri-calendar-line"></i>
                    Laporan Penjualan Harian
                </h2>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Total Transaksi</th>
                        <th>Item Terjual</th>
                        <th>Total Pelanggan</th>
                        <th>Total Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($laporan) > 0): ?>
                        <?php 
                        $grand_total = 0;
                        while($row = mysqli_fetch_assoc($laporan)): 
                            $grand_total += $row['total_pendapatan'];
                        ?>
                        <tr>
                            <td><strong><?= date('d/m/Y', strtotime($row['tanggal'])) ?></strong></td>
                            <td><?= $row['total_transaksi'] ?> transaksi</td>
                            <td><?= $row['total_item_terjual'] ?> item</td>
                            <td><?= $row['total_pelanggan'] ?> pelanggan</td>
                            <td><strong style="color: #28a745;">Rp <?= number_format($row['total_pendapatan'], 0, ',', '.') ?></strong></td>
                        </tr>
                        <?php endwhile; ?>
                        <tr style="background: #f8f9fa; font-weight: bold;">
                            <td colspan="4" style="text-align: right;">GRAND TOTAL:</td>
                            <td><strong style="color: #28a745; font-size: 18px;">Rp <?= number_format($grand_total, 0, ',', '.') ?></strong></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="no-data">
                                <i class="ri-file-list-line"></i>
                                <p>Tidak ada data penjualan pada periode ini</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>
                    <i class="ri-fire-line"></i>
                    Top 10 Produk Terlaris
                </h2>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Ranking</th>
                        <th>Produk</th>
                        <th>Jumlah Terjual</th>
                        <th>Total Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($produk_terlaris) > 0): ?>
                        <?php 
                        $rank = 1;
                        while($produk = mysqli_fetch_assoc($produk_terlaris)): 
                        ?>
                        <tr>
                            <td>
                                <strong style="font-size: 18px; color: #666;">#<?= $rank ?></strong>
                            </td>
                            <td>
                                <div class="product-info">
                                    <img src="assets/img/<?= htmlspecialchars($produk['foto']) ?>" 
                                         alt="<?= htmlspecialchars($produk['nama']) ?>" 
                                         class="product-image">
                                    <strong><?= htmlspecialchars($produk['nama']) ?></strong>
                                </div>
                            </td>
                            <td><strong><?= $produk['total_terjual'] ?></strong> item</td>
                            <td><strong style="color: #28a745;">Rp <?= number_format($produk['total_pendapatan'], 0, ',', '.') ?></strong></td>
                        </tr>
                        <?php 
                        $rank++;
                        endwhile; 
                        ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="no-data"> <i class="ri-emotion-sad-line"></i>
                                <p>Belum ada produk terjual pada periode ini</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function toggleCustomDate() {
            const periode = document.getElementById('periode').value;
            const mulaiGroup = document.getElementById('tanggal_mulai_group');
            const akhirGroup = document.getElementById('tanggal_akhir_group');
            
            if (periode === 'custom') {
                mulaiGroup.style.display = 'block';
                akhirGroup.style.display = 'block';
            } else {
                mulaiGroup.style.display = 'none';
                akhirGroup.style.display = 'none';
            }
        }
        
        // Initialize on page load
        toggleCustomDate();
    </script>
</body>
</html>