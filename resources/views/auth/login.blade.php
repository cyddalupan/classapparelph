<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CLASS Apparel PH</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        /* Login Page Specific Styles */
        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        .login-page::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            z-index: 1;
        }

        .login-container {
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 2;
        }

        .login-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            padding: 3rem;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .login-logo {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--dark);
            text-decoration: none;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            transition: var(--transition);
        }

        .login-logo:hover {
            color: var(--primary);
            transform: translateY(-2px);
        }

        .login-logo i {
            color: var(--primary);
            font-size: 2rem;
        }

        .login-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
            line-height: 1.2;
        }

        .login-subtitle {
            color: var(--gray);
            font-size: 1rem;
            font-weight: 400;
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .form-label i {
            color: var(--gray);
            font-size: 0.75rem;
        }

        .form-input {
            padding: 0.875rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            color: var(--dark);
            background: white;
            transition: var(--transition);
            width: 100%;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .form-input::placeholder {
            color: #94a3b8;
        }

        .form-input.error {
            border-color: #ef4444;
        }

        .form-error {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        /* Modern Checkbox Design */
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            user-select: none;
            padding: 0.5rem 0;
        }

        .checkbox-input {
            display: none;
        }

        .checkbox-custom {
            width: 20px;
            height: 20px;
            border: 2px solid #cbd5e1;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            flex-shrink: 0;
            position: relative;
        }

        .checkbox-custom::after {
            content: '';
            width: 10px;
            height: 10px;
            background: white;
            border-radius: 2px;
            transform: scale(0);
            transition: var(--transition);
        }

        .checkbox-input:checked + .checkbox-custom {
            background: var(--primary);
            border-color: var(--primary);
        }

        .checkbox-input:checked + .checkbox-custom::after {
            transform: scale(1);
        }

        .checkbox-input:focus + .checkbox-custom {
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .checkbox-label {
            font-size: 0.875rem;
            color: var(--dark);
            font-weight: 500;
            cursor: pointer;
        }

        .checkbox-group:hover .checkbox-custom {
            border-color: var(--primary);
        }

        .checkbox-group:hover .checkbox-label {
            color: var(--primary);
        }

        /* Login Button */
        .login-button {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 8px;
            padding: 1rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            margin-top: 0.5rem;
            width: 100%;
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.3);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .login-button:focus {
            outline: none;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.2);
        }

        .login-button i {
            font-size: 1.125rem;
        }

        /* Links Section */
        .login-links {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
        }

        .login-link {
            color: var(--gray);
            text-decoration: none;
            font-size: 0.875rem;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .login-link:hover {
            color: var(--primary);
        }

        .login-link i {
            font-size: 0.875rem;
        }

        .register-link {
            text-align: center;
            color: var(--gray);
            font-size: 0.875rem;
        }

        .register-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        /* Session Status */
        .session-status {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            text-align: center;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 640px) {
            .login-page {
                padding: 1rem;
            }

            .login-card {
                padding: 2rem;
            }

            .login-title {
                font-size: 1.75rem;
            }
        }

        /* Dark Mode Support */
        @media (prefers-color-scheme: dark) {
            .login-page {
                background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            }

            .login-card {
                background: #1e293b;
                border-color: #334155;
            }

            .login-title,
            .form-label,
            .checkbox-label {
                color: #f1f5f9;
            }

            .login-subtitle {
                color: #94a3b8;
            }

            .form-input {
                background: #334155;
                border-color: #475569;
                color: #f1f5f9;
            }

            .form-input::placeholder {
                color: #94a3b8;
            }

            .login-links {
                border-color: #475569;
            }

            .login-link,
            .register-link {
                color: #94a3b8;
            }
        }
    </style>
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <a href="/" class="login-logo">
                    <i class="fas fa-tshirt"></i>
                    CLASS Apparel PH
                </a>
                <h1 class="login-title">Welcome Back</h1>
                <p class="login-subtitle">Sign in to your account to continue</p>
            </div>

            <!-- Session Status -->
            @if(session('status'))
                <div class="session-status">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="login-form">
                @csrf

                <!-- Email Address -->
                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i>
                        Email Address
                    </label>
                    <input 
                        id="email" 
                        type="email" 
                        name="email" 
                        value="{{ old('email') }}" 
                        required 
                        autofocus 
                        autocomplete="username"
                        placeholder="Enter your email address"
                        class="form-input @error('email') error @enderror"
                    >
                    @error('email')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <input 
                        id="password" 
                        type="password" 
                        name="password" 
                        required 
                        autocomplete="current-password"
                        placeholder="Enter your password"
                        class="form-input @error('password') error @enderror"
                    >
                    @error('password')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Modern Remember Me Checkbox -->
                <label class="checkbox-group">
                    <input 
                        type="checkbox" 
                        id="remember_me" 
                        name="remember" 
                        class="checkbox-input"
                        {{ old('remember') ? 'checked' : '' }}
                    >
                    <span class="checkbox-custom"></span>
                    <span class="checkbox-label">Remember me on this device</span>
                </label>

                <!-- Login Button -->
                <button type="submit" class="login-button">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign In
                </button>
            </form>

            <div class="login-links">
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="login-link">
                        <i class="fas fa-key"></i>
                        Forgot your password?
                    </a>
                @endif

                @if (Route::has('register'))
                    <div class="register-link">
                        Don't have an account? 
                        <a href="{{ route('register') }}">
                            Create one now
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        // Form validation and enhancement
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.login-form');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const rememberCheckbox = document.getElementById('remember_me');

            // Add focus effects
            [emailInput, passwordInput].forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });

                input.addEventListener('blur', function() {
                    this.parentElement.classList.remove('focused');
                    this.classList.toggle('has-value', this.value.trim() !== '');
                });
            });

            // Checkbox enhancement
            rememberCheckbox.addEventListener('change', function() {
                const customCheckbox = this.nextElementSibling;
                if (this.checked) {
                    customCheckbox.style.transform = 'scale(1.1)';
                    setTimeout(() => {
                        customCheckbox.style.transform = 'scale(1)';
                    }, 150);
                }
            });

            // Form submission enhancement
            form.addEventListener('submit', function(e) {
                // Remove any previous error states
                document.querySelectorAll('.form-input').forEach(input => {
                    input.classList.remove('error');
                });

                // Basic validation
                let isValid = true;

                if (!emailInput.value.trim()) {
                    emailInput.classList.add('error');
                    emailInput.focus();
                    isValid = false;
                } else if (!isValidEmail(emailInput.value)) {
                    emailInput.classList.add('error');
                    emailInput.focus();
                    isValid = false;
                }

                if (!passwordInput.value.trim()) {
                    passwordInput.classList.add('error');
                    if (isValid) passwordInput.focus();
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                    
                    // Add shake animation to invalid inputs
                    document.querySelectorAll('.form-input.error').forEach(input => {
                        input.style.animation = 'shake 0.5s ease-in-out';
                        setTimeout(() => {
                            input.style.animation = '';
                        }, 500);
                    });
                } else {
                    // Add loading state to button
                    const submitButton = form.querySelector('.login-button');
                    const originalText = submitButton.innerHTML;
                    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';
                    submitButton.disabled = true;
                    
                    // Re-enable after 3 seconds (in case submission fails)
                    setTimeout(() => {
                        submitButton.innerHTML = originalText;
                        submitButton.disabled = false;
                    }, 3000);
                }
            });

            // Email validation helper
            function isValidEmail(email) {
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return re.test(email);
            }

            // Add shake animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes shake {
                    0%, 100% { transform: translateX(0); }
                    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
                    20%, 40%, 60%, 80% { transform: translateX(5px); }
                }
                
                .form-group.focused .form-label {
                    color: var(--primary);
                }
                
                .form-input.has-value {
                    border-color: #cbd5e1;
                }
            `;
            document.head.appendChild(style);

            // Auto-focus email field if empty
            if (!emailInput.value.trim()) {
                emailInput.focus();
            }
        });
    </script>
</body>
</html>