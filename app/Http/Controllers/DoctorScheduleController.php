<?php

namespace App\Http\Controllers;

use App\Models\CustomShift;
use App\Models\Employee;
use App\Models\OffDay;
use App\Models\ScheduleRequest;
use App\Services\ScheduleRequestService;
use Illuminate\Http\Request;

class DoctorScheduleController extends Controller
{
    protected $scheduleRequestService;

    public function __construct(ScheduleRequestService $scheduleRequestService)
    {
        $this->scheduleRequestService = $scheduleRequestService;
    }

    /**
     * ✅ Lấy bác sĩ hiện tại (tạo mới nếu chưa có)
     * 🔒 BẢO MẬT: Kiểm tra user_id khớp với employee
     */
    private function getDoctor()
    {
        $user = auth()->user();
        if (!$user) {
            abort(401, 'Vui lòng đăng nhập');
        }

        // Kiểm tra doctor tồn tại
        $doctor = $user->employee;
        
        // Nếu chưa có doctor record, tạo mới
        if (!$doctor) {
            $doctor = Employee::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);
        }

        // 🔒 KIỂM TRA BẢO MẬT: Đảm bảo employee này thuộc user hiện tại
        if ($doctor->user_id !== $user->id) {
            abort(403, '❌ Bạn không có quyền truy cập dữ liệu này');
        }

        return $doctor;
    }

    /**
     * ✅ GET /doctor/schedule - Hiển thị trang chính với 2 tabs
     * Tab 1: Calendar để đăng ký ca
     * Tab 2: Xin ngày nghỉ
     */
    public function create()
    {
        try {
            $doctor = $this->getDoctor();
            
            // Lấy tất cả ca làm việc
            $shifts = CustomShift::where('is_active', 1)
                ->orderBy('start_hour', 'asc')
                ->get();
            
            // Lấy data để hiển thị trên các tabs - chỉ của bác sĩ hiện tại
            $pendingRequests = $this->scheduleRequestService->getPendingRequests($doctor->id);
            $approvedRequests = $this->scheduleRequestService->getApprovedRequests($doctor->id);
            
            // ✅ THÊM: Lấy pending off-days
            $pendingOffDays = OffDay::where('employee_id', $doctor->id)
                ->where('status', 'pending')
                ->orderBy('date', 'asc')
                ->get();
            
            $approvedOffDays = $this->scheduleRequestService->getApprovedOffDays($doctor->id);

            return view('doctor.schedule.request-form', compact(
                'shifts',
                'pendingRequests',
                'approvedRequests',
                'pendingOffDays',  // ✅ THÊM
                'approvedOffDays',
                'doctor'
            ));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => '❌ Lỗi: ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ GET /doctor/schedule/official - Hiển thị lịch chính thức (approved schedules & off-days)
     * Bác sĩ xem lịch làm việc & ngày nghỉ đã được duyệt
     */
    public function officialSchedule()
    {
        try {
            $doctor = $this->getDoctor();
            
            // Lấy approved schedules của bác sĩ hiện tại
            $approvedSchedules = ScheduleRequest::where('employee_id', $doctor->id)
                ->where('status', 'approved')
                ->orderBy('work_date', 'asc')
                ->get();
            
            // Lấy approved off-days của bác sĩ hiện tại
            $approvedOffDays = OffDay::where('employee_id', $doctor->id)
                ->where('status', 'approved')
                ->orderBy('date', 'asc')
                ->get();

            return view('doctor.schedule.official-schedule', compact(
                'approvedSchedules',
                'approvedOffDays',
                'doctor'
            ));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => '❌ Lỗi: ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ POST /doctor/schedule - Bác sĩ đăng ký ca làm việc
     * Form submit từ modal trong tab "Lịch đăng ký ca"
     * Cho phép đăng ký bất kỳ ngày nào (kể cả quá khứ)
     */
    public function store(Request $request)
    {
        try {
            $doctor = $this->getDoctor();
            
            // Validate dữ liệu - cho phép đăng ký bất kỳ ngày nào (kể cả quá khứ)
            $validated = $request->validate([
                'work_date' => 'required|date',
                'shift_id' => 'required|integer|exists:custom_shifts,id',
                'start_hour' => 'nullable|integer|between:0,23',
                'start_minute' => 'nullable|integer|between:0,59',
                'end_hour' => 'nullable|integer|between:0,23',
                'end_minute' => 'nullable|integer|between:0,59',
            ]);

            // Tạo schedule request qua service (cho bác sĩ hiện tại)
            $this->scheduleRequestService->createScheduleRequest(
                $doctor->id,
                $validated['work_date'],
                (int)$validated['shift_id'],
                $validated
            );

            // Redirect về trang chính với success message
            return redirect(route('doctor.schedule.create'))
                ->with('success', '✅ Đã gửi đơn đăng ký ca làm việc!');
        } catch (\Exception $e) {
            // Redirect về trang chính với error message
            return redirect(route('doctor.schedule.create'))
                ->withErrors(['error' => '❌ ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ PUT /doctor/schedule/{scheduleRequest} - Cập nhật đơn đăng ký ca
     * Bác sĩ chỉ có thể cập nhật đơn của chính mình và chỉ khi đang pending
     * Có thể cập nhật cả những ngày đã qua
     * 🔒 BẢO MẬT: Kiểm tra ownership trước khi cập nhật
     */
    public function updateSchedule(Request $request, ScheduleRequest $scheduleRequest)
    {
        try {
            $doctor = $this->getDoctor();
            
            // 🔒 KIỂM TRA BẢO MẬT: Đơn này có thuộc bác sĩ hiện tại không?
            if ($scheduleRequest->employee_id !== $doctor->id) {
                abort(403, '❌ Bạn không có quyền cập nhật đơn này');
            }

            // Validate dữ liệu - cho phép cập nhật bất kỳ ngày nào (kể cả quá khứ)
            $validated = $request->validate([
                'work_date' => 'required|date',
                'shift_id' => 'required|integer|exists:custom_shifts,id',
                'start_hour' => 'nullable|integer|between:0,23',
                'start_minute' => 'nullable|integer|between:0,59',
                'end_hour' => 'nullable|integer|between:0,23',
                'end_minute' => 'nullable|integer|between:0,59',
            ]);

            // Cập nhật schedule request qua service
            $this->scheduleRequestService->updateScheduleRequest(
                $scheduleRequest->id,
                $doctor->id,
                $validated['work_date'],
                (int)$validated['shift_id'],
                $validated
            );

            // Redirect về trang chính với success message
            return redirect(route('doctor.schedule.create'))
                ->with('success', '✅ Đã cập nhật ca làm việc! Đơn này sẽ được duyệt lại.');
        } catch (\Exception $e) {
            // Redirect về trang chính với error message
            return redirect(route('doctor.schedule.create'))
                ->withErrors(['error' => '❌ ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ DELETE /doctor/schedule/{scheduleRequest} - Hủy đơn đăng ký ca
     * Bác sĩ chỉ có thể hủy đơn của chính mình
     * 🔒 BẢO MẬT: Kiểm tra ownership trước khi hủy
     */
    public function cancel(ScheduleRequest $scheduleRequest)
    {
        try {
            $doctor = $this->getDoctor();
            
            // 🔒 KIỂM TRA BẢO MẬT: Đơn này có thuộc bác sĩ hiện tại không?
            if ($scheduleRequest->employee_id !== $doctor->id) {
                abort(403, '❌ Không có quyền hủy đơn này');
            }

            // Hủy đơn qua service
            $this->scheduleRequestService->cancelScheduleRequest($scheduleRequest->id);

            return back()->with('success', '✅ Đã hủy đơn đăng ký');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => '❌ ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ POST /doctor/schedule/request-off-day - Bác sĩ xin ngày nghỉ
     * Form submit từ tab "Xin ngày nghỉ"
     */
    public function requestOffDay(Request $request)
    {
        try {
            $doctor = $this->getDoctor();

            // ✅ UPDATED: Validate reason tối thiểu 5 ký tự
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'nullable|string|min:5|max:200',
            ]);

            // Tạo off-day record cho mỗi ngày trong khoảng
            $startDate = strtotime($validated['start_date']);
            $endDate = strtotime($validated['end_date']);

            for ($date = $startDate; $date <= $endDate; $date += 86400) {
                $dayStr = date('Y-m-d', $date);
                
                OffDay::firstOrCreate(
                    [
                        'employee_id' => $doctor->id,
                        'date' => $dayStr,
                    ],
                    [
                        'reason' => $validated['reason'] ?? '',
                        'status' => 'pending',
                    ]
                );
            }

            // Redirect về trang chính với success message
            return redirect(route('doctor.schedule.create'))
                ->with('success', '✅ Đã gửi đơn xin nghỉ!');
        } catch (\Exception $e) {
            // Redirect về trang chính với error message
            return redirect(route('doctor.schedule.create'))
                ->withErrors(['error' => '❌ ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ PUT /doctor/schedule/off-day/{offDay} - Cập nhật đơn xin nghỉ
     * Bác sĩ chỉ có thể cập nhật đơn của chính mình và chỉ khi đang pending
     * 🔒 BẢO MẬT: Kiểm tra ownership trước khi cập nhật
     */
    public function updateOffDay(Request $request, OffDay $offDay)
    {
        try {
            $doctor = $this->getDoctor();

            // 🔒 KIỂM TRA BẢO MẬT: Đơn xin nghỉ này có thuộc bác sĩ hiện tại không?
            if ($offDay->employee_id !== $doctor->id) {
                abort(403, '❌ Không có quyền cập nhật');
            }

            // Kiểm tra trạng thái - chỉ cập nhật được đơn đang chờ duyệt
            if ($offDay->status !== 'pending') {
                return back()->withErrors(['error' => '❌ Chỉ có thể cập nhật đơn đang chờ duyệt']);
            }

            // Validate dữ liệu
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'nullable|string|min:5|max:200',
            ]);

            // Cập nhật đơn xin nghỉ
            $offDay->update([
                'date' => $validated['start_date'],
                'reason' => $validated['reason'] ?? '',
            ]);

            return redirect(route('doctor.schedule.create'))
                ->with('success', '✅ Cập nhật đơn xin nghỉ thành công!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => '❌ ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ DELETE /doctor/schedule/off-day/{offDay} - Hủy đơn xin nghỉ
     * Bác sĩ chỉ có thể hủy đơn của chính mình và đơn chưa được duyệt
     * 🔒 BẢO MẬT: Kiểm tra ownership trước khi hủy
     */
    public function destroyOffDay(OffDay $offDay)
    {
        try {
            $doctor = $this->getDoctor();

            // 🔒 KIỂM TRA BẢO MẬT: Đơn xin nghỉ này có thuộc bác sĩ hiện tại không?
            if ($offDay->employee_id !== $doctor->id) {
                abort(403, '❌ Không có quyền hủy');
            }

            // Kiểm tra trạng thái - chỉ hủy được đơn đang chờ duyệt
            if ($offDay->status !== 'pending') {
                return back()->withErrors(['error' => '❌ Chỉ có thể hủy đơn đang chờ duyệt']);
            }

            // Xóa đơn xin nghỉ
            $offDay->delete();

            return back()->with('success', '✅ Đã hủy đơn xin nghỉ');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => '❌ ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ DELETE /doctor/schedule/off-day/{offDay} - Alias cho destroyOffDay
     */
    public function cancelOffDay(OffDay $offDay)
    {
        return $this->destroyOffDay($offDay);
    }
}