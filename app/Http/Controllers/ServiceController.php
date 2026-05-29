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
        // Trim whitespace từ input
        $data = [
            'name' => trim($request->input('name', '')),
            'description' => trim($request->input('description', '')),
            'type' => trim($request->input('type', '')),
        ];

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:services,name',
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:Khám,Điều trị,Thẩm mỹ,Phẫu thuật,Chỉnh nha',
            'is_active' => 'nullable|boolean',
        ]);

        // Set is_active từ checkbox
        $validated['is_active'] = (bool) $request->input('is_active', false);

        try {
            $service = Service::create($validated);
            
            \Log::info('Service created successfully', [
                'id' => $service->id,
                'name' => $service->name,
                'type' => $service->type,
            ]);
            
            return back()->with('success', '✅ Thêm dịch vụ thành công');
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
            'type' => 'required|in:Khám,Điều trị,Thẩm mỹ,Phẫu thuật,Chỉnh nha',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = (bool) $request->input('is_active', false);

        try {
            $service->update($validated);
            
            \Log::info('Service updated successfully', [
                'id' => $service->id,
                'name' => $service->name,
            ]);
            
            return back()->with('success', '✅ Cập nhật dịch vụ thành công');
        } catch (\Exception $e) {
            \Log::error('Service update failed', [
                'message' => $e->getMessage(),
                'id' => $service->id,
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