<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// 1. Ambil daftar member aktif untuk dropdown
$member_query = mysqli_query($koneksi, "SELECT id_member, nama FROM tb_member WHERE status = 'active' ORDER BY nama ASC");
$members = mysqli_fetch_all($member_query, MYSQLI_ASSOC);

// 2. Ambil daftar menu aktif (sudah diperbaiki untuk tidak ada filter status)
$menu_query = mysqli_query($koneksi, "SELECT id_menu, nama, harga_satuan FROM tb_menu ORDER BY nama ASC");
$menus = mysqli_fetch_all($menu_query, MYSQLI_ASSOC);

// Simpan data menu dalam format JSON untuk digunakan di JavaScript
$menu_json = json_encode($menus);

// 3. Handle POST Request untuk menyimpan pesanan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_order'])) {
    
    // Data Pelanggan
    $is_member = isset($_POST['is_member']) ? (int) $_POST['is_member'] : 0;
    $id_member = $is_member ? (int) $_POST['id_member'] : 0;
    $nama_pelanggan = mysqli_real_escape_string($koneksi, $_POST['nama_pelanggan']);
    $nomor_hp = mysqli_real_escape_string($koneksi, $_POST['nomor_hp']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $catatan = mysqli_real_escape_string($koneksi, $_POST['catatan']);
    
    // Data Pembayaran
    $metode_pembayaran = mysqli_real_escape_string($koneksi, $_POST['metode_pembayaran']);
    
    // Data Item
    $items = json_decode($_POST['order_items'], true);
    
    if (empty($items)) {
        $error = "Pesanan harus memiliki minimal 1 item.";
    } else {
        // A. Insert ke tb_pembeli (untuk non-member)
        if (!$is_member) {
            mysqli_query($koneksi, "INSERT INTO tb_pembeli (nama_pembeli, nomor_pembeli, alamat) VALUES ('$nama_pelanggan', '$nomor_hp', '$alamat')");
            $id_pembeli = mysqli_insert_id($koneksi);
        } else {
            // Untuk member, kita perlu mengambil id_pembeli dari data member atau membuatnya jika belum ada.
            // ASUMSI: Setiap member memiliki 1 entri di tb_pembeli (atau menggunakan ID member sebagai ID pembeli sementara untuk order admin)
            // KITA AKAN SIMPLIFIKASI: Admin order tidak akan membuat entri baru di tb_pembeli jika itu member.
            // Jika Anda menggunakan id_member di tb_pesanan, maka struktur database harus disesuaikan.
            // KITA GUNAKAN LOGIKA PALING AMAN: Order admin menggunakan ID pembeli baru (meski member) atau harus ada kolom id_member di tb_pesanan.
            
            // KARENA STRUKTUR ANDA MENGGUNAKAN id_pembeli, KITA ASUMSIKAN:
            // 1. Cek tb_pembeli apakah ada yang namanya sama dengan member
            $pembeli_member_query = mysqli_query($koneksi, "SELECT id_pembeli FROM tb_pembeli WHERE nama_pembeli = '$nama_pelanggan' LIMIT 1");
            if(mysqli_num_rows($pembeli_member_query) > 0) {
                $id_pembeli = mysqli_fetch_assoc($pembeli_member_query)['id_pembeli'];
            } else {
                mysqli_query($koneksi, "INSERT INTO tb_pembeli (nama_pembeli, nomor_pembeli, alamat) VALUES ('$nama_pelanggan', '$nomor_hp', '$alamat')");
                $id_pembeli = mysqli_insert_id($koneksi);
            }
        }
        
        // B. Hitung Total dan Tentukan Status
        $total_semua = 0;
        foreach ($items as $item) {
            $total_semua += $item['subtotal'];
        }
        
        // C. Insert ke tb_pesanan
        $tanggal = date('Y-m-d H:i:s');
        
        // Tentukan diskon untuk member (Contoh: 10% jika member)
        $diskon = 0;
        if ($is_member) {
            $diskon = $total_semua * 0.10; // Diskon 10%
        }
        $grand_total = $total_semua - $diskon;
        
        // Catatan: Jika Anda tidak punya kolom 'diskon' dan 'grand_total' di tb_pesanan,
        // Anda harus membuatnya di database terlebih dahulu.
        
        $insert_pesanan_query = "
            INSERT INTO tb_pesanan 
            (id_pembeli, tanggal, status_pesanan, status_pembayaran, metode_pembayaran, catatan, total) 
            VALUES 
            ($id_pembeli, '$tanggal', 'completed', 'paid', '$metode_pembayaran', 'Pesanan Admin: $catatan', $grand_total)
        ";
        
        if (mysqli_query($koneksi, $insert_pesanan_query)) {
            $id_pesanan = mysqli_insert_id($koneksi);
            
            // D. Insert ke tb_detailpembayaran
            foreach ($items as $item) {
                $id_menu = (int) $item['id_menu'];
                $jumlah_pesanan = (int) $item['jumlah'];
                $subtotal = (float) $item['subtotal'];
                
                mysqli_query($koneksi, "
                    INSERT INTO tb_detailpembayaran 
                    (id_pesanan, id_menu, jumlah_pesanan, subtotal) 
                    VALUES 
                    ($id_pesanan, $id_menu, $jumlah_pesanan, $subtotal)
                ");
            }
            
            // E. Redirect dan Cetak Struk
            header("Location: print_struk.php?id=$id_pesanan&message=" . urlencode("Pesanan berhasil dibuat!"));
            exit();
            
        } else {
            $error = "Gagal menyimpan pesanan: " . mysqli_error($koneksi);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Pesanan Baru - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
    <link rel="stylesheet" href="assets/css/styles_admin.css"> <style>
        .form-container {
            max-width: 800px;
            margin: 20px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; color: #333; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .item-list-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .item-list-table th, .item-list-table td { padding: 10px; border: 1px solid #eee; text-align: left; }
        .item-list-table th { background: #f8f8f8; }
        .total-summary { 
            text-align: right; 
            margin-top: 20px; 
            padding-top: 15px;
            border-top: 2px solid #333;
            font-size: 1.2em;
        }
        .btn-remove { 
            background: none; 
            border: none; 
            color: crimson; 
            cursor: pointer; 
            font-size: 1.2em;
        }
        .btn-add-item {
            background: #007bff;
            color: #fff;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        .btn-submit {
            background: #28a745;
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 1.1em;
            margin-top: 20px;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-weight: bold;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        #discount_info {
            color: green;
            font-weight: bold;
            text-align: right;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="form-container">
            <div class="header-section">
                <h2><i class="ri-shopping-cart-line"></i> Buat Pesanan Baru</h2>
                <a href="admin_pesanan.php" class="btn btn-secondary"><i class="ri-arrow-left-line"></i> Kembali</a>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                
                <h3><i class="ri-user-line"></i> Info Pelanggan</h3>
                <div class="form-group">
                    <label>Tipe Pelanggan:</label>
                    <label><input type="radio" name="is_member" value="1" id="is_member_yes"> Member</label>
                    <label><input type="radio" name="is_member" value="0" id="is_member_no" checked> Umum (Non-member)</label>
                </div>
                
                <div class="form-group" id="member_select_group" style="display: none;">
                    <label for="id_member">Pilih Member:</label>
                    <select id="id_member" name="id_member">
                        <option value="">-- Pilih Member --</option>
                        <?php foreach ($members as $m): ?>
                            <option value="<?= $m['id_member'] ?>" data-nama="<?= htmlspecialchars($m['nama']) ?>"><?= htmlspecialchars($m['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div id="customer_details">
                    <div class="form-group">
                        <label for="nama_pelanggan">Nama Pelanggan:</label>
                        <input type="text" id="nama_pelanggan" name="nama_pelanggan" required>
                    </div>
                    <div class="form-group">
                        <label for="nomor_hp">Nomor HP:</label>
                        <input type="text" id="nomor_hp" name="nomor_hp">
                    </div>
                    <div class="form-group">
                        <label for="alamat">Alamat:</label>
                        <textarea id="alamat" name="alamat"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="catatan">Catatan Pesanan:</label>
                        <textarea id="catatan" name="catatan"></textarea>
                    </div>
                </div>

                <div class="separator"></div>
                
                <h3><i class="ri-cup-line"></i> Tambah Item</h3>
                <div class="form-group">
                    <label for="id_menu">Pilih Menu:</label>
                    <select id="menu_select">
                        <option value="">-- Pilih Menu --</option>
                        <?php foreach ($menus as $m): ?>
                            <option value="<?= $m['id_menu'] ?>" data-harga="<?= $m['harga_satuan'] ?>" data-nama="<?= htmlspecialchars($m['nama']) ?>"><?= htmlspecialchars($m['nama']) ?> (Rp <?= number_format($m['harga_satuan'], 0, ',', '.') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group" style="display: flex; gap: 10px;">
                    <div style="flex-grow: 1;">
                        <label for="quantity_input">Jumlah:</label>
                        <input type="number" id="quantity_input" value="1" min="1">
                    </div>
                    <button type="button" id="add_item_btn" class="btn-add-item" style="align-self: flex-end;">
                        <i class="ri-add-line"></i> Tambah Item
                    </button>
                </div>

                <div id="item_list_display">
                    <table class="item-list-table">
                        <thead>
                            <tr>
                                <th style="width: 50%;">Menu</th>
                                <th style="width: 15%;">Harga Satuan</th>
                                <th style="width: 10%;">Qty</th>
                                <th style="width: 20%;">Subtotal</th>
                                <th style="width: 5%;"></th>
                            </tr>
                        </thead>
                        <tbody id="order_items_body">
                            </tbody>
                    </table>
                </div>

                <div class="total-summary">
                    <div id="discount_info" style="display: none;">Diskon Member (10%): <span id="discount_amount">Rp 0</span></div>
                    <p style="font-size: 1.5em; margin: 5px 0;">GRAND TOTAL: <span id="grand_total_display">Rp 0</span></p>
                </div>
                
                <input type="hidden" name="order_items" id="order_items_input">
                <input type="hidden" name="grand_total_hidden" id="grand_total_hidden">
                
                <div class="separator"></div>

                <h3><i class="ri-bank-card-line"></i> Pembayaran</h3>
                <div class="form-group">
                    <label for="metode_pembayaran">Metode Pembayaran:</label>
                    <select id="metode_pembayaran" name="metode_pembayaran" required>
                        <option value="">-- Pilih Metode --</option>
                        <option value="cash">Cash (Tunai)</option>
                        <option value="gopay">GoPay</option>
                        <option value="dana">DANA</option>
                        <option value="qris">QRIS</option>
                        <option value="transfer_bank">Transfer Bank</option>
                    </select>
                </div>
                
                <button type="submit" name="submit_order" class="btn-submit">
                    <i class="ri-check-line"></i> Proses & Cetak Struk
                </button>
            </form>
        </div>
    </div>

<script>
    const menusData = <?= $menu_json ?>;
    let currentOrder = [];
    const discountRate = 0.10; // 10%
    
    // --- Elemen DOM ---
    const isMemberYes = document.getElementById('is_member_yes');
    const isMemberNo = document.getElementById('is_member_no');
    const memberSelectGroup = document.getElementById('member_select_group');
    const idMemberSelect = document.getElementById('id_member');
    const namaPelangganInput = document.getElementById('nama_pelanggan');
    const orderItemsBody = document.getElementById('order_items_body');
    const addItemBtn = document.getElementById('add_item_btn');
    const menuSelect = document.getElementById('menu_select');
    const quantityInput = document.getElementById('quantity_input');
    const orderItemsInput = document.getElementById('order_items_input');
    const grandTotalDisplay = document.getElementById('grand_total_display');
    const grandTotalHidden = document.getElementById('grand_total_hidden');
    const discountInfo = document.getElementById('discount_info');
    const discountAmountDisplay = document.getElementById('discount_amount');
    
    // --- Fungsi Helper ---
    function formatRupiah(number) {
        return 'Rp ' + (number).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
    
    function calculateTotal() {
        let subTotal = 0;
        currentOrder.forEach(item => {
            subTotal += item.subtotal;
        });
        
        let discount = 0;
        const isMember = isMemberYes.checked;

        if (isMember) {
            discount = subTotal * discountRate;
            discountInfo.style.display = 'block';
            discountAmountDisplay.textContent = formatRupiah(discount);
        } else {
            discountInfo.style.display = 'none';
        }

        const grandTotal = subTotal - discount;
        
        grandTotalDisplay.textContent = formatRupiah(grandTotal);
        grandTotalHidden.value = grandTotal;
        orderItemsInput.value = JSON.stringify(currentOrder);
    }

    function renderItems() {
        orderItemsBody.innerHTML = '';
        currentOrder.forEach((item, index) => {
            const row = orderItemsBody.insertRow();
            row.innerHTML = `
                <td>${item.nama}</td>
                <td>${formatRupiah(item.harga_satuan)}</td>
                <td><input type="number" min="1" value="${item.jumlah}" data-index="${index}" class="qty-input" style="width: 50px; padding: 5px;"></td>
                <td>${formatRupiah(item.subtotal)}</td>
                <td><button type="button" class="btn-remove" data-index="${index}"><i class="ri-delete-bin-line"></i></button></td>
            `;
        });
        
        // Pasang event listener untuk tombol hapus dan input jumlah
        document.querySelectorAll('.btn-remove').forEach(btn => {
            btn.addEventListener('click', removeItem);
        });
        document.querySelectorAll('.qty-input').forEach(input => {
            input.addEventListener('change', updateItemQuantity);
        });
        
        calculateTotal();
    }
    
    // --- Logika Order ---

    function updateItemQuantity(e) {
        const index = e.target.dataset.index;
        const newQty = parseInt(e.target.value);
        
        if (newQty > 0) {
            currentOrder[index].jumlah = newQty;
            currentOrder[index].subtotal = newQty * currentOrder[index].harga_satuan;
            renderItems();
        }
    }
    
    function removeItem(e) {
        const index = e.target.closest('button').dataset.index;
        currentOrder.splice(index, 1);
        renderItems();
    }
    
    function addItem() {
        const selectedOption = menuSelect.options[menuSelect.selectedIndex];
        if (!selectedOption.value) return;

        const id_menu = parseInt(selectedOption.value);
        const nama = selectedOption.dataset.nama;
        const harga_satuan = parseFloat(selectedOption.dataset.harga);
        const jumlah = parseInt(quantityInput.value);

        if (jumlah <= 0) {
            alert("Jumlah harus lebih dari 0.");
            return;
        }

        // Cek jika item sudah ada, update jumlahnya
        const existingItemIndex = currentOrder.findIndex(item => item.id_menu === id_menu);
        
        if (existingItemIndex > -1) {
            currentOrder[existingItemIndex].jumlah += jumlah;
            currentOrder[existingItemIndex].subtotal = currentOrder[existingItemIndex].jumlah * harga_satuan;
        } else {
            currentOrder.push({
                id_menu: id_menu,
                nama: nama,
                harga_satuan: harga_satuan,
                jumlah: jumlah,
                subtotal: jumlah * harga_satuan
            });
        }
        
        // Reset input
        quantityInput.value = 1;
        menuSelect.value = '';

        renderItems();
    }
    
    // --- Logika Member ---

    function toggleMemberFields() {
        const isMember = isMemberYes.checked;
        
        if (isMember) {
            memberSelectGroup.style.display = 'block';
            namaPelangganInput.readOnly = true;
            idMemberSelect.setAttribute('required', 'required');
        } else {
            memberSelectGroup.style.display = 'none';
            namaPelangganInput.readOnly = false;
            idMemberSelect.removeAttribute('required');
            namaPelangganInput.value = '';
            document.getElementById('nomor_hp').value = '';
            document.getElementById('alamat').value = '';
            idMemberSelect.value = ''; // Reset pilihan member
        }
        calculateTotal(); // Recalculate total for discount check
    }

    function fillMemberDetails() {
        const selectedOption = idMemberSelect.options[idMemberSelect.selectedIndex];
        
        if (selectedOption.value) {
            const nama = selectedOption.dataset.nama;
            // ASUMSI: Data Nomor HP dan Alamat tidak tersedia di sini, 
            // sehingga harus diisi manual atau diambil via AJAX
            namaPelangganInput.value = nama;
        } else {
            namaPelangganInput.value = '';
        }
    }

    // --- Inisialisasi Event Listener ---
    isMemberYes.addEventListener('change', toggleMemberFields);
    isMemberNo.addEventListener('change', toggleMemberFields);
    idMemberSelect.addEventListener('change', fillMemberDetails);
    addItemBtn.addEventListener('click', addItem);
    
    // Inisialisasi awal
    toggleMemberFields(); 
    renderItems(); // Render awal
    
</script>
</body>
</html>