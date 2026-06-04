<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - DentalCare</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        :root {
            --font-title: 'Outfit', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            --font-body: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            
            /* Theme color: Emerald & Soft Teal */
            --primary: #10b981;
            --primary-hover: #059669;
            --primary-light: #d1fae5;
            --primary-gradient: linear-gradient(135deg, #10b981 0%, #0d9488 100%);
            --primary-glow: rgba(16, 185, 129, 0.4);
            
            --success: #10b981;
            --success-light: #d1fae5;
            --success-dark: #065f46;
            
            --error: #ef4444;
            --error-light: #fee2e2;
            --error-dark: #991b1b;
            
            --info: #3b82f6;
            --info-light: #dbeafe;
            --info-dark: #1e3a8a;
            
            --bg-color: #f8fafc;
            --card-bg: rgba(255, 255, 255, 0.9);
            --sidebar-bg: linear-gradient(180deg, #042f2e 0%, #115e59 100%);
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: rgba(226, 232, 240, 0.8);
            
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-full: 9999px;
            
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 20px -2px rgba(15, 23, 42, 0.08);
            --shadow-lg: 0 10px 30px -5px rgba(15, 23, 42, 0.12);
        }

        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        body { 
            font-family: var(--font-body); 
            background: var(--bg-color);
            color: var(--text-main);
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
            background-image: 
                radial-gradient(at 0% 0%, rgba(16, 185, 129, 0.05) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(13, 148, 136, 0.05) 0px, transparent 50%);
        }

        /* Sidebar Navigation */
        .sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            color: white;
            padding: 30px 20px;
            overflow-y: auto;
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            box-shadow: 5px 0 30px rgba(4, 47, 46, 0.3);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }

        .sidebar-logo {
            font-family: var(--font-title);
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 35px;
            display: flex;
            align-items: center;
            gap: 12px;
            background: linear-gradient(135deg, #34d399 0%, #14b8a6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }
        
        .sidebar-logo-icon {
            width: 36px;
            height: 36px;
            border-radius: 9px;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            color: white;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        }

        .nav-icon {
            width: 22px;
            flex-shrink: 0;
        }

        .nav-menu {
            list-style: none;
            flex-grow: 1;
        }

        .nav-item {
            margin: 6px 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: var(--radius-md);
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            border: 1px solid transparent;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.05);
            color: white;
            transform: translateX(4px);
            border-color: rgba(255, 255, 255, 0.05);
        }

        .nav-link.active {
            background: var(--primary-gradient);
            color: white;
            font-weight: 600;
            box-shadow: 0 4px 15px var(--primary-glow);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .nav-icon {
            font-size: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* Menu group header */
        .menu-group-title {
            font-family: var(--font-title);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.4);
            margin-top: 25px;
            margin-bottom: 8px;
            padding-left: 16px;
            letter-spacing: 1.5px;
        }

        .divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.06);
            margin: 20px 0;
        }

        /* Badge */
        .badge {
            display: inline-block;
            background: #f43f5e;
            color: white;
            padding: 2px 8px;
            border-radius: var(--radius-full);
            font-size: 11px;
            font-weight: 700;
            margin-left: auto;
            box-shadow: 0 0 10px rgba(244, 63, 94, 0.4);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 40px;
            overflow-y: auto;
            min-height: 100vh;
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
            margin-bottom: 35px;
            gap: 20px;
        }

        .header-left h1 {
            font-family: var(--font-title);
            font-size: 32px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 6px;
            letter-spacing: -0.5px;
        }

        .header-subtitle {
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 400;
        }

        .header-right {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 22px;
            border: 1px solid transparent;
            border-radius: var(--radius-md);
            cursor: pointer;
            font-family: var(--font-body);
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.45);
        }

        .btn-secondary {
            background: white;
            color: var(--text-main);
            border-color: var(--border-color);
            box-shadow: var(--shadow-sm);
        }

        .btn-secondary:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .btn-danger {
            background: var(--error);
            color: white;
            box-shadow: 0 4px 14px rgba(239, 68, 68, 0.3);
        }

        .btn-danger:hover {
            background: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.45);
        }

        .btn-sm {
            padding: 8px 16px;
            font-size: 13px;
            border-radius: var(--radius-sm);
        }

        /* Alert */
        .alert {
            padding: 16px 20px;
            margin-bottom: 25px;
            border-radius: var(--radius-lg);
            display: none;
            font-size: 14px;
            font-weight: 500;
            line-height: 1.5;
            box-shadow: var(--shadow-sm);
            border: 1px solid transparent;
        }

        .alert.success {
            background: var(--success-light);
            color: var(--success-dark);
            border-color: rgba(16, 185, 129, 0.2);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert.error {
            background: var(--error-light);
            color: var(--error-dark);
            border-color: rgba(239, 68, 68, 0.2);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert.info {
            background: var(--info-light);
            color: var(--info-dark);
            border-color: rgba(59, 130, 246, 0.2);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Card */
        .card {
            background: var(--card-bg);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            padding: 30px;
            margin-bottom: 30px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            box-shadow: var(--shadow-lg);
        }

        /* User Profile */
        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px;
            margin-top: auto;
            background: rgba(255, 255, 255, 0.04);
            border-radius: var(--radius-lg);
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }
        
        .user-profile:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .user-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--primary-gradient);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 700;
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .user-info h4 {
            font-family: var(--font-title);
            font-size: 14px;
            margin: 0;
            font-weight: 600;
            color: white;
        }

        .user-info p {
            font-size: 11px;
            margin-top: 2px;
            color: rgba(255, 255, 255, 0.5);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                width: 240px;
                padding: 20px 15px;
            }
            .main-content {
                margin-left: 240px;
                padding: 30px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 80px;
                padding: 20px 10px;
                align-items: center;
            }

            .sidebar-logo span:not(.nav-icon) {
                display: none;
            }
            
            .sidebar-logo {
                margin-bottom: 25px;
            }

            .main-content {
                margin-left: 80px;
                padding: 24px;
            }

            .nav-link {
                padding: 12px;
                justify-content: center;
            }
            
            .nav-link span:not(.nav-icon) {
                display: none;
            }

            .menu-group-title, .divider {
                display: none;
            }
            
            .user-profile {
                padding: 8px;
                justify-content: center;
            }
            
            .user-info {
                display: none;
            }

            .badge {
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
            <div class="sidebar-logo-icon"><i class="ri-tooth-fill"></i></div>
            <span>DentalCare</span>
        </div>

        <ul class="nav-menu">
            <li class="nav-item">
                <a href="{{ route('doctor.dashboard') }}" class="nav-link @if(request()->routeIs('doctor.dashboard')) active @endif">
                    <i class="nav-icon ri-dashboard-3-line"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <div class="menu-group-title">Lịch làm việc</div>

            <li class="nav-item">
                <a href="{{ route('doctor.schedule.create') }}" class="nav-link @if(request()->routeIs('doctor.schedule.create')) active @endif">
                    <i class="nav-icon ri-edit-line"></i>
                    <span>Đăng ký ca &amp; Nghỉ</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('doctor.schedule.approved') }}" class="nav-link @if(request()->routeIs('doctor.schedule.approved')) active @endif">
                    <i class="nav-icon ri-calendar-check-line"></i>
                    <span>Lịch duyệt</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('doctor.schedule.off-days') }}" class="nav-link @if(request()->routeIs('doctor.schedule.off-days')) active @endif">
                    <i class="nav-icon ri-sun-line"></i>
                    <span>Ngày nghỉ</span>
                </a>
            </li>

            <div class="divider"></div>

            <div class="menu-group-title">Cài đặt</div>

            <li class="nav-item">
                <a href="{{ route('doctor.settings') }}" class="nav-link @if(request()->routeIs('doctor.settings')) active @endif">
                    <i class="nav-icon ri-user-settings-line"></i>
                    <span>Cài đặt cá nhân</span>
                </a>
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
            <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;border-radius:7px;background:rgba(239,68,68,0.15);color:rgba(239,68,68,0.8);font-size:15px;cursor:pointer;border:none;transition:all 0.2s;" title="Đăng xuất">
                    <i class="ri-logout-box-r-line"></i>
                </button>
            </form>
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