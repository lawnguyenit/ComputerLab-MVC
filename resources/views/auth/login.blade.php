<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Hệ thống quản lý phòng máy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Thêm favicon cho tab -->
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}">
</head>
<style>
        :root {
            --primary-color: #17a2b8;
            --secondary-color: #138496;
            --success-color: #1cc88a;
            --background-color: #f8f9fc;
            --text-color: #5a5c69;
        }
        
        body {
            font-family: 'Nunito', sans-serif;
            background: white;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
            background-position: center;
            background-repeat: no-repeat;
            background-color: #e2e8f0;
        }
        
        .login-container {
            max-width: 1000px;
            width: 100%;
            margin: 0 auto;
        }
        
        .card {
            border: none;
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(50, 50, 93, 0.1), 0 5px 15px rgba(0, 0, 0, 0.07);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background-color: rgba(255, 255, 255, 0.9);
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(50, 50, 93, 0.15), 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .card-body {
            padding: 2.5rem;
        }
        
        .login-side {
            background-color: rgba(255, 255, 255, 0.95);
        }
        
        .image-side {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 2rem;
            min-height: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .image-side::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            opacity: 0.3;
        }
        
        .image-side img {
            max-width: 80%;
            margin-bottom: 2rem;
            filter: drop-shadow(0 10px 15px rgba(0, 0, 0, 0.2));
            transition: transform 0.5s ease;
            position: relative;
            z-index: 1;
        }
        
        .image-side img:hover {
            transform: scale(1.05);
        }
        
        .image-side h3 {
            font-weight: 700;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }
        
        .image-side p {
            font-size: 1rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        
        .login-title {
            font-weight: 800;
            color: #17a2b8;
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 1.8rem;
            letter-spacing: 0.5px;
        }
        
        .form-control {
            border-radius: 10rem;
            padding: 0.6rem 1rem;
            font-size: 0.95rem;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            max-width: 90%;
            margin: 0 auto;
        }
        
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(23, 162, 184, 0.25);
            border-color: #17a2b8;
        }
        
        .input-group {
            max-width: 90%;
            margin: 0 auto;
        }
        
        .input-group-text {
            border-radius: 10rem 0 0 10rem;
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding-left: 1.5rem;
            padding-right: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .form-control {
            border-radius: 0 10rem 10rem 0;
        }
        
        .btn-login {
            background: linear-gradient(to right, #17a2b8, #138496);
            border: none;
            color: white;
            border-radius: 10rem;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(23, 162, 184, 0.4);
            max-width: 90%;
            margin: 0 auto;
        }
        
        .btn-login:hover {
            background: linear-gradient(to right, #138496, #17a2b8);
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(253, 255, 255, 0.5);
        }
        
        .btn-login:active {
            transform: translateY(-1px);
        }
        
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .invalid-feedback {
            font-size: 80%;
            margin-left: 1rem;
            color: #e74a3b;
        }
        
        .form-floating label {
            color: #6e707e;
        }
        
        .forgot-password {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.2s;
            font-weight: 600;
        }
        
        .forgot-password:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }
        
        @keyframes fadeOutAlert {
            0% {
                opacity: 1;
                transform: translateY(0);
            }
            80% {
                opacity: 1;
                transform: translateY(0);
            }
            100% {
                opacity: 0;
                transform: translateY(-20px);
                display: none;
            }
        }

        .alert {
            border-radius: 1rem;
            border: none;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            animation: fadeOutAlert 5s ease-in-out forwards;
            font-size: 12px;
            opacity: 1;
        }
        
        .alert-success {
            background-color: rgba(28, 200, 138, 0.15);
            color: #1cc88a;
            border-left: 4px solid #1cc88a;
        }
        
        hr {
            opacity: 0.1;
            margin: 1.5rem 0;
        }
        
        .input-group:focus-within .input-group-text {
            background-color: var(--secondary-color);
        }
        
        .logo-img {
            max-width: 100px;
            margin-bottom: 1rem;
        }
        
        @media (max-width: 767.98px) {
            .image-side {
                display: none;
            }
            
            .login-container {
                padding: 1rem;
            }
            
            .card-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="row g-0">
                <div class="col-md-6 login-side">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <i class="fas fa-desktop fa-3x mb-3 text-primary"></i>
                            <h4 class="login-title">ĐĂNG NHẬP HỆ THỐNG</h4>
                            <p class="text-muted" style="font-size: 1.5rem; font-weight: bold;">COMPUTER LAB</p>
                        </div>
                        
                        @if(session('status'))
                            <div class="alert alert-success mb-4">
                                <i class="fas fa-check-circle me-2"></i>{{ session('status') }}
                            </div>
                        @endif
                        
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            
                            <div class="mb-3">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                                        name="email" value="{{ old('email') }}" required autocomplete="email" 
                                        placeholder="Địa chỉ email" autofocus>
                                </div>
                                @error('email')
                                    <span class="invalid-feedback d-block mt-1" role="alert">
                                        <i class="fas fa-exclamation-circle me-1"></i><strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input id="password" type="password" 
                                        class="form-control @error('password') is-invalid @enderror" 
                                        name="password" required autocomplete="current-password" 
                                        placeholder="Mật khẩu">
                                </div>
                                @error('password')
                                    <span class="invalid-feedback d-block mt-1" role="alert">
                                        <i class="fas fa-exclamation-circle me-1"></i><strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            
                            <div class="mb-3 d-flex justify-content-between align-items-center">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" 
                                        id="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="remember">
                                        <i class="fas fa-save me-1"></i>Ghi nhớ đăng nhập
                                    </label>
                                </div>
                                <a href="{{ route('password.request') }}" class="forgot-password">
                                    <i class="fas fa-question-circle me-1"></i>Quên mật khẩu?
                                </a>
                            </div>
                            
                            <div class="d-grid gap-2 mb-3">
                                <button type="submit" class="btn btn-login">
                                    <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                                </button>
                            </div>
                            
                            <div class="text-center">
                                <hr>
                                <p class="small text-muted">
                                    <i class="fas fa-copyright me-1"></i>{{ date('Y') }} Hệ thống quản lý phòng máy tính
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-md-6 image-side">
                    <div>
                        <i class="fas fa-chalkboard-teacher fa-5x mb-4 text-light"></i>
                        <h3><i class="fas fa-graduation-cap me-2"></i>COMPUTER LAB</h3>
                        <p class="mt-3"><i class="fas fa-check-circle me-2"></i>Giải pháp hiện đại giúp quản lý phòng máy tính hiệu quả, 
                            theo dõi tình trạng thiết bị và lịch sử sử dụng.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>