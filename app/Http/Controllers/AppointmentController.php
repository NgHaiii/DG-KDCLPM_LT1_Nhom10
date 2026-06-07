<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\Employee;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AppointmentController extends Controller
{
    protected $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
        $this->middleware('auth');
    }

    /**
     * 📄 GET /patient/appointments - Trang danh sách lịch hẹn
     * Hiển thị lịch hẹn sắp tới và quá khứ
     */
    public function index()
    {
        $patientId = Auth::id();
        
        // Lấy lịch hẹn sắp tới
        $appointments = Appointment::where('patient_id', $patientId)
            ->where('appointment_date', '>=', now())
            ->where('status', '!=', 'cancelled')
            ->orderBy('appointment_date', 'asc')
            ->with('doctor', 'service')
            ->get();
        
        // Lấy lịch hẹn quá khứ
        $pastAppointments = Appointment::where('patient_id', $patientId)
            ->where('appointment_date', '<', now())
            ->orderBy('appointment_date', 'desc')
            ->with('doctor', 'service')
            ->get();
        
        return view('patient.appointments.index', compact('appointments', 'pastAppointments'));
    }

    /**
     * 📄 GET /patient/appointments/create - Trang đặt lịch hẹn
     * Hiển thị form đặt lịch với danh sách dịch vụ
     */
    public function create()
    {
        try {
            $services = Service::where('is_active', 1)
                ->orderBy('name', 'asc')
                ->get();
            
            if ($services->isEmpty()) {
                return view('patient.appointments.create', [
                    'services' => $services,
                    'warning' => 'Hiện chưa có dịch vụ khám nào khả dụng'
                ]);
            }

            return view('patient.appointments.create', compact('services'));
        } catch (\Exception $e) {
            Log::error('Error in create: ' . $e->getMessage());
            return back()->with('error', '❌ Lỗi khi tải trang đặt lịch');
        }
    }

    /**
     * 📁 API: GET /patient/api/service-categories
     * Lấy danh sách loại dịch vụ (categories = type)
     */
    public function getServiceCategories()
    {
        try {
            $categories = Service::where('is_active', 1)
                ->distinct()
                ->whereNotNull('type')
                ->pluck('type')
                ->filter()
                ->sort()
                ->values();

            if ($categories->isEmpty()) {
                return response()->json([], 200);
            }

            return response()->json($categories, 200);
        } catch (\Exception $e) {
            Log::error('Error in getServiceCategories: ' . $e->getMessage());
            return response()->json(['error' => 'Lỗi server'], 500);
        }
    }

    /**
     * 🔍 API: GET /patient/api/services-by-category
     * Lấy danh sách dịch vụ theo loại (type)
     * 
     * Query params:
     *   - category: Tên loại dịch vụ (required)
     */
    public function getServicesByCategory(Request $request)
    {
        try {
            $validated = $request->validate([
                'category' => 'required|string|max:255',
            ], [
                'category.required' => 'Vui lòng chọn loại dịch vụ',
            ]);

            $services = Service::where('type', $validated['category'])
                ->where('is_active', 1)
                ->orderBy('name', 'asc')
                ->get(['id', 'name', 'type', 'slots_required', 'actual_duration']);

            if ($services->isEmpty()) {
                return response()->json([], 200);
            }

            return response()->json($services, 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation error in getServicesByCategory: ' . json_encode($e->errors()));
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error in getServicesByCategory: ' . $e->getMessage());
            return response()->json(['error' => 'Lỗi server'], 500);
        }
    }

    /**
     * 👨‍⚕️ API: GET /patient/api/doctors-by-service
     * Lấy danh sách bác sĩ rảnh theo dịch vụ và ngày
     * 
     * Query params:
     *   - service_id: ID dịch vụ (required)
     *   - date: Ngày dạng Y-m-d (required, phải >= hôm nay)
     */
    public function getDoctorsByService(Request $request)
    {
        try {
            $validated = $request->validate([
                'service_id' => 'required|exists:services,id',
                'date' => 'required|date_format:Y-m-d|after_or_equal:today',
            ], [
                'service_id.required' => 'Vui lòng chọn dịch vụ',
                'service_id.exists' => 'Dịch vụ không tồn tại',
                'date.required' => 'Vui lòng chọn ngày',
                'date.date_format' => 'Định dạng ngày không hợp lệ',
                'date.after_or_equal' => 'Vui lòng chọn ngày trong tương lai',
            ]);

            $doctors = $this->appointmentService->getAvailableDoctorsByService(
                $validated['service_id'],
                $validated['date']
            );

            if ($doctors->isEmpty()) {
                return response()->json([], 200);
            }

            $response = $doctors->map(function ($item) {
                return [
                    'id' => $item['doctor']->id,
                    'name' => $item['doctor']->name,
                    'specialization' => $item['doctor']->specialization ?? 'N/A',
                    'phone' => $item['doctor']->phone ?? '',
                    'email' => $item['doctor']->email ?? '',
                    'available_slots_count' => count($item['available_slots']),
                    'slots_needed' => $item['slots_needed'],
                ];
            });

            return response()->json($response->values()->all(), 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation error in getDoctorsByService: ' . json_encode($e->errors()));
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error in getDoctorsByService: ' . $e->getMessage());
            return response()->json(['error' => 'Lỗi server: ' . $e->getMessage()], 500);
        }
    }

    /**
     * ⏰ API: GET /patient/api/available-slots
     * Lấy danh sách slot thời gian trống của bác sĩ trong ngày
     * 
     * Query params:
     *   - doctor_id: ID bác sĩ (required)
     *   - date: Ngày dạng Y-m-d (required, phải >= hôm nay)
     */
    public function getAvailableSlots(Request $request)
    {
        try {
            $validated = $request->validate([
                'doctor_id' => 'required|exists:employees,id',
                'date' => 'required|date_format:Y-m-d|after_or_equal:today',
            ], [
                'doctor_id.required' => 'Vui lòng chọn bác sĩ',
                'doctor_id.exists' => 'Bác sĩ không tồn tại',
                'date.required' => 'Vui lòng chọn ngày',
                'date.date_format' => 'Định dạng ngày không hợp lệ',
                'date.after_or_equal' => 'Vui lòng chọn ngày trong tương lai',
            ]);

            $slots = $this->appointmentService->getAvailableSlotsForDoctor(
                $validated['doctor_id'],
                $validated['date']
            );

            return response()->json($slots, 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation error in getAvailableSlots: ' . json_encode($e->errors()));
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error in getAvailableSlots: ' . $e->getMessage());
            return response()->json(['error' => 'Lỗi server: ' . $e->getMessage()], 500);
        }
    }

    /**
     * ✅ POST /patient/appointments - Tạo lịch hẹn mới
     * 
     * Body:
     *   - doctor_id: ID bác sĩ (required)
     *   - service_id: ID dịch vụ (required)
     *   - appointment_date: Ngày giờ dạng Y-m-d H:i (required)
     *   - notes: Ghi chú (optional, max 500 ký tự)
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'doctor_id' => 'required|exists:employees,id',
                'service_id' => 'required|exists:services,id',
                'appointment_date' => 'required|date_format:Y-m-d H:i',
                'notes' => 'nullable|string|max:500',
            ], [
                'doctor_id.required' => 'Vui lòng chọn bác sĩ',
                'service_id.required' => 'Vui lòng chọn dịch vụ',
                'appointment_date.required' => 'Vui lòng chọn ngày giờ',
                'appointment_date.date_format' => 'Định dạng ngày giờ không hợp lệ',
            ]);

            // Kiểm tra ngày giờ không quá khứ
            $appointmentDateTime = Carbon::createFromFormat('Y-m-d H:i', $validated['appointment_date']);
            if ($appointmentDateTime->isPast()) {
                return back()
                    ->withInput()
                    ->with('error', '❌ Không thể đặt lịch cho thời gian trong quá khứ');
            }

            // Tạo lịch hẹn
            $appointment = $this->appointmentService->createAppointment([
                'patient_id' => Auth::id(),
                'doctor_id' => $validated['doctor_id'],
                'service_id' => $validated['service_id'],
                'appointment_date' => $validated['appointment_date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            Log::info('Appointment created successfully for patient ' . Auth::id());

            return redirect()->route('patient.appointment.list')
                ->with('success', '✅ Lịch hẹn đã được tạo thành công! Vui lòng chờ xác nhận từ bác sĩ.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation error in store: ' . json_encode($e->errors()));
            return back()
                ->withInput()
                ->with('error', '❌ Dữ liệu không hợp lệ');
        } catch (\Exception $e) {
            Log::error('Error in store: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', '❌ Lỗi: ' . $e->getMessage());
        }
    }

    /**
     * 🔍 GET /patient/appointments/{id} - Xem chi tiết lịch hẹn
     * Hiển thị thông tin chi tiết của một lịch hẹn
     */
    public function show($id)
    {
        try {
            $appointment = Appointment::findOrFail($id);

            // Bảo mật: Kiểm tra bệnh nhân có quyền xem không
            if ($appointment->patient_id !== Auth::id()) {
                Log::warning('Unauthorized access attempt for appointment ' . $id . ' by user ' . Auth::id());
                abort(403, 'Không có quyền xem lịch hẹn này');
            }

            return view('patient.appointments.show', compact('appointment'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Appointment not found: ' . $id);
            return back()->with('error', '❌ Lịch hẹn không tồn tại');
        } catch (\Exception $e) {
            Log::error('Error in show: ' . $e->getMessage());
            return back()->with('error', '❌ Lỗi: ' . $e->getMessage());
        }
    }

    /**
     * ❌ POST /patient/appointments/{id}/cancel - Hủy lịch hẹn
     * Thay đổi trạng thái lịch hẹn thành 'cancelled'
     */
    public function cancel($id)
    {
        try {
            $appointment = Appointment::findOrFail($id);

            // Bảo mật: Kiểm tra bệnh nhân có quyền hủy không
            if ($appointment->patient_id !== Auth::id()) {
                Log::warning('Unauthorized cancel attempt for appointment ' . $id . ' by user ' . Auth::id());
                abort(403, 'Không có quyền hủy lịch hẹn này');
            }

            // Kiểm tra xem lịch hẹn có thể hủy không
            if ($appointment->status === 'cancelled') {
                return back()->with('warning', '⚠️ Lịch hẹn này đã được hủy trước đó');
            }

            if ($appointment->status === 'completed') {
                return back()->with('error', '❌ Không thể hủy lịch hẹn đã hoàn thành');
            }

            $this->appointmentService->cancelAppointment($id);

            Log::info('Appointment ' . $id . ' cancelled by patient ' . Auth::id());

            return back()->with('success', '✅ Lịch hẹn đã được hủy thành công.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Appointment not found for cancel: ' . $id);
            return back()->with('error', '❌ Lịch hẹn không tồn tại');
        } catch (\Exception $e) {
            Log::error('Error in cancel: ' . $e->getMessage());
            return back()->with('error', '❌ Lỗi: ' . $e->getMessage());
        }
    }
}