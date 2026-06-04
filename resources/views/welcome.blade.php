<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Quản Lý Nha Khoa - Hệ Thống Chuyên Nghiệp</title>

        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Remix Icons -->
        <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        <style>
            :root {
                --font-title: 'Outfit', -apple-system, sans-serif;
                --font-body: 'Inter', -apple-system, sans-serif;
            }
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: var(--font-body); }

            /* Navbar */
            .site-nav {
                position: sticky;
                top: 0;
                z-index: 50;
                background: rgba(255, 255, 255, 0.92);
                backdrop-filter: blur(16px);
                -webkit-backdrop-filter: blur(16px);
                border-bottom: 1px solid rgba(0, 0, 0, 0.06);
                padding: 0 24px;
            }
            .site-nav-inner {
                max-width: 1200px;
                margin: 0 auto;
                height: 68px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 24px;
            }
            .site-logo {
                display: flex;
                align-items: center;
                gap: 10px;
                text-decoration: none;
            }
            .site-logo-icon {
                width: 38px;
                height: 38px;
                border-radius: 10px;
                background: linear-gradient(135deg, #0ea5e9, #6366f1);
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 18px;
            }
            .site-logo-text {
                font-family: var(--font-title);
                font-size: 20px;
                font-weight: 700;
                background: linear-gradient(135deg, #0ea5e9, #6366f1);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }
            .site-nav-links {
                display: flex;
                gap: 32px;
                list-style: none;
            }
            .site-nav-links a {
                font-size: 14px;
                font-weight: 500;
                color: #475569;
                text-decoration: none;
                transition: color 0.2s;
            }
            .site-nav-links a:hover { color: #0ea5e9; }
            .nav-auth-btns { display: flex; gap: 12px; align-items: center; }
            .nav-btn-ghost {
                padding: 8px 18px;
                font-size: 14px;
                font-weight: 600;
                color: #475569;
                border: 1.5px solid #e2e8f0;
                border-radius: 8px;
                text-decoration: none;
                transition: all 0.2s;
                background: transparent;
            }
            .nav-btn-ghost:hover { border-color: #0ea5e9; color: #0ea5e9; }
            .nav-btn-solid {
                padding: 8px 18px;
                font-size: 14px;
                font-weight: 600;
                color: white;
                border-radius: 8px;
                text-decoration: none;
                transition: all 0.2s;
                background: linear-gradient(135deg, #0ea5e9, #6366f1);
                box-shadow: 0 4px 14px rgba(14, 165, 233, 0.3);
            }
            .nav-btn-solid:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(14, 165, 233, 0.4); }

            /* Hero */
            .hero-section {
                background: linear-gradient(135deg, #0c1445 0%, #0d3070 40%, #0a5fb4 80%, #0ea5e9 100%);
                position: relative;
                overflow: hidden;
                padding: 100px 24px 120px;
                text-align: center;
            }
            .hero-section::before {
                content: '';
                position: absolute;
                inset: 0;
                background:
                    radial-gradient(ellipse 800px 400px at 20% 80%, rgba(99, 102, 241, 0.3), transparent),
                    radial-gradient(ellipse 600px 300px at 80% 20%, rgba(14, 165, 233, 0.4), transparent);
                pointer-events: none;
            }
            .hero-inner {
                position: relative;
                z-index: 1;
                max-width: 800px;
                margin: 0 auto;
            }
            .hero-badge {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                background: rgba(255,255,255,0.12);
                border: 1px solid rgba(255,255,255,0.2);
                backdrop-filter: blur(8px);
                padding: 6px 16px;
                border-radius: 100px;
                color: rgba(255,255,255,0.9);
                font-size: 13px;
                font-weight: 500;
                margin-bottom: 28px;
            }
            .hero-badge i { color: #fbbf24; }
            .hero-title {
                font-family: var(--font-title);
                font-size: clamp(36px, 6vw, 64px);
                font-weight: 800;
                color: white;
                line-height: 1.1;
                letter-spacing: -1.5px;
                margin-bottom: 20px;
            }
            .hero-title span {
                background: linear-gradient(135deg, #7dd3fc, #c4b5fd);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }
            .hero-subtitle {
                font-size: 18px;
                color: rgba(255,255,255,0.78);
                max-width: 560px;
                margin: 0 auto 40px;
                line-height: 1.7;
            }
            .hero-buttons { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; }
            .hero-btn-primary {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 14px 28px;
                background: white;
                color: #0c1445;
                font-size: 15px;
                font-weight: 700;
                border-radius: 10px;
                text-decoration: none;
                transition: all 0.2s;
                box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            }
            .hero-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(0,0,0,0.2); }
            .hero-btn-outline {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 14px 28px;
                border: 2px solid rgba(255,255,255,0.5);
                color: white;
                font-size: 15px;
                font-weight: 600;
                border-radius: 10px;
                text-decoration: none;
                transition: all 0.2s;
            }
            .hero-btn-outline:hover { background: rgba(255,255,255,0.1); border-color: white; }

            /* Stats Bar */
            .stats-bar {
                background: white;
                border-bottom: 1px solid #f1f5f9;
                padding: 32px 24px;
            }
            .stats-bar-inner {
                max-width: 1000px;
                margin: 0 auto;
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 0;
            }
            .stats-bar-item {
                text-align: center;
                padding: 8px 20px;
                position: relative;
            }
            .stats-bar-item:not(:last-child)::after {
                content: '';
                position: absolute;
                right: 0;
                top: 10%;
                height: 80%;
                width: 1px;
                background: #e2e8f0;
            }
            .stats-bar-item .stat-num {
                font-family: var(--font-title);
                font-size: 32px;
                font-weight: 800;
                background: linear-gradient(135deg, #0ea5e9, #6366f1);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                line-height: 1.1;
            }
            .stats-bar-item .stat-label {
                font-size: 13px;
                color: #64748b;
                margin-top: 4px;
                font-weight: 500;
            }

            /* Sections */
            .section-wrap {
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 24px;
            }
            .section-header {
                text-align: center;
                margin-bottom: 56px;
            }
            .section-tag {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                background: linear-gradient(135deg, #e0f2fe, #ede9fe);
                color: #0369a1;
                font-size: 13px;
                font-weight: 600;
                padding: 5px 14px;
                border-radius: 100px;
                margin-bottom: 16px;
                letter-spacing: 0.3px;
            }
            .section-title {
                font-family: var(--font-title);
                font-size: clamp(28px, 4vw, 40px);
                font-weight: 800;
                color: #0f172a;
                line-height: 1.2;
                letter-spacing: -0.5px;
                margin-bottom: 14px;
            }
            .section-desc {
                font-size: 16px;
                color: #64748b;
                max-width: 520px;
                margin: 0 auto;
                line-height: 1.7;
            }

            /* Features */
            .features-section {
                padding: 88px 24px;
                background: #f8fafc;
            }
            .features-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 24px;
                max-width: 1100px;
                margin: 0 auto;
            }
            .feature-card {
                background: white;
                border-radius: 18px;
                padding: 28px;
                border: 1px solid #f1f5f9;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                box-shadow: 0 1px 8px rgba(0,0,0,0.04);
            }
            .feature-card:hover {
                transform: translateY(-6px);
                box-shadow: 0 20px 40px rgba(0,0,0,0.08);
            }
            .feature-icon-wrap {
                width: 52px;
                height: 52px;
                border-radius: 14px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 22px;
                margin-bottom: 18px;
            }
            .feature-card h3 {
                font-family: var(--font-title);
                font-size: 17px;
                font-weight: 700;
                color: #0f172a;
                margin-bottom: 8px;
            }
            .feature-card p {
                font-size: 14px;
                color: #64748b;
                line-height: 1.7;
            }

            /* Benefits */
            .benefits-section {
                padding: 88px 24px;
                background: white;
            }
            .benefits-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 32px;
                max-width: 900px;
                margin: 0 auto;
            }
            .benefit-item {
                display: flex;
                gap: 16px;
                align-items: flex-start;
            }
            .benefit-icon-wrap {
                width: 44px;
                height: 44px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 20px;
                flex-shrink: 0;
                background: linear-gradient(135deg, #e0f2fe, #ede9fe);
                color: #0369a1;
            }
            .benefit-item h3 {
                font-family: var(--font-title);
                font-size: 16px;
                font-weight: 700;
                color: #0f172a;
                margin-bottom: 6px;
            }
            .benefit-item p {
                font-size: 14px;
                color: #64748b;
                line-height: 1.65;
            }

            /* CTA */
            .cta-section {
                padding: 88px 24px;
                background: linear-gradient(135deg, #0c1445 0%, #0d3070 50%, #0a5fb4 100%);
                text-align: center;
                position: relative;
                overflow: hidden;
            }
            .cta-section::before {
                content: '';
                position: absolute;
                inset: 0;
                background: radial-gradient(ellipse 700px 400px at 50% 50%, rgba(99, 102, 241, 0.3), transparent);
                pointer-events: none;
            }
            .cta-inner { position: relative; z-index: 1; max-width: 600px; margin: 0 auto; }
            .cta-section h2 {
                font-family: var(--font-title);
                font-size: clamp(28px, 4vw, 42px);
                font-weight: 800;
                color: white;
                letter-spacing: -0.5px;
                margin-bottom: 16px;
            }
            .cta-section p {
                font-size: 17px;
                color: rgba(255,255,255,0.75);
                margin-bottom: 36px;
                line-height: 1.7;
            }

            /* Footer */
            .site-footer {
                background: #0f172a;
                color: rgba(255,255,255,0.7);
                padding: 60px 24px 36px;
            }
            .footer-inner {
                max-width: 1100px;
                margin: 0 auto;
            }
            .footer-top {
                display: grid;
                grid-template-columns: 1.5fr 1fr 1fr 1fr;
                gap: 48px;
                padding-bottom: 40px;
                border-bottom: 1px solid rgba(255,255,255,0.08);
                margin-bottom: 28px;
            }
            .footer-brand .footer-logo {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 14px;
            }
            .footer-logo-icon {
                width: 36px;
                height: 36px;
                border-radius: 9px;
                background: linear-gradient(135deg, #0ea5e9, #6366f1);
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 16px;
            }
            .footer-logo-text {
                font-family: var(--font-title);
                font-size: 18px;
                font-weight: 700;
                color: white;
            }
            .footer-brand p {
                font-size: 13px;
                line-height: 1.7;
                color: rgba(255,255,255,0.55);
            }
            .footer-col h4 {
                font-family: var(--font-title);
                font-size: 13px;
                font-weight: 700;
                color: white;
                text-transform: uppercase;
                letter-spacing: 1px;
                margin-bottom: 16px;
            }
            .footer-col ul { list-style: none; }
            .footer-col li { margin-bottom: 10px; }
            .footer-col a {
                font-size: 13px;
                color: rgba(255,255,255,0.55);
                text-decoration: none;
                transition: color 0.2s;
            }
            .footer-col a:hover { color: #7dd3fc; }
            .footer-bottom {
                display: flex;
                justify-content: space-between;
                align-items: center;
                font-size: 13px;
                color: rgba(255,255,255,0.4);
            }
            .footer-social { display: flex; gap: 12px; }
            .footer-social a {
                width: 34px;
                height: 34px;
                border-radius: 8px;
                background: rgba(255,255,255,0.07);
                display: flex;
                align-items: center;
                justify-content: center;
                color: rgba(255,255,255,0.6);
                font-size: 16px;
                transition: all 0.2s;
                text-decoration: none;
            }
            .footer-social a:hover { background: rgba(14, 165, 233, 0.25); color: #7dd3fc; }

            /* Responsive */
            @media (max-width: 900px) {
                .features-grid { grid-template-columns: repeat(2, 1fr); }
                .footer-top { grid-template-columns: 1fr 1fr; }
            }
            @media (max-width: 640px) {
                .stats-bar-inner { grid-template-columns: repeat(2, 1fr); }
                .features-grid { grid-template-columns: 1fr; }
                .benefits-grid { grid-template-columns: 1fr; }
                .footer-top { grid-template-columns: 1fr; gap: 32px; }
                .site-nav-links { display: none; }
                .footer-bottom { flex-direction: column; gap: 12px; text-align: center; }
            }
        </style>
    </head>
    <body>
        <!-- NAVBAR -->
        <nav class="site-nav">
            <div class="site-nav-inner">
                <a href="/" class="site-logo">
                    <div class="site-logo-icon"><i class="ri-tooth-fill"></i></div>
                    <span class="site-logo-text">DentalCare</span>
                </a>

                <ul class="site-nav-links">
                    <li><a href="#features">Tính Năng</a></li>
                    <li><a href="#benefits">Lợi Ích</a></li>
                    <li><a href="#contact">Liên Hệ</a></li>
                </ul>

                <div class="nav-auth-btns">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="nav-btn-solid">
                            <i class="ri-dashboard-3-line"></i> Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="nav-btn-ghost">Đăng Nhập</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="nav-btn-solid">Đăng Ký</a>
                        @endif
                    @endauth
                </div>
            </div>
        </nav>

        <!-- HERO -->
        <section class="hero-section">
            <div class="hero-inner animate-fade-in">
                <div class="hero-badge">
                    <i class="ri-star-fill"></i>
                    Hệ thống thế hệ mới 2026 — Chuyên nghiệp &amp; Hiện đại
                </div>
                <h1 class="hero-title">
                    Quản Lý <span>Phòng Khám</span><br>Nha Khoa Chuyên Nghiệp
                </h1>
                <p class="hero-subtitle">
                    Giải pháp toàn diện từ lịch hẹn, hồ sơ bệnh nhân, quản lý nhân sự đến báo cáo doanh thu — phân quyền chuẩn y tế.
                </p>
                <div class="hero-buttons">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="hero-btn-primary">
                            <i class="ri-dashboard-3-line"></i> Vào Hệ Thống
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="hero-btn-primary">
                            <i class="ri-rocket-line"></i> Bắt Đầu Ngay
                        </a>
                        <a href="{{ route('login') }}" class="hero-btn-outline">
                            <i class="ri-login-box-line"></i> Đăng Nhập
                        </a>
                    @endauth
                </div>
            </div>
        </section>

        <!-- STATS BAR -->
        <section class="stats-bar">
            <div class="stats-bar-inner">
                <div class="stats-bar-item">
                    <div class="stat-num">500+</div>
                    <div class="stat-label"><i class="ri-user-heart-line"></i> Bệnh nhân tin tưởng</div>
                </div>
                <div class="stats-bar-item">
                    <div class="stat-num">15+</div>
                    <div class="stat-label"><i class="ri-stethoscope-line"></i> Bác sĩ chuyên nghiệp</div>
                </div>
                <div class="stats-bar-item">
                    <div class="stat-num">98%</div>
                    <div class="stat-label"><i class="ri-emotion-happy-line"></i> Mức độ hài lòng</div>
                </div>
                <div class="stats-bar-item">
                    <div class="stat-num">5+</div>
                    <div class="stat-label"><i class="ri-award-line"></i> Năm kinh nghiệm</div>
                </div>
            </div>
        </section>

        <!-- FEATURES -->
        <section id="features" class="features-section">
            <div class="section-header">
                <div class="section-tag"><i class="ri-sparkling-line"></i> Tính năng nổi bật</div>
                <h2 class="section-title">Mọi thứ bạn cần để vận hành phòng khám</h2>
                <p class="section-desc">Hệ thống quản lý toàn diện giúp tiết kiệm thời gian và nâng cao chất lượng dịch vụ</p>
            </div>

            <div class="features-grid">
                <div class="feature-card animate-slide-left">
                    <div class="feature-icon-wrap" style="background: linear-gradient(135deg, #dbeafe, #ede9fe); color: #4f46e5;">
                        <i class="ri-user-heart-line"></i>
                    </div>
                    <h3>Quản Lý Bệnh Nhân</h3>
                    <p>Lưu trữ hồ sơ, lịch sử điều trị và theo dõi tình trạng bệnh nhân an toàn, dễ truy xuất</p>
                </div>

                <div class="feature-card animate-slide-left" style="animation-delay: 0.1s">
                    <div class="feature-icon-wrap" style="background: linear-gradient(135deg, #d1fae5, #e0f2fe); color: #0891b2;">
                        <i class="ri-calendar-check-line"></i>
                    </div>
                    <h3>Quản Lý Lịch Hẹn</h3>
                    <p>Đặt và quản lý lịch khám tự động, tránh trùng lịch và gửi nhắc nhở cho bệnh nhân</p>
                </div>

                <div class="feature-card animate-slide-left" style="animation-delay: 0.2s">
                    <div class="feature-icon-wrap" style="background: linear-gradient(135deg, #fef3c7, #fed7aa); color: #d97706;">
                        <i class="ri-user-settings-line"></i>
                    </div>
                    <h3>Quản Lý Nhân Viên</h3>
                    <p>Quản lý thông tin bác sĩ, nhân viên, ca làm việc và phân ca trực hiệu quả</p>
                </div>

                <div class="feature-card animate-slide-right">
                    <div class="feature-icon-wrap" style="background: linear-gradient(135deg, #fee2e2, #fce7f3); color: #dc2626;">
                        <i class="ri-hospital-line"></i>
                    </div>
                    <h3>Quản Lý Dịch Vụ</h3>
                    <p>Danh sách dịch vụ nha khoa, cập nhật giá theo thời gian thực và quản lý gói điều trị</p>
                </div>

                <div class="feature-card animate-slide-right" style="animation-delay: 0.1s">
                    <div class="feature-icon-wrap" style="background: linear-gradient(135deg, #f3e8ff, #dbeafe); color: #7c3aed;">
                        <i class="ri-bar-chart-box-line"></i>
                    </div>
                    <h3>Báo Cáo &amp; Thống Kê</h3>
                    <p>Biểu đồ doanh thu, thống kê bệnh nhân và báo cáo hiệu quả hoạt động chi tiết</p>
                </div>

                <div class="feature-card animate-slide-right" style="animation-delay: 0.2s">
                    <div class="feature-icon-wrap" style="background: linear-gradient(135deg, #dcfce7, #d1fae5); color: #15803d;">
                        <i class="ri-shield-check-line"></i>
                    </div>
                    <h3>Bảo Mật Cao</h3>
                    <p>Phân quyền theo vai trò (Admin/Bác sĩ/Nhân viên), mã hóa dữ liệu chuẩn y tế</p>
                </div>
            </div>
        </section>

        <!-- BENEFITS -->
        <section id="benefits" class="benefits-section">
            <div class="section-header">
                <div class="section-tag"><i class="ri-trophy-line"></i> Lợi ích</div>
                <h2 class="section-title">Tại sao chọn DentalCare?</h2>
                <p class="section-desc">Được tin tưởng bởi hàng trăm phòng khám nha khoa trên cả nước</p>
            </div>

            <div class="benefits-grid">
                <div class="benefit-item">
                    <div class="benefit-icon-wrap"><i class="ri-flashlight-line"></i></div>
                    <div>
                        <h3>Tiết Kiệm Thời Gian</h3>
                        <p>Tự động hóa công việc hành chính, giảm 70% thời gian xử lý giấy tờ và quản lý thủ công</p>
                    </div>
                </div>

                <div class="benefit-item">
                    <div class="benefit-icon-wrap"><i class="ri-line-chart-line"></i></div>
                    <div>
                        <h3>Tăng Doanh Thu</h3>
                        <p>Quản lý lịch hẹn tốt hơn, giảm lịch trống, tối ưu hóa quy trình để tăng doanh thu đến 30%</p>
                    </div>
                </div>

                <div class="benefit-item">
                    <div class="benefit-icon-wrap"><i class="ri-emotion-happy-line"></i></div>
                    <div>
                        <h3>Trải Nghiệm Bệnh Nhân</h3>
                        <p>Nhắc lịch tự động, hồ sơ điều trị rõ ràng giúp bệnh nhân tin tưởng và quay lại nhiều hơn</p>
                    </div>
                </div>

                <div class="benefit-item">
                    <div class="benefit-icon-wrap"><i class="ri-user-smile-line"></i></div>
                    <div>
                        <h3>Dễ Sử Dụng</h3>
                        <p>Giao diện trực quan, hiện đại. Nhân viên có thể sử dụng thành thạo chỉ sau 1 buổi hướng dẫn</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section id="contact" class="cta-section">
            <div class="cta-inner">
                <h2>Sẵn Sàng Nâng Cấp Phòng Khám?</h2>
                <p>Hàng trăm phòng khám đã tin tưởng DentalCare. Hãy trải nghiệm ngay hôm nay — miễn phí và không cần thẻ tín dụng.</p>
                @auth
                    <a href="{{ url('/dashboard') }}" class="hero-btn-primary" style="display: inline-flex; margin: 0 auto;">
                        <i class="ri-dashboard-3-line"></i> Vào Dashboard
                    </a>
                @else
                    <a href="{{ route('register') }}" class="hero-btn-primary" style="display: inline-flex; margin: 0 auto;">
                        <i class="ri-rocket-line"></i> Đăng Ký Miễn Phí
                    </a>
                @endauth
            </div>
        </section>

        <!-- FOOTER -->
        <footer class="site-footer">
            <div class="footer-inner">
                <div class="footer-top">
                    <div class="footer-brand">
                        <div class="footer-logo">
                            <div class="footer-logo-icon"><i class="ri-tooth-fill"></i></div>
                            <span class="footer-logo-text">DentalCare</span>
                        </div>
                        <p>Hệ thống quản lý nha khoa toàn diện, giúp phòng khám vận hành chuyên nghiệp và hiệu quả hơn.</p>
                    </div>

                    <div class="footer-col">
                        <h4>Sản phẩm</h4>
                        <ul>
                            <li><a href="#features">Tính năng</a></li>
                            <li><a href="#benefits">Lợi ích</a></li>
                            <li><a href="{{ route('login') }}">Đăng nhập</a></li>
                            @if(Route::has('register'))<li><a href="{{ route('register') }}">Đăng ký</a></li>@endif
                        </ul>
                    </div>

                    <div class="footer-col">
                        <h4>Dịch vụ</h4>
                        <ul>
                            <li><a href="#">Quản lý bệnh nhân</a></li>
                            <li><a href="#">Quản lý lịch hẹn</a></li>
                            <li><a href="#">Báo cáo thống kê</a></li>
                            <li><a href="#">Bảo mật dữ liệu</a></li>
                        </ul>
                    </div>

                    <div class="footer-col">
                        <h4>Liên hệ</h4>
                        <ul>
                            <li><a href="#">📍 TP. Hồ Chí Minh</a></li>
                            <li><a href="#">📞 0900 000 000</a></li>
                            <li><a href="#">✉️ support@dental.vn</a></li>
                        </ul>
                    </div>
                </div>

                <div class="footer-bottom">
                    <span>&copy; 2026 DentalCare Clinic. All rights reserved.</span>
                    <div class="footer-social">
                        <a href="#" title="Facebook"><i class="ri-facebook-fill"></i></a>
                        <a href="#" title="Twitter"><i class="ri-twitter-x-line"></i></a>
                        <a href="#" title="LinkedIn"><i class="ri-linkedin-fill"></i></a>
                        <a href="#" title="Email"><i class="ri-mail-line"></i></a>
                    </div>
                </div>
            </div>
        </footer>
    </body>
</html>
