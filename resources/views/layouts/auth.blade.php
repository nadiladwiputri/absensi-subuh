<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Subuh Monitor') - Sistem Absensi Subuh</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-dark: #0B4A3A;
            --primary-medium: #115E59;
            --primary-light: #34D399;
            --primary-soft: #ECFDF5;
            --text-dark: #1E293B;
            --text-muted: #64748B;
            --bg-light: #F8FAFC;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-light);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .auth-container {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            display: flex;
            width: 1000px;
            max-width: 100%;
            min-height: 600px;
            overflow: hidden;
        }

        .auth-sidebar {
            background-color: var(--primary-dark);
            background-image: linear-gradient(135deg, var(--primary-dark) 0%, #064E3B 100%);
            color: #ffffff;
            width: 45%;
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
        }

        /* Mosque watermark/graphic effect at bottom of sidebar */
        .auth-sidebar::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 200px;
            background: url('https://illustrations.popsy.co/white/mosque.svg') no-repeat bottom center;
            background-size: contain;
            opacity: 0.15;
            pointer-events: none;
        }

        .brand-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-top: 40px;
        }

        .logo-icon {
            font-size: 4rem;
            color: var(--primary-light);
            margin-bottom: 20px;
            filter: drop-shadow(0 0 10px rgba(52, 211, 153, 0.3));
        }

        .brand-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
        }

        .brand-subtitle {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.7);
            font-weight: 400;
            margin-bottom: 30px;
        }

        .brand-description {
            font-size: 0.95rem;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 300;
            text-align: center;
            max-width: 280px;
        }

        .auth-footer {
            text-align: center;
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.5);
            z-index: 2;
        }

        .auth-form-section {
            width: 55%;
            padding: 60px 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-header {
            margin-bottom: 30px;
        }

        .form-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .form-subtitle {
            font-size: 0.95rem;
            color: var(--text-muted);
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-label {
            display: block;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1rem;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px 14px 46px;
            border: 1.5px solid #E2E8F0;
            border-radius: 12px;
            font-size: 0.95rem;
            color: var(--text-dark);
            background-color: #FFFFFF;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-medium);
            box-shadow: 0 0 0 4px rgba(17, 94, 89, 0.1);
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            cursor: pointer;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 0.9rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-dark);
            cursor: pointer;
        }

        .remember-me input {
            accent-color: var(--primary-medium);
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .forgot-password {
            color: var(--primary-medium);
            text-decoration: none;
            font-weight: 500;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background-color: var(--primary-medium);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .btn-submit:hover {
            background-color: var(--primary-dark);
            box-shadow: 0 4px 12px rgba(11, 74, 58, 0.2);
        }

        .btn-secondary {
            width: 100%;
            padding: 14px;
            background-color: #ffffff;
            color: var(--primary-medium);
            border: 1.5px solid #E2E8F0;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 15px;
            text-decoration: none;
        }

        .btn-secondary:hover {
            background-color: var(--bg-light);
            border-color: var(--primary-medium);
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            color: var(--text-muted);
            margin: 20px 0;
            font-size: 0.85rem;
        }

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1.5px solid #E2E8F0;
        }

        .divider:not(:empty)::before {
            margin-right: 15px;
        }

        .divider:not(:empty)::after {
            margin-left: 15px;
        }

        .error-container {
            background-color: #FEF2F2;
            border-left: 4px solid #EF4444;
            color: #991B1B;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .error-list {
            list-style: none;
        }

        .footer-outer {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.85rem;
            color: var(--text-muted);
            text-align: center;
        }

        @media (max-width: 900px) {
            .auth-container {
                flex-direction: column;
                max-width: 450px;
            }
            .auth-sidebar {
                width: 100%;
                padding: 40px 30px;
                min-height: 200px;
            }
            .auth-form-section {
                width: 100%;
                padding: 40px 30px;
            }
            .brand-description {
                display: none;
            }
            .brand-container {
                margin-top: 10px;
            }
            .logo-icon {
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>

    <div class="auth-container">
        <!-- Sidebar -->
        <div class="auth-sidebar">
            <div class="brand-container">
                <!-- Simple Mosque Graphic/Icon -->
                <div class="logo-icon">
                    <i class="fa-solid fa-mosque"></i>
                </div>
                <h1 class="brand-title">Subuh Monitor</h1>
                <p class="brand-subtitle">Sistem Absensi Subuh</p>
                <p class="brand-description">
                    Sistem absensi sidik jari berbasis IoT untuk memantau kehadiran santri secara real-time.
                </p>
            </div>
            
            <div class="auth-footer">
                <p>&copy; 2026 Pesantren Digital Stewardship. All rights reserved.</p>
            </div>
        </div>
        
        <!-- Form Section -->
        <div class="auth-form-section">
            @yield('content')
        </div>
    </div>

    @yield('scripts')
</body>
</html>
