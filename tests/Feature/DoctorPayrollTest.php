<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Employee;
use App\Models\DoctorPayroll;
use App\Models\SalaryConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorPayrollTest extends TestCase
{
    use RefreshDatabase;

    protected User $doctorUser;
    protected Employee $doctorEmployee;
    protected SalaryConfig $salaryConfig;

    protected function setUp(): void
    {
        parent::setUp();

        // Tạo tài khoản người dùng bác sĩ
        $this->doctorUser = User::factory()->create([
            'role' => 'doctor',
            'email' => 'doctor@clinic.com',
        ]);

        // Tạo thông tin nhân viên (bác sĩ) tương ứng
        $this->doctorEmployee = Employee::create([
            'user_id' => $this->doctorUser->id,
            'code' => 'EMP001',
            'name' => 'Bác sĩ Nguyễn Văn A',
            'phone' => '0987654321',
            'email' => 'doctor@clinic.com',
            'is_doctor' => true,
            'status' => 'active',
        ]);

        // Tạo cấu hình lương mẫu
        $this->salaryConfig = SalaryConfig::create([
            'base_hourly_rate' => 100000,
            'doctor_coefficient' => 1.3,
            'note' => 'Cấu hình lương mặc định',
        ]);
    }

    /**
     * Test bác sĩ xem được danh sách bảng lương và các số liệu thống kê chính xác.
     */
    public function test_doctor_can_view_payroll_index_and_summary_metrics(): void
    {
        $currentMonth = (int) now()->month;
        $currentYear = (int) now()->year;

        // Bảng lương 1: Đã thanh toán (paid)
        DoctorPayroll::create([
            'payroll_code' => 'LG202606BS0001',
            'doctor_id' => $this->doctorEmployee->id,
            'salary_config_id' => $this->salaryConfig->id,
            'salary_month' => $currentMonth,
            'salary_year' => $currentYear,
            'base_hourly_rate' => 100000,
            'doctor_coefficient' => 1.3,
            'total_work_hours' => 40,
            'gross_amount' => 5200000,
            'net_amount' => 5200000,
            'status' => 'paid',
            'generated_at' => now(),
            'paid_at' => now(),
        ]);

        // Bảng lương 2: Đã duyệt nhưng chưa thanh toán (approved)
        DoctorPayroll::create([
            'payroll_code' => 'LG202606BS0002',
            'doctor_id' => $this->doctorEmployee->id,
            'salary_config_id' => $this->salaryConfig->id,
            'salary_month' => $currentMonth,
            'salary_year' => $currentYear,
            'base_hourly_rate' => 100000,
            'doctor_coefficient' => 1.3,
            'total_work_hours' => 30,
            'gross_amount' => 3900000,
            'net_amount' => 3900000,
            'status' => 'approved',
            'generated_at' => now(),
        ]);

        $response = $this->actingAs($this->doctorUser)
            ->get(route('doctor.payroll.index', [
                'month' => $currentMonth,
                'year' => $currentYear
            ]));

        $response->assertStatus(200);
        
        // Kiểm tra các số liệu thống kê được tính toán chính xác
        $response->assertViewHas('summary', function ($summary) {
            return $summary['total_payrolls'] === 2
                && (float)$summary['gross_amount'] === 9100000.0
                && (float)$summary['net_amount'] === 9100000.0
                && (float)$summary['paid_amount'] === 5200000.0 // Chỉ tính những bảng lương đã thanh toán (paid)
                && (float)$summary['confirmed_amount'] === 0.0; // Chưa có bảng lương nào được bác sĩ bấm xác nhận
        });
    }

    /**
     * Test bác sĩ xem được chi tiết bảng lương của chính mình (chỉ xem được nếu trạng thái là approved hoặc paid).
     */
    public function test_doctor_can_view_their_own_approved_or_paid_payroll_detail(): void
    {
        $payroll = DoctorPayroll::create([
            'payroll_code' => 'LG202606BS0003',
            'doctor_id' => $this->doctorEmployee->id,
            'salary_config_id' => $this->salaryConfig->id,
            'salary_month' => 6,
            'salary_year' => 2026,
            'status' => 'approved',
            'generated_at' => now(),
        ]);

        $response = $this->actingAs($this->doctorUser)
            ->get(route('doctor.payroll.show', $payroll));

        $response->assertStatus(200);
        $response->assertViewHas('payroll');
    }

    /**
     * Test bác sĩ không thể xem chi tiết bảng lương của bác sĩ khác hoặc bảng lương nháp (draft).
     */
    public function test_doctor_cannot_view_unapproved_or_other_doctors_payroll_detail(): void
    {
        // 1. Xem bảng lương bác sĩ khác -> Phải trả về 403 Forbidden
        $otherDoctorUser = User::factory()->create(['role' => 'doctor']);
        $otherEmployee = Employee::create([
            'user_id' => $otherDoctorUser->id,
            'code' => 'EMP002',
            'name' => 'Bác sĩ Khác',
            'phone' => '0987654322',
            'email' => 'other@clinic.com',
            'is_doctor' => true,
        ]);

        $otherPayroll = DoctorPayroll::create([
            'payroll_code' => 'LG202606BS0004',
            'doctor_id' => $otherEmployee->id,
            'salary_month' => 6,
            'salary_year' => 2026,
            'status' => 'approved',
        ]);

        $response1 = $this->actingAs($this->doctorUser)
            ->get(route('doctor.payroll.show', $otherPayroll));

        $response1->assertStatus(403);

        // 2. Xem bảng lương nháp (draft) của chính mình -> Phải trả về 403 Forbidden
        $draftPayroll = DoctorPayroll::create([
            'payroll_code' => 'LG202606BS0005',
            'doctor_id' => $this->doctorEmployee->id,
            'salary_month' => 6,
            'salary_year' => 2026,
            'status' => 'draft',
        ]);

        $response2 = $this->actingAs($this->doctorUser)
            ->get(route('doctor.payroll.show', $draftPayroll));

        $response2->assertStatus(403);
    }

    /**
     * Test bác sĩ bấm nút xác nhận nhận lương khi admin đã đánh dấu "Đã thanh toán" (paid).
     */
    public function test_doctor_can_acknowledge_paid_payroll(): void
    {
        $payroll = DoctorPayroll::create([
            'payroll_code' => 'LG202606BS0006',
            'doctor_id' => $this->doctorEmployee->id,
            'salary_month' => 6,
            'salary_year' => 2026,
            'status' => 'paid', // Bắt buộc phải là paid mới xác nhận được
            'generated_at' => now(),
        ]);

        $response = $this->actingAs($this->doctorUser)
            ->patch(route('doctor.payroll.acknowledge', $payroll));

        $response->assertStatus(302); // Redirect back
        $response->assertSessionHas('success', 'Xác nhận đã nhận lương thành công.');

        $payroll->refresh();
        $this->assertNotNull($payroll->doctor_confirmed_at); // Đã điền thời gian xác nhận
    }

    /**
     * Test bác sĩ không thể bấm xác nhận nhận lương khi admin chưa thanh toán (chỉ mới dừng ở approved).
     */
    public function test_doctor_cannot_acknowledge_unpaid_payroll(): void
    {
        $payroll = DoctorPayroll::create([
            'payroll_code' => 'LG202606BS0007',
            'doctor_id' => $this->doctorEmployee->id,
            'salary_month' => 6,
            'salary_year' => 2026,
            'status' => 'approved', // Mới được duyệt, chưa thanh toán (paid)
            'generated_at' => now(),
        ]);

        $response = $this->actingAs($this->doctorUser)
            ->patch(route('doctor.payroll.acknowledge', $payroll));

        $response->assertStatus(302);
        $response->assertSessionHas('error', 'Chỉ có thể xác nhận khi ban quản trị đã thanh toán.');

        $payroll->refresh();
        $this->assertNull($payroll->doctor_confirmed_at); // Vẫn chưa được xác nhận
    }

    /**
     * Test bác sĩ không thể bấm xác nhận 2 lần cho cùng một bảng lương.
     */
    public function test_doctor_cannot_acknowledge_already_acknowledged_payroll(): void
    {
        $payroll = DoctorPayroll::create([
            'payroll_code' => 'LG202606BS0008',
            'doctor_id' => $this->doctorEmployee->id,
            'salary_month' => 6,
            'salary_year' => 2026,
            'status' => 'paid',
            'doctor_confirmed_at' => now(), // Đã xác nhận rồi
            'generated_at' => now(),
        ]);

        $response = $this->actingAs($this->doctorUser)
            ->patch(route('doctor.payroll.acknowledge', $payroll));

        $response->assertStatus(302);
        $response->assertSessionHas('error', 'Bạn đã xác nhận bảng lương này rồi.');
    }
}
