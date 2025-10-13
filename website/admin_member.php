<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Handle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $action = $_GET['action'];
    
    if ($action == 'activate') {
        mysqli_query($koneksi, "UPDATE tb_member SET status = 'active' WHERE id_member = $id");
        header("Location: admin_member.php?message=Member berhasil diaktifkan");
    } elseif ($action == 'deactivate') {
        mysqli_query($koneksi, "UPDATE tb_member SET status = 'inactive' WHERE id_member = $id");
        header("Location: admin_member.php?message=Member berhasil dinonaktifkan");
    } elseif ($action == 'delete') {
        mysqli_query($koneksi, "DELETE FROM tb_member WHERE id_member = $id");
        header("Location: admin_member.php?message=Member berhasil dihapus");
    }
    exit();
}

// Filter
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_search = isset($_GET['search']) ? $_GET['search'] : '';

$where = [];
if ($filter_status) $where[] = "status = '$filter_status'";
if ($filter_search) $where[] = "(nama LIKE '%$filter_search%' OR email LIKE '%$filter_search%' OR telepon LIKE '%$filter_search%')";

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$members = mysqli_query($koneksi, "SELECT * FROM tb_member $where_clause ORDER BY tanggal_daftar DESC");

// Stats
$total = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM tb_member"))['c'];
$active = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM tb_member WHERE status='active'"))['c'];
$inactive = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM tb_member WHERE status='inactive'"))['c'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Member - Admin StarCoffee</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial; background: #f5f7fa; }
        
        .header { 
            background: linear-gradient(135deg, #004d40 0%, #00695c 100%);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header a { color: white; text-decoration: none; padding: 8px 15px; border-radius: 5px; transition: background 0.3s; }
        .header a:hover { background: rgba(255,255,255,0.1); }
        
        .container { max-width: 1400px; margin: 30px auto; padding: 0 20px; }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
        }
        .stat-icon.total { background: #2196f3; }
        .stat-icon.active { background: #4caf50; }
        .stat-icon.inactive { background: #6c757d; }
        .stat-info h3 { font-size: 32px; margin-bottom: 5px; }
        .stat-info p { color: #666; font-size: 14px; }
        
        .filters {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: end;
        }
        .filter-group { flex: 1; min-width: 200px; }
        .filter-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #333; }
        .filter-group select,
        .filter-group input { width: 100%; padding: 10px; border: 2px solid #e1e1e1; border-radius: 5px; }
        
        .btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: 600; text-decoration: none; display: inline-block; }
        .btn-primary { background: #2196f3; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-sm { padding: 6px 12px; font-size: 13px; }
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .card-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 2px solid #e1e1e1;
        }
        .card-header h2 { margin: 0; color: #333; }
        
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
        }
        tbody tr:hover { background: #f8f9fa; }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        
        .message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <a href="admin_dashboard.php"><i class="ri-arrow-left-line"></i> Dashboard</a>
            <span style="font-size: 20px; font-weight: bold;">Kelola Member VIP</span>
        </div>
        <a href="admin_logout.php"><i class="ri-logout-box-line"></i> Logout</a>
    </div>

    <div class="container">
        <?php if(isset($_GET['message'])): ?>
            <div class="message">
                <i class="ri-checkbox-circle-line"></i> <?= htmlspecialchars($_GET['message']) ?>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon total"><i class="ri-group-line"></i></div>
                <div class="stat-info">
                    <h3><?= $total ?></h3>
                    <p>Total Member</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon active"><i class="ri-user-check-line"></i></div>
                <div class="stat-info">
                    <h3><?= $active ?></h3>
                    <p>Member Aktif</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon inactive"><i class="ri-user-unfollow-line"></i></div>
                <div class="stat-info">
                    <h3><?= $inactive ?></h3>
                    <p>Member Nonaktif</p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <form class="filters" method="GET">
            <div class="filter-group">
                <label>Status</label>
                <select name="status">
                    <option value="">Semua Status</option>
                    <option value="active" <?= $filter_status=='active'?'selected':'' ?>>Aktif</option>
                    <option value="inactive" <?= $filter_status=='inactive'?'selected':'' ?>>Nonaktif</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Cari</label>
                <input type="text" name="search" value="<?= htmlspecialchars($filter_search) ?>" placeholder="Nama, email, atau telepon">
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="ri-search-line"></i> Cari
            </button>
            <a href="admin_member.php" class="btn btn-secondary">Reset</a>
        </form>

        <!-- Table -->
        <div class="card">
            <div class="card-header">
                <h2><i class="ri-vip-crown-line"></i> Daftar Member VIP</h2>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Telepon</th>
                        <th>Tanggal Daftar</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($members) > 0): ?>
                        <?php while($m = mysqli_fetch_assoc($members)): ?>
                        <tr>
                            <td><strong>#<?= str_pad($m['id_member'], 4, '0', STR_PAD_LEFT) ?></strong></td>
                            <td><strong><?= htmlspecialchars($m['nama']) ?></strong></td>
                            <td><?= htmlspecialchars($m['email']) ?></td>
                            <td><?= htmlspecialchars($m['telepon']) ?></td>
                            <td><?= date('d/m/Y', strtotime($m['tanggal_daftar'])) ?></td>
                            <td>
                                <span class="status-badge status-<?= $m['status'] ?>">
                                    <?= ucfirst($m['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if($m['status'] == 'active'): ?>
                                    <a href="?action=deactivate&id=<?= $m['id_member'] ?>" class="btn btn-warning btn-sm">
                                        <i class="ri-close-circle-line"></i> Nonaktifkan
                                    </a>
                                <?php else: ?>
                                    <a href="?action=activate&id=<?= $m['id_member'] ?>" class="btn btn-success btn-sm">
                                        <i class="ri-checkbox-circle-line"></i> Aktifkan
                                    </a>
                                <?php endif; ?>
                                <a href="?action=delete&id=<?= $m['id_member'] ?>" class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Yakin ingin menghapus member ini?')">
                                    <i class="ri-delete-bin-line"></i> Hapus
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #999;">
                                <i class="ri-user-line" style="font-size: 48px; display: block; margin-bottom: 10px;"></i>
                                Belum ada member
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>