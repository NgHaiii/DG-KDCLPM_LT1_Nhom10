<?php

namespace App\Http\Controllers;

use App\Models\PatientProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PatientProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Danh sách hồ sơ bệnh nhân cho nhân viên/lễ tân tra cứu.
     */
    public function index(Request $request)
    {
        $keyword = trim((string) $request->input('keyword'));

        $profiles = PatientProfile::query()
            ->search($keyword)
            ->latest('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('employees.patient-profiles.index', compact('profiles', 'keyword'));
    }

    /**
     * API tìm hồ sơ nhanh theo SĐT/tên.
     * Dùng cho form tiếp nhận offline.
     */
    public function search(Request $request)
    {
        $keyword = trim((string) $request->input('keyword'));

        if ($keyword === '') {
            return response()->json([]);
        }

        $profiles = PatientProfile::query()
            ->search($keyword)
            ->latest('updated_at')
            ->limit(10)
            ->get()
            ->map(function ($profile) {
                return [
                    'id' => $profile->id,
                    'full_name' => $profile->full_name,
                    'phone' => $profile->phone,
                    'email' => $profile->email,
                    'dob' => optional($profile->dob)->format('Y-m-d'),
                    'gender' => $profile->gender,
                    'gender_label' => $profile->gender_label,
                    'address' => $profile->address,
                    'source_label' => $profile->source_label,
                ];
            });

        return response()->json($profiles);
    }

    /**
     * Tạo hồ sơ nhanh cho bệnh nhân offline.
     */
    public function storeQuick(Request $request)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'dob' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:1000'],
        ], [
            'full_name.required' => 'Vui lòng nhập họ tên bệnh nhân.',
            'phone.required' => 'Vui lòng nhập số điện thoại bệnh nhân.',
            'email.email' => 'Email không đúng định dạng.',
        ]);

        try {
            $profile = PatientProfile::updateOrCreate(
                [
                    'phone' => $validated['phone'],
                ],
                [
                    'full_name' => $validated['full_name'],
                    'email' => $validated['email'] ?? null,
                    'dob' => $validated['dob'] ?? null,
                    'gender' => $validated['gender'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'source' => 'offline',
                    'is_temporary' => false,
                    'last_visit_at' => now(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Đã lưu hồ sơ bệnh nhân.',
                'profile' => [
                    'id' => $profile->id,
                    'full_name' => $profile->full_name,
                    'phone' => $profile->phone,
                    'email' => $profile->email,
                    'dob' => optional($profile->dob)->format('Y-m-d'),
                    'gender' => $profile->gender,
                    'address' => $profile->address,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Store quick patient profile error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Không thể lưu hồ sơ bệnh nhân.',
            ], 500);
        }
    }

    /**
     * Cập nhật hồ sơ bệnh nhân.
     */
    public function update(Request $request, PatientProfile $patientProfile)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'dob' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:1000'],
            'identity_number' => ['nullable', 'string', 'max:50'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
        ]);

        $patientProfile->update($validated);

        return back()->with('success', 'Đã cập nhật hồ sơ bệnh nhân.');
    }
}