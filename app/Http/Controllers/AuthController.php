<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    
    public function register(Request $request)
    {
        // Quy tắc email hệ thống:
        // - Doctor: BS001bs@hdat-dental.com.vn
        // - Employee: NV001nv@hdat-dental.com.vn
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
                    // patient: cho phép email bình thường
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

    // Hiển thị form đổi mật khẩu
    public function showChangePasswordForm()
    {
        return view('auth.change-password');
    }

    // Xử lý đổi mật khẩu
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|confirmed|min:6',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Mật khẩu cũ không đúng.']);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Đổi mật khẩu thành công! Vui lòng đăng nhập lại.');
    }

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