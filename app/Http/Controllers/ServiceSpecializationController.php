<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ServiceSpecializationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * GET /admin/services/specializations
     * Trang quản lý gán chuyên khoa cho dịch vụ
     */
    public function index()
    {
        try {
            $services = Service::where('is_active', 1)
                ->orderBy('name', 'asc')
                ->get();

            // Chỉ lấy chuyên khoa từ bác sĩ đang hoạt động.
            // Nếu lấy cả bác sĩ inactive, bệnh nhân có thể không tìm thấy bác sĩ khi đặt lịch.
            $specializations = Employee::where('is_doctor', 1)
                ->where('status', 'active')
                ->whereNotNull('specialization')
                ->where('specialization', '!=', '')
                ->distinct()
                ->pluck('specialization')
                ->map(fn ($specialization) => trim($specialization))
                ->filter()
                ->unique()
                ->sort()
                ->values()
                ->all();

            return view('admin.services.specializations', compact('services', 'specializations'));
        } catch (\Exception $e) {
            Log::error('Error in ServiceSpecializationController@index: ' . $e->getMessage());

            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    /**
     * PUT /admin/services/{id}/specialization
     * Cập nhật chuyên khoa bắt buộc cho dịch vụ.
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'required_specialization' => 'required|string|max:255',
            ], [
                'required_specialization.required' => 'Vui lòng chọn chuyên khoa',
            ]);

            $requiredSpecialization = trim($validated['required_specialization']);

            if ($requiredSpecialization === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng chọn chuyên khoa',
                ], 422);
            }

            $service = Service::findOrFail($id);

            // Chỉ cho phép gán chuyên khoa có ít nhất một bác sĩ đang hoạt động.
            // Logic đặt lịch bệnh nhân cũng lọc bác sĩ active, nên phần gán phải đồng nhất.
            $isValidSpecialization = Employee::where('is_doctor', 1)
                ->where('status', 'active')
                ->where('specialization', $requiredSpecialization)
                ->exists();

            if (!$isValidSpecialization) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chuyên khoa này chưa có bác sĩ đang hoạt động trong hệ thống',
                ], 422);
            }

            $oldSpecialization = $service->required_specialization;

            $service->update([
                'required_specialization' => $requiredSpecialization,
            ]);

            Log::info("Service '{$service->name}' specialization updated from '{$oldSpecialization}' to '{$requiredSpecialization}'");

            return response()->json([
                'success' => true,
                'message' => "Dịch vụ '{$service->name}' đã cập nhật chuyên khoa thành công",
                'data' => [
                    'service_id' => $service->id,
                    'service_name' => $service->name,
                    'specialization' => $service->required_specialization,
                ],
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation error in ServiceSpecializationController@update: ' . json_encode($e->errors()));

            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Service not found: ' . $id);

            return response()->json([
                'success' => false,
                'message' => 'Dịch vụ không tồn tại',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error updating service specialization: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Lỗi server: ' . $e->getMessage(),
            ], 500);
        }
    }
}