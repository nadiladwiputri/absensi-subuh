<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Orang Tua') - Subuh Monitor</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-dark: #0B4A3A;
            --primary-medium: #115E59;
            --primary-light: #34D399;
            --primary-soft: #ECFDF5;
            --text-dark: #1E293B;
            --text-muted: #64748B;
            --bg-light: #F0FDF4; /* soft mint background */
            --border-color: #E2E8F0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-light);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top Navbar Styling */
        .navbar {
            background-color: var(--primary-dark);
            color: #ffffff;
            padding: 16px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(11, 74, 58, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #ffffff;
        }

        .brand-icon {
            font-size: 1.8rem;
            color: var(--primary-light);
        }

        .brand-text {
            display: flex;
            flex-direction: column;
        }

        .brand-name {
            font-size: 1.2rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .brand-sub {
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.6);
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .navbar-user {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.95rem;
        }

        .user-label {
            font-size: 0.75rem;
            color: var(--primary-light);
            background-color: rgba(52, 211, 153, 0.1);
            padding: 2px 8px;
            border-radius: 99px;
            margin-top: 2px;
        }

        .btn-logout {
            background-color: rgba(239, 68, 68, 0.1);
            color: #EF4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
            padding: 8px 16px;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-logout:hover {
            background-color: #EF4444;
            color: #ffffff;
        }

        /* Container Styling */
        .main-container {
            max-width: 1200px;
            width: 100%;
            margin: 30px auto;
            padding: 0 20px;
            flex-grow: 1;
        }

        /* Typography Helper */
        h1, h2, h3, h4, h5, h6 {
            color: var(--primary-dark);
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 20px;
            color: var(--text-muted);
            font-size: 0.85rem;
            border-top: 1px solid var(--border-color);
            background-color: #ffffff;
            margin-top: auto;
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 12px 20px;
            }
            .user-info {
                display: none;
            }
        }
    </style>
    @yield('styles')
</head>
<body>

    <nav class="navbar">
        <a href="{{ route('wali.dashboard') }}" class="navbar-brand">
            <i class="fa-solid fa-mosque brand-icon"></i>
            <div class="brand-text">
                <span class="brand-name">Subuh Monitor</span>
                <span class="brand-sub">Wali Portal</span>
            </div>
        </a>

        <div class="navbar-user">
            <div class="user-info">
                <span class="user-name">Bpk/Ibu {{ Auth::guard('wali')->user()->nama_wali }}</span>
                <span class="user-label">Wali Santri</span>
            </div>
            
            <form action="{{ route('wali.logout') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" class="btn-logout">
                    <i class="fa-solid fa-right-from-bracket"></i> Keluar
                </button>
            </form>
        </div>
    </nav>

    <main class="main-container">
        @yield('content')
    </main>

    <footer class="footer">
        &copy; {{ date('Y') }} Subuh Monitor. Hak Cipta Dilindungi.
    </footer>

    @yield('scripts')
</body>
</html>
