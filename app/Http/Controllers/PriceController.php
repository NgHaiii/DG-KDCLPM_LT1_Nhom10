<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Price;
use Illuminate\Http\Request;

class PriceController extends Controller
{
    /**
     * Danh sách giá dịch vụ
     */
    public function index()
    {
        $prices = Price::with('service')->get();
        $services = Service::all();
        return view('admin.prices.index', compact('prices', 'services'));
    }

    /**
     * Thêm giá mới
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'price' => 'required|numeric|min:1',
            'applied_date' => 'nullable|date',
        ]);

        if (!$validated['price'] || $validated['price'] <= 0) {
            return back()->withErrors([
                'price' => 'Giá dịch vụ phải là một con số hợp lệ và lớn hơn 0'
            ]);
        }

        try {
            Price::create($validated);
            return back()->with('success', '✅ Thêm giá dịch vụ thành công');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Cập nhật giá
     */
    public function update(Request $request, Price $price)
    {
        $validated = $request->validate([
            'service_id' => 'nullable|exists:services,id',
            'price' => 'required|numeric|min:1',
            'applied_date' => 'nullable|date',
        ]);

        if (!$validated['price'] || $validated['price'] <= 0) {
            return back()->withErrors([
                'price' => 'Giá dịch vụ phải là một con số hợp lệ và lớn hơn 0'
            ]);
        }

        try {
            $price->update($validated);
            return back()->with('success', '✅ Cập nhật giá thành công');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Xóa giá
     */
    public function destroy(Price $price)
    {
        try {
            $price->delete();
            return back()->with('success', '✅ Xóa giá thành công');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}