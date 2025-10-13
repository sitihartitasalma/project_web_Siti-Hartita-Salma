<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Cek kolom pembayaran
$columns_check = mysqli_query($koneksi, "SHOW COLUMNS FROM tb_pesanan LIKE 'status_pembayaran'");
$has_payment_status = mysqli_num_rows($columns_check) > 0;

$method_check = mysqli_query($koneksi, "SHOW COLUMNS FROM tb_pesanan LIKE 'metode_pembayaran'");
$has_payment_method = mysqli_num_rows($method_check) > 0;

// Handle update status
if (isset($_POST['update_status'])) {
    $id_pesanan = (int) $_POST['id_pesanan'];
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    
    if ($has_payment_status) {
        $status_bayar = mysqli_real_escape_string($koneksi, $_POST['status_bayar']);
        
        if ($has_payment_method) {
            if ($status_bayar == 'paid' && !empty($_POST['metode_pembayaran'])) {
                $metode = mysqli_real_escape_string($koneksi, $_POST['metode_pembayaran']);
                mysqli_query($koneksi, "UPDATE tb_pesanan SET status_pesanan = '$status', status_pembayaran = '$status_bayar', metode_pembayaran = '$metode' WHERE id_pesanan = $id_pesanan");
            } else {
                mysqli_query($koneksi, "UPDATE tb_pesanan SET status_pesanan = '$status', status_pembayaran = '$status_bayar', metode_pembayaran = NULL WHERE id_pesanan = $id_pesanan");
            }
        } else {
            mysqli_query($koneksi, "UPDATE tb_pesanan SET status_pesanan = '$status', status_pembayaran = '$status_bayar' WHERE id_pesanan = $id_pesanan");
        }
    } else {
        mysqli_query($koneksi, "UPDATE tb_pesanan SET status_pesanan = '$status' WHERE id_pesanan = $id_pesanan");
    }
    
    header("Location: admin_pesanan.php?message=Status berhasil diupdate");
    exit();
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    mysqli_query($koneksi, "DELETE FROM tb_detailpembayaran WHERE id_pesanan = $id");
    mysqli_query($koneksi, "DELETE FROM tb_pesanan WHERE id_pesanan = $id");
    header("Location: admin_pesanan.php?message=Pesanan berhasil dihapus");
    exit();
}

// ✅ PERUBAHAN: Filter tidak termasuk 'pending'
$filter_status = isset($_GET['status']) && trim($_GET['status']) != '' ? mysqli_real_escape_string($koneksi, trim($_GET['status'])) : '';
$filter_pembayaran = isset($_GET['pembayaran']) && trim($_GET['pembayaran']) != '' ? mysqli_real_escape_string($koneksi, trim($_GET['pembayaran'])) : '';
$filter_tanggal = isset($_GET['tanggal']) && trim($_GET['tanggal']) != '' ? mysqli_real_escape_string($koneksi, trim($_GET['tanggal'])) : '';

$where = [];

// ✅ PERUBAHAN: SELALU tambahkan kondisi untuk menghapus pesanan pending dari list
$where[] = "p.status_pesanan != 'pending'";

if ($filter_status != '') {
    $where[] = "p.status_pesanan = '" . $filter_status . "'";
}
if ($filter_pembayaran != '' && $has_payment_status) {
    $where[] = "p.status_pembayaran = '" . $filter_pembayaran . "'";
}
if ($filter_tanggal != '') {
    $where[] = "DATE(p.tanggal) = '" . $filter_tanggal . "'";
}

$where_clause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

// Get pesanan dengan filter
$query = "
    SELECT p.*, b.nama_pembeli, b.nomor_pembeli, b.alamat" . 
    ($has_payment_status ? ", p.status_pembayaran" : "") . 
    ($has_payment_method ? ", p.metode_pembayaran" : "") . ",
           (SELECT SUM(subtotal) FROM tb_detailpembayaran WHERE id_pesanan = p.id_pesanan) as total
    FROM tb_pesanan p
    JOIN tb_pembeli b ON p.id_pembeli = b.id_pembeli
    $where_clause
    ORDER BY p.tanggal DESC
";

$pesanan = mysqli_query($koneksi, $query);

// Hitung statistik DENGAN filter yang sama
// Total pesanan (TIDAK termasuk pending)
$total_where = "WHERE p.status_pesanan != 'pending'";
if ($filter_status != '' || $filter_pembayaran != '' || $filter_tanggal != '') {
    $total_where = $where_clause;
}

$total_query = "SELECT COUNT(*) as c FROM tb_pesanan p $total_where";
$total_result = mysqli_query($koneksi, $total_query);
$total_pesanan = mysqli_fetch_assoc($total_result)['c'] ?? 0;

// ✅ PERUBAHAN: Hitung pending untuk info saja
$pending_count = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM tb_pesanan p WHERE p.status_pesanan = 'pending'"))['c'] ?? 0;

// Confirmed
$confirmed = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM tb_pesanan p WHERE p.status_pesanan = 'confirmed'"))['c'] ?? 0;

// Preparing
$preparing = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM tb_pesanan p WHERE p.status_pesanan = 'preparing'"))['c'] ?? 0;

// Ready
$ready = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM tb_pesanan p WHERE p.status_pesanan = 'ready'"))['c'] ?? 0;

// Selesai/Delivered
$selesai = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM tb_pesanan p WHERE p.status_pesanan = 'delivered'"))['c'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - Admin StarCoffee</title>
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
        .stat-icon.pending { background: #ff9800; }
        .stat-icon.confirmed { background: #4caf50; }
        .stat-icon.preparing { background: #ff5722; }
        .stat-icon.ready { background: #00bcd4; }
        .stat-icon.selesai { background: #4caf50; }
        .stat-info h3 { font-size: 32px; margin-bottom: 5px; }
        .stat-info p { color: #666; font-size: 14px; }
        
        .info-box {
            background: #fff3cd;
            color: #856404;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #ffc107;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
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
        .btn-info { background: #17a2b8; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-sm { padding: 6px 12px; font-size: 13px; }
        .btn:hover { opacity: 0.9; }
        
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
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #cce5ff; color: #004085; }
        .status-preparing { background: #ffe5cc; color: #bf360c; }
        .status-ready { background: #d1ecf1; color: #0c5460; }
        .status-delivered { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .status-paid { background: #d4edda; color: #155724; }
        .status-unpaid { background: #f8d7da; color: #721c24; }
        
        .payment-method {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 600;
            margin-top: 5px;
        }
        .method-gopay { background: #e8f5e9; color: #1b5e20; border: 1px solid #4caf50; }
        .method-dana { background: #e3f2fd; color: #0d47a1; border: 1px solid #2196f3; }
        .method-ovo { background: #f3e5f5; color: #4a148c; border: 1px solid #9c27b0; }
        .method-qris { background: #fce4ec; color: #880e4f; border: 1px solid #e91e63; }
        .method-transfer_bank { background: #e0f2f1; color: #004d40; border: 1px solid #009688; }
        .method-cash { background: #f5f5f5; color: #333; border: 1px solid #666; }
        
        .message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }
        .modal-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 2px solid #e1e1e1;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-body { padding: 20px; }
        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover { color: black; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-group select { width: 100%; padding: 10px; border: 2px solid #e1e1e1; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <a href="admin_dashboard.php"><i class="ri-arrow-left-line"></i> Dashboard</a>
            <span style="font-size: 20px; font-weight: bold;">Kelola Pesanan</span>
        </div>
        <a href="admin_logout.php"><i class="ri-logout-box-line"></i> Logout</a>
    </div>

    <div class="container">
        <?php if(isset($_GET['message'])): ?>
            <div class="message">
                <i class="ri-checkbox-circle-line"></i> <?= htmlspecialchars($_GET['message']) ?>
            </div>
        <?php endif; ?>

        <!-- ✅ INFO BOX PENDING -->
        <div class="info-box">
            <i class="ri-information-line"></i>
            <div>
                <strong>ℹ️ Pesanan Pending</strong>
                <br>
                <small>Terdapat <strong><?= $pending_count ?></strong> pesanan dengan status PENDING. Pesanan pending otomatis dibuat saat pelanggan memesan dan tidak ditampilkan di halaman ini. Admin harus mengubah statusnya menjadi "Confirmed" atau status lain untuk mengelolanya.</small>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon total"><i class="ri-shopping-bag-line"></i></div>
                <div class="stat-info">
                    <h3><?= $total_pesanan ?></h3>
                    <p>Total Pesanan (Tidak Pending)</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon pending"><i class="ri-time-line"></i></div>
                <div class="stat-info">
                    <h3><?= $pending_count ?></h3>
                    <p>Pesanan Pending</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon confirmed"><i class="ri-checkbox-circle-line"></i></div>
                <div class="stat-info">
                    <h3><?= $confirmed ?></h3>
                    <p>Pesanan Confirmed</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon preparing"><i class="ri-fire-line"></i></div>
                <div class="stat-info">
                    <h3><?= $preparing ?></h3>
                    <p>Sedang Dipersiapkan</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon ready"><i class="ri-check-double-line"></i></div>
                <div class="stat-info">
                    <h3><?= $ready ?></h3>
                    <p>Siap Diambil</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon selesai"><i class="ri-check-line"></i></div>
                <div class="stat-info">
                    <h3><?= $selesai ?></h3>
                    <p>Selesai</p>
                </div>
            </div>
        </div>

        <!-- Filter Form -->
        <form class="filters" method="GET" action="admin_pesanan.php">
            <div class="filter-group">
                <label>Status Pesanan</label>
                <select name="status">
                    <option value="">Semua Status</option>
                    <option value="confirmed" <?= $filter_status=='confirmed'?'selected':'' ?>>Confirmed</option>
                    <option value="preparing" <?= $filter_status=='preparing'?'selected':'' ?>>Preparing</option>
                    <option value="ready" <?= $filter_status=='ready'?'selected':'' ?>>Ready</option>
                    <option value="delivered" <?= $filter_status=='delivered'?'selected':'' ?>>Delivered</option>
                    <option value="cancelled" <?= $filter_status=='cancelled'?'selected':'' ?>>Cancelled</option>
                </select>
            </div>
            <?php if ($has_payment_status): ?>
            <div class="filter-group">
                <label>Status Pembayaran</label>
                <select name="pembayaran">
                    <option value="">Semua</option>
                    <option value="paid" <?= $filter_pembayaran=='paid'?'selected':'' ?>>Sudah Dibayar</option>
                    <option value="unpaid" <?= $filter_pembayaran=='unpaid'?'selected':'' ?>>Belum Dibayar</option>
                </select>
            </div>
            <?php endif; ?>
            <div class="filter-group">
                <label>Tanggal</label>
                <input type="date" name="tanggal" value="<?= htmlspecialchars($filter_tanggal) ?>">
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="ri-filter-line"></i> Filter
            </button>
            <a href="admin_pesanan.php" class="btn btn-secondary">Reset</a>
        </form>

        <!-- Table -->
        <div class="card">
            <div class="card-header">
                <h2><i class="ri-list-check"></i> Daftar Pesanan (<?= mysqli_num_rows($pesanan) ?> data)</h2>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pelanggan</th>
                        <th>Total</th>
                        <th>Status Pesanan</th>
                        <?php if ($has_payment_status): ?>
                        <th>Pembayaran</th>
                        <?php endif; ?>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($pesanan) > 0): ?>
                        <?php while($p = mysqli_fetch_assoc($pesanan)): 
                            $id_pesanan = $p['id_pesanan'] ?? 0;
                            $nama_pembeli = $p['nama_pembeli'] ?? '-';
                            $nomor_pembeli = $p['nomor_pembeli'] ?? '-';
                            $total = $p['total'] ?? 0;
                            $status_pesanan = $p['status_pesanan'] ?? 'pending';
                            $status_pembayaran = $p['status_pembayaran'] ?? 'unpaid';
                            $metode_pembayaran = $p['metode_pembayaran'] ?? null;
                            $tanggal = $p['tanggal'] ?? date('Y-m-d H:i:s');
                            
                            $metode_display = [
                                'gopay' => '🟢 GoPay',
                                'dana' => '📵 DANA',
                                'ovo' => '🟣 OVO',
                                'qris' => '💳 QRIS',
                                'transfer_bank' => '🏦 Transfer Bank',
                                'cash' => '💵 Tunai'
                            ];
                        ?>
                        <tr>
                            <td><strong>#<?= str_pad($id_pesanan, 4, '0', STR_PAD_LEFT) ?></strong></td>
                            <td>
                                <strong><?= htmlspecialchars($nama_pembeli) ?></strong><br>
                                <small><?= htmlspecialchars($nomor_pembeli) ?></small>
                            </td>
                            <td><strong>Rp <?= number_format($total, 0, ',', '.') ?></strong></td>
                            <td>
                                <span class="status-badge status-<?= $status_pesanan ?>">
                                    <?= ucfirst($status_pesanan) ?>
                                </span>
                            </td>
                            <?php if ($has_payment_status): ?>
                            <td>
                                <span class="status-badge status-<?= $status_pembayaran ?>">
                                    <?= $status_pembayaran=='paid'?'Sudah Dibayar':'Belum Dibayar' ?>
                                </span>
                                <?php if ($status_pembayaran == 'paid' && $metode_pembayaran): ?>
                                <br>
                                <span class="payment-method method-<?= $metode_pembayaran ?>">
                                    <?= $metode_display[$metode_pembayaran] ?? ucfirst($metode_pembayaran) ?>
                                </span>
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
                            <td><?= date('d/m/Y H:i', strtotime($tanggal)) ?></td>
                            <td>
                                <a href="print_struk.php?id=<?= $id_pesanan ?>" target="_blank" class="btn btn-success btn-sm">
                                    <i class="ri-printer-line"></i> Struk
                                </a>
                                <button class="btn btn-info btn-sm" onclick="updateStatus(<?= $id_pesanan ?>, '<?= $status_pesanan ?>', '<?= $status_pembayaran ?>', '<?= $metode_pembayaran ?>')">
                                    <i class="ri-edit-line"></i> Update
                                </button>
                                <a href="?delete=<?= $id_pesanan ?>" class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Yakin ingin menghapus pesanan ini?')">
                                    <i class="ri-delete-bin-line"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?= $has_payment_status ? '7' : '6' ?>" style="text-align: center; padding: 40px; color: #999;">
                                <i class="ri-inbox-line" style="font-size: 48px; display: block; margin-bottom: 10px;"></i>
                                Tidak ada pesanan dengan status non-pending. Pesanan pending akan ditampilkan di statistik atas.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Update Status -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Update Status</h3>
                <span class="close" onclick="closeModal('statusModal')">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="id_pesanan" id="status_id_pesanan">
                    
                    <div class="form-group">
                        <label>Status Pesanan</label>
                        <select name="status" id="status_pesanan">
                            <option value="confirmed">Confirmed</option>
                            <option value="preparing">Preparing</option>
                            <option value="ready">Ready</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    
                    <?php if ($has_payment_status): ?>
                    <div class="form-group">
                        <label>Status Pembayaran</label>
                        <select name="status_bayar" id="status_bayar" onchange="togglePaymentMethod()">
                            <option value="unpaid">Belum Dibayar</option>
                            <option value="paid">Sudah Dibayar</option>
                        </select>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($has_payment_method): ?>
                    <div class="form-group" id="payment_method_group" style="display: none;">
                        <label>Metode Pembayaran <span style="color: red;">*</span></label>
                        <select name="metode_pembayaran" id="metode_pembayaran" required>
                            <option value="">-- Pilih Metode Pembayaran --</option>
                            <option value="gopay">🟢 GoPay</option>
                            <option value="dana">📵 DANA</option>
                            <option value="ovo">🟣 OVO</option>
                            <option value="qris">💳 QRIS</option>
                            <option value="transfer_bank">🏦 Transfer Bank</option>
                            <option value="cash">💵 Tunai</option>
                        </select>
                    </div>
                    <?php endif; ?>
                    
                    <button type="submit" name="update_status" class="btn btn-success" style="width: 100%;">
                        <i class="ri-save-line"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const hasPaymentStatus = <?= $has_payment_status ? 'true' : 'false' ?>;
        const hasPaymentMethod = <?= $has_payment_method ? 'true' : 'false' ?>;
        
        function updateStatus(id, currentStatus, currentPayment, currentMethod) {
            document.getElementById('statusModal').style.display = 'block';
            document.getElementById('status_id_pesanan').value = id;
            document.getElementById('status_pesanan').value = currentStatus;
            
            if (hasPaymentStatus) {
                document.getElementById('status_bayar').value = currentPayment;
                
                if (hasPaymentMethod) {
                    document.getElementById('metode_pembayaran').value = currentMethod || '';
                    togglePaymentMethod();
                }
            }
        }
        
        function togglePaymentMethod() {
            if (!hasPaymentMethod) return;
            
            const statusBayar = document.getElementById('status_bayar').value;
            const methodGroup = document.getElementById('payment_method_group');
            const methodSelect = document.getElementById('metode_pembayaran');
            
            if (statusBayar === 'paid') {
                methodGroup.style.display = 'block';
                methodSelect.setAttribute('required', 'required');
            } else {
                methodGroup.style.display = 'none';
                methodSelect.removeAttribute('required');
                methodSelect.value = '';
            }
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>