<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - StudentMove</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .admin-login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }

        .admin-login-header {
            background: linear-gradient(135deg, #007BFF, #28A745);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .admin-login-header i {
            font-size: 3rem;
            margin-bottom: 15px;
            display: block;
        }

        .admin-login-header h1 {
            font-size: 1.8rem;
            font-weight: 600;
            margin: 0;
        }

        .admin-login-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .admin-login-body {
            padding: 40px 30px;
        }

        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
            padding: 12px 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert i {
            font-size: 1.2rem;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 0.95rem;
        }

        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #007BFF;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
        }

        .error-message {
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #007BFF, #0056b3);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #666;
            text-decoration: none;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .back-link a:hover {
            color: #007BFF;
        }

        .password-toggle {
            position: relative;
        }

        .password-toggle-btn {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            font-size: 1.2rem;
            padding: 5px;
        }

        .password-toggle-btn:hover {
            color: #007BFF;
        }
    </style>
</head>
<body>
    <div class="admin-login-container">
        <div class="admin-login-header">
            <i class="bi bi-shield-lock"></i>
            <h1>Admin Panel</h1>
            <p>Enter password to continue</p>
        </div>

        <div class="admin-login-body">
            @if(session('success'))
            <div class="alert alert-success">
                <i class="bi bi-check-circle"></i>
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle"></i>
                {{ session('error') }}
            </div>
            @endif

            <form method="POST" action="{{ route('admin.login') }}">
                @csrf

                <div class="form-group">
                    <label for="password">
                        <i class="bi bi-key"></i> Admin Password
                    </label>
                    <div class="password-toggle">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="@error('password') is-invalid @enderror" 
                            placeholder="Enter admin password"
                            required 
                            autofocus
                        >
                        <button type="button" class="password-toggle-btn" onclick="togglePassword()">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                    @error('password')
                    <div class="error-message">
                        <i class="bi bi-exclamation-circle"></i>
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <button type="submit" class="btn-login">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Login to Admin Panel
                </button>
            </form>

            <div class="back-link">
                <a href="{{ route('home') }}">
                    <i class="bi bi-arrow-left"></i> Back to Home
                </a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }
    </script>
</body>
</html>