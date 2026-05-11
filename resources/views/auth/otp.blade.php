<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận OTP - Quản lý Phòng khám Nha khoa</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 50px 40px;
            max-width: 450px;
            width: 100%;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        .header h1 { color: #333; font-size: 28px; margin-bottom: 10px; }
        .header p { color: #999; font-size: 14px; }
        .form-group { margin-bottom: 20px; }
        label {
            display: block;
            color: #444;
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 14px;
        }
        input[type="text"], input[type="password"], input[type="tel"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        input[type="text"]:focus, input[type="password"]:focus, input[type="tel"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        input[type="tel"][readonly] {
            background-color: #f5f5f5;
            color: #666;
            cursor: not-allowed;
        }
        .btn-submit {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            margin-top: 20px;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        .btn-send-otp {
            width: 100%;
            padding: 10px;
            background: #10b981;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
        }
        .btn-send-otp:hover {
            background: #059669;
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            animation: slideDown 0.3s ease;
        }
        .alert-error {
            background-color: #fee;
            color: #c33;
            border-left: 4px solid #f44;
        }
        .alert-success {
            background-color: #efe;
            color: #3a3;
            border-left: 4px solid #4a4;
        }
        .alert-error ul { margin-left: 20px; margin-top: 5px; }
        .alert-error li { margin-bottom: 5px; }
        .divider {
            text-align: center;
            margin: 30px 0 20px;
            position: relative;
            color: #999;
            font-size: 14px;
        }
        .divider::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 1px;
            background: #e0e0e0;
            transform: translateY(-50%);
        }
        .divider span {
            background: #fff;
            padding: 0 10px;
            position: relative;
            z-index: 1;
        }
        .footer-link { text-align: center; }
        .footer-link a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        .footer-link a:hover { color: #764ba2; text-decoration: underline; }
        .phone-display {
            background-color: #f0f4ff;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 14px;
            color: #444;
            border-left: 4px solid #667eea;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📱 Xác nhận OTP</h1>
            <p>Nhập mã xác nhận được gửi về số điện thoại</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-error">
                <strong>❌ Lỗi:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('otp_sent'))
            <div class="alert alert-success">
                ✓ {{ session('otp_sent') }}
            </div>
        @endif

        <!-- Nếu chưa gửi OTP, cho nhập số điện thoại và gửi mã -->
        @if (!session('otp_sent') && !session('otp'))
            <form method="POST" action="{{ route('password.send.otp') }}">
                @csrf
                <div class="form-group">
                    <label for="phone">📞 Số điện thoại</label>
                    <input 
                        type="tel" 
                        id="phone" 
                        name="phone" 
                        placeholder="Nhập số điện thoại của bạn (Ví dụ: 0912345678)"
                        required
                        autofocus
                        pattern="^[0-9]{10,11}$"
                    >
                </div>
                <button type="submit" class="btn-send-otp">📤 Gửi Mã OTP</button>
            </form>
        @endif

        <!-- Nếu đã gửi OTP, cho nhập mã OTP và mật khẩu mới -->
        @if (session('otp_sent') || session('otp'))
            <form method="POST" action="{{ route('password.verify.otp') }}">
                @csrf

                @if (session('phone'))
                    <div class="phone-display">
                        <strong>📱 Số điện thoại:</strong> {{ session('phone') }}
                    </div>
                @endif

                <div class="form-group">
                    <label for="otp">🔐 Mã OTP</label>
                    <input 
                        type="text" 
                        id="otp" 
                        name="otp" 
                        placeholder="Nhập 6 chữ số"
                        required
                        autofocus
                        pattern="^[0-9]{6}$"
                        maxlength="6"
                    >
                </div>

                <div class="form-group">
                    <label for="password">🔒 Mật khẩu mới</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Nhập mật khẩu mới (tối thiểu 6 ký tự)"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password_confirmation">✓ Xác nhận mật khẩu mới</label>
                    <input 
                        type="password" 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        placeholder="Nhập lại mật khẩu mới"
                        required
                    >
                </div>

                <button type="submit" class="btn-submit">Xác Nhận và Đặt lại Mật khẩu</button>
            </form>
        @endif

        <div class="divider">
            <span>hoặc</span>
        </div>

        <div class="footer-link">
            <p><a href="{{ route('login') }}">← Quay lại đăng nhập</a></p>
        </div>
    </div>
</body>
</html>