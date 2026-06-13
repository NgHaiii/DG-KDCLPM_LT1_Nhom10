<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MedicineController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Danh sách thuốc + tìm kiếm + lọc tồn kho.
     */
    public function index(Request $request)
    {
        $keyword = trim((string) $request->input('keyword'));
        $status = $request->input('status');
        $stock = $request->input('stock');
        $category = $request->input('category');

        $medicines = Medicine::query()
            ->search($keyword)
            ->when($status === 'active', function ($query) {
                $query->where('is_active', true);
            })
            ->when($status === 'inactive', function ($query) {
                $query->where('is_active', false);
            })
            ->when($stock === 'low', function ($query) {
                $query->whereColumn('stock_quantity', '<=', 'min_stock_quantity')
                    ->where('stock_quantity', '>', 0);
            })
            ->when($stock === 'out', function ($query) {
                $query->where('stock_quantity', '<=', 0);
            })
            ->when($category, function ($query) use ($category) {
                $query->where('category', $category);
            })
            ->orderByDesc('updated_at')
            ->paginate(12)
            ->withQueryString();

        $totalMedicines = Medicine::count();

        $activeMedicines = Medicine::where('is_active', true)->count();

        $lowStockMedicines = Medicine::whereColumn('stock_quantity', '<=', 'min_stock_quantity')
            ->where('stock_quantity', '>', 0)
            ->count();

        $outOfStockMedicines = Medicine::where('stock_quantity', '<=', 0)->count();

        $categories = Medicine::query()
            ->whereNotNull('category')
            ->where('category', '<>', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('admin.medicines.index', compact(
            'medicines',
            'keyword',
            'status',
            'stock',
            'category',
            'categories',
            'totalMedicines',
            'activeMedicines',
            'lowStockMedicines',
            'outOfStockMedicines'
        ));
    }

    /**
     * Form thêm thuốc.
     */
    public function create()
    {
        $medicine = new Medicine([
            'is_active' => true,
            'stock_quantity' => 0,
            'min_stock_quantity' => 0,
            'sale_price' => 0,
        ]);

        $categories = $this->getCategories();

        return view('admin.medicines.form', compact('medicine', 'categories'));
    }

    /**
     * Lưu thuốc mới.
     */
    public function store(Request $request)
    {
        $validated = $this->validateMedicine($request);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['min_stock_quantity'] = $validated['min_stock_quantity'] ?? 0;
        $validated['stock_quantity'] = $validated['stock_quantity'] ?? 0;

        Medicine::create($validated);

        return redirect()
            ->route('admin.medicines.index')
            ->with('success', 'Đã thêm thuốc mới thành công.');
    }

    /**
     * Không dùng trang chi tiết riêng để giữ giao diện gọn.
     * Nếu người dùng mở route show thì chuyển sang form sửa.
     */
    public function show(Medicine $medicine)
    {
        return redirect()->route('admin.medicines.edit', $medicine);
    }

    /**
     * Form sửa thuốc.
     */
    public function edit(Medicine $medicine)
    {
        $categories = $this->getCategories();

        return view('admin.medicines.form', compact('medicine', 'categories'));
    }

    /**
     * Cập nhật thuốc.
     */
    public function update(Request $request, Medicine $medicine)
    {
        $validated = $this->validateMedicine($request, $medicine);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['min_stock_quantity'] = $validated['min_stock_quantity'] ?? 0;
        $validated['stock_quantity'] = $validated['stock_quantity'] ?? 0;

        $medicine->update($validated);

        return redirect()
            ->route('admin.medicines.index')
            ->with('success', 'Đã cập nhật thuốc thành công.');
    }

    /**
     * Không xóa cứng thuốc vì thuốc có thể đã nằm trong hóa đơn.
     * Chỉ chuyển trạng thái ngừng sử dụng.
     */
    public function destroy(Medicine $medicine)
    {
        $medicine->update([
            'is_active' => false,
        ]);

        return redirect()
            ->route('admin.medicines.index')
            ->with('success', 'Đã ngừng sử dụng thuốc.');
    }

    /**
     * Cập nhật tồn kho nhanh.
     */
    public function adjustStock(Request $request, Medicine $medicine)
    {
        $validated = $request->validate([
            'adjustment_type' => ['required', 'in:set,increase,decrease'],
            'quantity' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:2000'],
        ], [
            'adjustment_type.required' => 'Vui lòng chọn kiểu cập nhật tồn kho.',
            'adjustment_type.in' => 'Kiểu cập nhật tồn kho không hợp lệ.',
            'quantity.required' => 'Vui lòng nhập số lượng.',
            'quantity.integer' => 'Số lượng phải là số nguyên.',
            'quantity.min' => 'Số lượng không được âm.',
        ]);

        $quantity = (int) $validated['quantity'];
        $type = $validated['adjustment_type'];

        if (in_array($type, ['increase', 'decrease'], true) && $quantity <= 0) {
            return back()->with('error', 'Số lượng tăng/giảm phải lớn hơn 0.');
        }

        if ($type === 'set') {
            $medicine->update([
                'stock_quantity' => $quantity,
                'description' => $validated['description'] ?? $medicine->description,
            ]);
        }

        if ($type === 'increase') {
            $medicine->increaseStock($quantity);

            if (!empty($validated['description'])) {
                $medicine->update([
                    'description' => $validated['description'],
                ]);
            }
        }

        if ($type === 'decrease') {
            if (!$medicine->hasEnoughStock($quantity)) {
                return back()->with('error', 'Số lượng giảm lớn hơn tồn kho hiện tại.');
            }

            $medicine->decreaseStock($quantity);

            if (!empty($validated['description'])) {
                $medicine->update([
                    'description' => $validated['description'],
                ]);
            }
        }

        return back()->with('success', 'Đã cập nhật tồn kho thuốc.');
    }

    private function validateMedicine(Request $request, ?Medicine $medicine = null): array
    {
        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('medicines', 'code')->ignore($medicine?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'generic_name' => ['nullable', 'string', 'max:255'],
            'strength' => ['nullable', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:50'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'min_stock_quantity' => ['nullable', 'integer', 'min:0'],
            'category' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:2000'],
        ], [
            'code.required' => 'Vui lòng nhập mã thuốc.',
            'code.unique' => 'Mã thuốc đã tồn tại.',
            'name.required' => 'Vui lòng nhập tên thuốc.',
            'unit.required' => 'Vui lòng nhập đơn vị tính.',
            'sale_price.required' => 'Vui lòng nhập giá bán.',
            'sale_price.numeric' => 'Giá bán phải là số.',
            'stock_quantity.required' => 'Vui lòng nhập số lượng tồn.',
            'stock_quantity.integer' => 'Số lượng tồn phải là số nguyên.',
        ]);
    }

    private function getCategories()
    {
        return Medicine::query()
            ->whereNotNull('category')
            ->where('category', '<>', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');
    }
}