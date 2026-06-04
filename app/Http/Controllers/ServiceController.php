<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::all();
        return view('admin.services.index', compact('services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:services,name',
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:Khám,Điều trị,Thẩm mỹ,Phẫu thuật',
            'is_active' => 'nullable|boolean',
            'slots_required' => 'required|integer|in:1,2,3,4,5,6',
            'duration_minutes' => 'required|integer|min:30|max:300',
        ]);

        // Convert to integer để đảm bảo tính toán chính xác
        $slots = (int) $validated['slots_required'];
        $duration = (int) $validated['duration_minutes'];
        
        // Tính actual_duration trên server
        $validated['actual_duration'] = $slots * $duration;
        
        // Set is_active từ checkbox
        $validated['is_active'] = $request->has('is_active');

        // DEBUG LOG - Xem giá trị trước create
        \Log::info('DEBUG - Before create', [
            'validated_data' => $validated,
        ]);

        try {
            $service = Service::create($validated);
            
            // DEBUG LOG - Xem giá trị sau create từ database
            \Log::info('DEBUG - After create from DB', [
                'id' => $service->id,
                'actual_duration' => $service->actual_duration,
            ]);
            
            \Log::info('Service created successfully', [
                'id' => $service->id,
                'name' => $service->name,
                'type' => $service->type,
                'slots_required' => $service->slots_required,
                'duration_minutes' => $service->duration_minutes,
                'actual_duration' => $service->actual_duration,
                'calculation' => $slots . ' × ' . $duration . ' = ' . $service->actual_duration,
            ]);
            
            return back()->with('success', '✅ Thêm dịch vụ thành công (Thời gian: ' . $service->actual_duration . 'p)');
        } catch (\Exception $e) {
            \Log::error('Service creation failed', [
                'message' => $e->getMessage(),
                'input' => $validated,
                'trace' => $e->getTraceAsString(),
            ]);
            
            return back()->withErrors(['error' => 'Lỗi: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:services,name,' . $service->id,
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:Khám,Điều trị,Thẩm mỹ,Phẫu thuật',
            'is_active' => 'nullable|boolean',
            'slots_required' => 'required|integer|in:1,2,3,4,5,6',
            'duration_minutes' => 'required|integer|min:30|max:300',
        ]);

        // Convert to integer để đảm bảo tính toán chính xác
        $slots = (int) $validated['slots_required'];
        $duration = (int) $validated['duration_minutes'];
        
        // Tính actual_duration trên server
        $validated['actual_duration'] = $slots * $duration;
        
        // Set is_active từ checkbox
        $validated['is_active'] = $request->has('is_active');

        try {
            $service->update($validated);
            
            \Log::info('Service updated successfully', [
                'id' => $service->id,
                'name' => $service->name,
                'slots_required' => $service->slots_required,
                'duration_minutes' => $service->duration_minutes,
                'actual_duration' => $service->actual_duration,
                'calculation' => $slots . ' × ' . $duration . ' = ' . $service->actual_duration,
            ]);
            
            return back()->with('success', '✅ Cập nhật dịch vụ thành công (Thời gian: ' . $service->actual_duration . 'p)');
        } catch (\Exception $e) {
            \Log::error('Service update failed', [
                'message' => $e->getMessage(),
                'id' => $service->id,
                'trace' => $e->getTraceAsString(),
            ]);
            
            return back()->withErrors(['error' => 'Lỗi: ' . $e->getMessage()]);
        }
    }

    public function destroy(Service $service)
    {
        if ($service->prices()->exists()) {
            return back()->withErrors([
                'error' => 'Không thể xóa dịch vụ này vì đã phát sinh giao dịch.'
            ]);
        }

        try {
            $service->delete();
            
            \Log::info('Service deleted successfully', ['id' => $service->id]);
            
            return back()->with('success', '✅ Xóa dịch vụ thành công');
        } catch (\Exception $e) {
            \Log::error('Service deletion failed', ['message' => $e->getMessage()]);
            
            return back()->withErrors(['error' => 'Lỗi: ' . $e->getMessage()]);
        }
    }
}