<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - DentalCare</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: #f5f7fa;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Navigation */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            overflow-y: auto;
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .sidebar-logo {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-menu {
            list-style: none;
        }

        .nav-item {
            margin: 8px 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            border-radius: 8px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            font-size: 14px;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.3);
            font-weight: 600;
            border-left: 3px solid white;
            padding-left: 12px;
        }

        .nav-icon {
            font-size: 18px;
        }

        /* Menu group header */
        .menu-group-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.6);
            margin-top: 20px;
            margin-bottom: 10px;
            padding-left: 15px;
            letter-spacing: 0.5px;
        }

        .divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
            margin: 15px 0;
        }

        /* Badge */
        .badge {
            display: inline-block;
            background: #ff6b6b;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            margin-left: auto;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
            overflow-y: auto;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header-left h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 5px;
        }

        .header-subtitle {
            color: #999;
            font-size: 14px;
        }

        .header-right {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #333;
        }

        .btn-secondary:hover {
            background: #d1d5db;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-sm {
            padding: 8px 15px;
            font-size: 13px;
        }

        /* Alert */
        .alert {
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            display: none;
        }

        .alert.success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
            display: block;
        }

        .alert.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
            display: block;
        }

        .alert.info {
            background: #dbeafe;
            color: #0c4a6e;
            border: 1px solid #93c5fd;
            display: block;
        }

        /* Card */
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            padding: 25px;
            margin-bottom: 30px;
        }

        /* User Profile */
        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            margin-top: auto;
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .user-info h4 {
            font-size: 13px;
            margin: 0;
            font-weight: 600;
        }

        .user-info p {
            font-size: 11px;
            margin: 2px 0 0 0;
            opacity: 0.8;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
                padding: 20px 15px;
                z-index: 1000;
            }

            .sidebar-logo {
                font-size: 16px;
                margin-bottom: 20px;
            }

            .main-content {
                margin-left: 200px;
                padding: 20px;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .header h1 {
                font-size: 22px;
            }

            .badge {
                display: none;
            }
        }

        @media (max-width: 600px) {
            .sidebar {
                width: 150px;
                z-index: 1000;
            }

            .main-content {
                margin-left: 150px;
            }

            .nav-link {
                padding: 10px 12px;
            }

            .nav-icon {
                font-size: 16px;
            }

            .menu-group-title {
                display: none;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <span class="nav-icon">🦷</span>
            <span>DentalCare</span>
        </div>

        <ul class="nav-menu">
            <!-- Dashboard -->
            <li class="nav-item">
                <a href="{{ route('doctor.dashboard') }}" class="nav-link @if(request()->routeIs('doctor.dashboard')) active @endif">
                    <span class="nav-icon">📊</span>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- ========== QUẢN LÝ CA LÀM VIỆC & NGÀY NGHỈ ========== -->
            <div class="menu-group-title">📅 Lịch làm việc</div>

            <li class="nav-item">
                <a href="{{ route('doctor.schedule.create') }}" class="nav-link @if(request()->routeIs('doctor.schedule.create')) active @endif">
                    <span class="nav-icon">✏️</span>
                    <span>Đăng ký ca & Nghỉ</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('doctor.schedule.approved') }}" class="nav-link @if(request()->routeIs('doctor.schedule.approved')) active @endif">
                    <span class="nav-icon">✅</span>
                    <span>Lịch duyệt</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('doctor.schedule.off-days') }}" class="nav-link @if(request()->routeIs('doctor.schedule.off-days')) active @endif">
                    <span class="nav-icon">🏖️</span>
                    <span>Ngày nghỉ</span>
                </a>
            </li>

            <div class="divider"></div>

            <!-- ========== CÀI ĐẶT ========== -->
            <div class="menu-group-title">⚙️ Cài đặt</div>

            <li class="nav-item">
                <a href="{{ route('doctor.settings') }}" class="nav-link @if(request()->routeIs('doctor.settings')) active @endif">
                    <span class="nav-icon">👤</span>
                    <span>Cài đặt cá nhân</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('logout') }}" class="nav-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <span class="nav-icon">🚪</span>
                    <span>Đăng xuất</span>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </li>
        </ul>

        <!-- User Profile -->
        @auth
        <div class="user-profile">
            <div class="user-avatar">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <div class="user-info">
                <h4>{{ Auth::user()->name }}</h4>
                <p>Bác sĩ</p>
            </div>
        </div>
        @endauth
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Header -->
            <div class="header">
                <div class="header-left">
                    <h1>@yield('page-title')</h1>
                    <p class="header-subtitle">@yield('page-subtitle')</p>
                </div>
                <div class="header-right">
                    @yield('header-actions')
                </div>
            </div>

            <!-- Alerts -->
            @if(session('success'))
                <div class="alert success">
                    {!! session('success') !!}
                </div>
            @endif

            @if(session('error'))
                <div class="alert error">
                    {!! session('error') !!}
                </div>
            @endif

            @if(session('info'))
                <div class="alert info">
                    {!! session('info') !!}
                </div>
            @endif

            <!-- Errors & Messages Display -->
            @if ($errors->any())
                <div style="background: #fee2e2; border: 2px solid #dc2626; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                    <h4 style="color: #991b1b; margin: 0 0 0.5rem 0;">❌ Lỗi:</h4>
                    @foreach ($errors->all() as $error)
                        <p style="color: #991b1b; margin: 0.25rem 0;">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @if (session('success'))
                <div style="background: #dcfce7; border: 2px solid #16a34a; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                    <p style="color: #15803d; margin: 0;">✅ {{ session('success') }}</p>
                </div>
            @endif

            <!-- Content -->
            @yield('content')
        </div>
    </main>

    @yield('scripts')
</body>
</html>