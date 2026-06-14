<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\ClinicalImage;
use App\Models\DentalChart;
use App\Models\DentalChartHistory;
use App\Models\PatientProfile;
use Illuminate\Http\Request;

class EmployeePatientRecordController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Danh sách hồ sơ bệnh nhân cho nhân viên/lễ tân.
     * Chỉ phục vụ tra cứu, tiếp nhận, điều phối - không chỉnh sửa bệnh án chuyên môn.
     */
    public function index(Request $request)
    {
        $keyword = trim((string) $request->input('keyword'));
        $source = $request->input('source');

        $profiles = PatientProfile::query()
            ->with([
                'appointments' => function ($query) {
                    $query->with(['service', 'doctor', 'room', 'medicalRecord'])
                        ->latest('appointment_date');
                },
            ])
            ->withCount([
                'appointments as total_visits_count',
                'appointments as completed_visits_count' => function ($query) {
                    $query->where('status', 'completed');
                },
            ])
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('full_name', 'like', "%{$keyword}%")
                        ->orWhere('phone', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%")
                        ->orWhere('identity_number', 'like', "%{$keyword}%");
                });
            })
            ->when($source, function ($query) use ($source) {
                $query->where('source', $source);
            })
            ->latest('updated_at')
            ->paginate(12)
            ->withQueryString();

        $todayVisitsCount = Appointment::whereDate('appointment_date', today())->count();

        $waitingCount = Appointment::whereIn('status', [
            'checked_in',
            'waiting',
        ])->count();

        $completedTodayCount = Appointment::whereDate('appointment_date', today())
            ->where('status', 'completed')
            ->count();

        return view('employees.patient-profiles.index', compact(
            'profiles',
            'keyword',
            'source',
            'todayVisitsCount',
            'waitingCount',
            'completedTodayCount'
        ));
    }

    /**
     * Chi tiết hồ sơ bệnh nhân cho nhân viên/lễ tân.
     * Read-only với bệnh án, sơ đồ răng, ảnh cận lâm sàng.
     */
    public function show(PatientProfile $patientProfile)
    {
        $patientProfile->load([
            'appointments' => function ($query) {
                $query->with(['service', 'doctor', 'room', 'medicalRecord', 'clinicalImages'])
                    ->latest('appointment_date');
            },
        ]);

        $appointments = $patientProfile->appointments;
        $latestAppointment = $appointments->first();
        $latestRecord = $latestAppointment?->medicalRecord;

        $dentalCharts = DentalChart::where('patient_profile_id', $patientProfile->id)
            ->get()
            ->keyBy('tooth_number');

        $dentalChartHistories = DentalChartHistory::where('patient_profile_id', $patientProfile->id)
            ->with('doctor')
            ->latest()
            ->limit(30)
            ->get();

        $clinicalImages = ClinicalImage::where('patient_profile_id', $patientProfile->id)
            ->with(['appointment', 'doctor'])
            ->latest('taken_date')
            ->latest()
            ->get();

        return view('employees.patient-profiles.show', compact(
            'patientProfile',
            'appointments',
            'latestAppointment',
            'latestRecord',
            'dentalCharts',
            'dentalChartHistories',
            'clinicalImages'
        ));
    }
}