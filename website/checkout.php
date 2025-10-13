<?php
session_start();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout - Coffee Shop</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      background-color: #004d40ff;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 20px 0;
    }

    .checkout-container {
      background-color: #fff;
      padding: 30px 40px;
      border-radius: 10px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 500px;
    }

    .checkout-container h2 {
      text-align: center;
      margin-bottom: 25px;
      color: #4B2C20;
    }

    /* MEMBER INFO BOX */
    .member-info {
      background: linear-gradient(135deg, #4caf50, #45a049);
      color: white;
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 20px;
      border-left: 4px solid #388e3c;
    }

    .member-info p {
      margin: 5px 0;
      font-size: 14px;
    }

    .member-info strong {
      font-size: 16px;
      display: block;
      margin-bottom: 5px;
    }

    .member-discount {
      background: rgba(255, 255, 255, 0.2);
      padding: 8px 12px;
      border-radius: 5px;
      margin-top: 10px;
      font-weight: bold;
      text-align: center;
    }

    form label {
      font-weight: bold;
      margin-bottom: 5px;
      display: block;
      color: #333;
    }

    input, textarea, select {
      width: 100%;
      padding: 10px;
      margin-bottom: 20px;
      border: 1px solid #ccc;
      border-radius: 5px;
      transition: border-color 0.3s;
      font-family: 'Poppins', sans-serif;
    }

    input:focus, textarea:focus, select:focus {
      border-color: #a0522d;
      outline: none;
    }

    .payment-methods {
      margin-bottom: 20px;
    }

    .payment-option {
      display: flex;
      align-items: center;
      padding: 12px;
      margin-bottom: 10px;
      border: 2px solid #e0e0e0;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.3s;
    }

    .payment-option:hover {
      border-color: #a0522d;
      background-color: #f9f9f9;
    }

    .payment-option.selected {
      border-color: #a0522d;
      background-color: #fff3e0;
    }

    .payment-option input[type="radio"] {
      margin-right: 12px;
      width: auto;
      margin-bottom: 0;
    }

    .payment-info {
      display: flex;
      align-items: center;
      flex: 1;
    }

    .payment-icon {
      font-size: 24px;
      margin-right: 12px;
      width: 30px;
      text-align: center;
    }

    .payment-details {
      flex: 1;
    }

    .payment-name {
      font-weight: bold;
      color: #333;
      margin-bottom: 2px;
    }

    .payment-desc {
      font-size: 12px;
      color: #666;
    }

    button {
      width: 100%;
      padding: 12px;
      background-color: #0b7dda;
      color: white;
      border: none;
      border-radius: 5px;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.3s;
      font-family: 'Poppins', sans-serif;
    }

    button:hover {
      background-color: #593120;
    }

    button:disabled {
      background-color: #ccc;
      cursor: not-allowed;
    }

    @media (max-width: 600px) {
      .checkout-container {
        padding: 20px;
        margin: 10px;
      }
      
      .payment-option {
        padding: 10px;
      }
      
      .payment-icon {
        font-size: 20px;
      }
    }
  </style>
</head>
<body>

  <div class="checkout-container">
    <h2>Checkout - Isi Data Pelanggan</h2>

    <!-- TAMPILKAN INFO MEMBER JIKA LOGIN -->
    <?php if (isset($_SESSION['is_member']) && $_SESSION['is_member']): ?>
    <div class="member-info">
      <strong>✨ MEMBER VIP ✨</strong>
      <p>Nama: <?= htmlspecialchars($_SESSION['member_nama']) ?></p>
      <p>Email: <?= htmlspecialchars($_SESSION['member_email']) ?></p>
      <div class="member-discount">Diskon 10% untuk semua produk</div>
    </div>
    <?php endif; ?>

    <!-- FORM CHECKOUT -->
    <form method="post" action="proses_checkout.php" id="checkoutForm">
        <label for="nama_pembeli">Nama:</label>
        <input type="text" id="nama_pembeli" name="nama_pembeli" required 
               value="<?= isset($_SESSION['member_nama']) ? htmlspecialchars($_SESSION['member_nama']) : '' ?>">

        <label for="nomor_pembeli">No HP:</label>
        <input type="text" id="nomor_pembeli" name="nomor_pembeli" required
               value="<?= isset($_SESSION['member_telepon']) ? htmlspecialchars($_SESSION['member_telepon']) : '' ?>">

        <label for="alamat">Alamat:</label>
        <textarea id="alamat" name="alamat" rows="3" required><?= isset($_SESSION['member_alamat']) ? htmlspecialchars($_SESSION['member_alamat']) : '' ?></textarea>

        <label for="catatan">Catatan Pesanan:</label>
        <textarea id="catatan" name="catatan" rows="2" placeholder="Catatan pesanan (opsional)"></textarea>

        <div class="payment-methods">
            <label>Pilih Metode Pembayaran:</label>
            
            <div class="payment-option" onclick="selectPayment('gopay')">
                <input type="radio" id="gopay" name="metode_pembayaran" value="E-Wallet (GoPay)" required>
                <div class="payment-info">
                    <div class="payment-icon">🟢</div>
                    <div class="payment-details">
                        <div class="payment-name">GoPay</div>
                        <div class="payment-desc">Pembayaran digital via GoPay</div>
                    </div>
                </div>
            </div>

            <div class="payment-option" onclick="selectPayment('dana')">
                <input type="radio" id="dana" name="metode_pembayaran" value="E-Wallet (DANA)">
                <div class="payment-info">
                    <div class="payment-icon">🔵</div>
                    <div class="payment-details">
                        <div class="payment-name">DANA</div>
                        <div class="payment-desc">Pembayaran digital via DANA</div>
                    </div>
                </div>
            </div>

            <div class="payment-option" onclick="selectPayment('ovo')">
                <input type="radio" id="ovo" name="metode_pembayaran" value="E-Wallet (OVO)">
                <div class="payment-info">
                    <div class="payment-icon">🟣</div>
                    <div class="payment-details">
                        <div class="payment-name">OVO</div>
                        <div class="payment-desc">Pembayaran digital via OVO</div>
                    </div>
                </div>
            </div>

            <div class="payment-option" onclick="selectPayment('transfer')">
                <input type="radio" id="transfer" name="metode_pembayaran" value="Transfer Bank (BCA)">
                <div class="payment-info">
                    <div class="payment-icon">🏦</div>
                    <div class="payment-details">
                        <div class="payment-name">Transfer Bank</div>
                        <div class="payment-desc">Transfer ke rekening BCA</div>
                    </div>
                </div>
            </div>

            <div class="payment-option" onclick="selectPayment('qris')">
                <input type="radio" id="qris" name="metode_pembayaran" value="QRIS">
                <div class="payment-info">
                    <div class="payment-icon">📱</div>
                    <div class="payment-details">
                        <div class="payment-name">QRIS</div>
                        <div class="payment-desc">Scan QR Code untuk pembayaran</div>
                    </div>
                </div>
            </div>

            <div class="payment-option" onclick="selectPayment('shopeepay')">
                <input type="radio" id="shopeepay" name="metode_pembayaran" value="E-Wallet (ShopeePay)">
                <div class="payment-info">
                    <div class="payment-icon">🧡</div>
                    <div class="payment-details">
                        <div class="payment-name">ShopeePay</div>
                        <div class="payment-desc">Pembayaran digital via ShopeePay</div>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" id="submitBtn">Proses Pesanan</button>
    </form>
  </div>

  <script>
    function selectPayment(method) {
        // Remove selected class from all options
        document.querySelectorAll('.payment-option').forEach(option => {
            option.classList.remove('selected');
        });
        
        // Add selected class to clicked option
        event.currentTarget.classList.add('selected');
        
        // Check the radio button
        document.getElementById(method).checked = true;
        
        // Enable submit button
        document.getElementById('submitBtn').disabled = false;
    }

    // Form validation
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        const selectedPayment = document.querySelector('input[name="metode_pembayaran"]:checked');
        
        if (!selectedPayment) {
            e.preventDefault();
            alert('Silakan pilih metode pembayaran terlebih dahulu!');
            return;
        }
    });

    // Initially disable submit button until payment method is selected
    document.addEventListener('DOMContentLoaded', function() {
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        
        // Enable button when any payment method is selected
        document.querySelectorAll('input[name="metode_pembayaran"]').forEach(radio => {
            radio.addEventListener('change', function() {
                submitBtn.disabled = false;
            });
        });
    });
  </script>

</body>
</html>