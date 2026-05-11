<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    // Hàm chuẩn hóa số điện thoại về dạng 84xxxxxxxxx
    private function formatPhoneToInternational($phone)
    {
        $phone = preg_replace('/[\s\.\-]/', '', $phone);
        if (substr($phone, 0, 1) === '0') {
            $phone = '84' . substr($phone, 1);
        }
        return $phone;
    }

    // Hàm gửi OTP qua eSMS.vn
    private function sendOtpEsms($phone, $otp)
    {
        $apiKey = 'FE0C6C4D5769BE85FC2441E3C7053D'; // Thay bằng API Key của bạn
        $secretKey = 'EAF3F0D11CDA6E4FE2255956E6A8D'; // Thay bằng Secret Key của bạn
        $brandName = ''; // Nếu chưa có brandname, để trống
        $phone = $this->formatPhoneToInternational($phone);

        $url = 'http://rest.esms.vn/MainService.svc/json/SendMultipleMessage_V4_post_json/';

        $data = [
            'ApiKey' => $apiKey,
            'SecretKey' => $secretKey,
            'Content' => "Ma OTP cua ban la: $otp",
            'Phone' => $phone,
            'Brandname' => $brandName,
            'SmsType' => 2, // 2: CSKH, 1: QC
            'IsUnicode' => 0,
            'Sandbox' => 0 // 1: test, 0: gửi thật
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        // Có thể log $result nếu cần debug
    }

    // Đăng ký
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $doctorEmailPattern = '/^BS\d{3}bs@hdat-dental\.com\.vn$/i';
        $employeeEmailPattern = '/^NV\d{3}nv@hdat-dental\.com\.vn$/i';

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', Rule::in(['doctor', 'employee', 'patient'])],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
                function (string $attribute, mixed $value, \Closure $fail) use ($request, $doctorEmailPattern, $employeeEmailPattern) {
                    $role = $request->input('role');
                    if ($role === 'doctor' && !preg_match($doctorEmailPattern, (string) $value)) {
                        $fail('Email bác sĩ phải đúng định dạng: BS001bs@hdat-dental.com.vn');
                        return;
                    }
                    if ($role === 'employee' && !preg_match($employeeEmailPattern, (string) $value)) {
                        $fail('Email nhân viên phải đúng định dạng: NV001nv@hdat-dental.com.vn');
                        return;
                    }
                },
            ],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        Auth::login($user);

        return $this->redirectToDashboard($user->role);
    }

    // Đăng nhập
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials)) {
            return back()->withErrors([
                'email' => 'Email hoặc mật khẩu không đúng.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return $this->redirectToDashboard(Auth::user()->role);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    // --- QUÊN MẬT KHẨU ---
    // Bước 1: Nhập email hoặc số điện thoại
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function handleForgotPassword(Request $request)
    {
        $request->validate([
            'email_or_phone' => 'required'
        ]);
        $user = User::where('email', $request->email_or_phone)
            ->orWhere('phone', $request->email_or_phone)
            ->first();

        if (!$user) {
            return back()->withErrors(['email_or_phone' => 'Không tìm thấy tài khoản!']);
        }
        session(['reset_user_id' => $user->id]);
        return redirect()->route('password.reset.form');
    }

    // Bước 2: Nhập mật khẩu cũ hoặc chọn "Quên mật khẩu cũ"
    public function showResetPasswordForm()
    {
        if (!session('reset_user_id')) return redirect()->route('password.request');
        return view('auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'password' => 'required|confirmed|min:6',
        ]);
        $user = User::find(session('reset_user_id'));
        if (!$user || !Hash::check($request->old_password, $user->password)) {
            return back()->withErrors(['old_password' => 'Mật khẩu cũ không đúng!']);
        }
        $user->password = Hash::make($request->password);
        $user->save();
        session()->forget('reset_user_id');
        return redirect()->route('login')->with('status', 'Đổi mật khẩu thành công!');
    }

    // Bước 3: Nếu không nhớ mật khẩu cũ, gửi OTP về sđt
    public function showOtpForm()
    {
        if (!session('reset_user_id')) return redirect()->route('password.request');
        return view('auth.otp');
    }

    public function sendOtp(Request $request)
    {
        $user = User::find(session('reset_user_id'));
        if (!$user || !$user->phone) {
            return back()->withErrors(['otp' => 'Không tìm thấy số điện thoại để gửi OTP!']);
        }
        $otp = rand(100000, 999999);
        session(['otp' => $otp, 'otp_expires' => now()->addMinutes(5)]);
        // Gửi OTP qua SMS thật
        $this->sendOtpEsms($user->phone, $otp);
        return back()->with('otp_sent', 'Mã xác nhận đã được gửi về số điện thoại của bạn.');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => 'required', 'password' => 'required|confirmed|min:6']);
        if ($request->otp != session('otp') || now()->greaterThan(session('otp_expires'))) {
            return back()->withErrors(['otp' => 'Mã xác nhận không đúng hoặc đã hết hạn!']);
        }
        $user = User::find(session('reset_user_id'));
        $user->password = Hash::make($request->password);
        $user->save();
        session()->forget(['reset_user_id', 'otp', 'otp_expires']);
        return redirect()->route('login')->with('status', 'Đặt lại mật khẩu thành công!');
    }

    // Điều hướng dashboard
    protected function redirectToDashboard(string $role)
    {
        return match ($role) {
            'admin' => redirect()->route('admin.dashboard'),
            'doctor' => redirect()->route('doctor.dashboard'),
            'employee' => redirect()->route('employee.dashboard'),
            default => redirect()->route('patient.dashboard'),
        };
    }
}