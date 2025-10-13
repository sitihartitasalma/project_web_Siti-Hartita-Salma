<?php
session_start();
include "koneksi.php";
?>
<!DOCTYPE html>
   <html lang="en">
   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">

      <!--=============== FAVICON ===============-->
      <link rel="shortcut icon" href="assets/img/favicon.png" type="image/x-icon">

      <!--=============== REMIXICONS ===============-->
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">

      <!--=============== SWIPER CSS ===============-->
      <link rel="stylesheet" href="assets/css/swiper-bundle.min.css">

      <!--=============== CSS ===============-->
      <link rel="stylesheet" href="assets/css/styles.css">

      <title>StarCoffee</title>
   <style>
      .cart-count {
         background: crimson;
         color: #fff;
         font-size: 12px;
         padding: 2px 6px;
         border-radius: 50%;
         position: absolute;
         top: -8px;
         right: -10px;
      }
      .nav__cart {
         position: relative;
      }
      
      /* Fix Navigation Layout */
      .nav__list {
         display: flex;
         align-items: center;
         gap: 1.5rem;
         flex-wrap: nowrap;
      }
      
      .nav__item {
         white-space: nowrap;
      }
      
      .nav__link {
         font-size: 14px;
         font-weight: 600;
      }
      
      /* Admin Link Styles */
      .admin-link {
         background: linear-gradient(135deg, #d84315, #bf360c);
         color: white !important;
         padding: 8px 16px;
         border-radius: 20px;
         font-weight: 700;
         display: inline-flex;
         align-items: center;
         gap: 6px;
         transition: all 0.3s ease;
         box-shadow: 0 4px 15px rgba(216, 67, 21, 0.3);
         font-size: 13px;
         text-transform: uppercase;
         letter-spacing: 0.5px;
      }
      
      .admin-link:hover {
         transform: translateY(-3px);
         box-shadow: 0 6px 20px rgba(216, 67, 21, 0.5);
         background: linear-gradient(135deg, #bf360c, #d84315);
      }
      
      .admin-link i {
         font-size: 16px;
         animation: rotate 3s linear infinite;
      }
      
      @keyframes rotate {
         0%, 90%, 100% { transform: rotate(0deg); }
         95% { transform: rotate(15deg); }
      }
      
      /* Member Status Styles */
      .member-status {
         background: linear-gradient(135deg, #2c5f2d 0%, #97bc62 100%);
         color: white;
         padding: 10px 15px;
         border-radius: 25px;
         font-size: 12px;
         font-weight: 600;
         display: flex;
         align-items: center;
         gap: 5px;
      }
      
      .member-discount {
         background: linear-gradient(135deg, #ff6b6b, #ffa500);
         color: white;
         padding: 4px 10px;
         border-radius: 15px;
         font-size: 10px;
         margin-left: 5px;
         font-weight: bold;
         animation: glow 2s ease-in-out infinite alternate;
      }
      
      @keyframes glow {
         from { box-shadow: 0 0 5px rgba(255, 107, 107, 0.5); }
         to { box-shadow: 0 0 15px rgba(255, 107, 107, 0.8); }
      }
      
      .login-link {
         color: #ffffffff;
         text-decoration: none;
         font-weight: 600;
         padding: 10px 20px;
         border: 2px solid #2c5f2d;
         border-radius: 25px;
         transition: all 0.3s ease;
         background: linear-gradient(45deg, transparent, rgba(44, 95, 45, 0.1));
         position: relative;
         overflow: hidden;
      }
      
      .login-link:hover {
         background: linear-gradient(135deg, #2c5f2d, #97bc62);
         color: white;
         transform: translateY(-2px);
         box-shadow: 0 5px 15px rgba(44, 95, 45, 0.3);
      }
      
      .login-link::before {
         content: "💎 ";
         margin-right: 5px;
      }
      
      .alert {
         position: fixed;
         top: 20px;
         right: 20px;
         padding: 15px 25px;
         border-radius: 8px;
         z-index: 1000;
         max-width: 400px;
         animation: slideIn 0.5s ease;
      }
      
      .alert-success {
         background: #d4edda;
         color: #155724;
         border: 1px solid #c3e6cb;
      }
      
      .alert-error {
         background: #f8d7da;
         color: #721c24;
         border: 1px solid #f5c6cb;
      }
      
      @keyframes slideIn {
         from { transform: translateX(100%); opacity: 0; }
         to { transform: translateX(0); opacity: 1; }
      }
      
      .member-price {
         color: #ff5100ff;
         font-weight: bold;
         text-decoration: line-through;
      }
      
      .discounted-price {
         color: #ff3300ff;
         font-weight: bold;
         font-size: 1.1em;
      }
      
      /* Responsive */
      @media screen and (max-width: 1150px) {
         .admin-link {
            padding: 8px 15px;
            font-size: 13px;
         }
      }
   </style>
</head>
<body>
   <header class="header" id="header">
      <nav class="nav container">
         <a href="#" class="nav__logo">STARCOFFEE</a>
         <div class="nav__menu" id="nav-menu">
            <ul class="nav__list">

               <!-- Icon keranjang -->
               <li class="nav__item nav__cart">
                  <a href="tampil_keranjang.php" class="nav__link" id="btnKeranjang">
                     <i class="ri-shopping-cart-line"></i>
                     <span class="cart-count" id="cart-count">
                        <?= isset($_SESSION['keranjang']) ? array_sum($_SESSION['keranjang']) : 0 ?>
                     </span>
                  </a>
               </li>

               <li><a href="#home" class="nav__link">HOME</a></li>
               <li><a href="#popular" class="nav__link">POPULAR</a></li>
               <li><a href="#about" class="nav__link">ABOUT US</a></li>
               <li><a href="#products" class="nav__link">PRODUCTS</a></li>
               <li><a href="#contact" class="nav__link">CONTACT</a></li>
               
               <!-- Admin Link - NEW! -->
               <li class="nav__item">
                  <a href="admin_login.php" class="admin-link">
                     <i class="ri-shield-user-line"></i>
                     ADMIN
                  </a>
               </li>
               
               <!-- Member Status / Login -->
               <li class="nav__item">
                  <?php if (isset($_SESSION['is_member']) && $_SESSION['is_member']): ?>
                     <div class="member-status">
                        <i class="ri-vip-crown-line"></i>
                        <?= htmlspecialchars($_SESSION['member_nama']) ?>
                        <span class="member-discount">⚡ VIP 10%</span>
                        <a href="logout.php" style="color: white; margin-left: 10px; text-decoration: underline;">Logout</a>
                     </div>
                  <?php else: ?>
                     <a href="login.php" class="login-link">
                        <i class="ri-user-line"></i> JADI MEMBER VIP
                     </a>
                  <?php endif; ?>
               </li>
            </ul>

            <div class="nav__close" id="nav-close">
               <i class="ri-close-large-line"></i>
            </div>
         </div>

         <div class="nav__toggle" id="nav-toggle">
            <i class="ri-apps-2-fill"></i>
         </div>
      </nav>
   </header>

   <!-- Alert Messages -->
   <?php if (isset($_GET['message'])): ?>
      <div class="alert alert-<?= isset($_GET['type']) ? $_GET['type'] : 'success' ?>" id="alert-message">
         <i class="ri-information-line"></i>
         <?= htmlspecialchars($_GET['message']) ?>
         <button onclick="this.parentElement.style.display='none'" style="float: right; background: none; border: none; font-size: 18px; cursor: pointer;">&times;</button>
      </div>
   <?php endif; ?>

      <!--==================== MAIN ====================-->
      <main class="main">
         <!--==================== HOME ====================-->
         <section class="home section" id="home">
            <div class="home__container container grid">
            <h1 class="home__title">COLD COFFE</h1>

            <div class="home__images">
               <div class="home__shape"></div>
               <img src="assets/img/home-splash.png" alt="image" class="home__splash">
               <img src="assets/img/bean-img.png" alt="image" class="home__bean-2">
               <img src="assets/img/home-coffee.png" alt="image" class="home__coffee">
               <img src="assets/img/bean-img.png" alt="image" class="home__bean-1">
               <img src="assets/img/ice-img.png" alt="image" class="home__ice-1">
               <img src="assets/img/ice-img.png" alt="image" class="home__ice-2">
               <img src="assets/img/leaf-img.png" alt="image" class="home__leaf">
            </div>

            <img src="assets/img/home-sticker.svg" alt="image" class="home__sticker">

            <div class="home__data">
               <p class="home__description"> 
                  Find delicious hot and cold coffees with the 
                  best varieties, calm the pleasure and enjoy 
                  a good coffee, order now.
                  <?php if (isset($_SESSION['is_member']) && $_SESSION['is_member']): ?>
                     <br><strong style="color: #ff6427ff; font-size: 1.1em; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">✨ HEMAT 10% di SETIAP PEMBELIAN sebagai Member VIP!</strong>
                  <?php endif; ?>
               </p>

               <a href="#about" class="button">Learn More</a>
            </div>
            </div>
         </section>

         <!--==================== POPULAR ====================-->
<section class="popular section" id="popular">
  <div class="popular__container container">
    <h2 class="section__title">POPULAR <br> CREATIONS</h2>

    <div class="popular__swiper swiper">
      <div class="swiper-wrapper">
        <article class="popular__card swiper-slide">
          <div class="popular__images">
            <div class="popular__shape"></div>
            <img src="assets/img/bean-img.png" alt="image" class="popular__bean-1">
            <img src="assets/img/bean-img.png" alt="image" class="popular__bean-2">
            <img src="assets/img/popular-coffee-1.png" alt="image" class="popular__coffee">
          </div>

          <div class="popular__data">
            <h2 class="popular__name">CAPPUCINO LATTE</h2>
            <p class="popular__description">
              Indulge in the simplicity of our delicious cold brew coffee.
            </p>
            
            <?php if (isset($_SESSION['is_member']) && $_SESSION['is_member']): ?>
               <span class="member-price">Rp 17.000</span>
               <a href="#contact" class="button button-dark">Member Price: Rp 15.500</a>
            <?php else: ?>
               <a href="#contact" class="button button-dark">Order now: Rp 17.000</a>
            <?php endif; ?>
          </div>
        </article>

        <article class="popular__card swiper-slide">
          <div class="popular__images">
            <div class="popular__shape"></div>
            <img src="assets/img/bean-img.png" alt="image" class="popular__bean-1">
            <img src="assets/img/bean-img.png" alt="image" class="popular__bean-2">
            <img src="assets/img/popular-coffee-2.png" alt="image" class="popular__coffee">
          </div>

          <div class="popular__data">
            <h2 class="popular__name">MILK COFFEE</h2>
            <p class="popular__description">
              Indulge in the simplicity of our delicious cold brew coffee.
            </p>
            
            <?php if (isset($_SESSION['is_member']) && $_SESSION['is_member']): ?>
               <span class="member-price">Rp 11.000</span>
               <a href="#contact" class="button button-dark">Member Price: Rp 9.500</a>
            <?php else: ?>
               <a href="#contact" class="button button-dark">Order now: Rp 11.000</a>
            <?php endif; ?>
          </div>
        </article>

        <article class="popular__card swiper-slide">
          <div class="popular__images">
            <div class="popular__shape"></div>
            <img src="assets/img/bean-img.png" alt="image" class="popular__bean-1">
            <img src="assets/img/bean-img.png" alt="image" class="popular__bean-2">
            <img src="assets/img/popular-coffee-3.png" alt="image" class="popular__coffee">
          </div>

          <div class="popular__data">
            <h2 class="popular__name">MOCHA COFFEE</h2>
            <p class="popular__description">
              Indulge in the simplicity of our delicious cold brew coffee.
            </p>
            
            <?php if (isset($_SESSION['is_member']) && $_SESSION['is_member']): ?>
               <span class="member-price">Rp 15.000</span>
               <a href="#contact" class="button button-dark">Member Price: Rp 13.500</a>
            <?php else: ?>
               <a href="#contact" class="button button-dark">Order now: Rp 15.000</a>
            <?php endif; ?>
          </div>
        </article>
      </div>
    </div>
  </div>
</section>

         <!--==================== ABOUT ====================-->
         <section class="about section" id="about">
            <div class="about__container container grid">
               <div class="about__data">
                  <h2 class="section__title">LEARN MORE <br> ABOUT US</h2>
                  <p class="about__description">
                     Welcome to StarCoffee, where coffee is pure passion. 
                     From bean to cup, we are dedicated to delivering 
                     excellence in every sip. Join us on a journey of 
                     flavor and quality, crafted with love to create the 
                     ultimate coffee experience.
                     
                     <?php if (!isset($_SESSION['is_member']) || !$_SESSION['is_member']): ?>
                        <br><br><strong style="color: #ff6b6b; font-size: 1.2em; background: linear-gradient(135deg, #ff6b6b, #ffa500); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">💎 BERGABUNG JADI MEMBER VIP & DAPATKAN DISKON 10% SELAMANYA!</strong>
                     <?php endif; ?>
                  </p>
                  <a href="#popular" class="button">The Best Coffees</a>
               </div>

               <div class="about__images">
                  <div class="about__shape"></div>
                  <img src="assets/img/leaf-img.png" alt="image" class="about__leaf-1">
                  <img src="assets/img/leaf-img.png" alt="image" class="about__leaf-2">
                  <img src="assets/img/about-coffee.png" alt="image" class="about__coffee">
               </div>
            </div>
         </section>

         <!--==================== PRODUCTS ====================-->
<section class="products section" id="products">
         <h2 class="section__title">THE MOST <br> REQUESTED</h2>
         
         <?php if (isset($_SESSION['is_member']) && $_SESSION['is_member']): ?>
            <div style="text-align: center; margin-bottom: 30px;">
               <div style="display: inline-flex; align-items: center; background: linear-gradient(135deg, #ff6b6b, #ffa500); color: white; padding: 15px 25px; border-radius: 50px; font-weight: bold; font-size: 16px; box-shadow: 0 8px 20px rgba(255, 107, 107, 0.4); animation: pulse 2s infinite;">
                  <i class="ri-vip-diamond-line" style="margin-right: 8px; font-size: 20px;"></i>
                  MEMBER VIP AKTIF - DISKON 10% BERLAKU UNTUK SEMUA PRODUK!
                  <i class="ri-discount-percent-line" style="margin-left: 8px; font-size: 20px;"></i>
               </div>
            </div>
            <style>
               @keyframes pulse {
                  0% { transform: scale(1); }
                  50% { transform: scale(1.05); }
                  100% { transform: scale(1); }
               }
            </style>
         <?php endif; ?>

         <div class="products__container container grid">
            <?php
            $sql = "SELECT * FROM tb_menu";
            $result = $koneksi->query($sql);

            if ($result->num_rows > 0) {
               while ($row = $result->fetch_assoc()) {
                  $harga_normal = (float)$row["harga_satuan"];
                  $harga_member = $harga_normal * 0.9; // diskon 10%
                  
                  echo '<article class="products__card">
                     <div class="products__images">
                        <div class="products__shape"></div>
                        <img src="assets/img/ice-img.png" alt="image" class="products__ice-1">
                        <img src="assets/img/ice-img.png" alt="image" class="products__ice-2">
                        <img src="assets/img/' . htmlspecialchars($row["foto"]) . '" alt="' . htmlspecialchars($row["nama"]) . '" class="products__coffee">
                     </div>
                     <div class="products__data">
                        <h3 class="products__name">' . htmlspecialchars($row["nama"]) . '</h3>
                        <div class="products__price-button">';
                        
                  if (isset($_SESSION['is_member']) && $_SESSION['is_member']) {
                     echo '<div>
                              <span class="member-price">Rp ' . number_format($harga_normal, 0, ",",".") . '</span><br>
                              <span class="discounted-price">Member: Rp ' . number_format($harga_member, 0, ",",".") . '</span>
                           </div>';
                  } else {
                     echo '<span class="products__price">Rp ' . number_format($harga_normal, 0, ",",".") . '</span>';
                  }
                  
                  echo '      <button class="products__button add-to-cart" data-id="' . $row['id_menu'] . '">
                              <i class="ri-shopping-bag-4-line"></i>
                           </button>
                        </div>
                     </div>
                  </article>';
               }
            } else {
               echo "<p>Tidak ada menu yang tersedia saat ini.</p>";
            }
            $koneksi->close();
            ?>
         </div>
      </section>

         <!--==================== CONTACT ====================-->
         <section class="contact section" id="contact">
            <h2 class="section__title">CONTACT US</h2>

            <div class="contact__container grid">
               <div class="contact__info grid">
                  <div>
                     <h3 class="contact__title">Write US</h3>
                     <div class="contact__social">
                        <a href="https://api.whatsapp.com/send?phone=51123456789&text=Hello, more information!" target="_blank" class="contact__social-link">
                           <i class="ri-whatsapp-fill"></i>
                        </a>
                         <a href="https://m.me/bedimcode" target="_blank" class="contact__social-link">
                           <i class="ri-messenger-fill"></i>
                        </a>
                         <a href="https://t.me/telegram" target="_blank" class="contact__social-link">
                           <i class="ri-telegram-2-fill"></i>
                        </a>
                     </div>
                  </div>
                  <div>
                     <h3 class="contact__title">Location</h3>
                     <address class="contact__address">
                     Pulosari-Mojoroto-Banjarmlati <br>
                     Kota Kediri 
                     </address>
                     <a href="https://maps.app.goo.gl/MAmMDxUBFXBSUzLH7" class="contact__map">
                        <i class="ri-map-pin-fill"></i>
                        <span>View On Map</span>
                     </a>
                  </div>
               </div>
               <div class="contact__info grid">  
                  <div>
                     <h3 class="contact__title">Delivery</h3>
                     <address class="contact__address">
                        +62 857-5507-4905
                     </address>
                  </div>
                  <div>
                     <h3 class="contact__title">Attention</h3>
                     <address class="contact__address">
                        Monday - Saturday <br> 
                        9AM - 10PM
                     </address>
                  </div>
               </div>
               <div class="contact__images">
                  <div class="contact__shape">
                  <img src="assets/img/contact-delivery.png" alt="image" class="contact_delivery">
                  </div>
               </div>
            </div>
         </section>
      </main>

      <!--==================== FOOTER ====================-->
      <footer class="footer">
         <div class="footer__container grid">
            <div>
               <h3 class="footer__title">Social</h3>
               <div class="footer__social">
                  <a href="https://www.facebook.com/" target="_blank" class="footer__social-link">
                  <i class="ri-facebook-circle-fill"></i>
                  </a>
                   <a href="https://www.instagram.com/" target="_blank" class="footer__social-link">
                  <i class="ri-instagram-fill"></i>
                  </a>
                   <a href="https://twitter.com" target="_blank" class="footer__social-link">
                  <i class="ri-twitter-x-line"></i>
                  </a>
               </div>
            </div>

            <div>
            <h3 class="footer__title">Payment Methods</h3>
            <div class="footer__pay">
               <img src="assets/img/footer-card-1.png" alt="image" class="footer__pay-card">
               <img src="assets/img/footer-card-2.png" alt="image" class="footer__pay-card">
               <img src="assets/img/footer-card-3.png" alt="image" class="footer__pay-card">
               <img src="assets/img/footer-card-4.png" alt="image" class="footer__pay-card">
            </div>
            </div>

            <div>
            <h3 class="footer__title">Subscribe For Discounts</h3>
            <form action="" class="footer__form">
               <input type="email" placeholder="Email" class="footer__input">
               <button type="submit" class="footer__button button">Subscribe</button>
            </form>
            </div>
          </div>

          <span class="footer__copy">
            &#169; All Rights Reserved By Bedimcode
          </span>
      </footer>

      <!--=============== JS ===============-->
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
   $(document).ready(function(){
      $(".add-to-cart").click(function(e){
         e.preventDefault();
         var id_menu = $(this).data("id");

         $.post("tambah_keranjang.php", {id_menu: id_menu}, function(data){
            if (data.success) {
               $("#cart-count").text(data.total_item);
            }
         }, "json");
      });
      
      // Auto hide alert after 5 seconds
      setTimeout(function() {
         $("#alert-message").fadeOut();
      }, 5000);
   });
</script>

      <!--========== SCROLL UP ==========-->
      <a href="#" class="scrollup" id="scroll-up">
         <i class="ri-arrow-up-line"></i>
      </a>

      <!--=============== SCROLLREVEAL ===============-->
      <script src="assets/js/scrollreveal.min.js"></script>

      <!--=============== SWIPER JS ===============-->
      <script src="assets/js/swiper-bundle.min.js"></script>

      <!--=============== MAIN JS ===============-->
      <script src="assets/js/main.js"></script>
   </body>
</html>