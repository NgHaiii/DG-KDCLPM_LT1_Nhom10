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

        /* Card */
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            padding: 25px;
            margin-bottom: 30px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
                padding: 20px 15px;
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
        }

        @media (max-width: 600px) {
            .sidebar {
                width: 150px;
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
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link @if(request()->routeIs('admin.dashboard')) active @endif">
                    <span class="nav-icon">📊</span>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.doctors') }}" class="nav-link @if(request()->routeIs('admin.doctors')) active @endif">
                    <span class="nav-icon">🩺</span>
                    <span>Quản lý bác sĩ</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.employees') }}" class="nav-link @if(request()->routeIs('admin.employees')) active @endif">
                    <span class="nav-icon">👨‍💼</span>
                    <span>Quản lý nhân viên</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.services') }}" class="nav-link @if(request()->routeIs('admin.services')) active @endif">
                    <span class="nav-icon">🏥</span>
                    <span>Quản lý dịch vụ</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.prices') }}" class="nav-link @if(request()->routeIs('admin.prices')) active @endif">
                    <span class="nav-icon">💰</span>
                    <span>Quản lý giá</span>
                </a>
            </li>
        </ul>
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

            <!-- Content -->
            @yield('content')
        </div>
    </main>

    @yield('scripts')
</body>
</html>