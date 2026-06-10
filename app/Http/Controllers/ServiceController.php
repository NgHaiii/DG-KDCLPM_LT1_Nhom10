<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::with('room')
            ->orderBy('name', 'asc')
            ->get();

        $rooms = Room::where('is_active', true)
            ->where('base_status', 'available')
            ->orderBy('type', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.services.index', compact('services', 'rooms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:services,name',
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:Khám,Điều trị,Thẩm mỹ,Phẫu thuật',
            'room_id' => 'nullable|exists:rooms,id',
            'is_active' => 'nullable|boolean',
            'slots_required' => 'required|integer|in:1,2,3,4,5,6',
            'duration_minutes' => 'required|integer|min:30|max:300',
        ], [
            'name.required' => 'Vui lòng nhập tên dịch vụ.',
            'name.unique' => 'Tên dịch vụ này đã tồn tại.',
            'type.required' => 'Vui lòng chọn loại dịch vụ.',
            'type.in' => 'Loại dịch vụ không hợp lệ.',
            'room_id.exists' => 'Phòng khám được chọn không tồn tại.',
            'slots_required.required' => 'Vui lòng chọn số slot.',
            'duration_minutes.required' => 'Vui lòng nhập thời lượng mỗi slot.',
        ]);

        if (!empty($validated['room_id'])) {
            $roomCheck = $this->validateRoomForServiceType($validated['room_id'], $validated['type']);

            if ($roomCheck !== true) {
                return back()
                    ->withErrors(['room_id' => $roomCheck])
                    ->withInput();
            }
        }

        $slots = (int) $validated['slots_required'];
        $duration = (int) $validated['duration_minutes'];

        $validated['actual_duration'] = $slots * $duration;
        $validated['is_active'] = $request->has('is_active');

        if (empty($validated['room_id'])) {
            $validated['room_id'] = null;
        }

        try {
            $service = Service::create($validated);

            Log::info('Service created successfully', [
                'id' => $service->id,
                'name' => $service->name,
                'type' => $service->type,
                'room_id' => $service->room_id,
                'slots_required' => $service->slots_required,
                'duration_minutes' => $service->duration_minutes,
                'actual_duration' => $service->actual_duration,
            ]);

            return back()->with(
                'success',
                'Thêm dịch vụ thành công. Thời gian thực tế: ' . $service->actual_duration . ' phút.'
            );
        } catch (\Exception $e) {
            Log::error('Service creation failed', [
                'message' => $e->getMessage(),
                'input' => $validated,
            ]);

            return back()
                ->withErrors(['error' => 'Lỗi khi thêm dịch vụ: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:services,name,' . $service->id,
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:Khám,Điều trị,Thẩm mỹ,Phẫu thuật',
            'room_id' => 'nullable|exists:rooms,id',
            'is_active' => 'nullable|boolean',
            'slots_required' => 'required|integer|in:1,2,3,4,5,6',
            'duration_minutes' => 'required|integer|min:30|max:300',
        ], [
            'name.required' => 'Vui lòng nhập tên dịch vụ.',
            'name.unique' => 'Tên dịch vụ này đã tồn tại.',
            'type.required' => 'Vui lòng chọn loại dịch vụ.',
            'type.in' => 'Loại dịch vụ không hợp lệ.',
            'room_id.exists' => 'Phòng khám được chọn không tồn tại.',
            'slots_required.required' => 'Vui lòng chọn số slot.',
            'duration_minutes.required' => 'Vui lòng nhập thời lượng mỗi slot.',
        ]);

        if (!empty($validated['room_id'])) {
            $roomCheck = $this->validateRoomForServiceType($validated['room_id'], $validated['type']);

            if ($roomCheck !== true) {
                return back()
                    ->withErrors(['room_id' => $roomCheck])
                    ->withInput();
            }
        }

        $slots = (int) $validated['slots_required'];
        $duration = (int) $validated['duration_minutes'];

        $validated['actual_duration'] = $slots * $duration;
        $validated['is_active'] = $request->has('is_active');

        if (empty($validated['room_id'])) {
            $validated['room_id'] = null;
        }

        try {
            $service->update($validated);

            Log::info('Service updated successfully', [
                'id' => $service->id,
                'name' => $service->name,
                'type' => $service->type,
                'room_id' => $service->room_id,
                'slots_required' => $service->slots_required,
                'duration_minutes' => $service->duration_minutes,
                'actual_duration' => $service->actual_duration,
            ]);

            return back()->with(
                'success',
                'Cập nhật dịch vụ thành công. Thời gian thực tế: ' . $service->actual_duration . ' phút.'
            );
        } catch (\Exception $e) {
            Log::error('Service update failed', [
                'message' => $e->getMessage(),
                'id' => $service->id,
            ]);

            return back()
                ->withErrors(['error' => 'Lỗi khi cập nhật dịch vụ: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy(Service $service)
    {
        if ($service->prices()->exists()) {
            return back()->withErrors([
                'error' => 'Không thể xóa dịch vụ này vì đã phát sinh bảng giá hoặc giao dịch liên quan.'
            ]);
        }

        try {
            $service->delete();

            Log::info('Service deleted successfully', [
                'id' => $service->id,
                'name' => $service->name,
            ]);

            return back()->with('success', 'Xóa dịch vụ thành công.');
        } catch (\Exception $e) {
            Log::error('Service deletion failed', [
                'message' => $e->getMessage(),
                'id' => $service->id,
            ]);

            return back()->withErrors([
                'error' => 'Lỗi khi xóa dịch vụ: ' . $e->getMessage()
            ]);
        }
    }

    private function validateRoomForServiceType($roomId, $serviceType)
    {
        $room = Room::find($roomId);

        if (!$room) {
            return 'Phòng khám được chọn không tồn tại.';
        }

        if (!$room->is_active) {
            return 'Phòng khám này đang ngừng hoạt động.';
        }

        if ($room->base_status !== 'available') {
            return 'Phòng khám này đang bảo trì, không thể gán cho dịch vụ.';
        }

        if ($room->type !== $serviceType) {
            return 'Phòng khám phải cùng loại với dịch vụ. Ví dụ: dịch vụ Khám chỉ được gán vào phòng loại Khám.';
        }

        return true;
    }
}