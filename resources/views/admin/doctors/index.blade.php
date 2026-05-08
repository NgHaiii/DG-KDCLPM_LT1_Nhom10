<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý bác sĩ - DentalCare</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; }
        .container { display: flex; min-height: 100vh; }
        
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            overflow-y: auto;
        }
        .sidebar h2 { font-size: 20px; margin-bottom: 30px; }
        .nav-menu { list-style: none; }
        .nav-item {
            padding: 12px 15px;
            margin: 8px 0;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .nav-item:hover { background: rgba(255, 255, 255, 0.2); }
        .nav-item.active { background: rgba(255, 255, 255, 0.3); }
        .nav-item a { color: inherit; text-decoration: none; display: block; }

        .main { flex: 1; padding: 30px; overflow-y: auto; }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .header h1 { font-size: 28px; color: #333; }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            display: none;
        }
        .alert.success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
            display: block;
        }

        .form-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }
        .form-card h2 { margin-bottom: 20px; color: #333; font-size: 18px; }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #666;
            font-weight: 600;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 5px rgba(102, 126, 234, 0.3);
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .checkbox-group input {
            width: auto;
        }

        .form-actions {
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4); }
        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }
        .btn-secondary:hover { background: #e0e0e0; }

        .table-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }
        .table-card h2 { margin-bottom: 20px; color: #333; font-size: 18px; }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead { background: #f9fafb; }
        th {
            padding: 12px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: #666;
            border-bottom: 2px solid #e5e7eb;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        tr:hover { background: #f9fafb; }

        .btn-edit, .btn-delete {
            padding: 6px 12px;
            font-size: 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .btn-edit {
            background: #3b82f6;
            color: white;
        }
        .btn-delete {
            background: #ef4444;
            color: white;
        }

        @media (max-width: 768px) {
            .sidebar { width: 200px; }
            .main { padding: 15px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>🦷 DentalCare</h2>
            <ul class="nav-menu">
                <li class="nav-item"><a href="{{ route('admin.dashboard') }}">📊 Dashboard</a></li>
                <li class="nav-item active"><a href="{{ route('admin.doctors') }}">🩺 Quản lý bác sĩ</a></li>
                <li class="nav-item"><a href="{{ route('admin.employees') }}">👨‍💼 Quản lý nhân viên</a></li>
            </ul>
        </div>

        <div class="main">
            <div class="header">
                <div>
                    <h1>Quản lý bác sĩ</h1>
                    <p style="color: #999; margin-top: 5px;">Thêm, sửa, xóa thông tin bác sĩ</p>
                </div>
            </div>

            @if(session('success'))
                <div class="alert success">
                    {!! session('success') !!}
                </div>
            @endif

            <div class="form-card">
                <h2>{{ isset($editEmployee) ? '✏️ Sửa thông tin bác sĩ' : '➕ Thêm bác sĩ mới' }}</h2>
                <form method="POST" action="{{ isset($editEmployee) ? route('admin.employee.update', $editEmployee) : route('admin.employee.store') }}">
                    @csrf
                    @if(isset($editEmployee))
                        @method('PUT')
                    @endif

                    <div class="form-group">
                        <label>Họ tên *</label>
                        <input type="text" name="name" value="{{ old('name', $editEmployee?->name ?? '') }}" required>
                    </div>

                    <div class="form-group">
                        <label>Chức vụ *</label>
                        <input type="text" name="position" value="{{ old('position', $editEmployee?->position ?? '') }}" placeholder="VD: Bác sĩ chuyên khoa" required>
                    </div>

                    <div class="form-group">
                        <label>Chuyên môn</label>
                        <input type="text" name="specialization" value="{{ old('specialization', $editEmployee?->specialization ?? '') }}" placeholder="VD: Cấy ghép implant">
                    </div>

                    <div class="form-group">
                        <label>Điện thoại</label>
                        <input type="tel" name="phone" value="{{ old('phone', $editEmployee?->phone ?? '') }}" placeholder="0123456789">
                    </div>

                    <div class="form-group">
                        <label>Địa chỉ</label>
                        <textarea name="address" rows="3" placeholder="Địa chỉ thường trú">{{ old('address', $editEmployee?->address ?? '') }}</textarea>
                    </div>

                    <input type="hidden" name="is_doctor" value="1">

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            {{ isset($editEmployee) ? '💾 Cập nhật' : '✅ Thêm bác sĩ' }}
                        </button>
                        @if(isset($editEmployee))
                            <a href="{{ route('admin.doctors') }}" class="btn btn-secondary">❌ Hủy</a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="table-card">
                <h2>📋 Danh sách bác sĩ ({{ count($employees ?? []) }} người)</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Mã</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>Chức vụ</th>
                            <th>Chuyên môn</th>
                            <th>Điện thoại</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees ?? [] as $emp)
                        <tr>
                            <td><strong>{{ $emp->code }}</strong></td>
                            <td>{{ $emp->name }}</td>
                            <td>{{ $emp->email }}</td>
                            <td>{{ $emp->position }}</td>
                            <td>{{ $emp->specialization ?? '---' }}</td>
                            <td>{{ $emp->phone ?? '---' }}</td>
                            <td>
                                <form method="GET" action="{{ route('admin.doctors') }}" style="display:inline">
                                    <input type="hidden" name="edit" value="{{ $emp->id }}">
                                    <button type="submit" class="btn-edit">✏️ Sửa</button>
                                </form>
                                <form action="{{ route('admin.employee.destroy', $emp) }}" method="POST" style="display:inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-delete" onclick="return confirm('Bạn chắc chắn muốn xóa?')">🗑️ Xóa</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align:center; color:#999;">Chưa có bác sĩ nào</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>