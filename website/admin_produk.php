<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// ---------------------------------------------
// 1. LOGIKA DINAMIS PEMILIHAN FOTO DARI FOLDER
// ---------------------------------------------
$image_dir = 'assets/img/';
$allowed_extensions = ['png', 'jpg', 'jpeg', 'gif'];
$product_photos = [];

// Coba baca isi folder assets/img
$files = @scandir($image_dir);

if ($files !== false) {
    // Hilangkan . dan ..
    $files = array_diff($files, array('.', '..')); 
    
    foreach ($files as $file) {
        $path_parts = pathinfo($file);
        // Cek ekstensi file (hanya ambil gambar)
        if (isset($path_parts['extension']) && in_array(strtolower($path_parts['extension']), $allowed_extensions)) {
            $product_photos[] = $file;
        }
    }
}
// ---------------------------------------------


// Handle delete
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    
    $check = mysqli_query($koneksi, "SELECT COUNT(*) as c FROM tb_detailpembayaran WHERE id_menu = $id");
    $has_orders = mysqli_fetch_assoc($check)['c'] > 0;
    
    if ($has_orders) {
        header("Location: admin_produk.php?error=Produk tidak dapat dihapus karena sudah pernah dipesan");
    } else {
        mysqli_query($koneksi, "DELETE FROM tb_menu WHERE id_menu = $id");
        header("Location: admin_produk.php?message=Produk berhasil dihapus");
    }
    exit();
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
    $harga = (float) $_POST['harga'];
    // Kolom deskripsi, kategori, dan status sudah dihapus dari logika ini
    $foto = mysqli_real_escape_string($koneksi, $_POST['foto']);
    
    // Validasi
    if (empty($nama) || $harga <= 0) {
        header("Location: admin_produk.php?error=Data tidak valid");
        exit();
    }
    
    if (isset($_POST['id_menu']) && !empty($_POST['id_menu'])) {
        // Update
        $id = (int) $_POST['id_menu'];
        // Query UPDATE hanya menggunakan kolom yang ada di DB: nama, harga_satuan, foto
        $sql = "UPDATE tb_menu SET 
                nama='$nama', 
                harga_satuan=$harga, 
                foto='$foto'
                WHERE id_menu=$id";
        
        if (mysqli_query($koneksi, $sql)) {
            header("Location: admin_produk.php?message=Produk berhasil diupdate");
        } else {
            header("Location: admin_produk.php?error=Gagal update produk: " . mysqli_error($koneksi));
        }
    } else {
        // Insert
        // Query INSERT hanya menggunakan kolom yang ada di DB: nama, harga_satuan, foto
        $sql = "INSERT INTO tb_menu (nama, harga_satuan, foto) 
                VALUES ('$nama', $harga, '$foto')";
        
        if (mysqli_query($koneksi, $sql)) {
            header("Location: admin_produk.php?message=Produk berhasil ditambahkan");
        } else {
            header("Location: admin_produk.php?error=Gagal menambah produk: " . mysqli_error($koneksi));
        }
    }
    exit();
}

// Get product for edit
$edit_product = null;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $edit_product = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM tb_menu WHERE id_menu = $edit_id"));
}

// Filter
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';

$where = [];
if ($search) $where[] = "(nama LIKE '%$search%')";

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$products = mysqli_query($koneksi, "SELECT * FROM tb_menu $where_clause ORDER BY id_menu DESC");
$total_products = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM tb_menu"))['c'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Admin StarCoffee</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
    <style>
        /* CSS DARI KODE SEBELUMNYA */
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
        
        .form-box { 
            background: white; 
            padding: 30px; 
            border-radius: 10px; 
            margin-bottom: 30px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-box h2 { 
            margin-bottom: 20px; 
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .form-group { 
            margin-bottom: 15px; 
        }
        .form-group label { 
            display: block; 
            margin-bottom: 5px; 
            font-weight: 600; 
            color: #333;
        }
        .form-group label .required {
            color: #dc3545;
        }
        input, select, textarea { 
            width: 100%; 
            padding: 12px; 
            border: 2px solid #e1e1e1; 
            border-radius: 8px; 
            font-size: 14px;
            transition: border-color 0.3s;
        }
        input:focus, select:focus, textarea:focus { 
            outline: none; 
            border-color: #004d40; 
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        button, .btn { 
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
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; }
        .btn-warning { background: #ffc107; color: black; }
        .btn-warning:hover { background: #e0a800; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-danger:hover { background: #c82333; }
        .btn-sm { padding: 8px 15px; font-size: 13px; }
        
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
        .filter-group { 
            flex: 1; 
            min-width: 200px; 
        }
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 20px;
            border-bottom: 2px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        }
        tbody tr:hover { 
            background: #f8f9fa;
        }
        
        .message { 
            background: #d4edda; 
            color: #155724; 
            padding: 15px 20px; 
            border-radius: 8px; 
            margin-bottom: 20px; 
            border-left: 4px solid #28a745;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            border-left-color: #dc3545;
        }
        
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 10px;
            }
            .form-grid {
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
                <i class="ri-cup-line"></i> Kelola Produk
            </span>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="admin_pesanan.php">
                <i class="ri-shopping-bag-line"></i> Pesanan
            </a>
            <a href="admin_logout.php">
                <i class="ri-logout-box-line"></i> Logout
            </a>
        </div>
    </div>

    <div class="container">
        <?php if(isset($_GET['message'])): ?>
            <div class="message">
                <i class="ri-checkbox-circle-line"></i>
                <span><?= htmlspecialchars($_GET['message']) ?></span>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_GET['error'])): ?>
            <div class="message error-message">
                <i class="ri-error-warning-line"></i>
                <span><?= htmlspecialchars($_GET['error']) ?></span>
            </div>
        <?php endif; ?>
        
        <div class="stats-bar">
            <div class="stat-item">
                <i class="ri-cup-line"></i>
                <h3><?= $total_products ?></h3>
                <p>Total Produk</p>
            </div>
        </div>

        <div class="form-box">
            <h2>
                <i class="<?= $edit_product ? 'ri-edit-line' : 'ri-add-circle-line' ?>"></i>
                <?= $edit_product ? 'Edit Produk' : 'Tambah Produk Baru' ?>
            </h2>
            <form method="POST">
                <?php if($edit_product): ?>
                    <input type="hidden" name="id_menu" value="<?= $edit_product['id_menu'] ?>">
                <?php endif; ?>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nama Produk <span class="required">*</span></label>
                        <input type="text" name="nama" placeholder="Contoh: Cappuccino" required 
                               value="<?= $edit_product ? htmlspecialchars($edit_product['nama']) : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Harga (Rp) <span class="required">*</span></label>
                        <input type="number" name="harga" placeholder="25000" required min="1000" step="100"
                               value="<?= $edit_product ? $edit_product['harga_satuan'] : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Foto Produk <span class="required">*</span></label>
                        <select name="foto" required>
                            <option value="">-- Pilih Foto dari File --</option>
                            <?php if (empty($product_photos)): ?>
                                <option value="" disabled>Tidak ada file gambar ditemukan di <?= $image_dir ?></option>
                            <?php else: ?>
                                <?php foreach ($product_photos as $file): ?>
                                    <option value="<?= htmlspecialchars($file) ?>" 
                                            <?= ($edit_product && $edit_product['foto'] == $file) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($file) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <small style="display: block; color: #666; margin-top: 5px;">File harus ada di folder: `<?= $image_dir ?>`</small>
                    </div>
                </div>
                
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="<?= $edit_product ? 'ri-refresh-line' : 'ri-save-line' ?>"></i>
                        <?= $edit_product ? 'Update Produk' : 'Simpan Produk' ?>
                    </button>
                    <?php if($edit_product): ?>
                        <a href="admin_produk.php" class="btn btn-secondary">
                            <i class="ri-close-line"></i> Batal
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <form class="filters" method="GET">
            <div class="filter-group">
                <label>Cari Produk</label>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Nama produk...">
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="ri-search-line"></i> Filter
            </button>
            <a href="admin_produk.php" class="btn btn-secondary">
                <i class="ri-refresh-line"></i> Reset
            </a>
        </form>

        <div class="card">
            <div class="card-header">
                <h2>
                    <i class="ri-list-check"></i>
                    Daftar Produk (<?= mysqli_num_rows($products) ?> item)
                </h2>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Foto</th>
                        <th>Nama Produk</th>
                        <th>Harga Normal</th>
                        <th>Harga Member</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($products) > 0): ?>
                        <?php while($p = mysqli_fetch_assoc($products)): ?>
                        <tr>
                            <td><strong>#<?= str_pad($p['id_menu'], 3, '0', STR_PAD_LEFT) ?></strong></td>
                            <td>
                                <img src="<?= $image_dir ?><?= htmlspecialchars($p['foto']) ?>" 
                                        alt="<?= htmlspecialchars($p['nama']) ?>" 
                                        class="product-image">
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($p['nama']) ?></strong>
                            </td>
                            <td><strong>Rp <?= number_format($p['harga_satuan'], 0, ',', '.') ?></strong></td>
                            <td style="color: #dc3545;">
                                <strong>Rp <?= number_format($p['harga_satuan'] * 0.9, 0, ',', '.') ?></strong>
                                <small style="display: block; color: #666;">(Diskon 10%)</small>
                            </td>
                            <td>
                                <a href="?edit=<?= $p['id_menu'] ?>" class="btn btn-warning btn-sm">
                                    <i class="ri-edit-line"></i> Edit
                                </a>
                                <a href="?delete=<?= $p['id_menu'] ?>" class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Yakin ingin menghapus produk <?= htmlspecialchars($p['nama']) ?>?')">
                                    <i class="ri-delete-bin-line"></i> Hapus
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: #999;">
                                <i class="ri-inbox-line" style="font-size: 48px; display: block; margin-bottom: 10px;"></i>
                                Tidak ada produk ditemukan
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>