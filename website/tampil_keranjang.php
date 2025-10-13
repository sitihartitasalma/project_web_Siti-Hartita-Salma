<?php
session_start();
include "koneksi.php";
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Keranjang Belanja</title>
  <link rel="stylesheet" href="assets/css/styles.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    /* Tambahkan CSS ini untuk menampilkan harga member */
    .member-price {
        color: #888;
        text-decoration: line-through;
        font-size: 0.9em;
    }
    .discounted-price {
        color: #c0392b;
        font-weight: bold;
    }
    .member-notice {
        background-color: #e8f5e9;
        border: 1px solid #c3e6cb;
        color: #155724;
        padding: 10px 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
  </style>
</head>
<body>
  
  <h2 class="cart__title">Keranjang Belanja</h2>

  <!-- Tampilkan notifikasi jika user adalah member -->
  <?php if (isset($_SESSION['is_member']) && $_SESSION['is_member'] === true): ?>
    <div class="member-notice">
      <strong>🎉 Status Member VIP Aktif!</strong> Anda mendapat diskon 10% untuk setiap produk.
    </div>
  <?php endif; ?>

  <table class="cart__table" border="1" cellpadding="8">
    <tr>
      <th>Produk</th>
      <th>Harga</th>
      <th>Jumlah</th>
      <th>Total</th>
    </tr>
    <?php
    $total_belanja = 0;

    if (!empty($_SESSION['keranjang'])) {
        foreach ($_SESSION['keranjang'] as $id_menu => $jumlah) {
            $query  = mysqli_query($koneksi, "SELECT * FROM tb_menu WHERE id_menu='$id_menu'");
            $produk = mysqli_fetch_assoc($query);

            if ($produk) {
                // --- PERUBAHAN LOGIKA HARGA ---
                $harga_normal = (float) $produk['harga_satuan'];
                
                // Cek apakah user adalah member yang sudah login
                if (isset($_SESSION['is_member']) && $_SESSION['is_member'] === true) {
                    // Jika member, berikan diskon 10%
                    $harga_akhir = $harga_normal * 0.9;
                } else {
                    // Jika bukan member, gunakan harga normal
                    $harga_akhir = $harga_normal;
                }
                
                $jumlah = (int) $jumlah;
                $total  = $harga_akhir * $jumlah;
                $total_belanja += $total;
                ?>
                <tr id="row-<?= $id_menu ?>">
                  <td><?= htmlspecialchars($produk['nama']) ?></td>
                  <td>
                    <?php if (isset($_SESSION['is_member']) && $_SESSION['is_member'] === true): ?>
                        <span class="member-price">Rp <?= number_format($harga_normal,0,',','.') ?></span><br>
                        <span class="discounted-price">Rp <?= number_format($harga_akhir,0,',','.') ?></span>
                    <?php else: ?>
                        Rp <?= number_format($harga_akhir,0,',','.') ?>
                    <?php endif; ?>
                  </td>
                  <td>
                    <button class="btn-update" data-id="<?= $id_menu ?>" data-action="minus">-</button>
                    <span id="qty-<?= $id_menu ?>"><?= $jumlah ?></span>
                    <button class="btn-update" data-id="<?= $id_menu ?>" data-action="plus">+</button>
                  </td>
                  <td id="total-<?= $id_menu ?>">Rp <?= number_format($total,0,',','.') ?></td>
                </tr>
                <?php
            }
        }
    }
    ?>
    <tr>
      <td colspan="3"><b>Total Belanja</b></td>
      <td><b id="grand-total">Rp <?= number_format($total_belanja,0,',','.') ?></b></td>
    </tr>
  </table>

  <a href="checkout.php" class="cart__checkout">Checkout</a>

  <script>
  $(document).ready(function(){
      $(".btn-update").click(function(){
          var id_menu = $(this).data("id");
          var action = $(this).data("action");

          $.post("update_keranjang.php", {id_menu: id_menu, action: action}, function(data){
              if (data.jumlah > 0) {
                  $("#qty-" + id_menu).text(data.jumlah);
                  $("#total-" + id_menu).text("Rp " + data.subtotal.toLocaleString("id-ID"));
              } else {
                  $("#row-" + id_menu).fadeOut(function(){$(this).remove();});
              }
              $("#grand-total").text("Rp " + data.total_belanja.toLocaleString("id-ID"));
              // Perbarui juga jumlah di icon keranjang (jika ada)
              $("#cart-count").text(data.total_item);
          }, "json");
      });
  });
  </script>

</body>
</html>