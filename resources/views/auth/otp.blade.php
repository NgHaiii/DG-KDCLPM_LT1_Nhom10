<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận OTP — DentalCare</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
      .floating-shape {
        position: absolute;
        border-radius: 50%;
        opacity: 0.25;
        animation: float-shape 20s ease-in-out infinite;
        pointer-events: none;
        filter: blur(2px);
      }

      .shape-1 {
        width: 300px;
        height: 300px;
        background: linear-gradient(135deg, #0ea5e9, #06b6d4);
        top: 10%;
        left: 5%;
        animation-delay: 0s;
      }

      .shape-2 {
        width: 200px;
        height: 200px;
        background: linear-gradient(135deg, #06b6d4, #14b8a6);
        bottom: 15%;
        right: 10%;
        animation-delay: 5s;
      }

      .shape-3 {
        width: 250px;
        height: 250px;
        background: linear-gradient(135deg, #14b8a6, #0ea5e9);
        top: 50%;
        right: 5%;
        animation-delay: 10s;
      }

      @keyframes float-shape {
        0%, 100% {
          transform: translate(0, 0) rotate(0deg);
        }
        25% {
          transform: translate(30px, -30px) rotate(90deg);
        }
        50% {
          transform: translate(-20px, 30px) rotate(180deg);
        }
        75% {
          transform: translate(40px, 20px) rotate(270deg);
        }
      }

      .floating-icon {
        position: absolute;
        opacity: 0.2;
        pointer-events: none;
        animation: float-icon 15s ease-in-out infinite;
        filter: drop-shadow(0 0 8px rgba(14, 165, 233, 0.3));
      }

      .icon-1 {
        width: 80px;
        height: 80px;
        top: 20%;
        left: 15%;
        animation-delay: 0s;
      }

      .icon-2 {
        width: 100px;
        height: 100px;
        bottom: 25%;
        right: 20%;
        animation-delay: 3s;
      }

      .icon-3 {
        width: 70px;
        height: 70px;
        top: 60%;
        left: 10%;
        animation-delay: 6s;
      }

      @keyframes float-icon {
        0%, 100% {
          transform: translateY(0) scale(1);
        }
        50% {
          transform: translateY(-30px) scale(1.1);
        }
      }

      .btn-secondary {
        @apply h-11 rounded-lg border border-primary text-primary font-semibold hover:bg-primary/5 transition-colors;
      }
    </style>
</head>
<body>
<div class="aurora-bg relative min-h-screen flex items-center justify-center overflow-hidden">
  <!-- Floating shapes -->
  <div class="floating-shape shape-1"></div>
  <div class="floating-shape shape-2"></div>
  <div class="floating-shape shape-3"></div>

  <!-- Floating icons -->
  <svg class="floating-icon icon-1" fill="currentColor" viewBox="0 0 24 24" style="color: #0ea5e9;">
    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
  </svg>

  <svg class="floating-icon icon-2" fill="currentColor" viewBox="0 0 24 24" style="color: #06b6d4;">
    <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"></path>
  </svg>

  <svg class="floating-icon icon-3" fill="currentColor" viewBox="0 0 24 24" style="color: #14b8a6;">
    <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
  </svg>

  <!-- Floating particles -->
  <div class="aurora-particles">
    @for ($i = 0; $i < 18; $i++)
      <span
        style="
          left: {{ (($i * 53) % 100) }}%;
          width: {{ (6 + ($i % 5) * 4) }}px;
          height: {{ (6 + ($i % 5) * 4) }}px;
          animation-duration: {{ (12 + ($i % 7) * 3) }}s;
          animation-delay: {{ (($i % 8) * 1.2) }}s;
        "
      ></span>
    @endfor
  </div>

  <div class="relative z-10 w-full max-w-6xl px-6 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
      
      <!-- Left brand panel -->
      <div class="flex flex-col justify-center animate-fade-in">
        <div class="flex items-center gap-2 mb-6">
          <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-primary to-blue-600 text-white shadow-lg animate-float">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <span class="text-2xl font-bold text-foreground">DentalCare</span>
        </div>

        <div class="mb-6 inline-flex items-center gap-2 rounded-full glass px-4 py-2 text-sm font-medium text-primary w-fit">
          <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
          </svg>
          Hệ thống thế hệ mới 2026
        </div>

        <h1 class="text-5xl lg:text-6xl font-bold leading-tight tracking-tight mb-4">
          Quản lý <span class="text-gradient">Phòng khám</span><br />
          Nha khoa hiện đại
        </h1>

        <p class="text-lg text-muted-foreground mb-8 max-w-md">
          Giải pháp toàn diện cho lịch khám, hồ sơ bệnh nhân, dịch vụ, doanh thu — phân quyền theo vai trò chuẩn y tế.
        </p>

        <div class="grid grid-cols-3 gap-4 max-w-md">
          @php
            $features = [
              ['icon' => 'shield', 'label' => 'Bảo mật'],
              ['icon' => 'activity', 'label' => 'Realtime'],
              ['icon' => 'heart', 'label' => 'Y tế'],
            ];
          @endphp
          @foreach ($features as $feature)
            <div class="glass card-lift rounded-2xl p-4 text-center animate-fade-in-up delay-{{ ($loop->index + 1) * 100 }}">
              <svg class="mx-auto h-6 w-6 text-primary mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                @if ($feature['icon'] === 'shield')
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m7.784-4.817a.75.75 0 00-1.052-.855L12 5.696v5.696a.75.75 0 001.5 0V5.696l5.232 5.228a.75.75 0 10.061-1.06z"></path>
                @elseif ($feature['icon'] === 'activity')
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                @else
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                @endif
              </svg>
              <div class="text-sm font-semibold text-foreground">{{ $feature['label'] }}</div>
            </div>
          @endforeach
        </div>

        <div class="mt-12 text-sm text-muted-foreground animate-fade-in delay-500">
          © 2026 DentalCare Clinic
        </div>
      </div>

      <!-- Right form -->
      <div class="flex items-center justify-center">
        <div class="glass w-full rounded-3xl p-8 shadow-2xl animate-scale-in border border-border backdrop-blur-xl">
          <div class="mb-8">
            <h2 class="text-3xl font-bold tracking-tight text-foreground">Xác nhận OTP</h2>
            <p class="mt-2 text-muted-foreground">Nhập mã xác nhận được gửi về số điện thoại</p>
          </div>

          @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 animate-fade-in-up">
              <strong>Lỗi:</strong>
              <ul class="mt-2 ml-4 list-disc">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          @if (session('otp_sent'))
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-700 animate-fade-in-up">
              ✓ {{ session('otp_sent') }}
            </div>
          @endif

          <!-- Nếu chưa gửi OTP, cho nhập số điện thoại và gửi mã -->
          @if (!session('otp_sent') && !session('otp'))
            <form method="POST" action="{{ route('password.send.otp') }}" class="space-y-5">
              @csrf

              <div class="space-y-2 animate-fade-in-up delay-100">
                <label for="phone" class="block text-sm font-semibold text-foreground">Số điện thoại</label>
                <input
                  type="tel"
                  id="phone"
                  name="phone"
                  placeholder="0912345678"
                  required
                  pattern="^[0-9]{10,11}$"
                  class="input-field h-12"
                />
                @error('phone')
                  <span class="text-xs text-red-600">{{ $message }}</span>
                @enderror
              </div>

              <button
                type="submit"
                class="btn-primary h-12 w-full text-base font-semibold animate-fade-in-up delay-200"
              >
                📤 Gửi Mã OTP
              </button>
            </form>
          @endif

          <!-- Nếu đã gửi OTP, cho nhập mã OTP và mật khẩu mới -->
          @if (session('otp_sent') || session('otp'))
            <form method="POST" action="{{ route('password.verify.otp') }}" class="space-y-5">
              @csrf

              @if (session('phone'))
                <div class="rounded-lg glass px-4 py-3 flex items-center gap-3 animate-fade-in-up delay-100">
                  <svg class="h-5 w-5 text-primary flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                  </svg>
                  <div>
                    <div class="text-xs text-muted-foreground">Số điện thoại</div>
                    <div class="text-sm font-semibold text-foreground">{{ session('phone') }}</div>
                  </div>
                </div>
              @endif

              <div class="space-y-2 animate-fade-in-up delay-200">
                <label for="otp" class="block text-sm font-semibold text-foreground">Mã OTP</label>
                <input
                  type="text"
                  id="otp"
                  name="otp"
                  placeholder="000000"
                  required
                  pattern="^[0-9]{6}$"
                  maxlength="6"
                  class="input-field h-12 tracking-widest text-center text-lg font-semibold"
                />
                @error('otp')
                  <span class="text-xs text-red-600">{{ $message }}</span>
                @enderror
              </div>

              <div class="space-y-2 animate-fade-in-up delay-300">
                <label for="password" class="block text-sm font-semibold text-foreground">Mật khẩu mới</label>
                <input
                  type="password"
                  id="password"
                  name="password"
                  placeholder="••••••"
                  required
                  class="input-field h-12"
                />
                @error('password')
                  <span class="text-xs text-red-600">{{ $message }}</span>
                @enderror
              </div>

              <div class="space-y-2 animate-fade-in-up delay-400">
                <label for="password_confirmation" class="block text-sm font-semibold text-foreground">Xác nhận Mật khẩu mới</label>
                <input
                  type="password"
                  id="password_confirmation"
                  name="password_confirmation"
                  placeholder="••••••"
                  required
                  class="input-field h-12"
                />
                @error('password_confirmation')
                  <span class="text-xs text-red-600">{{ $message }}</span>
                @enderror
              </div>

              <button
                type="submit"
                class="btn-primary h-12 w-full text-base font-semibold animate-fade-in-up delay-500"
              >
                Xác Nhận và Đặt lại Mật khẩu
              </button>
            </form>
          @endif

          <div class="mt-6 space-y-3 animate-fade-in-up delay-500">
            <p class="text-center text-sm text-muted-foreground">
              <a href="{{ route('login') }}" class="font-semibold text-primary hover:underline">← Quay lại đăng nhập</a>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>