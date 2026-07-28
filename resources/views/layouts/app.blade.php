<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Subuh Monitor</title>

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
            display: flex;
            min-height: 100vh;
            color: var(--text-dark);
        }

        /* Sidebar Styling */
        .sidebar {
            background-color: var(--primary-dark);
            width: 260px;
            color: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 30px 20px;
            box-shadow: 4px 0 20px rgba(11, 74, 58, 0.1);
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 40px;
            padding-left: 10px;
        }

        .brand-icon {
            font-size: 1.8rem;
            color: var(--primary-light);
        }

        .brand-name {
            font-size: 1.25rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .brand-sub {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.5);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .nav-menu {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex-grow: 1;
        }

        .nav-item a {
            display: flex;
            align-items: center;
            gap: 14px;
            color: rgba(255, 255, 255, 0.7);
            padding: 14px 16px;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-item a:hover, .nav-item.active a {
            background-color: var(--primary-medium);
            color: #ffffff;
        }

        .nav-item.active a {
            background-color: #D1FAE5; /* light green active state */
            color: var(--primary-dark);
            font-weight: 600;
        }

        .sidebar-user {
            background-color: rgba(255, 255, 255, 0.05);
            padding: 16px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: auto;
        }

        .user-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background-color: var(--primary-medium);
            display: flex;
            justify-content: center;
            align-items: center;
            color: #ffffff;
            font-size: 1.1rem;
            font-weight: 600;
            border: 2px solid var(--primary-light);
        }

        .user-info {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .user-name {
            font-size: 0.9rem;
            font-weight: 600;
            color: #ffffff;
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
        }

        .user-role {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.5);
            text-transform: capitalize;
        }

        .btn-logout {
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            padding: 5px;
            font-size: 1.1rem;
            transition: color 0.3s ease;
            margin-left: auto;
        }

        .btn-logout:hover {
            color: #EF4444;
        }

        /* Main Content Container */
        .main-wrapper {
            margin-left: 260px;
            flex-grow: 1;
            padding: 40px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        /* Header section */
        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .header-title h1 {
            font-size: 1.85rem;
            font-weight: 700;
            color: var(--primary-dark);
        }

        .header-title p {
            font-size: 0.95rem;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding: 10px 16px 10px 40px;
            border: 1.5px solid var(--border-color);
            border-radius: 12px;
            font-size: 0.9rem;
            width: 250px;
            transition: all 0.3s ease;
            background-color: #ffffff;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary-medium);
            box-shadow: 0 0 0 3px rgba(17, 94, 89, 0.1);
        }

        .search-box i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        /* Live indicator */
        .live-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            background-color: #D1FAE5;
            color: #065F46;
            padding: 8px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .live-dot {
            width: 8px;
            height: 8px;
            background-color: #10B981;
            border-radius: 50%;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            }
            70% {
                transform: scale(1);
                box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
            }
            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }

        /* Card and layouts */
        .card {
            background-color: #ffffff;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
            border: 1px solid rgba(226, 232, 240, 0.5);
        }

        /* Realtime Alert/Toast overlay */
        .toast-notification {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: #ffffff;
            border-left: 6px solid var(--primary-light);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            gap: 16px;
            z-index: 1000;
            transform: translateY(150px);
            opacity: 0;
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            max-width: 380px;
        }

        .toast-notification.show {
            transform: translateY(0);
            opacity: 1;
        }

        .toast-icon {
            font-size: 2.2rem;
            color: var(--primary-medium);
        }

        .toast-body {
            flex-grow: 1;
        }

        .toast-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 4px;
        }

        .toast-text {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .toast-close {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 1rem;
            align-self: flex-start;
        }

        /* Custom Responsive styles */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                padding: 20px 10px;
            }
            .brand-name, .brand-sub, .user-info, .sidebar-brand span, .brand-description {
                display: none;
            }
            .sidebar-brand {
                justify-content: center;
                padding-left: 0;
            }
            .main-wrapper {
                margin-left: 70px;
                padding: 20px;
            }
            .top-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }

        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .data-table th {
            text-align: left;
            padding: 16px 20px;
            font-size: 0.85rem;
            text-transform: uppercase;
            color: var(--text-muted);
            font-weight: 600;
            border-bottom: 1.5px solid var(--border-color);
            letter-spacing: 0.5px;
        }

        .data-table td {
            padding: 16px 20px;
            font-size: 0.95rem;
            border-bottom: 1px solid rgba(226, 232, 240, 0.5);
            vertical-align: middle;
        }

        .data-table tr:hover {
            background-color: #F8FAFC;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div>
            <div class="sidebar-brand">
                <i class="fa-solid fa-mosque brand-icon"></i>
                <div>
                    <h2 class="brand-name">Subuh Monitor</h2>
                </div>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item {{ Request::routeIs('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item {{ Request::routeIs('santri.*') ? 'active' : '' }}">
                    <a href="{{ route('santri.index') }}">
                        <i class="fa-solid fa-user-group"></i>
                        <span>Data Santri</span>
                    </a>
                </li>
                <li class="nav-item {{ Request::routeIs('rekapitulasi') ? 'active' : '' }}">
                    <a href="{{ route('rekapitulasi') }}">
                        <i class="fa-solid fa-clipboard-list"></i>
                        <span>Rekapitulasi</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Logged In User Info & Logout -->
        <div class="sidebar-user">
            <div class="user-avatar">
                {{ strtoupper(substr(Auth::user()->nama ?? 'A', 0, 1)) }}
            </div>
            <div class="user-info">
                <span class="user-name" title="{{ Auth::user()->nama }}">{{ Auth::user()->nama }}</span>
                <span class="user-role">{{ Auth::user()->role }}</span>
            </div>
            <form action="{{ route('logout') }}" method="POST" style="display: flex;">
                @csrf
                <button type="submit" class="btn-logout" title="Keluar">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <div class="top-header">
            <div class="header-title">
                <h1>@yield('header_title', 'Dashboard')</h1>
                <p>@yield('header_subtitle', 'Sistem Absensi Subuh Real-Time')</p>
            </div>
        </div>

        <!-- Page Content -->
        @yield('content')
    </div>



    @yield('scripts')
</body>
</html>
