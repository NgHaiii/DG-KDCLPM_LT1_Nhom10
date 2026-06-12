<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\ClinicalImage;
use App\Models\DentalChart;
use App\Models\DentalChartHistory;
use App\Models\Employee;
use App\Models\PatientProfile;
use Illuminate\Http\Request;

class AdminPatientRecordController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Danh sách hồ sơ bệnh án cho admin.
     * Admin chỉ xem, tìm kiếm, lọc và quản lý tổng quan.
     */
    public function index(Request $request)
    {
        $keyword = trim((string) $request->input('keyword'));
        $source = $request->input('source');
        $doctorId = $request->input('doctor_id');
        $status = $request->input('status');

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
            ->when($doctorId, function ($query) use ($doctorId) {
                $query->whereHas('appointments', function ($q) use ($doctorId) {
                    $q->where('doctor_id', $doctorId);
                });
            })
            ->when($status, function ($query) use ($status) {
                $query->whereHas('appointments', function ($q) use ($status) {
                    $q->where('status', $status);
                });
            })
            ->latest('updated_at')
            ->paginate(12)
            ->withQueryString();

        $doctors = Employee::where('is_doctor', 1)
            ->orderBy('name')
            ->get();

        $totalProfiles = PatientProfile::count();
        $onlineProfiles = PatientProfile::where('source', 'online')->count();
        $offlineProfiles = PatientProfile::where('source', 'offline')->count();

        $todayVisitsCount = Appointment::whereDate('appointment_date', today())->count();

        $completedTodayCount = Appointment::whereDate('appointment_date', today())
            ->where('status', 'completed')
            ->count();

        $waitingCount = Appointment::whereIn('status', [
            'checked_in',
            'waiting',
            'in_progress',
        ])->count();

        return view('admin.patient-records.index', compact(
            'profiles',
            'doctors',
            'keyword',
            'source',
            'doctorId',
            'status',
            'totalProfiles',
            'onlineProfiles',
            'offlineProfiles',
            'todayVisitsCount',
            'completedTodayCount',
            'waitingCount'
        ));
    }

    /**
     * Chi tiết hồ sơ bệnh án cho admin.
     * Chỉ xem, không chỉnh sửa nội dung chuyên môn.
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
            ->limit(50)
            ->get();

        $clinicalImages = ClinicalImage::where('patient_profile_id', $patientProfile->id)
            ->with(['appointment', 'doctor'])
            ->latest('taken_date')
            ->latest()
            ->get();

        return view('admin.patient-records.show', compact(
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