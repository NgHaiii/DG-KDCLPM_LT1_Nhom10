<!DOCTYPE html>
<html>
<head>
    <title>Quản lý nhân viên</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; }
        th { background: #eee; }
        form { display: inline; }
    </style>
</head>
<body>
    <h1>Danh sách nhân viên</h1>

    {{-- Form thêm/sửa nhân viên --}}
    <h2>{{ isset($editEmployee) ? 'Sửa nhân viên' : 'Thêm nhân viên' }}</h2>
    <form method="POST" action="{{ isset($editEmployee) ? route('employees.update', $editEmployee) : route('employees.store') }}">
        @csrf
        @if(isset($editEmployee))
            @method('PUT')
        @endif
        <label>Tên: <input name="name" value="{{ old('name', $editEmployee->name ?? '') }}" required></label><br>
        <label>Email: <input name="email" type="email" value="{{ old('email', $editEmployee->email ?? '') }}" required></label><br>
        <label>Điện thoại: <input name="phone" value="{{ old('phone', $editEmployee->phone ?? '') }}"></label><br>
        <label>Địa chỉ: <input name="address" value="{{ old('address', $editEmployee->address ?? '') }}"></label><br>
        <label>Chức vụ: <input name="position" value="{{ old('position', $editEmployee->position ?? '') }}" required></label><br>
        <label>Bác sĩ? <input name="is_doctor" type="checkbox" value="1" {{ old('is_doctor', $editEmployee->is_doctor ?? false) ? 'checked' : '' }}></label><br>
        <label>Chuyên môn: <input name="specialization" value="{{ old('specialization', $editEmployee->specialization ?? '') }}"></label><br>
        <button type="submit">{{ isset($editEmployee) ? 'Cập nhật' : 'Lưu' }}</button>
        @if(isset($editEmployee))
            <a href="{{ route('employees.index') }}">Hủy</a>
        @endif
    </form>

    {{-- Danh sách nhân viên --}}
    <h2>Danh sách</h2>
    <table>
        <tr>
            <th>ID</th><th>Tên</th><th>Email</th><th>Chức vụ</th><th>Bác sĩ?</th><th>Chuyên môn</th><th>Hành động</th>
        </tr>
        @foreach($employees as $employee)
        <tr>
            <td>{{ $employee->id }}</td>
            <td>
                <a href="#" onclick="alert('Thông tin:\nTên: {{ $employee->name }}\nEmail: {{ $employee->email }}\nĐiện thoại: {{ $employee->phone }}\nĐịa chỉ: {{ $employee->address }}\nChức vụ: {{ $employee->position }}\nBác sĩ?: {{ $employee->is_doctor ? 'Có' : 'Không' }}\nChuyên môn: {{ $employee->specialization }}')">
                    {{ $employee->name }}
                </a>
            </td>
            <td>{{ $employee->email }}</td>
            <td>{{ $employee->position }}</td>
            <td>{{ $employee->is_doctor ? 'Có' : 'Không' }}</td>
            <td>{{ $employee->specialization }}</td>
            <td>
                <form method="GET" action="{{ route('employees.index') }}" style="display:inline">
                    <input type="hidden" name="edit" value="{{ $employee->id }}">
                    <button type="submit">Sửa</button>
                </form>
                <form action="{{ route('employees.destroy', $employee) }}" method="POST" style="display:inline">
                    @csrf @method('DELETE')
                    <button type="submit" onclick="return confirm('Xóa?')">Xóa</button>
                </form>
            </td>
        </tr>
        @endforeach
    </table>
</body>
</html>