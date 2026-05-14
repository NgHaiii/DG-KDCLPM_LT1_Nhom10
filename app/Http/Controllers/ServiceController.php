<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    // Danh sách dịch vụ
    public function index()
    {
        $services = Service::all();
        return view('admin.services.index', compact('services'));
    }

    // Thêm dịch vụ
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:services',
            'description' => 'nullable',
            'type' => 'nullable',
        ]);

        try {
            Service::create($validated);
            return back()->with('success', '✅ Thêm dịch vụ thành công');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // Cập nhật dịch vụ
    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|unique:services,name,' . $service->id,
            'description' => 'nullable',
            'type' => 'nullable',
        ]);

        try {
            $service->update($validated);
            return back()->with('success', '✅ Cập nhật dịch vụ thành công');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // Xóa dịch vụ
    public function destroy(Service $service)
    {
        // Kiểm tra xem dịch vụ có được sử dụng không
        if ($service->prices()->exists()) {
            return back()->withErrors([
                'error' => 'Không thể xóa dịch vụ này vì đã phát sinh giao dịch.'
            ]);
        }

        $service->delete();
        return back()->with('success', '✅ Xóa dịch vụ thành công');
    }
}