<?php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php?page=dashboard');
    exit;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    // Simple authentication (you should use proper password hashing in production)
    $valid_users = [
        'admin' => 'admin123',
        'jury' => 'jury123'
    ];
    
    if (isset($valid_users[$username]) && $valid_users[$username] === $password) {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = $username;
        $_SESSION['login_time'] = time();
        
        // Set remember me cookie if checked
        if ($remember) {
            setcookie('remember_user', $username, time() + (30 * 24 * 60 * 60), '/'); // 30 days
        }
        
        header('Location: index.php?page=dashboard');
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In - GREATER Art Competition</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1E90FF;
            --secondary: #FFD700;
            --accent: #00BFFF;
            --success: #32CD32;
            --warning: #FFA500;
            --danger: #FF6347;
            --dark: #2C3E50;
            --light: #F8F9FA;
            --gray: #6C757D;
            --white: #FFFFFF;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Background Animation */
        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>') repeat;
            animation: drift 20s linear infinite;
            pointer-events: none;
        }

        @keyframes drift {
            0% { transform: translateX(0) translateY(0); }
            100% { transform: translateX(-50px) translateY(-50px); }
        }

        /* Floating Elements */
        .floating-elements {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
        }

        .floating-element {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 15s infinite linear;
        }

        .floating-element:nth-child(1) {
            width: 80px;
            height: 80px;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-element:nth-child(2) {
            width: 120px;
            height: 120px;
            left: 80%;
            animation-delay: -5s;
        }

        .floating-element:nth-child(3) {
            width: 60px;
            height: 60px;
            left: 50%;
            animation-delay: -10s;
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100px) rotate(360deg);
                opacity: 0;
            }
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 90%;
            max-width: 1000px;
            display: flex;
            min-height: 600px;
            position: relative;
            z-index: 1;
        }

        .login-left {
            flex: 1;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .login-left::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: pulse 4s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1) rotate(0deg); opacity: 0.3; }
            50% { transform: scale(1.2) rotate(180deg); opacity: 0.1; }
        }

        .welcome-content {
            position: relative;
            z-index: 2;
        }

        .logo {
            width: 120px;
            height: 120px;
            background: var(--secondary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .logo::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60px;
            height: 60px;
            background: var(--primary);
            border-radius: 50%;
        }

        .logo::after {
            content: '☀️';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 36px;
            z-index: 2;
        }

        .welcome-content h1 {
            color: white;
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 15px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .welcome-content p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .features {
            display: flex;
            gap: 30px;
            margin-top: 40px;
        }

        .feature {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: white;
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 10px;
            backdrop-filter: blur(10px);
        }

        .feature span {
            font-size: 14px;
            text-align: center;
            opacity: 0.9;
        }

        .login-right {
            flex: 1;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-header h2 {
            color: var(--dark);
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .login-header p {
            color: var(--gray);
            font-size: 16px;
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .form-group {
            position: relative;
        }

        .form-input {
            width: 100%;
            padding: 18px 20px 18px 55px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(30, 144, 255, 0.1);
        }

        .form-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            font-size: 18px;
            transition: color 0.3s ease;
        }

        .form-input:focus + .form-icon {
            color: var(--primary);
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 10px 0;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
        }

        .remember-me label {
            color: var(--dark);
            font-size: 14px;
            cursor: pointer;
        }

        .forgot-password {
            color: var(--primary);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .forgot-password:hover {
            color: var(--accent);
        }

        .login-btn {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white;
            padding: 18px 30px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(30, 144, 255, 0.3);
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(30, 144, 255, 0.4);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .error-message {
            background: #fee;
            color: var(--danger);
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid var(--danger);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .back-link {
            text-align: center;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #e1e5e9;
        }

        .back-link a {
            color: var(--gray);
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .back-link a:hover {
            color: var(--primary);
        }

        .security-info {
            background: #e8f4fd;
            border: 1px solid #bee5eb;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
        }

        .security-info h4 {
            color: var(--primary);
            font-size: 14px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .security-info p {
            color: var(--dark);
            font-size: 12px;
            line-height: 1.4;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                max-width: 400px;
                min-height: auto;
            }

            .login-left {
                padding: 40px 30px;
            }

            .welcome-content h1 {
                font-size: 28px;
            }

            .features {
                flex-direction: column;
                gap: 20px;
            }

            .login-right {
                padding: 40px 30px;
            }

            .login-header h2 {
                font-size: 24px;
            }
        }

        /* Loading Animation */
        .loading {
            display: none;
            position: relative;
        }

        .loading::after {
            content: '';
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: translateY(-50%) rotate(0deg); }
            100% { transform: translateY(-50%) rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="floating-elements">
        <div class="floating-element"></div>
        <div class="floating-element"></div>
        <div class="floating-element"></div>
    </div>

    <div class="login-container">
        <div class="login-left">
            <div class="welcome-content">
                <div class="logo"></div>
                <h1>GREATER</h1>
                <p>Growing Rwanda Energy Awareness Through Higher Education</p>
                
                <div class="features">
                    <div class="feature">
                        <div class="feature-icon">
                            <i class="fas fa-palette"></i>
                        </div>
                        <span>Art Competition</span>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <span>Green Energy</span>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <span>Recognition</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="login-right">
            <div class="login-header">
                <h2>Log In</h2>
                <p>Login to continue to admin panel</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form class="login-form" method="POST" action="" id="loginForm">
                <div class="form-group">
                    <input type="text" 
                           class="form-input" 
                           name="username" 
                           placeholder="Username" 
                           required
                           value="<?php echo isset($_COOKIE['remember_user']) ? htmlspecialchars($_COOKIE['remember_user']) : ''; ?>">
                    <i class="fas fa-user form-icon"></i>
                </div>

                <div class="form-group">
                    <input type="password" 
                           class="form-input" 
                           name="password" 
                           placeholder="Password" 
                           required>
                    <i class="fas fa-lock form-icon"></i>
                </div>

                <div class="form-options">
                    <div class="remember-me">
                        <input type="checkbox" 
                               id="remember" 
                               name="remember"
                               <?php echo isset($_COOKIE['remember_user']) ? 'checked' : ''; ?>>
                        <label for="remember">Remember Me</label>
                    </div>
                    <a href="#" class="forgot-password">Lost your password?</a>
                </div>

                <button type="submit" class="login-btn" id="loginBtn">
                    <span class="btn-text">Log In</span>
                    <div class="loading"></div>
                </button>
            </form>

            <div class="security-info">
                <h4>
                    <i class="fas fa-shield-alt"></i>
                    Secure Login
                </h4>
                <p>Your login is protected with industry-standard security measures. Use your admin credentials to access the GREATER Art Competition management panel.</p>
            </div>

            <div class="back-link">
                <a href="../">
                    <i class="fas fa-arrow-left"></i>
                    ← Go to GREATER Project
                </a>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('loginBtn');
            const btnText = btn.querySelector('.btn-text');
            const loading = btn.querySelector('.loading');
            
            btnText.style.opacity = '0';
            loading.style.display = 'block';
            btn.style.pointerEvents = 'none';
            
            // Re-enable after 3 seconds if form doesn't submit
            setTimeout(() => {
                btnText.style.opacity = '1';
                loading.style.display = 'none';
                btn.style.pointerEvents = 'auto';
            }, 3000);
        });

        // Add floating animation to input focus
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-2px)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });

        // Auto-focus username field
        document.addEventListener('DOMContentLoaded', function() {
            const usernameField = document.querySelector('input[name="username"]');
            if (usernameField && !usernameField.value) {
                usernameField.focus();
            }
        });
    </script>
</body>
</html>