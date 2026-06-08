<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Service;
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

    public function index()
    {
        $patientId = Auth::id();

        $appointments = Appointment::where('patient_id', $patientId)
            ->where('appointment_date', '>=', now())
            ->where('status', '!=', 'cancelled')
            ->orderBy('appointment_date', 'asc')
            ->with('doctor', 'service')
            ->get();

        $pastAppointments = Appointment::where('patient_id', $patientId)
            ->where('appointment_date', '<', now())
            ->orderBy('appointment_date', 'desc')
            ->with('doctor', 'service')
            ->get();

        return view('patient.appointments.index', compact('appointments', 'pastAppointments'));
    }

    public function create()
    {
        try {
            $services = Service::where('is_active', 1)
                ->orderBy('name', 'asc')
                ->get();

            if ($services->isEmpty()) {
                return view('patient.appointments.create', [
                    'services' => $services,
                    'warning' => 'Hiện chưa có dịch vụ khám nào khả dụng',
                ]);
            }

            return view('patient.appointments.create', compact('services'));
        } catch (\Exception $e) {
            Log::error('Error in create: ' . $e->getMessage());

            return back()->with('error', 'Lỗi khi tải trang đặt lịch');
        }
    }

    public function getServiceCategories()
    {
        try {
            $categories = Service::where('is_active', 1)
                ->whereNotNull('type')
                ->pluck('type')
                ->map(fn ($type) => trim($type))
                ->filter()
                ->unique()
                ->sort()
                ->values();

            return response()->json($categories, 200);
        } catch (\Exception $e) {
            Log::error('Error in getServiceCategories: ' . $e->getMessage());

            return response()->json([
                'error' => 'Lỗi server: ' . $e->getMessage(),
            ], 500);
        }
    }

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
                ->get([
                    'id',
                    'name',
                    'type',
                    'required_specialization',
                    'slots_required',
                    'duration_minutes',
                    'actual_duration',
                ]);

            return response()->json($services, 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation error in getServicesByCategory: ' . json_encode($e->errors()));

            return response()->json([
                'error' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in getServicesByCategory: ' . $e->getMessage());

            return response()->json([
                'error' => 'Lỗi server: ' . $e->getMessage(),
            ], 500);
        }
    }

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
                'date.after_or_equal' => 'Vui lòng chọn ngày hôm nay hoặc trong tương lai',
            ]);

            $doctors = $this->appointmentService->getAvailableDoctorsByService(
                $validated['service_id'],
                $validated['date']
            );

            $response = $doctors->map(function ($item) {
                return [
                    'id' => $item['doctor']->id,
                    'name' => $item['doctor']->name,
                    'specialization' => $item['doctor']->specialization ?? 'N/A',
                    'phone' => $item['doctor']->phone ?? '',
                    'email' => $item['doctor']->email ?? '',
                    'available_slots_count' => count($item['available_slots'] ?? []),
                    'slots_needed' => $item['slots_needed'] ?? 1,
                ];
            });

            return response()->json($response->values()->all(), 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation error in getDoctorsByService: ' . json_encode($e->errors()));

            return response()->json([
                'error' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in getDoctorsByService: ' . $e->getMessage());

            return response()->json([
                'error' => 'Lỗi server: ' . $e->getMessage(),
            ], 500);
        }
    }

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
                'date.after_or_equal' => 'Vui lòng chọn ngày hôm nay hoặc trong tương lai',
            ]);

            $slots = $this->appointmentService->getAvailableSlotsForDoctor(
                $validated['doctor_id'],
                $validated['date']
            );

            return response()->json($slots, 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation error in getAvailableSlots: ' . json_encode($e->errors()));

            return response()->json([
                'error' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in getAvailableSlots: ' . $e->getMessage());

            return response()->json([
                'error' => 'Lỗi server: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getAvailableTimes(Request $request)
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
                'date.after_or_equal' => 'Vui lòng chọn ngày hôm nay hoặc trong tương lai',
            ]);

            $times = $this->appointmentService->getAvailableTimesByService(
                $validated['service_id'],
                $validated['date']
            );

            return response()->json($times, 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation error in getAvailableTimes: ' . json_encode($e->errors()));

            return response()->json([
                'error' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in getAvailableTimes: ' . $e->getMessage());

            return response()->json([
                'error' => 'Lỗi server: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getDoctorsByTime(Request $request)
    {
        try {
            $validated = $request->validate([
                'service_id' => 'required|exists:services,id',
                'date' => 'required|date_format:Y-m-d|after_or_equal:today',
                'start_time' => 'required|date_format:H:i',
            ], [
                'service_id.required' => 'Vui lòng chọn dịch vụ',
                'service_id.exists' => 'Dịch vụ không tồn tại',
                'date.required' => 'Vui lòng chọn ngày',
                'date.date_format' => 'Định dạng ngày không hợp lệ',
                'date.after_or_equal' => 'Vui lòng chọn ngày hôm nay hoặc trong tương lai',
                'start_time.required' => 'Vui lòng chọn thời gian',
                'start_time.date_format' => 'Định dạng thời gian không hợp lệ',
            ]);

            $doctors = $this->appointmentService->getAvailableDoctorsByServiceAndTime(
                $validated['service_id'],
                $validated['date'],
                $validated['start_time']
            );

            return response()->json($doctors, 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation error in getDoctorsByTime: ' . json_encode($e->errors()));

            return response()->json([
                'error' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in getDoctorsByTime: ' . $e->getMessage());

            return response()->json([
                'error' => 'Lỗi server: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'patient_name' => 'required|string|max:100',
                'patient_phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s]{8,20}$/'],
                'patient_email' => 'nullable|email|max:255',
                'patient_dob' => 'nullable|date|before_or_equal:today',
                'patient_gender' => 'nullable|in:Nam,Nữ,Khác',
                'patient_address' => 'nullable|string|max:255',
                'emergency_phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]{8,20}$/'],

                'doctor_id' => 'required|exists:employees,id',
                'service_id' => 'required|exists:services,id',
                'appointment_date' => 'required|date_format:Y-m-d H:i',
                'notes' => 'nullable|string|max:500',
            ], [
                'patient_name.required' => 'Vui lòng nhập họ và tên',
                'patient_phone.required' => 'Vui lòng nhập số điện thoại',
                'patient_phone.regex' => 'Số điện thoại không hợp lệ',
                'patient_email.email' => 'Email không hợp lệ',
                'patient_dob.before_or_equal' => 'Ngày sinh không hợp lệ',
                'patient_gender.in' => 'Giới tính không hợp lệ',
                'emergency_phone.regex' => 'Số điện thoại người thân không hợp lệ',

                'doctor_id.required' => 'Vui lòng chọn bác sĩ',
                'service_id.required' => 'Vui lòng chọn dịch vụ',
                'appointment_date.required' => 'Vui lòng chọn ngày giờ',
                'appointment_date.date_format' => 'Định dạng ngày giờ không hợp lệ',
            ]);

            $appointmentDateTime = Carbon::createFromFormat('Y-m-d H:i', $validated['appointment_date']);

            if ($appointmentDateTime->isPast()) {
                return back()
                    ->withInput()
                    ->with('error', 'Không thể đặt lịch cho thời gian trong quá khứ');
            }

            $appointment = $this->appointmentService->createAppointment([
                'patient_id' => Auth::id(),
                'doctor_id' => $validated['doctor_id'],
                'service_id' => $validated['service_id'],
                'appointment_date' => $validated['appointment_date'],
                'notes' => $this->buildAppointmentNotes($validated),
            ]);

            Log::info('Appointment created successfully: ID=' . $appointment->id . ', Patient=' . Auth::id());

            return redirect()->route('patient.appointment.list')
                ->with('success', 'Lịch hẹn đã được tạo thành công. Vui lòng chờ xác nhận từ bác sĩ.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation error in store: ' . json_encode($e->errors()));

            return back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('error', 'Dữ liệu không hợp lệ');
        } catch (\Exception $e) {
            Log::error('Error in store: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $appointment = Appointment::findOrFail($id);

            if ($appointment->patient_id !== Auth::id()) {
                Log::warning('Unauthorized access attempt for appointment ' . $id . ' by user ' . Auth::id());
                abort(403, 'Không có quyền xem lịch hẹn này');
            }

            return view('patient.appointments.show', compact('appointment'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Appointment not found: ' . $id);

            return back()->with('error', 'Lịch hẹn không tồn tại');
        } catch (\Exception $e) {
            Log::error('Error in show: ' . $e->getMessage());

            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    public function cancel($id)
    {
        try {
            $appointment = Appointment::findOrFail($id);

            if ($appointment->patient_id !== Auth::id()) {
                Log::warning('Unauthorized cancel attempt for appointment ' . $id . ' by user ' . Auth::id());
                abort(403, 'Không có quyền hủy lịch hẹn này');
            }

            if ($appointment->status === 'cancelled') {
                return back()->with('warning', 'Lịch hẹn này đã được hủy trước đó');
            }

            if ($appointment->status === 'completed') {
                return back()->with('error', 'Không thể hủy lịch hẹn đã hoàn thành');
            }

            $this->appointmentService->cancelAppointment($id);

            Log::info('Appointment ' . $id . ' cancelled by patient ' . Auth::id());

            return back()->with('success', 'Lịch hẹn đã được hủy thành công.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Appointment not found for cancel: ' . $id);

            return back()->with('error', 'Lịch hẹn không tồn tại');
        } catch (\Exception $e) {
            Log::error('Error in cancel: ' . $e->getMessage());

            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    private function buildAppointmentNotes(array $validated): string
    {
        $lines = [
            'THÔNG TIN BỆNH NHÂN',
            'Họ tên: ' . $validated['patient_name'],
            'SĐT: ' . $validated['patient_phone'],
        ];

        if (!empty($validated['patient_email'])) {
            $lines[] = 'Email: ' . $validated['patient_email'];
        }

        if (!empty($validated['patient_dob'])) {
            $lines[] = 'Ngày sinh: ' . $validated['patient_dob'];
        }

        if (!empty($validated['patient_gender'])) {
            $lines[] = 'Giới tính: ' . $validated['patient_gender'];
        }

        if (!empty($validated['patient_address'])) {
            $lines[] = 'Địa chỉ: ' . $validated['patient_address'];
        }

        if (!empty($validated['emergency_phone'])) {
            $lines[] = 'SĐT người thân: ' . $validated['emergency_phone'];
        }

        if (!empty($validated['notes'])) {
            $lines[] = '';
            $lines[] = 'TRIỆU CHỨNG / GHI CHÚ';
            $lines[] = $validated['notes'];
        }

        return trim(implode("\n", $lines));
    }
}