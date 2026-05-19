@extends('layouts.app')

@section('title', 'Đăng ký lịch trực & ca làm việc')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-blue-900 mb-2">
            🩺 Đăng ký lịch trực & ca làm việc
        </h1>
        <p class="text-gray-600">
            Bác sĩ {{ $employee->name }} - Đăng ký ca sáng, chiều, tối + ca trực
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left: Form đăng ký -->
        <div class="lg:col-span-2">
            <!-- Tab Navigation -->
            <div class="mb-6">
                <div class="flex gap-2 border-b border-gray-200">
                    <button class="tab-btn active px-4 py-2 font-semibold text-blue-600 border-b-2 border-blue-600" data-tab="schedule">
                        📋 Đăng ký ca làm việc
                    </button>
                    <button class="tab-btn px-4 py-2 font-semibold text-gray-600 border-b-2 border-transparent hover:text-blue-600" data-tab="duty">
                        🩺 Đăng ký ca trực
                    </button>
                    <button class="tab-btn px-4 py-2 font-semibold text-gray-600 border-b-2 border-transparent hover:text-blue-600" data-tab="offday">
                        🏖️ Xin nghỉ
                    </button>
                </div>
            </div>

            <!-- Tab 1: Ca làm việc thường -->
            <div id="schedule-tab" class="tab-content card p-6 mb-6">
                <h3 class="text-lg font-bold mb-4 text-blue-900">📋 Đăng ký ca làm việc thường</h3>
                <form id="scheduleForm">
                    @csrf
                    <input type="hidden" name="duty_type" value="shift">
                    
                    <div class="mb-4">
                        <label for="schedule_date" class="block text-sm font-semibold text-gray-700 mb-2">
                            📆 Ngày làm việc
                        </label>
                        <input 
                            type="date" 
                            id="schedule_date" 
                            name="work_date"
                            class="input-field w-full"
                            required
                            min="{{ date('Y-m-d') }}"
                        >
                    </div>

                    <div class="mb-4">
                        <label for="schedule_shift" class="block text-sm font-semibold text-gray-700 mb-2">
                            ⏰ Ca làm việc (Sáng, Chiều, Tối)
                        </label>
                        <select id="schedule_shift" name="shift_id" class="input-field w-full" required>
                            <option value="">-- Chọn ca --</option>
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}">
                                    {{ $shift->name }} ({{ $shift->start_time }} - {{ $shift->end_time }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-6">
                        <label for="schedule_reason" class="block text-sm font-semibold text-gray-700 mb-2">
                            📝 Ghi chú (tùy chọn)
                        </label>
                        <textarea id="schedule_reason" name="reason" class="input-field w-full" rows="3"></textarea>
                    </div>

                    <button type="submit" class="btn-primary w-full">✅ Gửi đơn đăng ký ca</button>
                </form>
            </div>

            <!-- Tab 2: Ca trực (Riêng bác sĩ) -->
            <div id="duty-tab" class="tab-content hidden card p-6 mb-6 border-l-4 border-purple-500">
                <h3 class="text-lg font-bold mb-4 text-purple-900">🩺 Đăng ký ca trực</h3>
                <p class="text-sm text-gray-600 mb-6">
                    Ca trực là ca khác biệt với ca làm việc thường. Bác sĩ trực sẽ có trách nhiệm cấp cứu 24h.
                </p>
                <form id="dutyForm">
                    @csrf
                    <input type="hidden" name="duty_type" value="duty">
                    
                    <div class="mb-4">
                        <label for="duty_date" class="block text-sm font-semibold text-gray-700 mb-2">
                            📆 Ngày trực
                        </label>
                        <input 
                            type="date" 
                            id="duty_date" 
                            name="work_date"
                            class="input-field w-full"
                            required
                            min="{{ date('Y-m-d') }}"
                        >
                    </div>

                    <div class="mb-4">
                        <label for="duty_shift" class="block text-sm font-semibold text-gray-700 mb-2">
                            ⏰ Khung giờ trực
                        </label>
                        <select id="duty_shift" name="shift_id" class="input-field w-full" required>
                            <option value="">-- Chọn khung giờ --</option>
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}">
                                    {{ $shift->name }} ({{ $shift->start_time }} - {{ $shift->end_time }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-6">
                        <label for="duty_reason" class="block text-sm font-semibold text-gray-700 mb-2">
                            📝 Ghi chú (tùy chọn)
                        </label>
                        <textarea id="duty_reason" name="reason" class="input-field w-full" rows="3"></textarea>
                    </div>

                    <button type="submit" class="btn-primary w-full bg-purple-600 hover:bg-purple-700">✅ Gửi đơn ca trực</button>
                </form>
            </div>

            <!-- Tab 3: Xin nghỉ -->
            <div id="offday-tab" class="tab-content hidden card p-6 mb-6">
                <h3 class="text-lg font-bold mb-4 text-gray-900">🏖️ Đơn xin nghỉ</h3>
                <form id="offdayForm">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="offday_date" class="block text-sm font-semibold text-gray-700 mb-2">
                            📆 Ngày xin nghỉ
                        </label>
                        <input 
                            type="date" 
                            id="offday_date" 
                            name="date"
                            class="input-field w-full"
                            required
                            min="{{ date('Y-m-d') }}"
                        >
                    </div>

                    <div class="mb-6">
                        <label for="offday_reason" class="block text-sm font-semibold text-gray-700 mb-2">
                            📝 Lý do xin nghỉ
                        </label>
                        <textarea id="offday_reason" name="reason" class="input-field w-full" rows="3" required></textarea>
                    </div>

                    <button type="submit" class="btn-primary w-full">✅ Gửi đơn xin nghỉ</button>
                </form>
            </div>
        </div>

        <!-- Right: Sidebar -->
        <div class="lg:col-span-1">
            <!-- Thông tin bác sĩ -->
            <div class="card p-6 mb-6 bg-blue-50 border-l-4 border-blue-600">
                <h3 class="text-lg font-bold text-blue-900 mb-4">👨‍⚕️ Thông tin của bạn</h3>
                <div class="space-y-2 text-sm">
                    <p><strong>Tên:</strong> {{ $employee->name }}</p>
                    <p><strong>Mã:</strong> {{ $employee->code }}</p>
                    <p><strong>Chuyên khoa:</strong> {{ $employee->specialization ?? 'Tổng hợp' }}</p>
                    <p><strong>Trạng thái:</strong> 
                        <span class="badge badge-success">{{ $employee->status ?? 'Hoạt động' }}</span>
                    </p>
                </div>
            </div>

            <!-- Ngày nghỉ đã duyệt -->
            <div class="card p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">🏖️ Ngày nghỉ sắp tới</h3>
                @if($approvedOffDays->count() > 0)
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        @foreach($approvedOffDays as $offDay)
                        <div class="p-2 bg-green-50 border-l-2 border-green-500 rounded">
                            <p class="font-semibold text-green-800 text-sm">
                                {{ $offDay->date->format('d/m/Y') }}
                            </p>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-sm">Không có ngày nghỉ</p>
                @endif
                <a href="{{ route('doctor.schedule.my-off-days') }}" class="text-blue-600 text-sm mt-3 inline-block hover:underline">
                    Xem tất cả →
                </a>
            </div>
        </div>
    </div>

    <!-- Pending Requests -->
    @if($pendingRequests->count() > 0)
    <div class="mt-8 card p-6 bg-yellow-50 border border-yellow-200">
        <h3 class="text-lg font-bold text-yellow-900 mb-4">⏳ Đơn chờ xác nhận ({{ $pendingRequests->count() }})</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-yellow-100">
                    <tr>
                        <th class="px-4 py-2 text-left">Ngày</th>
                        <th class="px-4 py-2 text-left">Ca</th>
                        <th class="px-4 py-2 text-left">Loại</th>
                        <th class="px-4 py-2 text-left">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingRequests as $req)
                    <tr class="border-b border-yellow-200 hover:bg-yellow-100">
                        <td class="px-4 py-2">{{ $req->work_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-2 font-semibold">{{ $req->shift->name }}</td>
                        <td class="px-4 py-2">
                            <span class="badge {{ $req->duty_type === 'duty' ? 'badge-warning' : 'badge-primary' }}">
                                {{ $req->duty_type === 'duty' ? '🩺 Ca trực' : '📋 Ca thường' }}
                            </span>
                        </td>
                        <td class="px-4 py-2">
                            <button class="text-red-600 hover:text-red-800 cancel-request text-sm" data-id="{{ $req->id }}">
                                Hủy
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Approved Requests -->
    @if($approvedRequests->count() > 0)
    <div class="mt-8 card p-6 bg-green-50 border border-green-200">
        <h3 class="text-lg font-bold text-green-900 mb-4">✅ Lịch đã duyệt ({{ $approvedRequests->count() }})</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($approvedRequests as $req)
            <div class="p-4 bg-white border-l-4 {{ $req->duty_type === 'duty' ? 'border-purple-500' : 'border-blue-500' }} rounded">
                <p class="font-bold {{ $req->duty_type === 'duty' ? 'text-purple-700' : 'text-blue-700' }}">
                    {{ $req->work_date->format('d/m') }}
                </p>
                <p class="text-sm font-semibold text-gray-900">{{ $req->shift->name }}</p>
                <p class="text-xs text-gray-600">{{ $req->shift->start_time }} - {{ $req->shift->end_time }}</p>
                <p class="text-xs text-gray-500 mt-1">
                    {{ $req->duty_type === 'duty' ? '🩺 Ca trực' : '📋 Ca thường' }}
                </p>
            </div>
            @endforeach
        </div>
        <a href="{{ route('doctor.schedule.my-approved-schedules') }}" class="text-green-600 text-sm mt-4 inline-block hover:underline">
            Xem tất cả →
        </a>
    </div>
    @endif
</div>

<!-- Modal Thành công -->
<div id="successModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-md">
        <h3 class="text-2xl font-bold text-green-600 mb-4">✅ Thành công!</h3>
        <p id="successMessage" class="text-gray-700 mb-6"></p>
        <button onclick="closeModal()" class="btn-primary w-full">Đóng</button>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Tab switching
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const tabName = this.dataset.tab;
        
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.add('hidden');
        });
        
        document.getElementById(tabName + '-tab').classList.remove('hidden');
        
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('active', 'text-blue-600', 'border-b-blue-600');
            b.classList.add('text-gray-600', 'border-b-transparent');
        });
        this.classList.add('active', 'text-blue-600', 'border-b-blue-600');
        this.classList.remove('text-gray-600', 'border-b-transparent');
    });
});

// Submit schedule form
document.getElementById('scheduleForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    await submitForm('{{ route("doctor.schedule.store") }}', this, 'Đăng ký ca làm việc thành công');
});

// Submit duty form
document.getElementById('dutyForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    await submitForm('{{ route("doctor.schedule.store") }}', this, 'Đơn ca trực đã được gửi');
});

// Submit offday form
document.getElementById('offdayForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    await submitForm('{{ route("doctor.schedule.request-off-day") }}', this, 'Đơn xin nghỉ đã được gửi');
});

// Cancel request
document.querySelectorAll('.cancel-request').forEach(btn => {
    btn.addEventListener('click', async function() {
        if (!confirm('Bạn có chắc muốn hủy?')) return;
        
        const id = this.dataset.id;
        try {
            const response = await fetch(`{{ route('doctor.schedule.cancel', ':id') }}`.replace(':id', id), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            
            const data = await response.json();
            if (data.success) {
                showSuccess(data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                alert('❌ ' + data.message);
            }
        } catch (error) {
            alert('❌ ' + error.message);
        }
    });
});

async function submitForm(url, form, message) {
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: new FormData(form)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess(data.message);
            form.reset();
            setTimeout(() => location.reload(), 2000);
        } else {
            alert('❌ ' + data.message);
        }
    } catch (error) {
        alert('❌ ' + error.message);
    }
}

function showSuccess(message) {
    document.getElementById('successMessage').textContent = message;
    document.getElementById('successModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('successModal').classList.add('hidden');
}
</script>
@endsection