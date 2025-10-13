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

// Cek kolom is_member
$member_column_check = mysqli_query($koneksi, "SHOW COLUMNS FROM tb_pesanan LIKE 'is_member'");
$has_member_column = mysqli_num_rows($member_column_check) > 0;

// Ambil data member aktif
$member_query = mysqli_query($koneksi, "SELECT id_member, nama, telepon, email FROM tb_member WHERE status = 'active' ORDER BY nama ASC");
$members = [];
while ($m = mysqli_fetch_assoc($member_query)) {
    $members[] = $m;
}

// Ambil data menu
$menu_query = mysqli_query($koneksi, "SELECT id_menu, nama, harga_satuan FROM tb_menu ORDER BY nama ASC");
$menus = [];
while ($mn = mysqli_fetch_assoc($menu_query)) {
    $menus[] = $mn;
}

// ==========================================
// PROSES PEMESANAN
// ==========================================
if (isset($_POST['create_order'])) {
    $is_member = isset($_POST['is_member']) ? 1 : 0;
    
    $nama_pembeli = mysqli_real_escape_string($koneksi, $_POST['nama_pembeli']);
    $nomor_pembeli = mysqli_real_escape_string($koneksi, $_POST['nomor_pembeli']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    // ✅ STATUS SELALU PENDING - TIDAK DARI FORM
    $status_pesanan = 'pending';
    $catatan = mysqli_real_escape_string($koneksi, $_POST['catatan'] ?? '');
    
    // Cek atau buat pembeli
    $buyer_check = mysqli_query($koneksi, "SELECT id_pembeli FROM tb_pembeli WHERE nomor_pembeli = '$nomor_pembeli' LIMIT 1");
    if (mysqli_num_rows($buyer_check) > 0) {
        $buyer_data = mysqli_fetch_assoc($buyer_check);
        $id_pembeli = $buyer_data['id_pembeli'];
        mysqli_query($koneksi, "UPDATE tb_pembeli SET nama_pembeli = '$nama_pembeli', alamat = '$alamat' WHERE id_pembeli = $id_pembeli");
    } else {
        mysqli_query($koneksi, "INSERT INTO tb_pembeli (nama_pembeli, nomor_pembeli, alamat) VALUES ('$nama_pembeli', '$nomor_pembeli', '$alamat')");
        $id_pembeli = mysqli_insert_id($koneksi);
    }

    if ($id_pembeli) {
        $status_bayar = 'unpaid';
        $metode = 'NULL';

        if ($has_payment_status && isset($_POST['status_bayar_new'])) {
            $status_bayar = mysqli_real_escape_string($koneksi, $_POST['status_bayar_new']);
            if ($has_payment_method && $status_bayar == 'paid' && !empty($_POST['metode_pembayaran_new'])) {
                $metode = "'" . mysqli_real_escape_string($koneksi, $_POST['metode_pembayaran_new']) . "'";
            }
        }

        $payment_fields = $has_payment_status ? ", status_pembayaran" : "";
        $payment_values = $has_payment_status ? ", '$status_bayar'" : "";
        
        $method_fields = $has_payment_method ? ", metode_pembayaran" : "";
        $method_values = $has_payment_method ? ", $metode" : "";
        
        $member_fields = $has_member_column ? ", is_member" : "";
        $member_values = $has_member_column ? ", $is_member" : "";

        // ✅ QUERY MENGGUNAKAN STATUS PENDING
        $insert_order_query = "
            INSERT INTO tb_pesanan (id_pembeli, tanggal, status_pesanan, catatan $payment_fields $method_fields $member_fields)
            VALUES ($id_pembeli, NOW(), '$status_pesanan', '$catatan' $payment_values $method_values $member_values)
        ";
        
        if (mysqli_query($koneksi, $insert_order_query)) {
            $id_pesanan_baru = mysqli_insert_id($koneksi);

            if ($id_pesanan_baru && isset($_POST['menu_id']) && is_array($_POST['menu_id'])) {
                $items = $_POST['menu_id'];
                $quantities = $_POST['quantity'];
                $total_items_price = 0;
                
                foreach ($items as $index => $id_menu) {
                    $id_menu = (int) $id_menu;
                    $quantity = (int) ($quantities[$index] ?? 0);

                    if ($id_menu > 0 && $quantity > 0) {
                        $menu_query = mysqli_query($koneksi, "SELECT harga_satuan FROM tb_menu WHERE id_menu = $id_menu");
                        if (mysqli_num_rows($menu_query) > 0) {
                            $menu = mysqli_fetch_assoc($menu_query);
                            $harga_satuan = $menu['harga_satuan'];
                            
                            if ($is_member) {
                                $harga_satuan = $harga_satuan * 0.9;
                            }
                            
                            $subtotal = $harga_satuan * $quantity;
                            $total_items_price += $subtotal;

                            mysqli_query($koneksi, "
                                INSERT INTO tb_detailpembayaran (id_pesanan, id_menu, jumlah_pesanan, harga_satuan, subtotal)
                                VALUES ($id_pesanan_baru, $id_menu, $quantity, $harga_satuan, $subtotal)
                            ");
                        }
                    }
                }
                
                if ($total_items_price > 0) {
                    header("Location: print_struk.php?id=" . $id_pesanan_baru . "&auto_print=1");
                    exit();
                } else {
                    mysqli_query($koneksi, "DELETE FROM tb_pesanan WHERE id_pesanan = $id_pesanan_baru");
                    header("Location: admin_buat_pesanan.php?error=Tidak ada item yang ditambahkan.");
                    exit();
                }
            }
        } else {
            $error_msg = mysqli_error($koneksi);
            header("Location: admin_buat_pesanan.php?error=Gagal membuat pesanan: " . urlencode($error_msg));
            exit();
        }
    }

    header("Location: admin_buat_pesanan.php?error=Gagal membuat pesanan!");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Pesanan - Admin StarCoffee</title>
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
        .header a { color: white; text-decoration: none; padding: 8px 15px; border-radius: 5px; }
        .header a:hover { background: rgba(255,255,255,0.1); }
        
        .container { max-width: 1000px; margin: 30px auto; padding: 0 20px; }
        
        .card { background: white; border-radius: 10px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card h3 { margin-bottom: 20px; color: #004d40; display: flex; align-items: center; gap: 10px; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #333; }
        .form-group input, .form-group select, .form-group textarea { 
            width: 100%; padding: 12px; border: 2px solid #e1e1e1; border-radius: 5px; 
        }
        
        .member-toggle { 
            background: #e3f2fd; 
            padding: 15px; 
            border-radius: 8px; 
            margin-bottom: 20px;
            border-left: 4px solid #2196f3;
        }
        .member-toggle label { display: flex; align-items: center; gap: 10px; cursor: pointer; }
        .member-toggle input[type="checkbox"] { width: 20px; height: 20px; cursor: pointer; }
        
        .item-row { 
            display: grid; 
            grid-template-columns: 2fr 1fr 60px; 
            gap: 10px; 
            margin-bottom: 10px; 
            align-items: end;
        }
        
        .btn { 
            padding: 12px 20px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            font-weight: 600; 
            text-decoration: none; 
            display: inline-block; 
        }
        .btn-primary { background: #4caf50; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-info { background: #2196f3; color: white; }
        .btn-sm { padding: 8px 15px; font-size: 14px; }
        
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: flex-start; gap: 10px; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffc107; }
        
        .status-display {
            background: #f0f0f0;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #2196f3;
            color: #0c5460;
            font-weight: 600;
        }
        
        .discount-info {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
            border-left: 4px solid #4caf50;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        code {
            background: #f5f5f5;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
            font-size: 12px;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <a href="admin_pesanan.php"><i class="ri-arrow-left-line"></i> Kembali</a>
            <span style="font-size: 20px; font-weight: bold;">Buat Pesanan Baru</span>
        </div>
        <a href="admin_logout.php"><i class="ri-logout-box-line"></i> Logout</a>
    </div>

    <div class="container">
        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <i class="ri-error-warning-line" style="font-size: 20px;"></i>
                <span><?= htmlspecialchars($_GET['error']) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!$has_member_column): ?>
            <div class="alert alert-warning">
                <i class="ri-alert-line" style="font-size: 20px;"></i>
                <div>
                    <strong>⚠️ PENTING! Kolom is_member belum ada!</strong><br>
                    Diskon member tidak akan berfungsi. Jalankan query ini di phpMyAdmin:<br>
                    <code>ALTER TABLE tb_pesanan ADD COLUMN is_member TINYINT(1) DEFAULT 0 AFTER id_pembeli;</code>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="card">
                <h3><i class="ri-user-line"></i> Data Pembeli</h3>
                
                <div class="member-toggle">
                    <label>
                        <input type="checkbox" name="is_member" id="is_member" onchange="toggleMemberSelect()">
                        <strong>✨ Pelanggan adalah Member VIP (Diskon 10%)</strong>
                    </label>
                </div>

                <div id="member_select_group" style="display: none;">
                    <div class="form-group">
                        <label>Pilih Member:</label>
                        <select id="id_member" onchange="fillMemberData()">
                            <option value="">-- Pilih Member --</option>
                            <?php foreach ($members as $m): ?>
                                <option value="<?= $m['id_member'] ?>" 
                                        data-nama="<?= htmlspecialchars($m['nama']) ?>"
                                        data-telepon="<?= htmlspecialchars($m['telepon']) ?>"
                                        data-email="<?= htmlspecialchars($m['email']) ?>">
                                    <?= htmlspecialchars($m['nama']) ?> - <?= htmlspecialchars($m['telepon']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Nama Pembeli: <span style="color: red;">*</span></label>
                        <input type="text" name="nama_pembeli" id="nama_pembeli" required placeholder="Nama lengkap pembeli">
                    </div>
                    
                    <div class="form-group">
                        <label>Nomor Telepon: <span style="color: red;">*</span></label>
                        <input type="text" name="nomor_pembeli" id="nomor_pembeli" required placeholder="08xxxxxxxxxx">
                    </div>
                </div>

                <div class="form-group">
                    <label>Email: <span id="email_required" style="display: none; color: red;">*</span></label>
                    <input type="email" name="email_pembeli" id="email_pembeli" placeholder="contoh@email.com">
                </div>
                
                <div class="form-group">
                    <label>Alamat Pengiriman: <span style="color: red;">*</span></label>
                    <textarea name="alamat" id="alamat" rows="3" required placeholder="Alamat lengkap pengiriman"></textarea>
                </div>
            </div>

            <div class="card">
                <h3><i class="ri-shopping-cart-line"></i> Item Pesanan</h3>
                
                <div id="order_items_list">
                    <div class="item-row">
                        <select name="menu_id[]" required>
                            <option value="">-- Pilih Menu --</option>
                            <?php foreach($menus as $menu): ?>
                                <option value="<?= $menu['id_menu'] ?>">
                                    <?= htmlspecialchars($menu['nama']) ?> (Rp <?= number_format($menu['harga_satuan'], 0, ',', '.') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" name="quantity[]" min="1" value="1" required placeholder="Qty">
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeItem(this)">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                </div>
                
                <button type="button" class="btn btn-info btn-sm" onclick="addItem()">
                    <i class="ri-add-line"></i> Tambah Item
                </button>

                <div class="discount-info" id="discount_info" style="display: none;">
                    <i class="ri-gift-line"></i> <strong>🎉 Diskon Member 10% akan diterapkan pada setiap item!</strong>
                </div>
            </div>

            <div class="card">
                <h3><i class="ri-file-text-line"></i> Detail Pesanan</h3>
                
                <!-- ✅ STATUS PESANAN LANGSUNG PENDING (TIDAK BISA DIPILIH) -->
                <div class="form-group">
                    <label>Status Pesanan:</label>
                    <div class="status-display">
                        <i class="ri-information-line"></i> Status otomatis: <strong>PENDING</strong>
                        <br>
                        <small>Pesanan akan otomatis berstatus "Pending". Admin dapat mengubahnya di halaman kelola pesanan.</small>
                    </div>
                </div>

                <?php if ($has_payment_status): ?>
                <div class="form-group">
                    <label>Status Pembayaran:</label>
                    <select name="status_bayar_new" id="status_bayar_new" onchange="togglePaymentMethod()">
                        <option value="unpaid">Belum Dibayar</option>
                        <option value="paid">Sudah Dibayar</option>
                    </select>
                </div>
                <?php endif; ?>

                <?php if ($has_payment_method): ?>
                <div class="form-group" id="payment_method_group_new" style="display: none;">
                    <label>Metode Pembayaran: <span style="color: red;">*</span></label>
                    <select name="metode_pembayaran_new" id="metode_pembayaran_new">
                        <option value="">-- Pilih Metode --</option>
                        <option value="cash">💵 Tunai</option>
                        <option value="gopay">🟢 GoPay</option>
                        <option value="dana">📵 DANA</option>
                        <option value="ovo">🟣 OVO</option>
                        <option value="qris">💳 QRIS</option>
                        <option value="transfer_bank">🏦 Transfer Bank</option>
                    </select>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label>Catatan:</label>
                    <textarea name="catatan" rows="2" placeholder="Catatan tambahan (opsional)"></textarea>
                </div>
            </div>

            <button type="submit" name="create_order" class="btn btn-primary" style="width: 100%; font-size: 16px;">
                <i class="ri-check-line"></i> Buat Pesanan & Cetak Struk
            </button>
        </form>
    </div>

    <script>
        const menus = <?= json_encode($menus) ?>;

        function toggleMemberSelect() {
            const isMember = document.getElementById('is_member').checked;
            const memberGroup = document.getElementById('member_select_group');
            const discountInfo = document.getElementById('discount_info');
            const emailRequired = document.getElementById('email_required');
            const emailInput = document.getElementById('email_pembeli');
            
            memberGroup.style.display = isMember ? 'block' : 'none';
            discountInfo.style.display = isMember ? 'block' : 'none';
            
            if (isMember) {
                emailRequired.style.display = 'inline';
                emailInput.setAttribute('required', 'required');
            } else {
                emailRequired.style.display = 'none';
                emailInput.removeAttribute('required');
                document.getElementById('id_member').value = '';
                document.getElementById('nama_pembeli').value = '';
                document.getElementById('nomor_pembeli').value = '';
                document.getElementById('email_pembeli').value = '';
                document.getElementById('alamat').value = '';
            }
        }

        function fillMemberData() {
            const select = document.getElementById('id_member');
            const option = select.options[select.selectedIndex];
            
            if (option.value) {
                document.getElementById('nama_pembeli').value = option.dataset.nama;
                document.getElementById('nomor_pembeli').value = option.dataset.telepon;
                document.getElementById('email_pembeli').value = option.dataset.email;
                document.getElementById('alamat').value = '';
                document.getElementById('alamat').focus();
            }
        }

        function addItem() {
            const list = document.getElementById('order_items_list');
            const firstItem = list.querySelector('.item-row');
            const newItem = firstItem.cloneNode(true);
            
            newItem.querySelector('select').selectedIndex = 0;
            newItem.querySelector('input').value = '1';
            
            list.appendChild(newItem);
        }

        function removeItem(button) {
            const list = document.getElementById('order_items_list');
            const items = list.querySelectorAll('.item-row');
            
            if (items.length > 1) {
                button.closest('.item-row').remove();
            } else {
                alert("Minimal harus ada satu item!");
            }
        }

        function togglePaymentMethod() {
            const statusBayar = document.getElementById('status_bayar_new').value;
            const methodGroup = document.getElementById('payment_method_group_new');
            const methodSelect = document.getElementById('metode_pembayaran_new');
            
            if (statusBayar === 'paid') {
                methodGroup.style.display = 'block';
                methodSelect.setAttribute('required', 'required');
            } else {
                methodGroup.style.display = 'none';
                methodSelect.removeAttribute('required');
            }
        }
    </script>
</body>
</html>