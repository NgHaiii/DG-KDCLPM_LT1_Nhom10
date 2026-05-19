@extends('layouts.admin-layout')

@section('title', 'Xét duyệt lịch làm việc')

@section('page-title', 'Xét duyệt lịch làm việc')
@section('page-subtitle', 'Phê duyệt hoặc từ chối các đơn đăng ký ca làm việc và ca trực')

@section('content')

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="card p-6 bg-yellow-50 border-l-4 border-yellow-500">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm text-gray-600">Đơn đăng ký chờ xác nhận</p>
                <p class="text-3xl font-bold text-yellow-700 mt-2">{{ $stats['total_pending_requests'] }}</p>
            </div>
            <span class="text-4xl">📋</span>
        </div>
    </div>

    <div class="card p-6 bg-orange-50 border-l-4 border-orange-500">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm text-gray-600">Đơn xin nghỉ chờ xác nhận</p>
                <p class="text-3xl font-bold text-orange-700 mt-2">{{ $stats['total_pending_offdays'] }}</p>
            </div>
            <span class="text-4xl">🏖️</span>
        </div>
    </div>

    <div class="card p-6 bg-green-50 border-l-4 border-green-500">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm text-gray-600">Lịch được duyệt hôm nay</p>
                <p class="text-3xl font-bold text-green-700 mt-2">{{ $stats['total_approved_today'] }}</p>
            </div>
            <span class="text-4xl">✅</span>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<div class="mb-6">
    <div class="flex gap-2 border-b border-gray-200">
        <button class="tab-btn active px-4 py-3 font-semibold text-blue-600 border-b-2 border-blue-600" data-tab="schedule-requests">
            📋 Đơn đăng ký ca ({{ $pendingRequests->total() }})
        </button>
        <button class="tab-btn px-4 py-3 font-semibold text-gray-600 border-b-2 border-transparent hover:text-blue-600" data-tab="off-days">
            🏖️ Đơn xin nghỉ ({{ $pendingOffDays->total() }})
        </button>
    </div>
</div>

<!-- Tab 1: Đơn đăng ký ca -->
<div id="schedule-requests-tab" class="tab-content">
    <div class="card p-6">
        <h3 class="text-xl font-bold text-gray-900 mb-6">📋 Danh sách đơn đăng ký ca chờ xác nhận</h3>

        @if($pendingRequests->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b-2 border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Nhân viên</th>
                            <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Vị trí</th>
                            <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Ngày</th>
                            <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Ca</th>
                            <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Loại</th>
                            <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Ngày đăng ký</th>
                            <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($pendingRequests as $request)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                        <span class="text-sm font-bold text-blue-700">
                                            {{ strtoupper(substr($request->employee->name, 0, 1)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900">{{ $request->employee->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $request->employee->code }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($request->employee->is_doctor)
                                    <span class="badge badge-primary">👨‍⚕️ Bác sĩ</span>
                                @else
                                    <span class="badge badge-secondary">👨‍💼 Nhân viên</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-bold text-gray-900">
                                {{ $request->work_date->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-900">{{ $request->shift->name }}</p>
                                <p class="text-sm text-gray-600">{{ $request->shift->start_time }} - {{ $request->shift->end_time }}</p>
                            </td>
                            <td class="px-6 py-4">
                                @if($request->duty_type === 'duty')
                                    <span class="badge badge-warning">🩺 Ca trực</span>
                                @else
                                    <span class="badge badge-primary">📋 Ca thường</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $request->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.schedule-approval.show', $request->id) }}" class="btn-primary text-sm py-2">
                                    Xem chi tiết
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $pendingRequests->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <p class="text-2xl text-gray-500">✅ Không có đơn chờ xác nhận</p>
            </div>
        @endif
    </div>
</div>

<!-- Tab 2: Đơn xin nghỉ -->
<div id="off-days-tab" class="tab-content hidden">
    <div class="card p-6">
        <h3 class="text-xl font-bold text-gray-900 mb-6">🏖️ Danh sách đơn xin nghỉ chờ xác nhận</h3>

        @if($pendingOffDays->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b-2 border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Nhân viên</th>
                            <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Vị trí</th>
                            <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Ngày xin nghỉ</th>
                            <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Lý do</th>
                            <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Ngày đăng ký</th>
                            <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($pendingOffDays as $offDay)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center">
                                        <span class="text-sm font-bold text-orange-700">
                                            {{ strtoupper(substr($offDay->employee->name, 0, 1)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900">{{ $offDay->employee->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $offDay->employee->code }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($offDay->employee->is_doctor)
                                    <span class="badge badge-primary">👨‍⚕️ Bác sĩ</span>
                                @else
                                    <span class="badge badge-secondary">👨‍💼 Nhân viên</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-bold text-gray-900">
                                {{ $offDay->date->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $offDay->reason }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $offDay->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex gap-2">
                                    <button class="btn bg-green-600 hover:bg-green-700 text-white py-1 px-3 text-sm approve-offday" data-id="{{ $offDay->id }}">
                                        ✅ Duyệt
                                    </button>
                                    <button class="btn bg-red-600 hover:bg-red-700 text-white py-1 px-3 text-sm reject-offday" data-id="{{ $offDay->id }}">
                                        ❌ Từ chối
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $pendingOffDays->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <p class="text-2xl text-gray-500">✅ Không có đơn xin nghỉ chờ xác nhận</p>
            </div>
        @endif
    </div>
</div>

<!-- Quick Links -->
<div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
    <a href="{{ route('admin.schedule-approval.doctors') }}" class="card p-6 hover:shadow-lg transition">
        <div class="flex items-center gap-4">
            <span class="text-4xl">👨‍⚕️</span>
            <div>
                <h3 class="font-bold text-gray-900">Danh sách bác sĩ</h3>
                <p class="text-sm text-gray-600">Xem lịch của bác sĩ</p>
            </div>
        </div>
    </a>

    <a href="{{ route('admin.schedule-approval.employees') }}" class="card p-6 hover:shadow-lg transition">
        <div class="flex items-center gap-4">
            <span class="text-4xl">👨‍💼</span>
            <div>
                <h3 class="font-bold text-gray-900">Danh sách nhân viên</h3>
                <p class="text-sm text-gray-600">Xem lịch của nhân viên</p>
            </div>
        </div>
    </a>

    <a href="{{ route('admin.dashboard') }}" class="card p-6 hover:shadow-lg transition">
        <div class="flex items-center gap-4">
            <span class="text-4xl">📊</span>
            <div>
                <h3 class="font-bold text-gray-900">Dashboard</h3>
                <p class="text-sm text-gray-600">Quay lại dashboard</p>
            </div>
        </div>
    </a>
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

// Approve off day
document.querySelectorAll('.approve-offday').forEach(btn => {
    btn.addEventListener('click', async function() {
        const id = this.dataset.id;
        const notes = prompt('Ghi chú (tùy chọn):');
        
        try {
            const response = await fetch(`{{ route('admin.schedule-approval.off-day.approve', ':id') }}`.replace(':id', id), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ notes: notes })
            });
            
            const data = await response.json();
            if (data.success) {
                alert('✅ ' + data.message);
                location.reload();
            } else {
                alert('❌ ' + data.message);
            }
        } catch (error) {
            alert('❌ ' + error.message);
        }
    });
});

// Reject off day
document.querySelectorAll('.reject-offday').forEach(btn => {
    btn.addEventListener('click', async function() {
        const id = this.dataset.id;
        const notes = prompt('Lý do từ chối:');
        
        if (!notes) return;
        
        try {
            const response = await fetch(`{{ route('admin.schedule-approval.off-day.reject', ':id') }}`.replace(':id', id), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ notes: notes })
            });
            
            const data = await response.json();
            if (data.success) {
                alert('✅ ' + data.message);
                location.reload();
            } else {
                alert('❌ ' + data.message);
            }
        } catch (error) {
            alert('❌ ' + error.message);
        }
    });
});
</script>
@endsection