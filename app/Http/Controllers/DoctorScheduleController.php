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
     * ✅ GET /doctor/schedule - Hiển thị trang chính với 3 tabs
     * Tab 1: Calendar để đăng ký ca
     * Tab 2: Xin ngày nghỉ
     * Tab 3: Xem ca & ngày nghỉ đã duyệt
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
            $approvedOffDays = $this->scheduleRequestService->getApprovedOffDays($doctor->id);

            return view('doctor.schedule.request-form', compact(
                'shifts',
                'pendingRequests',
                'approvedRequests',
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

            // Validate dữ liệu
            $validated = $request->validate([
                'start_date' => 'required|date',
                'reason' => 'nullable|string|max:500',
            ]);

            OffDay::firstOrCreate(
                [
                    'employee_id' => $doctor->id,
                    'date' => $validated['start_date'],
                ],
                [
                    'reason' => $validated['reason'] ?? '',
                    'status' => 'pending',
                ]
            );

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
     * ✅ DELETE /doctor/schedule/off-day/{offDay} - Hủy đơn xin nghỉ
     * Bác sĩ chỉ có thể hủy đơn của chính mình và đơn chưa được duyệt
     * 🔒 BẢO MẬT: Kiểm tra ownership trước khi hủy
     */
    public function cancelOffDay(OffDay $offDay)
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
}