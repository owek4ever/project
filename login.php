<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TT Login - User Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 60%);
            animation: rotate 30s linear infinite;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .logo {
            position: absolute;
            top: 40px;
            left: 40px;
            width: 100px;
            height: auto;
            z-index: 10;
        }

        .logo img {
            width: 100%;
            height: auto;
            display: block;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            padding: 60px 50px;
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 5;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .login-title {
            font-size: 32px;
            font-weight: 600;
            color: #2c3e50;
            text-align: center;
            margin-bottom: 40px;
            letter-spacing: -0.5px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group input {
            width: 100%;
            padding: 18px 20px 18px 50px;
            border: 2px solid #e8e8e8;
            border-radius: 12px;
            font-size: 16px;
            background: #f9f9f9;
            color: #333;
            transition: all 0.3s ease;
            outline: none;
        }

        .form-group i {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            font-size: 18px;
        }

        .form-group input::placeholder {
            color: #999;
            font-weight: 400;
        }

        .form-group input:focus {
            border-color: #3498db;
            background: white;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            transform: translateY(-2px);
        }

        .login-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            position: relative;
            overflow: hidden;
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.6s;
        }

        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(52, 152, 219, 0.4);
        }

        .login-btn:hover::before {
            left: 100%;
        }

        .login-btn:active {
            transform: translateY(-1px);
        }

        .login-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .error-message {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 15px 20px;
            border-radius: 12px;
            font-size: 14px;
            margin-bottom: 20px;
            display: none;
            text-align: center;
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
        }

        .success-message {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
            padding: 15px 20px;
            border-radius: 12px;
            font-size: 14px;
            margin-bottom: 20px;
            display: none;
            text-align: center;
            box-shadow: 0 5px 15px rgba(46, 204, 113, 0.3);
        }

        @media (max-width: 480px) {
            .logo {
                top: 20px;
                left: 20px;
                width: 70px;
            }
            
            .login-container {
                padding: 40px 30px;
                margin: 20px;
                border-radius: 15px;
            }
            
            .login-title {
                font-size: 28px;
                margin-bottom: 30px;
            }
            
            .form-group input {
                padding: 15px 15px 15px 45px;
                font-size: 16px;
            }
            
            .login-btn {
                padding: 16px;
                font-size: 16px;
            }
        }

        .login-container {
            animation: slideUp 0.8s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .logo {
            animation: fadeIn 1s ease-out 0.3s both;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="logo">
        <img src="logo.png" alt="Tunisie Telecom Logo" style="width:200px; height:auto;">
    </div>
    
    <div class="login-container">
        <h1 class="login-title">Log In</h1>

        <div class="error-message" id="errorMessage">
            Invalid email or password. Please try again.
        </div>

        <div class="success-message" id="successMessage">
            Login successful! Redirecting...
        </div>

        <form id="loginForm" method="POST">
            <div class="form-group">
                <i class="fas fa-envelope"></i>
                <input type="email" id="email" name="email" required placeholder="Email address">
            </div>

            <div class="form-group">
                <i class="fas fa-lock"></i>
                <input type="password" id="password" name="password" required placeholder="Password">
            </div>

            <button type="submit" class="login-btn" id="loginBtn">
                Log In
            </button>
        </form>
    </div>

    <script>
        function showMessage(type, message) {
            const errorMsg = document.getElementById('errorMessage');
            const successMsg = document.getElementById('successMessage');
            
            errorMsg.style.display = 'none';
            successMsg.style.display = 'none';
            
            if (type === 'error') {
                errorMsg.textContent = message;
                errorMsg.style.display = 'block';
            } else if (type === 'success') {
                successMsg.textContent = message;
                successMsg.style.display = 'block';
            }
        }

        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                showMessage('error', 'Please fill in all required fields.');
                return;
            }

            const loginBtn = document.getElementById('loginBtn');
            const originalText = loginBtn.textContent;
            
            loginBtn.textContent = 'Logging in...';
            loginBtn.disabled = true;
            
            try {
                const formData = new FormData();
                formData.append('email', email);
                formData.append('password', password);
                
                const response = await fetch('auth.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Store complete user data in localStorage for profile functionality
                    const userData = {
                        user_id: data.user.user_id,
                        name: data.user.name,
                        email: data.user.email,
                        role: data.user.role,
                        department: data.user.department || 'Administration',
                        profile_picture: data.user.profile_picture || null
                    };
                    
                    localStorage.setItem('currentUser', JSON.stringify(userData));
                    
                    showMessage('success', 'Welcome! Redirecting to dashboard...');
                    
                    // Redirect based on user role
                    setTimeout(() => {
                        if (data.user.role === 'employee') {
                            window.location.href = 'employee_dashboard.php';
                        } else {
                            window.location.href = 'dashboard.php';
                        }
                    }, 1500);
                } else {
                    showMessage('error', data.message || 'Invalid email or password. Please try again.');
                }
                
            } catch (error) {
                console.error('Login error:', error);
                showMessage('error', 'An error occurred. Please try again.');
            } finally {
                loginBtn.textContent = originalText;
                loginBtn.disabled = false;
            }
        });

        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentNode.style.transform = 'translateY(-2px)';
            });
            
            input.addEventListener('blur', function() {
                this.parentNode.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>