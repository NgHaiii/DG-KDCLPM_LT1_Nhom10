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

            --primary: #0ea5e9;
            --primary-hover: #0284c7;
            --primary-light: #e0f2fe;
            --primary-gradient: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%);
            --primary-glow: rgba(14, 165, 233, 0.4);

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
            --sidebar-bg: linear-gradient(180deg, #082f49 0%, #0c4a6e 100%);
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

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: var(--font-body);
            background: var(--bg-color);
            color: var(--text-main);
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
            background-image:
                radial-gradient(at 0% 0%, rgba(14, 165, 233, 0.05) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(56, 189, 248, 0.05) 0px, transparent 50%);
        }

        .sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            color: white;
            padding: 28px 16px;
            overflow-y: auto;
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            box-shadow: 5px 0 30px rgba(17, 12, 36, 0.3);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }

        .sidebar-logo {
            font-family: var(--font-title);
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 32px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 8px;
            text-decoration: none;
        }

        .sidebar-logo-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: white;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.4);
        }

        .sidebar-logo-text {
            background: linear-gradient(135deg, #7dd3fc 0%, #38bdf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }

        .nav-menu {
            list-style: none;
            flex-grow: 1;
        }

        .nav-item {
            margin: 3px 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 10px 14px;
            border-radius: var(--radius-md);
            color: rgba(255, 255, 255, 0.65);
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            border: 1px solid transparent;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.07);
            color: white;
            transform: translateX(3px);
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
            width: 22px;
            flex-shrink: 0;
        }

        .nav-group-title {
            font-family: var(--font-title);
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.35);
            margin-top: 22px;
            margin-bottom: 6px;
            padding-left: 14px;
            letter-spacing: 1.5px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px;
            margin-top: 16px;
            background: rgba(255, 255, 255, 0.04);
            border-radius: var(--radius-lg);
            border: 1px solid rgba(255, 255, 255, 0.06);
            transition: all 0.3s ease;
        }

        .user-profile:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-gradient);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 700;
            box-shadow: 0 0 14px rgba(14, 165, 233, 0.35);
            border: 2px solid rgba(255, 255, 255, 0.2);
            flex-shrink: 0;
        }

        .user-profile-info {
            flex: 1;
            min-width: 0;
        }

        .user-profile-info h4 {
            font-family: var(--font-title);
            font-size: 13px;
            font-weight: 600;
            color: white;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-profile-info p {
            font-size: 11px;
            margin-top: 1px;
            color: rgba(255, 255, 255, 0.45);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .logout-icon-btn {
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: rgba(239, 68, 68, 0.15);
            color: rgba(239, 68, 68, 0.8);
            font-size: 16px;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .logout-icon-btn:hover {
            background: rgba(239, 68, 68, 0.25);
            color: #ef4444;
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 36px 40px;
            overflow-y: auto;
            min-height: 100vh;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 32px;
            gap: 20px;
        }

        .header-left h1 {
            font-family: var(--font-title);
            font-size: 30px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 4px;
            letter-spacing: -0.5px;
        }

        .header-subtitle {
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 400;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .header-subtitle i {
            color: var(--primary);
            font-size: 15px;
        }

        .header-right {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            border: 1px solid transparent;
            border-radius: var(--radius-md);
            cursor: pointer;
            font-family: var(--font-body);
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 4px 14px rgba(14, 165, 233, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(14, 165, 233, 0.45);
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
            padding: 7px 14px;
            font-size: 13px;
            border-radius: var(--radius-sm);
        }

        .alert {
            padding: 14px 18px;
            margin-bottom: 22px;
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

        .card {
            background: var(--card-bg);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            padding: 28px;
            margin-bottom: 28px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            box-shadow: var(--shadow-lg);
        }

        @media (max-width: 1024px) {
            .sidebar { width: 240px; padding: 20px 12px; }
            .main-content { margin-left: 240px; padding: 28px; }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 72px;
                padding: 20px 8px;
                align-items: center;
            }
            .sidebar-logo-text, .nav-group-title { display: none; }
            .sidebar-logo { padding: 0; justify-content: center; margin-bottom: 24px; }
            .main-content { margin-left: 72px; padding: 20px; }
            .nav-link { padding: 11px; justify-content: center; }
            .nav-link span:not(.nav-icon) { display: none; }
            .user-profile { padding: 8px; justify-content: center; }
            .user-profile-info, .logout-icon-btn { display: none; }
        }
    </style>
    @yield('styles')
</head>
<body>
    @php
        $patientRecordsUrl = \Illuminate\Support\Facades\Route::has('admin.patient-records.index')
            ? route('admin.patient-records.index')
            : '#';
    @endphp

    <aside class="sidebar">
        <a href="{{ route('admin.dashboard') }}" class="sidebar-logo">
            <div class="sidebar-logo-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2C9.5 2 7.5 3.5 6.5 5.5C5.5 4.5 4 4 3 5C1.5 6.5 2 9 3 10.5C4 12 5 13 5.5 15C6 17 6 20 7.5 21.5C8.5 22.5 10 22 10.5 20.5L12 16L13.5 20.5C14 22 15.5 22.5 16.5 21.5C18 20 18 17 18.5 15C19 13 20 12 21 10.5C22 9 22.5 6.5 21 5C20 4 18.5 4.5 17.5 5.5C16.5 3.5 14.5 2 12 2Z" fill="white" opacity="0.9"/>
                    <path d="M11 9H13M12 8V10" stroke="white" stroke-width="1.5" stroke-linecap="round" opacity="0.7"/>
                </svg>
            </div>
            <span class="sidebar-logo-text">DentalCare</span>
        </a>

        <ul class="nav-menu">
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link @if(request()->routeIs('admin.dashboard')) active @endif">
                    <i class="nav-icon ri-dashboard-3-line"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <div class="nav-group-title">Hồ sơ bệnh án</div>
            <li class="nav-item">
                <a href="{{ $patientRecordsUrl }}" class="nav-link @if(request()->routeIs('admin.patient-records*')) active @endif">
                    <i class="nav-icon ri-folder-user-line"></i>
                    <span>Quản lý hồ sơ bệnh án</span>
                </a>
            </li>

            <div class="nav-group-title">Quản lý nhân sự</div>
            <li class="nav-item">
                <a href="{{ route('admin.doctors') }}" class="nav-link @if(request()->routeIs('admin.doctors')) active @endif">
                    <i class="nav-icon ri-stethoscope-line"></i>
                    <span>Quản lý bác sĩ</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.employees') }}" class="nav-link @if(request()->routeIs('admin.employees')) active @endif">
                    <i class="nav-icon ri-user-settings-line"></i>
                    <span>Quản lý nhân viên</span>
                </a>
            </li>

            <div class="nav-group-title">Ca làm việc</div>
            <li class="nav-item">
                <a href="{{ route('admin.shifts.index') }}" class="nav-link @if(request()->routeIs('admin.shifts.*')) active @endif">
                    <i class="nav-icon ri-time-line"></i>
                    <span>Quản lý ca làm việc</span>
                </a>
            </li>

            <div class="nav-group-title">Lịch trình &amp; Ca trực</div>
            <li class="nav-item">
                <a href="{{ route('admin.schedule-approval.index') }}" class="nav-link @if(request()->routeIs('admin.schedule-approval.*')) active @endif">
                    <i class="nav-icon ri-calendar-check-line"></i>
                    <span>Phê duyệt lịch trình</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.duty.index') }}" class="nav-link @if(request()->routeIs('admin.duty.*')) active @endif">
                    <i class="nav-icon ri-alarm-warning-line"></i>
                    <span>Giao ca trực</span>
                </a>
            </li>

            <div class="nav-group-title">Dịch vụ &amp; Giá</div>
            <li class="nav-item">
                <a href="{{ route('admin.services.index') }}" class="nav-link @if(request()->routeIs('admin.services.index')) active @endif">
                    <i class="nav-icon ri-hospital-line"></i>
                    <span>Quản lý dịch vụ</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.service-specialization.index') }}" class="nav-link @if(request()->routeIs('admin.service-specialization.index')) active @endif">
                    <i class="nav-icon ri-links-line"></i>
                    <span>Gán chuyên khoa dịch vụ</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.prices.index') }}" class="nav-link @if(request()->routeIs('admin.prices.index')) active @endif">
                    <i class="nav-icon ri-price-tag-3-line"></i>
                    <span>Quản lý giá</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.rooms.index') }}" class="nav-link @if(request()->routeIs('admin.rooms.*')) active @endif">
                    <i class="nav-icon ri-door-open-line"></i>
                    <span>Quản lý phòng khám</span>
                </a>
            </li>
        </ul>

        @auth
        <div class="user-profile">
            <div class="user-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
            <div class="user-profile-info">
                <h4>{{ Auth::user()->name }}</h4>
                <p>Quản trị viên</p>
            </div>
            <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" class="logout-icon-btn" title="Đăng xuất">
                    <i class="ri-logout-box-r-line"></i>
                </button>
            </form>
        </div>
        @endauth
    </aside>

    <main class="main-content">
        <div class="container">
            <div class="header">
                <div class="header-left">
                    <h1>@yield('page-title')</h1>
                    <p class="header-subtitle">
                        <i class="ri-map-pin-2-line"></i>
                        @yield('page-subtitle')
                    </p>
                </div>
                <div class="header-right">
                    @yield('header-actions')
                </div>
            </div>

            @if(session('success'))
                <div class="alert success">
                    <i class="ri-checkbox-circle-line" style="font-size:18px;flex-shrink:0;"></i>
                    {!! session('success') !!}
                </div>
            @endif

            @if(session('error'))
                <div class="alert error">
                    <i class="ri-error-warning-line" style="font-size:18px;flex-shrink:0;"></i>
                    {!! session('error') !!}
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    @yield('scripts')
</body>
</html>