<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại Mật khẩu - Quản lý Phòng khám Nha khoa</title>
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
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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
        .btn-forgot {
            width: 100%;
            padding: 10px;
            background: #ff9800;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
            margin-top: 15px;
        }
        .btn-forgot:hover {
            background: #e68900;
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
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Đặt lại Mật khẩu</h1>
            <p>Nhập mật khẩu cũ và mật khẩu mới</p>
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

        <form method="POST" action="{{ route('password.reset') }}">
            @csrf

            <div class="form-group">
                <label for="old_password">🔑 Mật khẩu cũ</label>
                <input 
                    type="password" 
                    id="old_password" 
                    name="old_password" 
                    placeholder="Nhập mật khẩu hiện tại"
                    required
                    autofocus
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

            <button type="submit" class="btn-submit">Đặt lại Mật khẩu</button>
        </form>

        <form method="GET" action="{{ route('password.otp.form') }}">
            <button type="submit" class="btn-forgot">📱 Tôi quên mật khẩu cũ (Xác nhận bằng OTP)</button>
        </form>

        <div class="divider">
            <span>hoặc</span>
        </div>

        <div class="footer-link">
            <p><a href="{{ route('login') }}">← Quay lại đăng nhập</a></p>
        </div>
    </div>
</body>
</html>