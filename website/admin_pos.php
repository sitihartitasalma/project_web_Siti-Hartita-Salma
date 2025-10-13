<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// =========================================================================
// LOGIC PEMESANAN ADMIN (POS)
// =========================================================================

// Inisialisasi Keranjang di Session
if (!isset($_SESSION['admin_cart'])) {
    $_SESSION['admin_cart'] = [];
}

// Handle Aksi Keranjang (Tambah/Kurangi/Hapus)
if (isset($_POST['action']) && isset($_POST['id_menu'])) {
    $id_menu = (int)$_POST['id_menu'];
    $menu = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT id_menu, nama, harga_satuan FROM tb_menu WHERE id_menu = $id_menu AND status = 'available'"));

    if ($menu) {
        if ($_POST['action'] == 'add') {
            $qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;
            
            if (isset($_SESSION['admin_cart'][$id_menu])) {
                $_SESSION['admin_cart'][$id_menu]['qty'] += $qty;
            } else {
                $_SESSION['admin_cart'][$id_menu] = [
                    'id_menu' => $id_menu,
                    'nama' => $menu['nama'],
                    'harga' => $menu['harga_satuan'],
                    'qty' => $qty,
                ];
            }
        } elseif ($_POST['action'] == 'update_qty' && isset($_POST['qty'])) {
            $qty = (int)$_POST['qty'];
            if ($qty > 0) {
                $_SESSION['admin_cart'][$id_menu]['qty'] = $qty;
            } else {
                unset($_SESSION['admin_cart'][$id_menu]);
            }
        } elseif ($_POST['action'] == 'remove') {
            unset($_SESSION['admin_cart'][$id_menu]);
        }
    }
    // Redirect untuk menghindari resubmit form
    header("Location: admin_pos.php");
    exit();
}

// Handle Transaksi Selesai
if (isset($_POST['checkout'])) {
    if (empty($_SESSION['admin_cart'])) {
        header("Location: admin_pos.php?error=Keranjang kosong, tidak bisa checkout.");
        exit();
    }

    $nama_pembeli = mysqli_real_escape_string($koneksi, trim($_POST['nama_pembeli']));
    $metode_pembayaran = mysqli_real_escape_string($koneksi, $_POST['metode_pembayaran']);
    $catatan = mysqli_real_escape_string($koneksi, trim($_POST['catatan']));
    $total_bayar = 0;
    
    // Hitung total dan persiapkan detail
    $detail_sql_values = [];
    foreach ($_SESSION['admin_cart'] as $item) {
        $subtotal = $item['harga'] * $item['qty'];
        $total_bayar += $subtotal;
        // Simpan untuk insert ke tb_detailpembayaran nanti
        $detail_sql_values[] = [
            'id_menu' => $item['id_menu'],
            'harga_satuan' => $item['harga'],
            'jumlah_pesanan' => $item['qty'],
            'subtotal' => $subtotal,
        ];
    }
    
    // 1. Simpan data pembeli sementara (jika tidak ada sistem member)
    // Asumsi: Semua pesanan admin masuk ke satu id_pembeli default atau buat pembeli baru
    // Kita akan buat pembeli baru sederhana:
    $nama_pembeli_esc = $nama_pembeli ?: 'Pelanggan Kasir';
    $pembeli_sql = "INSERT INTO tb_pembeli (nama_pembeli, nomor_pembeli, alamat) VALUES ('$nama_pembeli_esc', '', '')";
    mysqli_query($koneksi, $pembeli_sql);
    $id_pembeli = mysqli_insert_id($koneksi); // ID pembeli baru

    // 2. Insert ke tabel tb_pesanan
    $pesanan_sql = "INSERT INTO tb_pesanan (id_pembeli, tanggal, status_pesanan, status_pembayaran, metode_pembayaran, catatan) 
                    VALUES ($id_pembeli, NOW(), 'delivered', 'paid', '$metode_pembayaran', '$catatan')";

    if (mysqli_query($koneksi, $pesanan_sql)) {
        $id_pesanan = mysqli_insert_id($koneksi);

        // 3. Insert ke tabel tb_detailpembayaran
        $detail_insert_values = [];
        foreach ($detail_sql_values as $item) {
            $id_m = $item['id_menu'];
            $harga = $item['harga_satuan'];
            $qty = $item['jumlah_pesanan'];
            $sub = $item['subtotal'];
            $detail_insert_values[] = "($id_pesanan, $id_m, $harga, $qty, $sub)";
        }
        
        if (!empty($detail_insert_values)) {
            $detail_sql = "INSERT INTO tb_detailpembayaran (id_pesanan, id_menu, harga_satuan, jumlah_pesanan, subtotal) 
                           VALUES " . implode(", ", $detail_insert_values);
            mysqli_query($koneksi, $detail_sql);
        }

        // Hapus keranjang
        unset($_SESSION['admin_cart']);
        header("Location: admin_pesanan.php?message=Pesanan Kasir berhasil dibuat! ID Pesanan: $id_pesanan");
        exit();
    } else {
        header("Location: admin_pos.php?error=Gagal menyimpan pesanan: " . mysqli_error($koneksi));
        exit();
    }
}


// Ambil semua produk yang available
$products = mysqli_query($koneksi, "SELECT * FROM tb_menu WHERE status = 'available' ORDER BY kategori, nama");
$products_grouped = [];
while ($p = mysqli_fetch_assoc($products)) {
    $products_grouped[$p['kategori']][] = $p;
}

$cart_total = array_sum(array_map(function($item) {
    return $item['harga'] * $item['qty'];
}, $_SESSION['admin_cart']));

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS (Point of Sale) - Admin StarCoffee</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f5f7fa; }
        
        .header { 
            background: linear-gradient(135deg, #004d40 0%, #00695c 100%);
            color: white;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        
        .pos-container { 
            display: flex; 
            max-width: 1600px; 
            margin: 20px auto; 
            padding: 0 20px; 
            gap: 20px; 
        }
        .product-panel { 
            flex: 2; 
            background: white; 
            padding: 20px; 
            border-radius: 10px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
            min-height: 80vh;
        }
        .cart-panel { 
            flex: 1; 
            background: white; 
            padding: 20px; 
            border-radius: 10px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
            position: sticky; 
            top: 20px; 
            max-height: 80vh; 
            overflow-y: auto;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        /* Product List Styles */
        .category-title {
            background: #e0f2f1;
            color: #004d40;
            padding: 10px;
            margin: 20px 0 10px 0;
            border-radius: 5px;
            font-size: 1.2em;
            font-weight: 600;
        }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
        }
        .product-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            position: relative;
        }
        .product-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transform: translateY(-3px);
            border-color: #004d40;
        }
        .product-card img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            background: #eee;
        }
        .product-info {
            padding: 10px;
        }
        .product-info h4 {
            font-size: 1em;
            margin: 5px 0;
            height: 2.4em; /* 2 lines for name */
            overflow: hidden;
            color: #333;
        }
        .product-info p {
            font-size: 1.1em;
            font-weight: 700;
            color: #004d40;
            margin-bottom: 0;
        }
        
        /* Cart Styles */
        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
        }
        .cart-header h3 {
            color: #004d40;
            font-size: 1.5em;
        }
        .cart-list {
            list-style: none;
            padding: 0;
            margin: 0;
            max-height: 40vh;
            overflow-y: auto;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f5f7fa;
        }
        .cart-item-info {
            flex: 1;
        }
        .cart-item-info strong {
            display: block;
            font-size: 0.9em;
            color: #333;
        }
        .cart-item-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .cart-item-controls input {
            width: 50px;
            text-align: center;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .remove-btn {
            background: none;
            border: none;
            color: #e74c3c;
            cursor: pointer;
            font-size: 1.2em;
        }

        .total-box {
            padding: 15px;
            background: #004d40;
            color: white;
            border-radius: 8px;
            margin: 15px 0;
            display: flex;
            justify-content: space-between;
            font-size: 1.4em;
            font-weight: 700;
        }

        .checkout-form .form-group {
            margin-bottom: 15px;
        }
        .checkout-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
            font-size: 0.9em;
        }
        .checkout-form input[type="text"], 
        .checkout-form select,
        .checkout-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }
        .btn-checkout {
            width: 100%;
            background: #00695c;
            color: white;
            border: none;
            padding: 15px;
            border-radius: 5px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-checkout:hover {
            background: #004d40;
        }

        /* Utility */
        .text-center { text-align: center; }
        .text-danger { color: #e74c3c; }
        .text-success { color: #28a745; }
    </style>
</head>
<body>
    <div class="header">
        <h1><i class="ri-store-2-line"></i> POS StarCoffee</h1>
        <a href="admin_dashboard.php"><i class="ri-dashboard-line"></i> Dashboard Admin</a>
    </div>

    <div class="pos-container">
        <div class="product-panel">
            <h2><i class="ri-coffee-line"></i> Pilih Menu</h2>
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error"><i class="ri-error-warning-line"></i> <?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>

            <?php if (empty($products_grouped)): ?>
                <div class="text-center" style="padding: 50px; color: #999;">
                    <i class="ri-box-3-line" style="font-size: 48px; display: block; margin-bottom: 10px;"></i>
                    <p>Tidak ada produk aktif. Tambahkan produk di <a href="admin_produk.php">Kelola Produk</a>.</p>
                </div>
            <?php endif; ?>

            <?php foreach ($products_grouped as $kategori => $list): ?>
                <div class="category-title"><?= htmlspecialchars(ucfirst($kategori)) ?></div>
                <div class="product-grid">
                    <?php foreach ($list as $p): ?>
                        <div class="product-card" onclick="addToCart(<?= $p['id_menu'] ?>)">
                            <img src="<?= htmlspecialchars($p['foto'] ?: 'assets/img/default.jpg') ?>" alt="<?= htmlspecialchars($p['nama']) ?>">
                            <div class="product-info">
                                <h4><?= htmlspecialchars($p['nama']) ?></h4>
                                <p>Rp <?= number_format($p['harga_satuan'], 0, ',', '.') ?></p>
                            </div>
                            <form id="form-add-<?= $p['id_menu'] ?>" method="POST" style="display: none;">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="id_menu" value="<?= $p['id_menu'] ?>">
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="cart-panel">
            <div class="cart-header">
                <h3><i class="ri-shopping-cart-line"></i> Keranjang</h3>
                <small class="text-danger">Admin: <?= htmlspecialchars($_SESSION['admin_nama'] ?? 'Kasir') ?></small>
            </div>

            <ul class="cart-list">
                <?php if (empty($_SESSION['admin_cart'])): ?>
                    <li class="text-center" style="padding: 30px; color: #999;">
                        <i class="ri-add-line" style="font-size: 36px; display: block; margin-bottom: 10px;"></i>
                        <p>Keranjang kosong. Klik menu untuk menambah.</p>
                    </li>
                <?php else: ?>
                    <?php foreach ($_SESSION['admin_cart'] as $item): ?>
                    <li class="cart-item">
                        <div class="cart-item-info">
                            <strong><?= htmlspecialchars($item['nama']) ?></strong>
                            <small>Rp <?= number_format($item['harga'], 0, ',', '.') ?></small>
                        </div>
                        <div class="cart-item-controls">
                            <form method="POST" style="display: flex; gap: 5px; align-items: center;">
                                <input type="hidden" name="action" value="update_qty">
                                <input type="hidden" name="id_menu" value="<?= $item['id_menu'] ?>">
                                <input type="number" name="qty" value="<?= $item['qty'] ?>" min="1" onchange="this.form.submit()">
                            </form>
                            <form method="POST">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="id_menu" value="<?= $item['id_menu'] ?>">
                                <button type="submit" class="remove-btn" title="Hapus"><i class="ri-delete-bin-line"></i></button>
                            </form>
                        </div>
                    </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
            
            <div class="total-box">
                <span>TOTAL</span>
                <span>Rp <?= number_format($cart_total, 0, ',', '.') ?></span>
            </div>

            <form method="POST" class="checkout-form" onsubmit="return confirm('Selesaikan transaksi ini? Total: Rp <?= number_format($cart_total, 0, ',', '.') ?>')">
                <input type="hidden" name="checkout" value="1">

                <div class="form-group">
                    <label for="nama_pembeli"><i class="ri-user-line"></i> Nama Pembeli (Opsional)</label>
                    <input type="text" id="nama_pembeli" name="nama_pembeli" placeholder="Contoh: Budi">
                </div>

                <div class="form-group">
                    <label for="metode_pembayaran"><i class="ri-wallet-line"></i> Metode Pembayaran</label>
                    <select id="metode_pembayaran" name="metode_pembayaran" required>
                        <option value="">Pilih Metode</option>
                        <option value="cash">Cash (Tunai)</option>
                        <option value="debit">Debit Card</option>
                        <option value="ewallet">E-Wallet</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="catatan"><i class="ri-file-text-line"></i> Catatan</label>
                    <textarea id="catatan" name="catatan" rows="2" placeholder="Tambahan catatan pesanan..."></textarea>
                </div>

                <button type="submit" class="btn-checkout" <?= empty($_SESSION['admin_cart']) ? 'disabled' : '' ?>>
                    <i class="ri-check-line"></i> Bayar & Selesaikan Transaksi
                </button>
            </form>
        </div>
    </div>
    
    <script>
        function addToCart(id_menu) {
            document.getElementById('form-add-' + id_menu).submit();
        }
    </script>
</body>
</html>