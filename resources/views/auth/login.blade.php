<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--favicon-->
    <link rel="icon" href="{{asset('backend/assets/images/favicon-32x32.png')}}" type="image/png')}}" />
    <!--plugins-->
    <link href="{{asset('backend/assets/plugins/simplebar/css/simplebar.css')}}" rel="stylesheet" />
    <link href="{{asset('backend/assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css')}}" rel="stylesheet" />
    <link href="{{asset('backend/assets/plugins/metismenu/css/metisMenu.min.css')}}" rel="stylesheet" />
    <!-- Bootstrap CSS -->
    <link href="{{asset('backend/assets/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('backend/assets/css/bootstrap-extended.css')}}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{asset('backend/assets/css/app.css')}}" rel="stylesheet">
    <link href="{{asset('backend/assets/css/icons.css')}}" rel="stylesheet">
    <title>Login - Padma Inventory System</title>
    
    <style>
        :root {
            --primary-color: #df2591;
            --primary-dark: #b91d75;
            --secondary-color: #818285;
            --accent-color: #df2591;
            --text-dark: #1f2937;
            --text-light: #818285;
            --bg-gradient-start: #df2591;
            --bg-gradient-end: #818285;
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Background Pattern */
        body::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
            animation: backgroundMove 20s ease-in-out infinite;
        }

        @keyframes backgroundMove {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(20px, 20px); }
        }

        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-left {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 60px 40px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .login-left::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 30s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .login-left-content {
            position: relative;
            z-index: 1;
        }

        .brand-logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .brand-logo i {
            font-size: 40px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .login-left h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 15px;
            line-height: 1.2;
        }

        .login-left p {
            font-size: 16px;
            opacity: 0.9;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin: 30px 0 0 0;
        }

        .feature-list li {
            padding: 12px 0;
            display: flex;
            align-items: center;
            font-size: 14px;
            opacity: 0.95;
        }

        .feature-list li i {
            font-size: 20px;
            margin-right: 12px;
            color: #ffffff;
            background: rgba(255, 255, 255, 0.25);
            padding: 8px;
            border-radius: 8px;
        }

        .login-right {
            padding: 60px 50px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-header h2 {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .login-header p {
            color: var(--text-light);
            font-size: 15px;
        }

        .form-floating {
            margin-bottom: 20px;
        }

        .form-floating > .form-control {
            height: 58px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 1rem 1rem;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .form-floating > .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(223, 37, 145, 0.15);
        }

        .form-floating > label {
            padding: 1rem 1rem;
            font-size: 14px;
            color: var(--text-light);
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-light);
            font-size: 20px;
            z-index: 5;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: var(--primary-color);
        }

        .btn-login {
            width: 100%;
            height: 56px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(223, 37, 145, 0.4);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(223, 37, 145, 0.6);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .forgot-link {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .forgot-link:hover {
            color: var(--primary-dark);
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 20px;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .divider {
            text-align: center;
            margin: 30px 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 1px;
            background: #e5e7eb;
        }

        .divider span {
            background: white;
            padding: 0 15px;
            position: relative;
            color: var(--text-light);
            font-size: 14px;
        }

        @media (max-width: 991px) {
            .login-left {
                padding: 40px 30px;
            }

            .login-right {
                padding: 40px 30px;
            }

            .login-left h1 {
                font-size: 24px;
            }

            .feature-list {
                display: none;
            }
        }

        @media (max-width: 576px) {
            .login-card {
                border-radius: 15px;
            }

            .login-right {
                padding: 30px 20px;
            }

            .login-header h2 {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>
<div class="login-container">
    <div class="login-card">
        <div class="row g-0">
            <!-- Left Side - Branding & Features -->
            <div class="col-lg-5 d-none d-lg-block">
                <div class="login-left">
                    <div class="login-left-content">
                        <div class="brand-logo">
                            <i class='bx bx-package'></i>
                        </div>
                        
                        <h1>Padma Inventory Management System</h1>
                        <p>Complete solution for managing your inventory, stock, and warehouse operations efficiently.</p>
                        
                        <ul class="feature-list">
                            <li>
                                <i class='bx bx-check-circle'></i>
                                <span>Real-time Stock Management</span>
                            </li>
                            <li>
                                <i class='bx bx-check-circle'></i>
                                <span>CSV Import & Export</span>
                            </li>
                            <li>
                                <i class='bx bx-check-circle'></i>
                                <span>Multi-branch Support</span>
                            </li>
                            <li>
                                <i class='bx bx-check-circle'></i>
                                <span>Detailed Reports & Analytics</span>
                            </li>
                            <li>
                                <i class='bx bx-check-circle'></i>
                                <span>Package Management</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Right Side - Login Form -->
            <div class="col-lg-7">
                <div class="login-right">
                    <div class="login-header">
                        <h2>Welcome Back!</h2>
                        <p>Please login to access your inventory dashboard</p>
                    </div>

                    @if(Session::has('fail'))
                        <div class="alert alert-danger d-flex align-items-center">
                            <i class='bx bx-error-circle me-2' style="font-size: 20px;"></i>
                            <div>
                                {{Session::get('fail')}}
                                {{session::forget('fail')}}
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <!-- Username Field -->
                        <div class="form-floating">
                            <input id="email" 
                                   type="text" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   required 
                                   autocomplete="email" 
                                   autofocus 
                                   placeholder="Username">
                            <label for="email">
                                <i class='bx bx-user me-2'></i>Username
                            </label>
                            @error('email')
                                <div class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                        </div>

                        <!-- Password Field -->
                        <div class="form-floating position-relative">
                            <input id="password" 
                                   type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   name="password" 
                                   required 
                                   autocomplete="current-password" 
                                   placeholder="Password">
                            <label for="password">
                                <i class='bx bx-lock-alt me-2'></i>Password
                            </label>
                            <span class="password-toggle" id="togglePassword">
                                <i class='bx bx-hide' id="toggleIcon"></i>
                            </span>
                            @error('password')
                                <div class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                        </div>

                        <!-- Remember Me & Forgot Password -->
                        <div class="remember-forgot">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       name="remember" 
                                       id="remember" 
                                       {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">
                                    Remember me
                                </label>
                            </div>
                            
                            @if (Route::has('password.request'))
                                <a class="forgot-link" href="{{ route('password.request') }}">
                                    Forgot password?
                                </a>
                            @endif
                        </div>

                        <!-- Login Button -->
                        <button type="submit" class="btn-login">
                            <i class='bx bx-log-in-circle'></i>
                            <span>Sign In</span>
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <small class="text-muted">
                            © {{ date('Y') }} Padma Inventory System. All rights reserved.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Bootstrap JS -->
<script src="{{asset('backend/assets/js/bootstrap.bundle.min.js')}}"></script>
<!--plugins-->
<script src="{{asset('backend/assets/js/jquery.min.js')}}"></script>

<script>
    // Password Toggle
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('bx-hide');
            toggleIcon.classList.add('bx-show');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('bx-show');
            toggleIcon.classList.add('bx-hide');
        }
    });

    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 500);
        });
    }, 5000);

    // Form validation animation
    document.querySelector('form').addEventListener('submit', function(e) {
        const btn = document.querySelector('.btn-login');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Signing in...';
        btn.disabled = true;
    });

    // Add floating label animation
    document.querySelectorAll('.form-floating input').forEach(function(input) {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            if (this.value === '') {
                this.parentElement.classList.remove('focused');
            }
        });
    });
</script>
</body>


</html>
