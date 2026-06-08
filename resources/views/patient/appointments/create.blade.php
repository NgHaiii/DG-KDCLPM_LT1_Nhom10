@extends('layouts.patient-layout')

@section('title', 'Đặt Lịch Khám - DentalCare')

@section('page-title', 'Đặt Lịch Khám Bệnh')
@section('page-subtitle', 'Vui lòng nhập thông tin cá nhân, chọn dịch vụ, thời gian và bác sĩ phù hợp')

@section('styles')
<style>
    .appointment-container { max-width: 820px; margin: 0 auto; }

    .card {
        border: 1px solid var(--border-color);
        background: white;
        border-radius: var(--radius-lg);
        padding: 32px;
        box-shadow: var(--shadow-md);
    }

    .form-section { margin-bottom: 32px; }
    .form-section:last-child { margin-bottom: 0; }

    .section-title {
        font-family: var(--font-title);
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 16px;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title i { color: var(--primary); font-size: 20px; }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .form-group { margin-bottom: 16px; }
    .form-grid .form-group { margin-bottom: 0; }
    .form-group:last-child { margin-bottom: 0; }

    .form-group.full-width { grid-column: 1 / -1; }

    .form-group label {
        display: block;
        font-weight: 500;
        margin-bottom: 8px;
        color: var(--text-main);
        font-size: 14px;
    }

    .required::after {
        content: ' *';
        color: var(--error);
        font-weight: 600;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 11px 14px;
        border: 1px solid #e2e8f0;
        border-radius: var(--radius-md);
        font-size: 14px;
        font-family: var(--font-body);
        background: white;
        color: var(--text-main);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .form-group input:hover,
    .form-group select:hover { border-color: #cbd5e1; }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
        background: #f0f9ff;
    }

    .form-group input:disabled,
    .form-group select:disabled {
        background: #f8fafc;
        color: #94a3b8;
        cursor: not-allowed;
        opacity: 0.6;
    }

    .form-group textarea {
        resize: vertical;
        min-height: 100px;
        font-family: var(--font-body);
    }

    .info-box {
        background: linear-gradient(135deg, #dbeafe 0%, #e0f2fe 100%);
        border: 1px solid rgba(59, 130, 246, 0.2);
        border-radius: var(--radius-lg);
        padding: 16px;
        margin-bottom: 24px;
        font-size: 13px;
        color: var(--info-dark);
        display: flex;
        gap: 12px;
        align-items: flex-start;
    }

    .info-box i {
        font-size: 18px;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .loading-state {
        display: none;
        font-size: 13px;
        color: var(--text-muted);
        margin-top: 8px;
        align-items: center;
        gap: 8px;
    }

    .loading-state.show { display: flex; }

    .spinner {
        display: inline-block;
        width: 14px;
        height: 14px;
        border: 2px solid rgba(14, 165, 233, 0.2);
        border-top-color: var(--primary);
        border-radius: 50%;
        animation: spin 0.6s linear infinite;
    }

    @keyframes spin { to { transform: rotate(360deg); } }

    .error-text {
        color: var(--error);
        font-size: 12px;
        margin-top: 6px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .help-text {
        color: var(--text-muted);
        font-size: 12px;
        margin-top: 6px;
    }

    .form-actions {
        display: flex;
        gap: 12px;
        margin-top: 32px;
        padding-top: 32px;
        border-top: 1px solid var(--border-color);
    }

    .form-actions .btn { flex: 1; }

    .divider {
        height: 1px;
        background: var(--border-color);
        margin: 24px 0;
    }

    .step-indicator {
        display: flex;
        justify-content: space-between;
        margin-bottom: 32px;
        position: relative;
    }

    .step-indicator::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 0;
        right: 0;
        height: 2px;
        background: var(--border-color);
        z-index: 0;
    }

    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        position: relative;
        z-index: 1;
    }

    .step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: white;
        border: 2px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 16px;
        color: var(--text-muted);
        transition: all 0.3s;
    }

    .step.active .step-number {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
        box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.1);
    }

    .step.completed .step-number {
        background: var(--success);
        border-color: var(--success);
        color: white;
    }

    .step-label {
        font-size: 12px;
        font-weight: 500;
        color: var(--text-muted);
        text-align: center;
        max-width: 90px;
    }

    @media (max-width: 768px) {
        .appointment-container { max-width: 100%; }
        .card { padding: 24px; }
        .form-grid { grid-template-columns: 1fr; }
        .form-actions { flex-direction: column-reverse; }
        .step-indicator { margin-bottom: 24px; }
        .step-label { display: none; }
    }
</style>
@endsection

@section('content')
<div class="appointment-container">
    <div class="step-indicator">
        <div class="step active" id="step1">
            <div class="step-number">1</div>
            <div class="step-label">Thông tin</div>
        </div>
        <div class="step" id="step2">
            <div class="step-number">2</div>
            <div class="step-label">Dịch vụ</div>
        </div>
        <div class="step" id="step3">
            <div class="step-number">3</div>
            <div class="step-label">Ngày & giờ</div>
        </div>
        <div class="step" id="step4">
            <div class="step-number">4</div>
            <div class="step-label">Bác sĩ</div>
        </div>
    </div>

    <div class="card">
        <div class="info-box">
            <i class="ri-lightbulb-flash-line"></i>
            <div>
                <strong>Cách thức đặt lịch:</strong>
                Nhập thông tin cá nhân -> Chọn dịch vụ -> Chọn ngày -> Chọn thời gian -> Chọn bác sĩ còn rảnh -> Xác nhận.
            </div>
        </div>

        <form id="appointmentForm" method="POST" action="{{ route('patient.appointment.store') }}" onsubmit="return validateForm()">
            @csrf

            <div class="form-section">
                <div class="section-title">
                    <i class="ri-user-heart-line"></i> 1. Thông Tin Cá Nhân
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="patient_name" class="required">Họ và tên</label>
                        <input type="text"
                               id="patient_name"
                               name="patient_name"
                               value="{{ old('patient_name', Auth::user()->name ?? '') }}"
                               placeholder="Nhập họ và tên"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="patient_phone" class="required">Số điện thoại</label>
                        <input type="tel"
                               id="patient_phone"
                               name="patient_phone"
                               value="{{ old('patient_phone') }}"
                               placeholder="Ví dụ: 0987654321"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="patient_email">Email</label>
                        <input type="email"
                               id="patient_email"
                               name="patient_email"
                               value="{{ old('patient_email', Auth::user()->email ?? '') }}"
                               placeholder="email@example.com">
                    </div>

                    <div class="form-group">
                        <label for="patient_dob">Ngày sinh</label>
                        <input type="date"
                               id="patient_dob"
                               name="patient_dob"
                               value="{{ old('patient_dob') }}"
                               max="{{ date('Y-m-d') }}">
                    </div>

                    <div class="form-group">
                        <label for="patient_gender">Giới tính</label>
                        <select id="patient_gender" name="patient_gender">
                            <option value="">-- Chọn giới tính --</option>
                            <option value="Nam" @selected(old('patient_gender') === 'Nam')>Nam</option>
                            <option value="Nữ" @selected(old('patient_gender') === 'Nữ')>Nữ</option>
                            <option value="Khác" @selected(old('patient_gender') === 'Khác')>Khác</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="emergency_phone">Số điện thoại người thân</label>
                        <input type="tel"
                               id="emergency_phone"
                               name="emergency_phone"
                               value="{{ old('emergency_phone') }}"
                               placeholder="Liên hệ khi cần thiết">
                    </div>

                    <div class="form-group full-width">
                        <label for="patient_address">Địa chỉ</label>
                        <input type="text"
                               id="patient_address"
                               name="patient_address"
                               value="{{ old('patient_address') }}"
                               placeholder="Nhập địa chỉ liên hệ">
                    </div>
                </div>

                <div class="help-text">Thông tin này giúp phòng khám liên hệ và tiếp đón bệnh nhân chính xác hơn.</div>
            </div>

            <div class="divider"></div>

            <div class="form-section">
                <div class="section-title">
                    <i class="ri-folders-line"></i> 2. Loại Dịch Vụ
                </div>
                <div class="form-group">
                    <label for="service_category" class="required">Chọn loại dịch vụ</label>
                    <select id="service_category" name="service_category" required onchange="onCategoryChange()">
                        <option value="">-- Vui lòng chọn loại dịch vụ --</option>
                    </select>
                    <div class="loading-state" id="categoryLoading">
                        <span class="spinner"></span>
                        <span>Đang tải loại dịch vụ...</span>
                    </div>
                    @error('service_category')
                        <div class="error-text"><i class="ri-error-warning-fill"></i>{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="divider"></div>

            <div class="form-section">
                <div class="section-title">
                    <i class="ri-hospital-line"></i> 3. Dịch Vụ Cụ Thể
                </div>
                <div class="form-group">
                    <label for="service_id" class="required">Dịch vụ khám</label>
                    <select id="service_id" name="service_id" required disabled onchange="onServiceChange()">
                        <option value="">-- Chọn dịch vụ --</option>
                    </select>
                    <div class="loading-state" id="serviceLoading">
                        <span class="spinner"></span>
                        <span>Đang tải dịch vụ...</span>
                    </div>
                    <div class="help-text">
                        <i class="ri-information-line" style="font-size: 12px;"></i>
                        Dịch vụ cần được gán chuyên khoa để hệ thống tìm đúng bác sĩ.
                    </div>
                    @error('service_id')
                        <div class="error-text"><i class="ri-error-warning-fill"></i>{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="divider"></div>

            <div class="form-section">
                <div class="section-title">
                    <i class="ri-calendar-line"></i> 4. Chọn Ngày Khám
                </div>
                <div class="form-group">
                    <label for="appointment_date_only" class="required">Ngày khám</label>
                    <input type="date"
                           id="appointment_date_only"
                           name="appointment_date_only"
                           required
                           disabled
                           min="{{ date('Y-m-d') }}"
                           onchange="onDateChange()">
                    <div class="help-text">Sau khi chọn ngày, hệ thống sẽ lấy các giờ còn bác sĩ rảnh theo ca trực.</div>
                    @error('appointment_date')
                        <div class="error-text"><i class="ri-error-warning-fill"></i>{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="divider"></div>

            <div class="form-section">
                <div class="section-title">
                    <i class="ri-time-line"></i> 5. Chọn Thời Gian
                </div>
                <div class="form-group">
                    <label for="time_slot" class="required">Thời gian khám</label>
                    <select id="time_slot" name="time_slot" required disabled onchange="onTimeSlotChange()">
                        <option value="">-- Chọn thời gian --</option>
                    </select>
                    <div class="loading-state" id="timeLoading">
                        <span class="spinner"></span>
                        <span>Đang tìm khung giờ còn bác sĩ rảnh...</span>
                    </div>
                    <div class="help-text">Chỉ hiển thị khung giờ có bác sĩ đúng chuyên khoa còn trống.</div>
                </div>
            </div>

            <div class="divider"></div>

            <div class="form-section">
                <div class="section-title">
                    <i class="ri-user-doctor-line"></i> 6. Chọn Bác Sĩ
                </div>
                <div class="form-group">
                    <label for="doctor_id" class="required">Bác sĩ</label>
                    <select id="doctor_id" name="doctor_id" required disabled onchange="onDoctorChange()">
                        <option value="">-- Chọn bác sĩ --</option>
                    </select>
                    <div class="loading-state" id="doctorLoading">
                        <span class="spinner"></span>
                        <span>Đang lọc bác sĩ theo giờ đã chọn...</span>
                    </div>
                    @error('doctor_id')
                        <div class="error-text"><i class="ri-error-warning-fill"></i>{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="divider"></div>

            <div class="form-section">
                <div class="section-title">
                    <i class="ri-edit-2-line"></i> 7. Ghi Chú Thêm
                </div>
                <div class="form-group">
                    <label for="notes">Mô tả triệu chứng</label>
                    <textarea id="notes"
                              name="notes"
                              placeholder="Vui lòng mô tả triệu chứng hoặc thông tin quan trọng bạn muốn chia sẻ với bác sĩ...">{{ old('notes') }}</textarea>
                    <div class="help-text">Tối đa 500 ký tự nếu dùng validation hiện tại của controller.</div>
                </div>
            </div>

            <input type="hidden" id="appointment_date" name="appointment_date" />

            <div class="form-actions">
                <a href="{{ route('patient.dashboard') }}" class="btn btn-secondary">
                    <i class="ri-arrow-left-line"></i> Quay Lại
                </a>
                <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                    <i class="ri-check-circle-line"></i> Đặt Lịch Khám
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let selectedServiceDuration = 30;

    document.addEventListener('DOMContentLoaded', function() {
        loadCategories();
        setStepState(1);
    });

    function loadCategories() {
        const categorySelect = document.getElementById('service_category');
        const categoryLoading = document.getElementById('categoryLoading');

        categoryLoading.classList.add('show');

        fetch('/patient/api/service-categories')
            .then(handleJsonResponse)
            .then(categories => {
                categoryLoading.classList.remove('show');

                if (!Array.isArray(categories) || categories.length === 0) {
                    categorySelect.innerHTML = '<option value="" disabled>Không có loại dịch vụ nào</option>';
                    return;
                }

                let html = '<option value="">-- Vui lòng chọn loại dịch vụ --</option>';

                categories.forEach(category => {
                    html += `<option value="${escapeHtml(category)}">${escapeHtml(category)}</option>`;
                });

                categorySelect.innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                categoryLoading.classList.remove('show');
                categorySelect.innerHTML = '<option value="" disabled>Lỗi khi tải loại dịch vụ</option>';
            });
    }

    function onCategoryChange() {
        const category = document.getElementById('service_category').value;
        const serviceSelect = document.getElementById('service_id');
        const dateInput = document.getElementById('appointment_date_only');

        resetAfterCategory();

        if (!category) {
            serviceSelect.disabled = true;
            dateInput.disabled = true;
            setStepState(1);
            return;
        }

        loadServices(category);
        setStepState(2);
    }

    function loadServices(category) {
        const serviceSelect = document.getElementById('service_id');
        const serviceLoading = document.getElementById('serviceLoading');

        serviceSelect.disabled = true;
        serviceSelect.innerHTML = '<option value="">-- Đang tải dịch vụ... --</option>';
        serviceLoading.classList.add('show');

        fetch(`/patient/api/services-by-category?category=${encodeURIComponent(category)}`)
            .then(handleJsonResponse)
            .then(services => {
                serviceLoading.classList.remove('show');
                serviceSelect.disabled = false;

                if (!Array.isArray(services) || services.length === 0) {
                    serviceSelect.innerHTML = '<option value="" disabled>Không có dịch vụ nào trong loại này</option>';
                    return;
                }

                let html = '<option value="">-- Chọn dịch vụ --</option>';

                services.forEach(service => {
                    const slots = Number(service.slots_required || 1);
                    const duration = Number(service.actual_duration || service.duration_minutes || (slots * 30));

                    html += `<option value="${service.id}" data-duration="${duration}">
                        ${escapeHtml(service.name)} (${duration} phút)
                    </option>`;
                });

                serviceSelect.innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                serviceLoading.classList.remove('show');
                serviceSelect.disabled = false;
                serviceSelect.innerHTML = '<option value="" disabled>Lỗi khi tải dịch vụ</option>';
            });
    }

    function onServiceChange() {
        const serviceSelect = document.getElementById('service_id');
        const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
        const dateInput = document.getElementById('appointment_date_only');

        resetAfterService();

        if (!serviceSelect.value) {
            dateInput.disabled = true;
            setStepState(2);
            return;
        }

        selectedServiceDuration = Number(selectedOption.dataset.duration || 30);

        dateInput.disabled = false;
        setStepState(3);
    }

    function onDateChange() {
        const serviceId = document.getElementById('service_id').value;
        const date = document.getElementById('appointment_date_only').value;

        resetTimeAndDoctor();

        if (!serviceId || !date) {
            return;
        }

        loadTimeSlotsForDate(serviceId, date);
    }

    function loadTimeSlotsForDate(serviceId, date) {
        const timeSlotSelect = document.getElementById('time_slot');
        const timeLoading = document.getElementById('timeLoading');

        timeSlotSelect.disabled = true;
        timeSlotSelect.innerHTML = '<option value="">-- Đang tải khung giờ... --</option>';
        timeLoading.classList.add('show');

        fetch(`/patient/api/available-times?service_id=${encodeURIComponent(serviceId)}&date=${encodeURIComponent(date)}`)
            .then(handleJsonResponse)
            .then(times => {
                timeLoading.classList.remove('show');

                if (!Array.isArray(times) || times.length === 0) {
                    timeSlotSelect.disabled = false;
                    timeSlotSelect.innerHTML = '<option value="" disabled>Không có khung giờ còn bác sĩ rảnh</option>';
                    return;
                }

                let html = '<option value="">-- Chọn thời gian --</option>';

                times.forEach(slot => {
                    const startTime = normalizeTime(slot.start_time);
                    const endTime = normalizeTime(slot.end_time);
                    const doctorCount = Number(slot.doctor_count || 0);

                    html += `<option value="${startTime}">
                        ${startTime} - ${endTime} (${doctorCount} bác sĩ rảnh)
                    </option>`;
                });

                timeSlotSelect.innerHTML = html;
                timeSlotSelect.disabled = false;
            })
            .catch(error => {
                console.error('Error:', error);
                timeLoading.classList.remove('show');
                timeSlotSelect.disabled = false;
                timeSlotSelect.innerHTML = '<option value="" disabled>Lỗi khi tải khung giờ</option>';
            });
    }

    function onTimeSlotChange() {
        const time = document.getElementById('time_slot').value;
        const date = document.getElementById('appointment_date_only').value;

        resetDoctorOnly();

        if (!time || !date) {
            document.getElementById('appointment_date').value = '';
            setStepState(3);
            return;
        }

        document.getElementById('appointment_date').value = `${date} ${normalizeTime(time)}`;
        loadDoctorsForSelectedTime(normalizeTime(time));
        setStepState(4);
    }

    function loadDoctorsForSelectedTime(time) {
        const doctorSelect = document.getElementById('doctor_id');
        const doctorLoading = document.getElementById('doctorLoading');
        const serviceId = document.getElementById('service_id').value;
        const date = document.getElementById('appointment_date_only').value;

        doctorSelect.disabled = true;
        doctorSelect.innerHTML = '<option value="">-- Đang lọc bác sĩ... --</option>';
        doctorLoading.classList.add('show');

        fetch(`/patient/api/doctors-by-time?service_id=${encodeURIComponent(serviceId)}&date=${encodeURIComponent(date)}&start_time=${encodeURIComponent(time)}`)
            .then(handleJsonResponse)
            .then(doctors => {
                doctorLoading.classList.remove('show');
                doctorSelect.disabled = false;

                if (!Array.isArray(doctors) || doctors.length === 0) {
                    doctorSelect.innerHTML = '<option value="" disabled>Không có bác sĩ rảnh vào giờ này</option>';
                    return;
                }

                let html = '<option value="">-- Chọn bác sĩ --</option>';

                doctors.forEach(doctor => {
                    html += `<option value="${doctor.id}">
                        ${escapeHtml(doctor.name)} - ${escapeHtml(doctor.specialization || 'N/A')}
                    </option>`;
                });

                doctorSelect.innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                doctorLoading.classList.remove('show');
                doctorSelect.disabled = false;
                doctorSelect.innerHTML = '<option value="" disabled>Lỗi khi tải bác sĩ</option>';
            });
    }

    function onDoctorChange() {
        const doctorId = document.getElementById('doctor_id').value;

        document.getElementById('submitBtn').disabled = !doctorId;

        if (doctorId) {
            document.getElementById('step4').classList.add('completed');
        } else {
            document.getElementById('step4').classList.remove('completed');
        }
    }

    function validateForm() {
        const patientName = document.getElementById('patient_name').value.trim();
        const patientPhone = document.getElementById('patient_phone').value.trim();
        const category = document.getElementById('service_category').value;
        const serviceId = document.getElementById('service_id').value;
        const date = document.getElementById('appointment_date_only').value;
        const time = document.getElementById('time_slot').value;
        const doctorId = document.getElementById('doctor_id').value;
        const appointmentDate = document.getElementById('appointment_date').value;

        if (!patientName) {
            alert('Vui lòng nhập họ và tên');
            return false;
        }

        if (!patientPhone) {
            alert('Vui lòng nhập số điện thoại');
            return false;
        }

        if (!/^[0-9+\-\s]{8,15}$/.test(patientPhone)) {
            alert('Số điện thoại không hợp lệ');
            return false;
        }

        if (!category) {
            alert('Vui lòng chọn loại dịch vụ');
            return false;
        }

        if (!serviceId) {
            alert('Vui lòng chọn dịch vụ');
            return false;
        }

        if (!date) {
            alert('Vui lòng chọn ngày khám');
            return false;
        }

        if (!time || !appointmentDate) {
            alert('Vui lòng chọn thời gian khám');
            return false;
        }

        if (!doctorId) {
            alert('Vui lòng chọn bác sĩ');
            return false;
        }

        return true;
    }

    function resetAfterCategory() {
        document.getElementById('service_id').disabled = true;
        document.getElementById('service_id').innerHTML = '<option value="">-- Chọn dịch vụ --</option>';
        document.getElementById('appointment_date_only').disabled = true;
        document.getElementById('appointment_date_only').value = '';
        selectedServiceDuration = 30;
        resetTimeAndDoctor();
    }

    function resetAfterService() {
        document.getElementById('appointment_date_only').value = '';
        resetTimeAndDoctor();
    }

    function resetTimeAndDoctor() {
        document.getElementById('time_slot').disabled = true;
        document.getElementById('time_slot').innerHTML = '<option value="">-- Chọn thời gian --</option>';
        document.getElementById('appointment_date').value = '';
        resetDoctorOnly();
        setStepState(3);
    }

    function resetDoctorOnly() {
        document.getElementById('doctor_id').disabled = true;
        document.getElementById('doctor_id').innerHTML = '<option value="">-- Chọn bác sĩ --</option>';
        document.getElementById('submitBtn').disabled = true;
        document.getElementById('step4').classList.remove('active', 'completed');
    }

    function setStepState(activeStep) {
        for (let i = 1; i <= 4; i++) {
            const step = document.getElementById(`step${i}`);

            step.classList.remove('active', 'completed');

            if (i < activeStep) {
                step.classList.add('completed');
            }

            if (i === activeStep) {
                step.classList.add('active');
            }
        }
    }

    function normalizeTime(time) {
        return String(time || '').slice(0, 5);
    }

    function handleJsonResponse(response) {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        return response.json();
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }
</script>
@endsection