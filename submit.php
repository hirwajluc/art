<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set proper headers
header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// DB config
require_once 'db.php';

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// If user is already authenticated, redirect to submission page
if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
    header('Location: submission.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Your Art - Greater Art Competition 2025</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            max-width: 520px;
            width: 100%;
            text-align: center;
            margin: auto;
        }

        @media (min-width: 1200px) {
            .login-container {
                padding: 50px 55px;
            }
        }

        .logos-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .logo {
            max-height: 80px;
            max-width: 150px;
            object-fit: contain;
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }

        .form-group {
            margin-bottom: 25px;
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
        }

        input[type="text"] {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-bottom: 15px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .otp-section {
            display: none;
            margin-top: 20px;
        }

        .otp-section.show {
            display: block;
        }

        .countdown {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
            border-left: 4px solid #667eea;
        }

        .countdown.expired {
            border-left-color: #dc3545;
            background: #fff5f5;
        }

        .error {
            background: #fff5f5;
            color: #dc3545;
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
            border-left: 4px solid #dc3545;
        }

        .success {
            background: #f0f9ff;
            color: #0369a1;
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
            border-left: 4px solid #0369a1;
        }

        .loading {
            display: none;
            text-align: center;
            margin: 20px 0;
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .info {
            background: #f0f9ff;
            color: #0369a1;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #0369a1;
            text-align: left;
        }

        .back-link {
            display: inline-block;
            color: #667eea;
            text-decoration: none;
            margin-top: 20px;
            font-weight: 600;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .logos-container {
                flex-direction: column;
                gap: 15px;
            }
            
            .logo {
                max-height: 60px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logos-container">
            <img src="Greater_full_logo.png" alt="Greater ArtCompetition2025 Logo" class="logo" />
            <img src="erasmusplus.png" alt="Erasmus Plus Logo" class="logo" />
        </div>
        
        <h1>Submit Your Art</h1>
        <p class="subtitle">Enter your user code to begin submission</p>

        <div id="message-container"></div>

        <form id="loginForm">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="form-group">
                <label for="userCode">User Code</label>
                <input type="text" id="userCode" name="userCode" placeholder="Enter your GAC code (e.g., GAC1234)" required>
            </div>

            <button type="submit" class="btn" id="submitBtn">Get OTP</button>
        </form>

        <div class="otp-section" id="otpSection">
            <div class="countdown" id="countdown">
                <strong>OTP sent to your email!</strong><br>
                <span id="timer">5:00</span> remaining
            </div>

            <form id="otpForm">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" id="hiddenUserCode" name="userCode">
                
                <div class="form-group">
                    <label for="otp">Enter OTP</label>
                    <input type="text" id="otp" name="otp" placeholder="Enter 6-digit OTP" maxlength="6" required>
                </div>

                <button type="submit" class="btn" id="verifyBtn">Verify OTP</button>
                <button type="button" class="btn" id="resendBtn" style="background: #6c757d;">Resend OTP</button>
            </form>
        </div>

        <div class="loading" id="loading">
            <div class="spinner"></div>
            <p>Processing...</p>
        </div>

        <div class="info">
            <strong>Need help?</strong><br>
            • Your user code was sent to your email after registration<br>
            • OTP expires in 5 minutes<br>
            • Contact support at info@greaterproject.eu
        </div>

        <a href="registration.php" class="back-link">← If not registered click here!</a>
    </div>

    <script>
        let countdownTimer;
        let otpExpiry;

        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const userCode = document.getElementById('userCode').value.trim();
            const submitBtn = document.getElementById('submitBtn');
            const loading = document.getElementById('loading');
            
            if (!userCode) {
                showMessage('Please enter your user code', 'error');
                return;
            }

            // Validate user code format
            if (!/^GAC\d{4}$/.test(userCode)) {
                showMessage('Invalid user code format. It should be like GAC1234', 'error');
                return;
            }

            submitBtn.disabled = true;
            loading.style.display = 'block';
            
            try {
                const response = await fetch('auth_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'request_otp',
                        userCode: userCode,
                        csrf_token: document.querySelector('input[name="csrf_token"]').value
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    showMessage('OTP sent to your registered email address!', 'success');
                    document.getElementById('hiddenUserCode').value = userCode;
                    document.getElementById('otpSection').classList.add('show');
                    document.getElementById('loginForm').style.display = 'none';
                    startCountdown();
                } else {
                    showMessage(data.message || 'Failed to send OTP', 'error');
                }
            } catch (error) {
                showMessage('Network error. Please try again.', 'error');
                console.error('Error:', error);
            } finally {
                submitBtn.disabled = false;
                loading.style.display = 'none';
            }
        });

        document.getElementById('otpForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const otp = document.getElementById('otp').value.trim();
            const userCode = document.getElementById('hiddenUserCode').value;
            const verifyBtn = document.getElementById('verifyBtn');
            const loading = document.getElementById('loading');
            
            if (!otp || otp.length !== 6) {
                showMessage('Please enter a valid 6-digit OTP', 'error');
                return;
            }

            verifyBtn.disabled = true;
            loading.style.display = 'block';
            
            try {
                const response = await fetch('auth_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'verify_otp',
                        userCode: userCode,
                        otp: otp,
                        csrf_token: document.querySelector('input[name="csrf_token"]').value
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    showMessage('Authentication successful! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = 'submission.php';
                    }, 1500);
                } else {
                    showMessage(data.message || 'Invalid OTP', 'error');
                }
            } catch (error) {
                showMessage('Network error. Please try again.', 'error');
                console.error('Error:', error);
            } finally {
                verifyBtn.disabled = false;
                loading.style.display = 'none';
            }
        });

        document.getElementById('resendBtn').addEventListener('click', async function() {
            const userCode = document.getElementById('hiddenUserCode').value;
            const resendBtn = document.getElementById('resendBtn');
            
            resendBtn.disabled = true;
            
            try {
                const response = await fetch('auth_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'request_otp',
                        userCode: userCode,
                        csrf_token: document.querySelector('input[name="csrf_token"]').value
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    showMessage('New OTP sent to your email!', 'success');
                    document.getElementById('otp').value = '';
                    startCountdown();
                } else {
                    showMessage(data.message || 'Failed to resend OTP', 'error');
                }
            } catch (error) {
                showMessage('Network error. Please try again.', 'error');
            } finally {
                setTimeout(() => {
                    resendBtn.disabled = false;
                }, 30000); // 30 second cooldown
            }
        });

        function startCountdown() {
            otpExpiry = Date.now() + (5 * 60 * 1000); // 5 minutes from now
            
            if (countdownTimer) {
                clearInterval(countdownTimer);
            }
            
            countdownTimer = setInterval(updateCountdown, 1000);
            updateCountdown();
        }

        function updateCountdown() {
            const now = Date.now();
            const timeLeft = otpExpiry - now;
            
            if (timeLeft <= 0) {
                clearInterval(countdownTimer);
                document.getElementById('countdown').classList.add('expired');
                document.getElementById('timer').textContent = 'EXPIRED';
                document.getElementById('verifyBtn').disabled = true;
                showMessage('OTP has expired. Please request a new one.', 'error');
                return;
            }
            
            const minutes = Math.floor(timeLeft / 60000);
            const seconds = Math.floor((timeLeft % 60000) / 1000);
            document.getElementById('timer').textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        }

        function showMessage(message, type) {
            const container = document.getElementById('message-container');
            container.innerHTML = `<div class="${type}">${message}</div>`;
            
            // Auto-hide success messages after 5 seconds
            if (type === 'success') {
                setTimeout(() => {
                    container.innerHTML = '';
                }, 5000);
            }
        }

        // Auto-focus on OTP input when section is shown
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.target.classList.contains('show')) {
                    setTimeout(() => {
                        document.getElementById('otp').focus();
                    }, 100);
                }
            });
        });

        observer.observe(document.getElementById('otpSection'), {
            attributes: true,
            attributeFilter: ['class']
        });
    </script>
</body>
</html>