@extends('layouts.patient-layout')

@section('title', 'Đặt Lịch Khám - DentalCare')

@section('page-title', 'Đặt Lịch Khám Bệnh')
@section('page-subtitle', 'Vui lòng chọn dịch vụ, bác sĩ và thời gian phù hợp')

@section('styles')
<style>
    .appointment-container {
        max-width: 700px;
        margin: 0 auto;
    }

    .card {
        border: 1px solid var(--border-color);
        background: white;
        border-radius: var(--radius-lg);
        padding: 32px;
        box-shadow: var(--shadow-md);
    }

    .form-section {
        margin-bottom: 32px;
    }

    .form-section:last-child {
        margin-bottom: 0;
    }

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

    .section-title i {
        color: var(--primary);
        font-size: 20px;
    }

    .form-group {
        margin-bottom: 16px;
    }

    .form-group:last-child {
        margin-bottom: 0;
    }

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
    .form-group select:hover {
        border-color: #cbd5e1;
    }

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
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .loading-state.show {
        display: flex;
    }

    .spinner {
        display: inline-block;
        width: 14px;
        height: 14px;
        border: 2px solid rgba(14, 165, 233, 0.2);
        border-top-color: var(--primary);
        border-radius: 50%;
        animation: spin 0.6s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

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

    .form-actions .btn {
        flex: 1;
    }

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
        max-width: 80px;
    }

    @media (max-width: 768px) {
        .card {
            padding: 24px;
        }

        .form-actions {
            flex-direction: column-reverse;
        }

        .step-indicator {
            margin-bottom: 24px;
        }

        .step-label {
            display: none;
        }
    }
</style>
@endsection

@section('content')
<div class="appointment-container">
    <!-- Step Indicator -->
    <div class="step-indicator">
        <div class="step active" id="step1">
            <div class="step-number">1</div>
            <div class="step-label">Loại dịch vụ</div>
        </div>
        <div class="step" id="step2">
            <div class="step-number">2</div>
            <div class="step-label">Dịch vụ cụ thể</div>
        </div>
        <div class="step" id="step3">
            <div class="step-number">3</div>
            <div class="step-label">Ngày & bác sĩ</div>
        </div>
        <div class="step" id="step4">
            <div class="step-number">4</div>
            <div class="step-label">Xác nhận</div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="card">
        <!-- Info Box -->
        <div class="info-box">
            <i class="ri-lightbulb-flash-line"></i>
            <div>
                <strong>Cách thức đặt lịch:</strong> Loại dịch vụ → Dịch vụ cụ thể → Chọn ngày → Chọn bác sĩ → Chọn thời gian → Xác nhận
            </div>
        </div>

        <form id="appointmentForm" method="POST" action="{{ route('patient.appointment.store') }}" onsubmit="return validateForm()">
            @csrf

            <!-- 1. Service Category Selection -->
            <div class="form-section">
                <div class="section-title">
                    <i class="ri-folders-line"></i> 1. Loại Dịch Vụ
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

            <!-- 2. Specific Service Selection -->
            <div class="form-section">
                <div class="section-title">
                    <i class="ri-hospital-line"></i> 2. Dịch Vụ Cụ Thể
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
                        Mỗi dịch vụ có thời lượng khác nhau
                    </div>
                    @error('service_id')
                        <div class="error-text"><i class="ri-error-warning-fill"></i>{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="divider"></div>

            <!-- 3. Date & Doctor Selection -->
            <div class="form-section">
                <!-- Date -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="ri-calendar-line"></i> 3. Chọn Ngày Khám
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
                        <div class="help-text">Chọn ngày khám trong tương lai</div>
                        @error('appointment_date')
                            <div class="error-text"><i class="ri-error-warning-fill"></i>{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="divider"></div>

                <!-- Doctor -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="ri-user-doctor-line"></i> 4. Chọn Bác Sĩ
                    </div>
                    <div class="form-group">
                        <label for="doctor_id" class="required">Bác sĩ</label>
                        <select id="doctor_id" name="doctor_id" required disabled onchange="onDoctorChange()">
                            <option value="">-- Chọn bác sĩ --</option>
                        </select>
                        <div class="loading-state" id="doctorLoading">
                            <span class="spinner"></span>
                            <span>Đang tìm bác sĩ rảnh...</span>
                        </div>
                        @error('doctor_id')
                            <div class="error-text"><i class="ri-error-warning-fill"></i>{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="divider"></div>

            <!-- 5. Time Slot Selection -->
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
                        <span>Đang tải...</span>
                    </div>
                </div>
            </div>

            <div class="divider"></div>

            <!-- 6. Notes -->
            <div class="form-section">
                <div class="section-title">
                    <i class="ri-edit-2-line"></i> 6. Ghi Chú Thêm (Tùy Chọn)
                </div>
                <div class="form-group">
                    <label for="notes">Mô tả triệu chứng</label>
                    <textarea id="notes" 
                        name="notes" 
                        placeholder="Vui lòng mô tả triệu chứng hoặc bất kỳ thông tin quan trọng nào bạn muốn chia sẻ với bác sĩ..."></textarea>
                    <div class="help-text">Tối đa 500 ký tự</div>
                </div>
            </div>

            <!-- Hidden input -->
            <input type="hidden" id="appointment_date" name="appointment_date" />

            <!-- Form Actions -->
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
    // Load danh sách loại dịch vụ khi page load
    document.addEventListener('DOMContentLoaded', function() {
        loadCategories();
    });

    function loadCategories() {
        const categorySelect = document.getElementById('service_category');
        const categoryLoading = document.getElementById('categoryLoading');

        categoryLoading.classList.add('show');

        fetch('/patient/api/service-categories')
            .then(response => response.json())
            .then(categories => {
                categoryLoading.classList.remove('show');

                if (!categories || categories.length === 0) {
                    categorySelect.innerHTML = '<option value="" disabled>❌ Không có loại dịch vụ nào</option>';
                    return;
                }

                let html = '<option value="">-- Vui lòng chọn loại dịch vụ --</option>';
                categories.forEach(category => {
                    html += `<option value="${category}">${category}</option>`;
                });
                categorySelect.innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                categoryLoading.classList.remove('show');
                categorySelect.innerHTML = '<option value="" disabled>⚠️ Lỗi khi tải loại dịch vụ</option>';
            });
    }

    function onCategoryChange() {
        const category = document.getElementById('service_category').value;
        const serviceSelect = document.getElementById('service_id');
        const dateInput = document.getElementById('appointment_date_only');
        const step2 = document.getElementById('step2');
        const step3 = document.getElementById('step3');

        if (!category) {
            serviceSelect.disabled = true;
            serviceSelect.innerHTML = '<option value="">-- Chọn dịch vụ --</option>';
            dateInput.disabled = true;
            dateInput.value = '';
            resetDoctorAndTime();
            step2.classList.remove('active', 'completed');
            step3.classList.remove('active');
            document.getElementById('step1').classList.remove('completed');
        } else {
            loadServices(category);
            document.getElementById('step1').classList.add('completed');
            step2.classList.add('active');
        }
    }

    function loadServices(category) {
        const serviceSelect = document.getElementById('service_id');
        const serviceLoading = document.getElementById('serviceLoading');

        serviceSelect.disabled = true;
        serviceLoading.classList.add('show');
        serviceSelect.innerHTML = '<option value="">-- Đang tải dịch vụ... --</option>';

        fetch(`/patient/api/services-by-category?category=${encodeURIComponent(category)}`)
            .then(response => response.json())
            .then(services => {
                serviceLoading.classList.remove('show');
                serviceSelect.disabled = false;

                if (!services || services.length === 0) {
                    serviceSelect.innerHTML = '<option value="" disabled>❌ Không có dịch vụ nào trong loại này</option>';
                    return;
                }

                let html = '<option value="">-- Chọn dịch vụ --</option>';
                services.forEach(service => {
                    html += `<option value="${service.id}" 
                        data-slots="${service.slots_required}" 
                        data-duration="${service.actual_duration || (service.slots_required * 30)}">
                        ${service.name} (${service.slots_required} slot, ${service.actual_duration || (service.slots_required * 30)} phút)
                    </option>`;
                });
                serviceSelect.innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                serviceLoading.classList.remove('show');
                serviceSelect.disabled = false;
                serviceSelect.innerHTML = '<option value="" disabled>⚠️ Lỗi khi tải dịch vụ</option>';
            });
    }

    function onServiceChange() {
        const serviceId = document.getElementById('service_id').value;
        const dateInput = document.getElementById('appointment_date_only');
        const step2 = document.getElementById('step2');

        if (!serviceId) {
            dateInput.disabled = true;
            dateInput.value = '';
            resetDoctorAndTime();
            step2.classList.remove('completed');
        } else {
            dateInput.disabled = false;
            step2.classList.add('completed');
        }
    }

    function onDateChange() {
        const serviceId = document.getElementById('service_id').value;
        const date = document.getElementById('appointment_date_only').value;

        if (!serviceId || !date) {
            resetDoctorAndTime();
            return;
        }

        loadDoctors(serviceId, date);
    }

    function loadDoctors(serviceId, date) {
        const doctorSelect = document.getElementById('doctor_id');
        const doctorLoading = document.getElementById('doctorLoading');
        const timeSlotSelect = document.getElementById('time_slot');

        doctorSelect.disabled = true;
        doctorLoading.classList.add('show');
        doctorSelect.innerHTML = '<option value="">-- Đang tải danh sách bác sĩ... --</option>';
        timeSlotSelect.innerHTML = '<option value="">-- Chọn thời gian --</option>';
        timeSlotSelect.disabled = true;

        fetch(`/patient/api/doctors-by-service?service_id=${serviceId}&date=${date}`)
            .then(response => response.json())
            .then(doctors => {
                doctorLoading.classList.remove('show');
                doctorSelect.disabled = false;

                if (!doctors || doctors.length === 0) {
                    doctorSelect.innerHTML = '<option value="" disabled>❌ Không có bác sĩ nào rảnh vào ngày này</option>';
                    return;
                }

                let html = '<option value="">-- Chọn bác sĩ --</option>';
                doctors.forEach(doctor => {
                    html += `<option value="${doctor.id}" data-slots="${doctor.available_slots_count}">
                        ${doctor.name} • ${doctor.specialization} (${doctor.available_slots_count} slot)
                    </option>`;
                });
                doctorSelect.innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                doctorLoading.classList.remove('show');
                doctorSelect.disabled = false;
                doctorSelect.innerHTML = '<option value="" disabled>⚠️ Lỗi khi tải danh sách bác sĩ</option>';
            });
    }

    function onDoctorChange() {
        const doctorId = document.getElementById('doctor_id').value;
        const date = document.getElementById('appointment_date_only').value;

        if (!doctorId || !date) {
            document.getElementById('time_slot').innerHTML = '<option value="">-- Chọn thời gian --</option>';
            document.getElementById('time_slot').disabled = true;
            return;
        }

        loadTimeSlots(doctorId, date);
    }

    function loadTimeSlots(doctorId, date) {
        const timeSlotSelect = document.getElementById('time_slot');
        const timeLoading = document.getElementById('timeLoading');
        const step4 = document.getElementById('step4');

        timeSlotSelect.disabled = true;
        timeLoading.classList.add('show');
        timeSlotSelect.innerHTML = '<option value="">-- Đang tải slot thời gian... --</option>';

        fetch(`/patient/api/available-slots?doctor_id=${doctorId}&date=${date}`)
            .then(response => response.json())
            .then(slots => {
                timeLoading.classList.remove('show');
                timeSlotSelect.disabled = false;
                step4.classList.add('active');

                if (!slots || slots.length === 0) {
                    timeSlotSelect.innerHTML = '<option value="" disabled>❌ Không có slot nào trống</option>';
                    return;
                }

                let html = '<option value="">-- Chọn thời gian --</option>';
                slots.forEach(slot => {
                    html += `<option value="${slot.start_time}">
                        ${slot.start_time} - ${slot.end_time}
                    </option>`;
                });
                timeSlotSelect.innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                timeLoading.classList.remove('show');
                timeSlotSelect.disabled = false;
                timeSlotSelect.innerHTML = '<option value="" disabled>⚠️ Lỗi khi tải slot</option>';
            });
    }

    function onTimeSlotChange() {
        const time = document.getElementById('time_slot').value;
        const date = document.getElementById('appointment_date_only').value;

        if (time && date) {
            document.getElementById('appointment_date').value = `${date} ${time}`;
            document.getElementById('submitBtn').disabled = false;
        } else {
            document.getElementById('submitBtn').disabled = true;
        }
    }

    function validateForm() {
        const category = document.getElementById('service_category').value;
        const serviceId = document.getElementById('service_id').value;
        const doctorId = document.getElementById('doctor_id').value;
        const appointmentDate = document.getElementById('appointment_date').value;

        if (!category) {
            alert('❌ Vui lòng chọn loại dịch vụ');
            return false;
        }

        if (!serviceId) {
            alert('❌ Vui lòng chọn dịch vụ');
            return false;
        }

        if (!doctorId) {
            alert('❌ Vui lòng chọn bác sĩ');
            return false;
        }

        if (!appointmentDate) {
            alert('❌ Vui lòng chọn thời gian khám');
            return false;
        }

        return true;
    }

    function resetDoctorAndTime() {
        document.getElementById('doctor_id').disabled = true;
        document.getElementById('doctor_id').innerHTML = '<option value="">-- Chọn bác sĩ --</option>';
        document.getElementById('time_slot').disabled = true;
        document.getElementById('time_slot').innerHTML = '<option value="">-- Chọn thời gian --</option>';
        document.getElementById('submitBtn').disabled = true;
        document.getElementById('step3').classList.remove('active');
        document.getElementById('step4').classList.remove('active');
    }
</script>
@endsection