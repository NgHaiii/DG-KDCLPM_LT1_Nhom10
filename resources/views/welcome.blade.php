<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Quản Lý Nha Khoa - Hệ Thống Chuyên Nghiệp</title>
        
        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="bg-slate-50 dark:bg-slate-900 text-gray-900 dark:text-white">
        <!-- NAVBAR -->
        <nav class="navbar">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo -->
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-lg">🦷</span>
                        </div>
                        <span class="text-xl font-bold gradient-text">Nha Khoa</span>
                    </div>

                    <!-- Nav Links -->
                    <div class="hidden md:flex gap-8">
                        <a href="#features" class="nav-link">Tính Năng</a>
                        <a href="#benefits" class="nav-link">Lợi Ích</a>
                        <a href="#contact" class="nav-link">Liên Hệ</a>
                    </div>

                    <!-- Auth Buttons -->
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn btn-primary text-sm">
                            Dashboard
                        </a>
                    @else
                        <div class="flex gap-3">
                            <a href="{{ route('login') }}" class="btn btn-outline text-sm">
                                Đăng Nhập
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn btn-primary text-sm">
                                    Đăng Ký
                                </a>
                            @endif
                        </div>
                    @endauth
                </div>
            </div>
        </nav>

        <!-- HERO SECTION -->
        <section class="section-hero">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center relative z-10 animate-fade-in">
                <h1 class="text-white text-5xl md:text-6xl font-bold mb-6">
                    Quản Lý Nha Khoa Chuyên Nghiệp
                </h1>
                <p class="text-white/90 text-lg md:text-xl mb-8 max-w-2xl mx-auto">
                    Giải pháp quản lý nha khoa toàn diện - Từ quản lý bệnh nhân đến dịch vụ và nhân viên
                </p>
                <div class="flex gap-4 justify-center flex-wrap">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn btn-primary">
                            Vào Hệ Thống
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="btn btn-primary">
                            Bắt Đầu Ngay
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-outline text-white border-white hover:bg-white/10">
                            Đăng Nhập
                        </a>
                    @endauth
                </div>
            </div>
        </section>

        <!-- FEATURES SECTION -->
        <section id="features" class="py-20 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-center text-4xl font-bold mb-16">✨ Tính Năng Chính</h2>
                
                <div class="grid md:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="card-hover animate-slide-left">
                        <div class="text-4xl mb-4">👥</div>
                        <h3 class="text-xl font-bold mb-3">Quản Lý Bệnh Nhân</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Lưu trữ thông tin bệnh nhân an toàn, theo dõi lịch hẹn và điều trị
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="card-hover animate-slide-left" style="animation-delay: 0.1s">
                        <div class="text-4xl mb-4">💼</div>
                        <h3 class="text-xl font-bold mb-3">Quản Lý Nhân Viên</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Quản lý thông tin nhân viên, ca làm việc và lương bổng dễ dàng
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="card-hover animate-slide-left" style="animation-delay: 0.2s">
                        <div class="text-4xl mb-4">🏥</div>
                        <h3 class="text-xl font-bold mb-3">Quản Lý Dịch Vụ</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Quản lý danh sách dịch vụ, giá cả và báo cáo chi tiết
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="card-hover animate-slide-right">
                        <div class="text-4xl mb-4">💰</div>
                        <h3 class="text-xl font-bold mb-3">Quản Lý Giá Cả</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Thiết lập và cập nhật giá dịch vụ theo thời gian thực
                        </p>
                    </div>

                    <!-- Feature 5 -->
                    <div class="card-hover animate-slide-right" style="animation-delay: 0.1s">
                        <div class="text-4xl mb-4">📊</div>
                        <h3 class="text-xl font-bold mb-3">Báo Cáo & Thống Kê</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Xem báo cáo chi tiết về doanh thu, bệnh nhân và hiệu quả
                        </p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="card-hover animate-slide-right" style="animation-delay: 0.2s">
                        <div class="text-4xl mb-4">🔒</div>
                        <h3 class="text-xl font-bold mb-3">Bảo Mật Cao</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Mã hóa dữ liệu và quyền truy cập theo vai trò
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- BENEFITS SECTION -->
        <section id="benefits" class="py-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-r from-blue-50 dark:from-blue-900/20 to-cyan-50 dark:to-cyan-900/20">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-center text-4xl font-bold mb-16">🎯 Lợi Ích</h2>
                
                <div class="grid md:grid-cols-2 gap-12">
                    <div class="flex gap-6">
                        <div class="text-3xl">⚡</div>
                        <div>
                            <h3 class="text-xl font-bold mb-2">Tiết Kiệm Thời Gian</h3>
                            <p class="text-gray-600 dark:text-gray-400">
                                Tự động hóa công việc, giảm bớt phiền toái cho nhân viên
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex gap-6">
                        <div class="text-3xl">📈</div>
                        <div>
                            <h3 class="text-xl font-bold mb-2">Tăng Hiệu Quả</h3>
                            <p class="text-gray-600 dark:text-gray-400">
                                Quản lý tốt hơn = Doanh thu cao hơn
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-6">
                        <div class="text-3xl">😊</div>
                        <div>
                            <h3 class="text-xl font-bold mb-2">Trải Nghiệm Bệnh Nhân</h3>
                            <p class="text-gray-600 dark:text-gray-400">
                                Quản lý lịch hẹn tốt hơn, dịch vụ chuyên nghiệp
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-6">
                        <div class="text-3xl">🎓</div>
                        <div>
                            <h3 class="text-xl font-bold mb-2">Dễ Sử Dụng</h3>
                            <p class="text-gray-600 dark:text-gray-400">
                                Giao diện trực quan, không cần đào tạo phức tạp
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA SECTION -->
        <section id="contact" class="py-20 px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-4xl font-bold mb-8">Sẵn Sàng Nâng Cấp?</h2>
                <p class="text-xl text-gray-600 dark:text-gray-400 mb-8">
                    Hãy bắt đầu sử dụng hệ thống quản lý nha khoa của chúng tôi ngay hôm nay
                </p>
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn btn-primary text-lg px-8 py-4">
                        Vào Dashboard
                    </a>
                @else
                    <a href="{{ route('register') }}" class="btn btn-primary text-lg px-8 py-4">
                        Đăng Ký Miễn Phí
                    </a>
                @endauth
            </div>
        </section>

        <!-- FOOTER -->
        <footer class="bg-gray-900 dark:bg-black text-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <p>&copy; 2026 Quản Lý Nha Khoa. All rights reserved.</p>
            </div>
        </footer>
    </body>
</html>