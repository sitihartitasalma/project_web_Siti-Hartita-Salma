<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Register - StarCoffee</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #d44604ff 0%, #00c9c9ff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .auth-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 400px;
            max-width: 90vw;
        }
        
        .auth-header {
            background: linear-gradient(135deg, #0e62e0ff 0%, #f0832bff 100%);
            color: white;
            text-align: center;
            padding: 2rem;
        }
        
        .auth-header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .auth-header p {
            opacity: 0.9;
        }
        
        .auth-body {
            padding: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
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
            border-color: #2c5f2d;
        }
        
        .btn {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, #000dffff 0%, #97bc62 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .auth-switch {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e1e1e1;
        }
        
        .auth-switch a {
            color: #2c5f2d;
            text-decoration: none;
            font-weight: 500;
        }
        
        .auth-switch a:hover {
            text-decoration: underline;
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
        }
        
        .back-home:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .member-benefits {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #2c5f2d;
        }
        
        .member-benefits h4 {
            color: #2c5f2d;
            margin-bottom: 0.5rem;
        }
        
        .member-benefits p {
            color: #666;
            font-size: 0.9rem;
        }
        
        .hidden {
            display: none;
        }
        
        .alert {
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1rem;
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
    </style>
</head>
<body>
    <a href="index.php" class="back-home">
        <i class="ri-home-line"></i> Back to Home
    </a>

    <div class="auth-container">
        <div class="auth-header">
            <h1 id="auth-title">STARCOFFEE</h1>
            <p id="auth-subtitle">Login to your member account</p>
        </div>
        
        <div class="auth-body">
            <div class="member-benefits">
                <h4><i class="ri-vip-crown-line"></i> Member Benefits</h4>
                <p>Get 10% discount on every purchase as a registered member!</p>
            </div>

            <div id="alert-container"></div>
            
            <!-- Form Login -->
            <form id="login-form" action="proses_login.php" method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn">
                    <i class="ri-login-box-line"></i> Login
                </button>
                
                <div class="auth-switch">
                    <p>Don't have an account? <a href="#" id="show-register">Register here</a></p>
                </div>
            </form>
            
            <!-- Form Register -->
            <form id="register-form" action="proses_register.php" method="POST" class="hidden">
                <div class="form-group">
                    <label for="reg-nama">Full Name</label>
                    <input type="text" id="reg-nama" name="nama" required>
                </div>
                
                <div class="form-group">
                    <label for="reg-email">Email</label>
                    <input type="email" id="reg-email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="reg-telepon">Phone Number</label>
                    <input type="tel" id="reg-telepon" name="telepon" required>
                </div>
                
                <div class="form-group">
                    <label for="reg-alamat">Address</label>
                    <input type="text" id="reg-alamat" name="alamat" required>
                </div>
                
                <div class="form-group">
                    <label for="reg-password">Password</label>
                    <input type="password" id="reg-password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="reg-confirm-password">Confirm Password</label>
                    <input type="password" id="reg-confirm-password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn">
                    <i class="ri-user-add-line"></i> Register Now
                </button>
                
                <div class="auth-switch">
                    <p>Already have an account? <a href="#" id="show-login">Login here</a></p>
                </div>
            </form>
        </div>
    </div>

    <script>
        const loginForm = document.getElementById('login-form');
        const registerForm = document.getElementById('register-form');
        const showRegisterLink = document.getElementById('show-register');
        const showLoginLink = document.getElementById('show-login');
        const authTitle = document.getElementById('auth-title');
        const authSubtitle = document.getElementById('auth-subtitle');
        
        showRegisterLink.addEventListener('click', (e) => {
            e.preventDefault();
            loginForm.classList.add('hidden');
            registerForm.classList.remove('hidden');
            authTitle.textContent = 'REGISTER MEMBER';
            authSubtitle.textContent = 'Join and get 10% discount';
        });
        
        showLoginLink.addEventListener('click', (e) => {
            e.preventDefault();
            registerForm.classList.add('hidden');
            loginForm.classList.remove('hidden');
            authTitle.textContent = 'STARCOFFEE';
            authSubtitle.textContent = 'Login to your member account';
        });
        
        // Validasi konfirmasi password
        registerForm.addEventListener('submit', (e) => {
            const password = document.getElementById('reg-password').value;
            const confirmPassword = document.getElementById('reg-confirm-password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                showAlert('Password and confirm password do not match!', 'error');
            }
        });
        
        function showAlert(message, type) {
            const alertContainer = document.getElementById('alert-container');
            alertContainer.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
            
            setTimeout(() => {
                alertContainer.innerHTML = '';
            }, 5000);
        }
        
        // Cek URL parameter untuk pesan
        const urlParams = new URLSearchParams(window.location.search);
        const message = urlParams.get('message');
        const type = urlParams.get('type');
        
        if (message) {
            showAlert(decodeURIComponent(message), type || 'error');
        }
    </script>
</body>
</html>