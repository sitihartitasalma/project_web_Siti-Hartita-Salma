<?php
session_start();

// Jika ingin logout otomatis saat browser ditutup, tambahkan ini:
// Hapus cookie session agar tidak persist
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Jika sudah login dan masih dalam waktu yang ditentukan (misalnya 1 jam)
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    // Cek apakah session sudah expired (opsional, set timeout 1 jam)
    $timeout_duration = 3600; // 1 jam dalam detik
    
    if (isset($_SESSION['admin_login_time'])) {
        $elapsed_time = time() - $_SESSION['admin_login_time'];
        
        if ($elapsed_time > $timeout_duration) {
            // Session expired, destroy dan minta login lagi
            session_unset();
            session_destroy();
            session_start();
            header("Location: admin_login.php?error=Session expired, silakan login kembali");
            exit();
        } else {
            // Session masih valid, redirect ke dashboard
            header("Location: admin_dashboard.php");
            exit();
        }
    } else {
        // Tidak ada login time, redirect ke dashboard
        header("Location: admin_dashboard.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - StarCoffee</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #004d40 0%, #00695c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .admin-login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            width: 400px;
            max-width: 90vw;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #d84315 0%, #bf360c 100%);
            color: white;
            text-align: center;
            padding: 2rem;
        }
        
        .admin-header i {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }
        
        .admin-header h1 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        .admin-header p {
            opacity: 0.9;
            font-size: 0.9rem;
        }
        
        .admin-body {
            padding: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 600;
        }
        
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e1e1;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #d84315;
        }
        
        .btn-admin {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, #d84315 0%, #bf360c 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(216, 67, 21, 0.3);
        }
        
        .back-home {
            position: absolute;
            top: 2rem;
            left: 2rem;
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            text-decoration: none;
            transition: background 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .back-home:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .alert {
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        
        .alert-success {
            background: #e8f5e8;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        
        .admin-info {
            background: #fff3e0;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #ff9800;
        }
        
        .admin-info h4 {
            color: #ef6c00;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .admin-info p {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-home">
        <i class="ri-home-line"></i> Kembali ke Beranda
    </a>

    <div class="admin-login-container">
        <div class="admin-header">
            <i class="ri-shield-user-line"></i>
            <h1>ADMIN PANEL</h1>
            <p>StarCoffee Management System</p>
        </div>
        
        <div class="admin-body">
            <div class="admin-info">
                <h4><i class="ri-information-line"></i> Akses Admin</h4>
                <p>• Kelola data produk (Tambah, Edit, Hapus)</p>
                <p>• Monitor pesanan pelanggan</p>
                <p>• Manajemen data member</p>
                <p>• Lihat statistik penjualan</p>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    <i class="ri-error-warning-line"></i>
                    <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['message'])): ?>
                <div class="alert alert-success">
                    <i class="ri-checkbox-circle-line"></i>
                    <?= htmlspecialchars($_GET['message']) ?>
                </div>
            <?php endif; ?>
            
            <form action="proses_admin_login.php" method="POST">
                <div class="form-group">
                    <label for="username">Username Admin</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn-admin">
                    <i class="ri-login-circle-line"></i> 
                    Masuk ke Panel Admin
                </button>
            </form>
        </div>
    </div>

    <script>
        // Auto focus ke username field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });
        
        // Enter key navigation
        document.getElementById('username').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('password').focus();
            }
        });
    </script>
</body>
</html>