<?php
session_start();
include "koneksi.php";

// TAMBAHAN: Pengecekan Koneksi Database
if (mysqli_connect_errno()) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Get statistics with NULL handling
$stats = [];
$stats['total_produk'] = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as count FROM tb_menu"))['count'] ?? 0;
$stats['total_pesanan'] = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as count FROM tb_pesanan"))['count'] ?? 0;
$stats['total_member'] = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as count FROM tb_member"))['count'] ?? 0;

// Get today's orders
$today = date('Y-m-d');
$stats['pesanan_hari_ini'] = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as count FROM tb_pesanan WHERE DATE(tanggal) = '$today'"))['count'] ?? 0;

// Get total revenue today with COALESCE (Sudah Bagus)
$revenue_query = mysqli_query($koneksi, "
    SELECT COALESCE(SUM(d.subtotal), 0) as total 
    FROM tb_detailpembayaran d 
    JOIN tb_pesanan p ON d.id_pesanan = p.id_pesanan 
    WHERE DATE(p.tanggal) = '$today'
");
$revenue_result = mysqli_fetch_assoc($revenue_query);
$stats['revenue_hari_ini'] = $revenue_result['total'] ?? 0;

// Get recent orders with COALESCE (Sudah Bagus)
$recent_orders = mysqli_query($koneksi, "
    SELECT p.id_pesanan, p.tanggal, b.nama_pembeli, b.nomor_pembeli,
            COALESCE((SELECT SUM(subtotal) FROM tb_detailpembayaran WHERE id_pesanan = p.id_pesanan), 0) as total
    FROM tb_pesanan p
    JOIN tb_pembeli b ON p.id_pembeli = b.id_pembeli
    ORDER BY p.tanggal DESC
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - StarCoffee</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            color: #333;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #004d40 0%, #00695c 100%);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .admin-header h1 {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .admin-nav {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        .admin-nav a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .admin-nav a:hover {
            background: rgba(255,255,255,0.1);
        }
        
        /* Style khusus untuk tombol Buat Pesanan di header */
        .admin-nav a.btn-create-order {
            background: rgba(76, 175, 80, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.5);
            font-weight: 600;
        }
        
        .admin-nav a.btn-create-order:hover {
            background: rgba(76, 175, 80, 0.5);
            border-color: white;
        }
        
        .admin-user {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .admin-user a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(244, 67, 54, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.4);
            font-weight: 600;
            margin-left: 1rem;
        }
        
        .admin-user a:hover {
            background: rgba(244, 67, 54, 0.4);
            border-color: white;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(244, 67, 54, 0.3);
        }
        
        .admin-user a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            font-weight: 600;
        }
        
        .admin-user a:hover {
            background: rgba(255, 255, 255, 0.25);
            border-color: white;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            padding: 0.5rem;
            border-radius: 10px;
            color: white;
        }
        
        .stat-icon.products { background: #4caf50; }
        .stat-icon.orders { background: #2196f3; }
        .stat-icon.members { background: #ff9800; }
        .stat-icon.revenue { background: #9c27b0; }
        .stat-icon.today { background: #f44336; }
        
        .stat-info h3 {
            font-size: 2rem;
            margin-bottom: 0.25rem;
        }
        
        .stat-info p {
            color: #666;
            font-size: 0.9rem;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .card-header {
            background: #f8f9fa;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header h3 {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .recent-orders table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .recent-orders th,
        .recent-orders td {
            text-align: left;
            padding: 0.75rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .recent-orders th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }
        
        .quick-actions {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .action-btn {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
        }
        
        .action-btn:hover {
            border-color: #007bff;
            background: #f8f9fa;
            transform: translateY(-1px);
        }
        
        .action-btn i {
            font-size: 1.5rem;
            padding: 0.5rem;
            border-radius: 8px;
            color: white;
        }
        
        .action-btn.products i { background: #4caf50; }
        .action-btn.orders i { background: #2196f3; }
        .action-btn.members i { background: #ff9800; }
        .action-btn.settings i { background: #6c757d; }
        .action-btn.reports i { background: #00bcd4; }
        
        /* Style khusus untuk tombol Buat Pesanan di quick actions */
        .action-btn.create-order {
            background: linear-gradient(135deg, #4caf50 0%, #45a049 100%);
            border-color: #4caf50;
            color: white;
        }
        
        .action-btn.create-order:hover {
            border-color: #45a049;
            background: linear-gradient(135deg, #45a049 0%, #388e3c 100%);
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
        }
        
        .action-btn.create-order i {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .action-btn.create-order strong,
        .action-btn.create-order p {
            color: white;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        @media (max-width: 768px) {
            .admin-header {
                flex-direction: column;
                gap: 1rem;
            }
            
            .admin-nav {
                flex-wrap: wrap;
            }
            
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .container {
                padding: 0 0.5rem;
            }
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <h1>
            <i class="ri-dashboard-3-line"></i>
            Admin Dashboard
        </h1>
        
        <nav class="admin-nav">
            <a href="admin_produk.php">
                <i class="ri-product-hunt-line"></i> Produk
            </a>
            <a href="admin_pesanan.php">
                <i class="ri-shopping-bag-line"></i> Pesanan
            </a>
            <!-- TAMBAHAN BARU: Link Buat Pesanan di Header -->
            <a href="admin_buat_pesanan.php" class="btn-create-order">
                <i class="ri-add-circle-line"></i> Buat Pesanan
            </a>
            <!-- AKHIR TAMBAHAN -->
            <a href="admin_member.php">
                <i class="ri-group-line"></i> Member
            </a>
            <a href="admin_laporan.php">
                <i class="ri-bar-chart-line"></i> Laporan
            </a>
            <a href="index.php" target="_blank">
                <i class="ri-external-link-line"></i> Lihat Website
            </a>
        </nav>
        
        <div class="admin-user">
            <i class="ri-user-3-line"></i>
            <span><?= htmlspecialchars($_SESSION['admin_nama'] ?? $_SESSION['admin_username']) ?></span>
            <a href="admin_logout.php">
                <i class="ri-logout-box-line"></i> Logout
            </a>
        </div>
    </header>

    <div class="container">
        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-success">
                <i class="ri-checkbox-circle-line"></i>
                <?= htmlspecialchars($_GET['message']) ?>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon products">
                    <i class="ri-cup-line"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $stats['total_produk'] ?></h3>
                    <p>Total Produk</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon orders">
                    <i class="ri-shopping-bag-line"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $stats['total_pesanan'] ?></h3>
                    <p>Total Pesanan</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon members">
                    <i class="ri-group-line"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $stats['total_member'] ?></h3>
                    <p>Total Member</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon today">
                    <i class="ri-calendar-today-line"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $stats['pesanan_hari_ini'] ?></h3>
                    <p>Pesanan Hari Ini</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon revenue">
                    <i class="ri-money-dollar-circle-line"></i>
                </div>
                <div class="stat-info">
                    <h3>Rp <?= number_format($stats['revenue_hari_ini'], 0, ',', '.') ?></h3>
                    <p>Revenue Hari Ini</p>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="card">
                <div class="card-header">
                    <h3>
                        <i class="ri-time-line"></i>
                        Pesanan Terbaru
                    </h3>
                    <a href="admin_pesanan.php" style="color: #007bff; text-decoration: none;">
                        Lihat Semua <i class="ri-arrow-right-line"></i>
                    </a>
                </div>
                <div class="card-body">
                    <div class="recent-orders">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Pelanggan</th>
                                    <th>No HP</th>
                                    <th>Total</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($recent_orders) > 0): ?>
                                    <?php while ($order = mysqli_fetch_assoc($recent_orders)): 
                                        // Handle NULL values
                                        $id_pesanan = $order['id_pesanan'] ?? 0;
                                        $nama_pembeli = $order['nama_pembeli'] ?? '-';
                                        $nomor_pembeli = $order['nomor_pembeli'] ?? '-';
                                        $total = $order['total'] ?? 0;
                                        $tanggal = $order['tanggal'] ?? date('Y-m-d H:i:s');
                                    ?>
                                    <tr>
                                        <td>#<?= str_pad($id_pesanan, 4, '0', STR_PAD_LEFT) ?></td>
                                        <td><?= htmlspecialchars($nama_pembeli) ?></td>
                                        <td><?= htmlspecialchars($nomor_pembeli) ?></td>
                                        <td>Rp <?= number_format($total, 0, ',', '.') ?></td>
                                        <td><?= date('d/m H:i', strtotime($tanggal)) ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center; padding: 2rem;">
                                            Belum ada pesanan
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>
                        <i class="ri-flashlight-line"></i>
                        Aksi Cepat
                    </h3>
                </div>
                <div class="card-body">
                    <div class="quick-actions">
                        <!-- TAMBAHAN BARU: Link Buat Pesanan di Quick Actions (PALING ATAS) -->
                        <a href="admin_buat_pesanan.php" class="action-btn create-order">
                            <i class="ri-add-box-line"></i>
                            <div>
                                <strong>🎯 Buat Pesanan Baru</strong>
                                <p>Member & Non-member (Diskon 10%)</p>
                            </div>
                        </a>
                        <!-- AKHIR TAMBAHAN -->
                        
                        <a href="admin_produk.php" class="action-btn products">
                            <i class="ri-add-circle-line"></i>
                            <div>
                                <strong>Kelola Produk</strong>
                                <p>Tambah & edit menu coffee</p>
                            </div>
                        </a>
                        
                        <a href="admin_pesanan.php" class="action-btn orders">
                            <i class="ri-eye-line"></i>
                            <div>
                                <strong>Lihat Pesanan</strong>
                                <p>Monitor pesanan masuk</p>
                            </div>
                        </a>
                        
                        <a href="admin_laporan.php" class="action-btn reports">
                            <i class="ri-bar-chart-line"></i>
                            <div>
                                <strong>Laporan Penjualan</strong>
                                <p>Analisis data & performa</p>
                            </div>
                        </a>
                        
                        <a href="admin_member.php" class="action-btn members">
                            <i class="ri-user-settings-line"></i>
                            <div>
                                <strong>Kelola Member</strong>
                                <p>Manajemen data member VIP</p>
                            </div>
                        </a>
                        
                        <a href="index.php" target="_blank" class="action-btn settings">
                            <i class="ri-external-link-line"></i>
                            <div>
                                <strong>Lihat Website</strong>
                                <p>Buka website coffee shop</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>