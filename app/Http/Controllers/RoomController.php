<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RoomController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $type = $request->query('type');
        $status = $request->query('status');

        $rooms = Room::query()
            ->when($type, function ($query) use ($type) {
                $query->where('type', $type);
            })
            ->when($status, function ($query) use ($status) {
                if ($status === 'inactive') {
                    $query->where('is_active', false);
                } elseif ($status === 'maintenance') {
                    $query->where('is_active', true)
                        ->where('base_status', 'maintenance');
                } elseif ($status === 'available') {
                    $query->where('is_active', true)
                        ->where('base_status', 'available');
                }
            })
            ->withCount(['services', 'appointments'])
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $roomTypes = Room::TYPES;
        $baseStatuses = Room::BASE_STATUSES;

        return view('admin.rooms.index', compact(
            'rooms',
            'roomTypes',
            'baseStatuses',
            'type',
            'status'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:rooms,code',
            'name' => 'required|string|max:255',
            'type' => 'required|in:Khám,Điều trị,Thẩm mỹ,Phẫu thuật',
            'floor' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:1|max:10',
            'base_status' => 'required|in:available,maintenance',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ], [
            'code.required' => 'Vui lòng nhập mã phòng',
            'code.unique' => 'Mã phòng đã tồn tại',
            'name.required' => 'Vui lòng nhập tên phòng',
            'type.required' => 'Vui lòng chọn loại dịch vụ của phòng',
            'type.in' => 'Loại dịch vụ không hợp lệ',
            'capacity.required' => 'Vui lòng nhập sức chứa',
            'capacity.min' => 'Sức chứa tối thiểu là 1',
            'capacity.max' => 'Sức chứa tối đa là 10',
            'base_status.required' => 'Vui lòng chọn trạng thái phòng',
        ]);

        $validated['is_active'] = $request->has('is_active');

        try {
            Room::create($validated);

            return back()->with('success', 'Thêm phòng khám thành công.');
        } catch (\Exception $e) {
            Log::error('Room creation failed: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Lỗi khi thêm phòng khám: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:rooms,code,' . $room->id,
            'name' => 'required|string|max:255',
            'type' => 'required|in:Khám,Điều trị,Thẩm mỹ,Phẫu thuật',
            'floor' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:1|max:10',
            'base_status' => 'required|in:available,maintenance',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ], [
            'code.required' => 'Vui lòng nhập mã phòng',
            'code.unique' => 'Mã phòng đã tồn tại',
            'name.required' => 'Vui lòng nhập tên phòng',
            'type.required' => 'Vui lòng chọn loại dịch vụ của phòng',
            'type.in' => 'Loại dịch vụ không hợp lệ',
            'capacity.required' => 'Vui lòng nhập sức chứa',
            'capacity.min' => 'Sức chứa tối thiểu là 1',
            'capacity.max' => 'Sức chứa tối đa là 10',
            'base_status.required' => 'Vui lòng chọn trạng thái phòng',
        ]);

        $validated['is_active'] = $request->has('is_active');

        try {
            $room->update($validated);

            return back()->with('success', 'Cập nhật phòng khám thành công.');
        } catch (\Exception $e) {
            Log::error('Room update failed: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Lỗi khi cập nhật phòng khám: ' . $e->getMessage());
        }
    }

    public function destroy(Room $room)
    {
        try {
            if ($room->services()->exists()) {
                return back()->with('error', 'Không thể xóa phòng vì phòng này đã được gán cho dịch vụ.');
            }

            if ($room->appointments()->exists()) {
                return back()->with('error', 'Không thể xóa phòng vì phòng này đã có lịch hẹn.');
            }

            $room->delete();

            return back()->with('success', 'Xóa phòng khám thành công.');
        } catch (\Exception $e) {
            Log::error('Room deletion failed: ' . $e->getMessage());

            return back()->with('error', 'Lỗi khi xóa phòng khám: ' . $e->getMessage());
        }
    }
}