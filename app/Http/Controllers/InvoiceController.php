<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Medicine;
use App\Models\Payment;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ==================== EMPLOYEE / CASHIER ====================

    public function index(Request $request)
    {
        $this->syncCompletedAppointmentsToInvoices();

        $keyword = trim((string) $request->input('keyword'));
        $status = $request->input('status', 'all');
        $date = $request->input('date');

        $invoices = Invoice::with([
                'appointment.service',
                'appointment.room',
                'appointment.medicalRecord',
                'patient',
                'patientProfile.user',
                'doctor',
                'service',
                'cashier',
                'verifier',
                'sentToPatientBy',
            ])
            ->when($status && $status !== 'all', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($date, function ($query) use ($date) {
                $query->whereDate('appointment_date', $date);
            })
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('invoice_code', 'like', "%{$keyword}%")
                        ->orWhere('patient_name', 'like', "%{$keyword}%")
                        ->orWhere('patient_phone', 'like', "%{$keyword}%")
                        ->orWhere('doctor_name', 'like', "%{$keyword}%")
                        ->orWhere('service_name', 'like', "%{$keyword}%")
                        ->orWhereHas('patientProfile', function ($profileQuery) use ($keyword) {
                            $profileQuery->where('full_name', 'like', "%{$keyword}%")
                                ->orWhere('phone', 'like', "%{$keyword}%")
                                ->orWhere('identity_number', 'like', "%{$keyword}%");
                        });
                });
            })
            ->orderByDesc('appointment_date')
            ->orderByDesc('issued_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $unpaidCount = Invoice::where('status', 'unpaid')->count();

        $paymentPendingCount = Invoice::where('status', 'payment_pending')->count();

        $paidTodayCount = Invoice::where('status', 'paid')
            ->whereDate('paid_at', today())
            ->count();

        $paidTodayTotal = Invoice::where('status', 'paid')
            ->whereDate('paid_at', today())
            ->sum('paid_amount');

        $cancelledCount = Invoice::where('status', 'cancelled')->count();

        $completedInvoicesCount = Invoice::whereNotNull('appointment_id')->count();

        return view('employees.invoices.index', compact(
            'invoices',
            'keyword',
            'status',
            'date',
            'unpaidCount',
            'paymentPendingCount',
            'paidTodayCount',
            'paidTodayTotal',
            'cancelledCount',
            'completedInvoicesCount'
        ));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load([
            'appointment.medicalRecord',
            'appointment.room',
            'appointment.service',
            'appointment.doctor',
            'patient',
            'patientProfile.user',
            'doctor',
            'service',
            'cashier',
            'verifier',
            'sentToPatientBy',
            'payments',
        ]);

        $medicines = Medicine::active()
            ->orderBy('name')
            ->get();

        $printMode = false;

        return view('employees.invoices.show', compact('invoice', 'medicines', 'printMode'));
    }

    public function print(Invoice $invoice)
    {
        $invoice->load([
            'appointment.medicalRecord',
            'appointment.room',
            'appointment.service',
            'appointment.doctor',
            'patient',
            'patientProfile.user',
            'doctor',
            'service',
            'cashier',
            'verifier',
            'sentToPatientBy',
            'payments',
        ]);

        return view('employees.invoices.print', compact('invoice'));
    }

    public function addMedicine(Request $request, Invoice $invoice)
    {
        if (!$invoice->isUnpaid()) {
            return $this->backToInvoice($invoice, 'error', 'Chỉ có thể thêm thuốc vào hóa đơn đang chờ thanh toán.');
        }

        if ($invoice->isSentToPatient()) {
            return $this->backToInvoice($invoice, 'error', 'Hóa đơn đã gửi cho bệnh nhân, không thể chỉnh thuốc. Hãy hủy gửi hoặc tạo hóa đơn điều chỉnh nếu cần.');
        }

        $validated = $request->validate([
            'medicine_id' => ['required', 'exists:medicines,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ], [
            'medicine_id.required' => 'Vui lòng chọn thuốc.',
            'medicine_id.exists' => 'Thuốc không tồn tại.',
            'quantity.required' => 'Vui lòng nhập số lượng.',
            'quantity.integer' => 'Số lượng phải là số nguyên.',
            'quantity.min' => 'Số lượng phải lớn hơn 0.',
        ]);

        $medicine = Medicine::active()->findOrFail($validated['medicine_id']);
        $quantity = (int) $validated['quantity'];

        $items = collect($invoice->medicine_items ?: [])->values();

        $existingIndex = $items->search(function ($item) use ($medicine) {
            return (int) ($item['medicine_id'] ?? 0) === (int) $medicine->id;
        });

        $newQuantity = $quantity;

        if ($existingIndex !== false) {
            $oldItem = $items[$existingIndex];
            $newQuantity += (int) ($oldItem['quantity'] ?? 0);
        }

        if (!$medicine->hasEnoughStock($newQuantity)) {
            return $this->backToInvoice($invoice, 'error', "Thuốc {$medicine->display_name} không đủ tồn kho.");
        }

        $newItem = [
            'medicine_id' => $medicine->id,
            'code' => $medicine->code,
            'name' => $medicine->display_name,
            'unit' => $medicine->unit,
            'quantity' => $newQuantity,
            'unit_price' => (float) $medicine->sale_price,
            'total' => $newQuantity * (float) $medicine->sale_price,
        ];

        if ($existingIndex !== false) {
            $items[$existingIndex] = $newItem;
        } else {
            $items->push($newItem);
        }

        $invoice->medicine_items = $items->values()->all();
        $invoice->recalculateTotals();
        $invoice->save();

        return $this->backToInvoice($invoice, 'success', 'Đã thêm thuốc vào hóa đơn.');
    }

    public function removeMedicine(Invoice $invoice, int $index)
    {
        if (!$invoice->isUnpaid()) {
            return $this->backToInvoice($invoice, 'error', 'Chỉ có thể xóa thuốc khỏi hóa đơn đang chờ thanh toán.');
        }

        if ($invoice->isSentToPatient()) {
            return $this->backToInvoice($invoice, 'error', 'Hóa đơn đã gửi cho bệnh nhân, không thể chỉnh thuốc.');
        }

        $items = collect($invoice->medicine_items ?: [])->values();

        if (!$items->has($index)) {
            return $this->backToInvoice($invoice, 'error', 'Dòng thuốc không tồn tại.');
        }

        $items->forget($index);

        $invoice->medicine_items = $items->values()->all();
        $invoice->recalculateTotals();
        $invoice->save();

        return $this->backToInvoice($invoice, 'success', 'Đã xóa thuốc khỏi hóa đơn.');
    }

    public function updateExtras(Request $request, Invoice $invoice)
    {
        if (!$invoice->isUnpaid()) {
            return $this->backToInvoice($invoice, 'error', 'Chỉ có thể cập nhật chi phí khi hóa đơn chưa thanh toán.');
        }

        if ($invoice->isSentToPatient()) {
            return $this->backToInvoice($invoice, 'error', 'Hóa đơn đã gửi cho bệnh nhân, không thể chỉnh phụ phí/giảm giá.');
        }

        $validated = $request->validate([
            'extra_items' => ['nullable', 'array'],
            'extra_items.*.name' => ['nullable', 'string', 'max:255'],
            'extra_items.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'extra_items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $extraItems = collect($validated['extra_items'] ?? [])
            ->filter(function ($item) {
                return filled($item['name'] ?? null);
            })
            ->map(function ($item) {
                $quantity = max(0, (float) ($item['quantity'] ?? 1));
                $unitPrice = max(0, (float) ($item['unit_price'] ?? 0));

                return [
                    'name' => trim((string) $item['name']),
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total' => $quantity * $unitPrice,
                ];
            })
            ->values()
            ->all();

        $invoice->extra_items = $extraItems;
        $invoice->discount_amount = max(0, (float) ($validated['discount_amount'] ?? 0));
        $invoice->notes = $validated['notes'] ?? $invoice->notes;
        $invoice->recalculateTotals();
        $invoice->save();

        return $this->backToInvoice($invoice, 'success', 'Đã cập nhật hóa đơn.');
    }

    public function sendToPatient(Request $request, Invoice $invoice)
    {
        $invoice->loadMissing(['patientProfile.user', 'patient']);

        if (!$invoice->isUnpaid()) {
            return $this->backToInvoice($invoice, 'error', 'Chỉ có thể gửi hóa đơn đang chờ thanh toán cho bệnh nhân.');
        }

        if ($invoice->isSentToPatient()) {
            return $this->backToInvoice($invoice, 'error', 'Hóa đơn này đã được gửi cho bệnh nhân.');
        }

        if (!$invoice->hasPatientAccount()) {
            return $this->backToInvoice($invoice, 'error', 'Bệnh nhân chưa có tài khoản, không thể gửi hóa đơn thanh toán online. Vui lòng thu tại quầy.');
        }

        $invoice->recalculateTotals();
        $invoice->save();

        if ((float) $invoice->total_amount <= 0) {
            return $this->backToInvoice($invoice, 'error', 'Tổng tiền hóa đơn không hợp lệ, không thể gửi cho bệnh nhân.');
        }

        $validated = $request->validate([
            'payment_due_at' => ['nullable', 'date', 'after:now'],
        ], [
            'payment_due_at.date' => 'Hạn thanh toán không hợp lệ.',
            'payment_due_at.after' => 'Hạn thanh toán phải lớn hơn thời điểm hiện tại.',
        ]);

        $dueAt = !empty($validated['payment_due_at'])
            ? $validated['payment_due_at']
            : now()->addDays(3)->endOfDay();

        $invoice->sendToPatient(Auth::id(), $dueAt);

        return $this->backToInvoice($invoice, 'success', 'Đã gửi hóa đơn thanh toán online cho bệnh nhân.');
    }

    public function confirmPayment(Request $request, Invoice $invoice)
    {
        if (!$invoice->isUnpaid() && !$invoice->isPaymentPending()) {
            return back()->with('error', 'Hóa đơn này không còn ở trạng thái có thể xác nhận thanh toán.');
        }

        $validated = $request->validate([
            'payment_method' => ['required', 'in:cash,bank_transfer,card,momo,other'],
            'transaction_reference' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán.',
            'payment_method.in' => 'Phương thức thanh toán không hợp lệ.',
        ]);

        try {
            DB::transaction(function () use ($invoice, $validated) {
                $lockedInvoice = Invoice::whereKey($invoice->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (!$lockedInvoice->isUnpaid() && !$lockedInvoice->isPaymentPending()) {
                    throw new \Exception('Hóa đơn này đã được xử lý trước đó.');
                }

                $lockedInvoice->recalculateTotals();
                $lockedInvoice->save();

                if ((float) $lockedInvoice->total_amount <= 0) {
                    throw new \Exception('Tổng tiền hóa đơn không hợp lệ.');
                }

                foreach (($lockedInvoice->medicine_items ?: []) as $item) {
                    $medicineId = $item['medicine_id'] ?? null;
                    $quantity = (int) ($item['quantity'] ?? 0);

                    if (!$medicineId || $quantity <= 0) {
                        continue;
                    }

                    $medicine = Medicine::whereKey($medicineId)
                        ->lockForUpdate()
                        ->first();

                    if (!$medicine) {
                        throw new \Exception('Một thuốc trong hóa đơn không tồn tại.');
                    }

                    if (!$medicine->hasEnoughStock($quantity)) {
                        throw new \Exception("Thuốc {$medicine->display_name} không đủ tồn kho.");
                    }
                }

                foreach (($lockedInvoice->medicine_items ?: []) as $item) {
                    $medicineId = $item['medicine_id'] ?? null;
                    $quantity = (int) ($item['quantity'] ?? 0);

                    if (!$medicineId || $quantity <= 0) {
                        continue;
                    }

                    $medicine = Medicine::whereKey($medicineId)
                        ->lockForUpdate()
                        ->first();

                    if ($medicine) {
                        $medicine->decreaseStock($quantity);
                    }
                }

                $paymentMethodForPayment = $validated['payment_method'] === 'momo'
                    ? 'e_wallet'
                    : $validated['payment_method'];

                Payment::create([
                    'payment_code' => Payment::generateCode(),
                    'invoice_id' => $lockedInvoice->id,
                    'appointment_id' => $lockedInvoice->appointment_id,
                    'patient_id' => $lockedInvoice->patient_id,
                    'patient_profile_id' => $lockedInvoice->patient_profile_id,
                    'cashier_id' => Auth::id(),
                    'amount' => $lockedInvoice->total_amount,
                    'payment_method' => $paymentMethodForPayment,
                    'status' => 'success',
                    'payer_name' => $lockedInvoice->display_patient_name,
                    'payer_phone' => $lockedInvoice->display_patient_phone,
                    'transaction_reference' => $validated['transaction_reference'] ?? null,
                    'note' => $validated['note'] ?? null,
                    'paid_at' => now(),
                ]);

                $lockedInvoice->markAsPaid($validated['payment_method'], Auth::id());
            });

            return redirect()
                ->route('employees.invoices.show', $invoice->id)
                ->with('success', 'Đã xác nhận thanh toán thành công.');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel(Invoice $invoice)
    {
        if ($invoice->isPaid()) {
            return back()->with('error', 'Không thể hủy hóa đơn đã thanh toán.');
        }

        if ($invoice->isCancelled()) {
            return back()->with('error', 'Hóa đơn này đã bị hủy trước đó.');
        }

        $invoice->update([
            'status' => 'cancelled',
            'notes' => trim(($invoice->notes ?: '') . "\nHóa đơn bị hủy bởi người dùng #" . Auth::id() . ' lúc ' . now()->format('d/m/Y H:i')),
        ]);

        return redirect()
            ->route('employees.invoices.index')
            ->with('success', 'Đã hủy hóa đơn.');
    }

    // ==================== PATIENT ====================

    public function patientIndex()
    {
        $userId = Auth::id();

        $invoices = Invoice::with([
                'appointment.room',
                'appointment.medicalRecord',
                'doctor',
                'service',
                'payments',
            ])
            ->visibleToPatients()
            ->where(function ($query) use ($userId) {
                $query->where('patient_id', $userId)
                    ->orWhereHas('patientProfile', function ($profileQuery) use ($userId) {
                        $profileQuery->where('user_id', $userId);
                    });
            })
            ->orderByDesc('appointment_date')
            ->orderByDesc('issued_at')
            ->paginate(10);

        return view('patient.invoices.index', compact('invoices'));
    }

    public function patientShow(Invoice $invoice)
    {
        $userId = Auth::id();

        $invoice->load([
            'patientProfile',
            'appointment.room',
            'appointment.medicalRecord',
            'doctor',
            'service',
            'payments',
        ]);

        $allowed = (int) $invoice->patient_id === (int) $userId
            || (int) optional($invoice->patientProfile)->user_id === (int) $userId;

        if (!$allowed) {
            abort(403, 'Bạn không có quyền xem hóa đơn này.');
        }

        if (!$invoice->isSentToPatient() && !$invoice->isPaymentPending() && !$invoice->isPaid()) {
            abort(403, 'Hóa đơn này chưa được gửi cho bệnh nhân.');
        }

        return view('patient.invoices.show', compact('invoice'));
    }

    public function patientSubmitPaymentProof(Request $request, Invoice $invoice)
    {
        $userId = Auth::id();

        $invoice->load(['patientProfile']);

        $allowed = (int) $invoice->patient_id === (int) $userId
            || (int) optional($invoice->patientProfile)->user_id === (int) $userId;

        if (!$allowed) {
            abort(403, 'Bạn không có quyền thanh toán hóa đơn này.');
        }

        if (!$invoice->canPatientSubmitPayment()) {
            return redirect()
                ->route('patient.invoices.show', $invoice->id)
                ->with('error', 'Hóa đơn này không thể gửi xác nhận thanh toán. Có thể đã quá hạn, đã thanh toán hoặc chưa được gửi cho bạn.');
        }

        $validated = $request->validate([
            'payment_proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'patient_payment_note' => ['nullable', 'string', 'max:1000'],
        ], [
            'payment_proof.required' => 'Vui lòng tải ảnh bill chuyển khoản.',
            'payment_proof.image' => 'File tải lên phải là hình ảnh.',
            'payment_proof.mimes' => 'Ảnh bill phải có định dạng JPG, JPEG, PNG hoặc WEBP.',
            'payment_proof.max' => 'Ảnh bill không được vượt quá 5MB.',
            'patient_payment_note.max' => 'Ghi chú không được vượt quá 1000 ký tự.',
        ]);

        if ($invoice->patient_payment_proof && Storage::disk('public')->exists($invoice->patient_payment_proof)) {
            Storage::disk('public')->delete($invoice->patient_payment_proof);
        }

        $proofPath = $request->file('payment_proof')->store('payment-proofs', 'public');

        $invoice->markPaymentPending(
            $proofPath,
            $validated['patient_payment_note'] ?? null
        );

        return redirect()
            ->route('patient.invoices.show', $invoice->id)
            ->with('success', 'Đã gửi bill chuyển khoản. Thu ngân sẽ kiểm tra và xác nhận thanh toán.');
    }

    // ==================== AUTO GENERATE INVOICE ====================

    public function ensureInvoiceForAppointment(Appointment $appointment): Invoice
    {
        return $this->generateFromAppointment($appointment);
    }

    public function generateFromAppointment(Appointment $appointment): Invoice
    {
        $appointment->loadMissing([
            'patient',
            'patientProfile',
            'doctor',
            'service',
            'room',
            'medicalRecord',
        ]);

        if ($appointment->status !== 'completed') {
            throw new \Exception('Chỉ tạo hóa đơn khi ca khám đã hoàn thành.');
        }

        if (!$appointment->service) {
            throw new \Exception('Ca khám chưa có dịch vụ, không thể tạo hóa đơn.');
        }

        if (!$appointment->patient_id && !$appointment->patient_profile_id) {
            throw new \Exception('Ca khám chưa có thông tin bệnh nhân, không thể tạo hóa đơn.');
        }

        $invoice = Invoice::where('appointment_id', $appointment->id)->first();

        if ($invoice && !$invoice->isUnpaid()) {
            return $invoice;
        }

        $servicePrice = $this->resolveServicePrice($appointment->service);

        if (!$invoice) {
            $invoice = new Invoice();
            $invoice->invoice_code = Invoice::generateCode();
            $invoice->appointment_id = $appointment->id;
            $invoice->issued_at = now();
            $invoice->status = 'unpaid';
        }

        $patientName = $appointment->patientProfile?->full_name
            ?? $appointment->patient?->name
            ?? data_get($appointment->patient_snapshot, 'full_name')
            ?? 'Bệnh nhân #' . $appointment->id;

        $patientPhone = $appointment->patientProfile?->phone
            ?? data_get($appointment->patient_snapshot, 'phone')
            ?? $appointment->patient?->phone
            ?? $appointment->patient?->phone_number
            ?? null;

        $doctorName = $appointment->doctor?->name
            ?? $appointment->doctor?->user?->name
            ?? null;

        $invoice->fill([
            'patient_id' => $appointment->patient_id,
            'patient_profile_id' => $appointment->patient_profile_id,
            'doctor_id' => $appointment->doctor_id,
            'service_id' => $appointment->service_id,
            'created_by' => $invoice->created_by ?: Auth::id(),

            'patient_name' => $patientName,
            'patient_phone' => $patientPhone,
            'doctor_name' => $doctorName,
            'service_name' => $appointment->service?->name,
            'appointment_date' => $appointment->appointment_date,

            'service_amount' => $servicePrice,
            'service_price' => $servicePrice,
            'medicine_items' => $invoice->medicine_items ?: [],
            'extra_items' => $invoice->extra_items ?: [],
            'discount_amount' => $invoice->discount_amount ?: 0,
            'notes' => $invoice->notes,
        ]);

        $invoice->recalculateTotals();
        $invoice->save();

        return $invoice;
    }

    private function syncCompletedAppointmentsToInvoices(): void
    {
        Appointment::with([
                'patient',
                'patientProfile',
                'doctor',
                'service',
                'room',
                'medicalRecord',
            ])
            ->where('status', 'completed')
            ->whereNotNull('service_id')
            ->where(function ($query) {
                $query->whereNotNull('patient_id')
                    ->orWhereNotNull('patient_profile_id');
            })
            ->whereDoesntHave('invoice')
            ->orderByDesc('completed_at')
            ->orderByDesc('appointment_date')
            ->chunkById(100, function ($appointments) {
                foreach ($appointments as $appointment) {
                    try {
                        $this->generateFromAppointment($appointment);
                    } catch (Throwable $e) {
                        report($e);
                    }
                }
            });
    }

    private function resolveServicePrice(?Service $service): float
    {
        if (!$service) {
            return 0;
        }

        if (method_exists($service, 'getCurrentPrice')) {
            $price = $service->getCurrentPrice();

            if ($price && isset($price->price)) {
                return (float) $price->price;
            }
        }

        if ($service->relationLoaded('currentPrice') && $service->currentPrice && isset($service->currentPrice->price)) {
            return (float) $service->currentPrice->price;
        }

        if (isset($service->price)) {
            return (float) $service->price;
        }

        if (isset($service->service_price)) {
            return (float) $service->service_price;
        }

        return 0;
    }

    private function backToInvoice(Invoice $invoice, string $type, string $message)
    {
        return redirect()
            ->route('employees.invoices.show', $invoice->id)
            ->with($type, $message);
    }
}