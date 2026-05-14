<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập — DentalCare</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
      /* Floating animated shapes */
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

      /* Floating icons */
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
            <h2 class="text-3xl font-bold tracking-tight text-foreground">Đăng nhập hệ thống</h2>
            <p class="mt-2 text-muted-foreground">Vui lòng nhập thông tin tài khoản của bạn</p>
          </div>

          @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 animate-fade-in-up">
              <strong>Lỗi:</strong> {{ $errors->first() }}
            </div>
          @endif

          <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf
            
            <div class="space-y-2 animate-fade-in-up delay-100">
              <label for="email" class="block text-sm font-semibold text-foreground">Tên đăng nhập</label>
              <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="admin@dental.local"
                required
                class="input-field h-12"
              />
              @error('email')
                <span class="text-xs text-red-600">{{ $message }}</span>
              @enderror
            </div>

            <div class="space-y-2 animate-fade-in-up delay-200">
              <label for="password" class="block text-sm font-semibold text-foreground">Mật khẩu</label>
              <div class="relative">
                <input
                  id="password"
                  type="password"
                  name="password"
                  placeholder="••••••"
                  required
                  class="input-field h-12 pr-11"
                />
                <button
                  type="button"
                  onclick="togglePassword()"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground transition-colors hover:text-foreground"
                >
                  <svg id="eye-icon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                  </svg>
                </button>
              </div>
              @error('password')
                <span class="text-xs text-red-600">{{ $message }}</span>
              @enderror
            </div>

            <div class="flex justify-between items-center animate-fade-in-up delay-300">
            <a href="{{ route('password.request') }}" class="text-sm font-medium text-primary hover:underline">Quên mật khẩu?</a>
            </div>

            <button
              type="submit"
              class="btn-primary h-12 w-full text-base font-semibold animate-fade-in-up delay-400"
            >
              Đăng nhập
            </button>
          </form>

          <div class="mt-6 animate-fade-in-up delay-500">
            <p class="text-center text-sm text-muted-foreground">
              Bạn chưa có tài khoản? 
              <a href="{{ route('register') }}" class="font-semibold text-primary hover:underline">Đăng ký ngay</a>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  function togglePassword() {
    const input = document.getElementById('password');
    const icon = document.getElementById('eye-icon');
    if (input.type === 'password') {
      input.type = 'text';
      icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>';
    } else {
      input.type = 'password';
      icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
    }
  }
</script>
</body>
</html>