<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RevenueReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        return $this->renderRevenueReport($request, 'admin.revenue.index');
    }

    public function showInvoice(Invoice $invoice)
    {
        $this->loadInvoiceDetail($invoice);

        return view('admin.revenue.invoice-show', compact('invoice'));
    }

    public function employeeIndex(Request $request)
    {
        return $this->renderRevenueReport($request, 'employees.revenue.index');
    }

    public function employeeShowInvoice(Invoice $invoice)
    {
        $this->loadInvoiceDetail($invoice);

        return view('employees.revenue.invoice-show', compact('invoice'));
    }

    private function renderRevenueReport(Request $request, string $view)
    {
        [$startDate, $endDate] = $this->resolveDateRange($request);

        $serviceId = $request->input('service_id');
        $doctorId = $request->input('doctor_id');
        $paymentMethod = $request->input('payment_method');
        $source = $request->input('source');

        $baseQuery = $this->paidInvoiceQuery(
            $startDate,
            $endDate,
            $serviceId,
            $doctorId,
            $paymentMethod,
            $source
        );

        $summary = $this->buildSummary(clone $baseQuery);

        $previousSummary = $this->buildPreviousSummary(
            $startDate,
            $endDate,
            $serviceId,
            $doctorId,
            $paymentMethod,
            $source
        );

        $dailyRevenue = $this->buildDailyRevenue(clone $baseQuery);
        $serviceRevenue = $this->buildServiceRevenue(clone $baseQuery);
        $doctorRevenue = $this->buildDoctorRevenue(clone $baseQuery);
        $paymentRevenue = $this->buildPaymentRevenue(clone $baseQuery);
        $sourceRevenue = $this->buildSourceRevenue(clone $baseQuery);

        $invoices = (clone $baseQuery)
            ->with([
                'appointment.room',
                'appointment.service',
                'appointment.doctor',
                'patient',
                'patientProfile',
                'doctor',
                'service',
                'cashier',
            ])
            ->orderByRaw('COALESCE(invoices.paid_at, invoices.updated_at, invoices.created_at) DESC')
            ->paginate(15)
            ->withQueryString();

        $services = Service::query()
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $doctors = Employee::query()
            ->where('is_doctor', 1)
            ->orderBy('name')
            ->get();

        return view($view, compact(
            'startDate',
            'endDate',
            'serviceId',
            'doctorId',
            'paymentMethod',
            'source',
            'summary',
            'previousSummary',
            'dailyRevenue',
            'serviceRevenue',
            'doctorRevenue',
            'paymentRevenue',
            'sourceRevenue',
            'invoices',
            'services',
            'doctors'
        ));
    }

    private function loadInvoiceDetail(Invoice $invoice): void
    {
        $invoice->load([
            'appointment.service',
            'appointment.doctor',
            'appointment.room',
            'appointment.medicalRecord',
            'patient',
            'patientProfile',
            'doctor',
            'service',
            'cashier',
        ]);
    }

    private function resolveDateRange(Request $request): array
    {
        try {
            $startDate = $request->filled('start_date')
                ? Carbon::parse($request->input('start_date'))->startOfDay()
                : now()->startOfMonth();

            $endDate = $request->filled('end_date')
                ? Carbon::parse($request->input('end_date'))->endOfDay()
                : now()->endOfDay();
        } catch (\Throwable $e) {
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfDay();
        }

        if ($startDate->greaterThan($endDate)) {
            [$startDate, $endDate] = [
                $endDate->copy()->startOfDay(),
                $startDate->copy()->endOfDay(),
            ];
        }

        return [$startDate, $endDate];
    }

    private function paidInvoiceQuery(
        Carbon $startDate,
        Carbon $endDate,
        $serviceId = null,
        $doctorId = null,
        $paymentMethod = null,
        $source = null
    ) {
        $query = Invoice::query()
            ->where('invoices.status', 'paid')
            ->whereBetween(DB::raw('COALESCE(invoices.paid_at, invoices.updated_at, invoices.created_at)'), [
                $startDate,
                $endDate,
            ]);

        if ($serviceId) {
            $query->where('invoices.service_id', $serviceId);
        }

        if ($doctorId) {
            $query->where('invoices.doctor_id', $doctorId);
        }

        if ($paymentMethod && $paymentMethod !== 'all') {
            $query->where('invoices.payment_method', $paymentMethod);
        }

        if ($source && $source !== 'all') {
            $query->where(function ($q) use ($source) {
                $q->whereHas('appointment', function ($appointmentQuery) use ($source) {
                    $appointmentQuery->where('source', $source);
                })->orWhereHas('patientProfile', function ($profileQuery) use ($source) {
                    $profileQuery->where('source', $source);
                });
            });
        }

        return $query;
    }

    private function buildSummary($query): array
    {
        $row = $query
            ->selectRaw('COUNT(*) as invoice_count')
            ->selectRaw('SUM(CASE WHEN invoices.paid_amount > 0 THEN invoices.paid_amount ELSE invoices.total_amount END) as revenue_total')
            ->selectRaw('SUM(COALESCE(invoices.service_price, invoices.service_amount, 0)) as service_total')
            ->selectRaw('SUM(COALESCE(invoices.medicine_total, 0)) as medicine_total')
            ->selectRaw('SUM(COALESCE(invoices.extra_total, invoices.extra_amount, 0)) as extra_total')
            ->selectRaw('SUM(COALESCE(invoices.discount_amount, 0)) as discount_total')
            ->first();

        $invoiceCount = (int) ($row->invoice_count ?? 0);
        $revenueTotal = (float) ($row->revenue_total ?? 0);

        return [
            'invoice_count' => $invoiceCount,
            'revenue_total' => $revenueTotal,
            'service_total' => (float) ($row->service_total ?? 0),
            'medicine_total' => (float) ($row->medicine_total ?? 0),
            'extra_total' => (float) ($row->extra_total ?? 0),
            'discount_total' => (float) ($row->discount_total ?? 0),
            'average_invoice' => $invoiceCount > 0 ? $revenueTotal / $invoiceCount : 0,
        ];
    }

    private function buildPreviousSummary(
        Carbon $startDate,
        Carbon $endDate,
        $serviceId = null,
        $doctorId = null,
        $paymentMethod = null,
        $source = null
    ): array {
        $days = $startDate->diffInDays($endDate) + 1;

        $previousEnd = $startDate->copy()->subSecond();
        $previousStart = $previousEnd->copy()->subDays($days - 1)->startOfDay();

        $query = $this->paidInvoiceQuery(
            $previousStart,
            $previousEnd,
            $serviceId,
            $doctorId,
            $paymentMethod,
            $source
        );

        return $this->buildSummary($query);
    }

    private function buildDailyRevenue($query)
    {
        return $query
            ->selectRaw('DATE(COALESCE(invoices.paid_at, invoices.updated_at, invoices.created_at)) as report_date')
            ->selectRaw('COUNT(*) as invoice_count')
            ->selectRaw('SUM(CASE WHEN invoices.paid_amount > 0 THEN invoices.paid_amount ELSE invoices.total_amount END) as revenue_total')
            ->groupBy('report_date')
            ->orderBy('report_date')
            ->get();
    }

    private function buildServiceRevenue($query)
    {
        return $query
            ->leftJoin('services', 'services.id', '=', 'invoices.service_id')
            ->selectRaw("COALESCE(invoices.service_name, services.name, 'Chưa xác định') as label")
            ->selectRaw('COUNT(*) as invoice_count')
            ->selectRaw('SUM(CASE WHEN invoices.paid_amount > 0 THEN invoices.paid_amount ELSE invoices.total_amount END) as revenue_total')
            ->groupBy('label')
            ->orderByDesc('revenue_total')
            ->limit(8)
            ->get();
    }

    private function buildDoctorRevenue($query)
    {
        return $query
            ->leftJoin('employees', 'employees.id', '=', 'invoices.doctor_id')
            ->selectRaw("COALESCE(invoices.doctor_name, employees.name, 'Chưa xác định') as label")
            ->selectRaw('COUNT(*) as invoice_count')
            ->selectRaw('SUM(CASE WHEN invoices.paid_amount > 0 THEN invoices.paid_amount ELSE invoices.total_amount END) as revenue_total')
            ->groupBy('label')
            ->orderByDesc('revenue_total')
            ->limit(8)
            ->get();
    }

    private function buildPaymentRevenue($query)
    {
        return $query
            ->selectRaw("COALESCE(invoices.payment_method, 'unknown') as label")
            ->selectRaw('COUNT(*) as invoice_count')
            ->selectRaw('SUM(CASE WHEN invoices.paid_amount > 0 THEN invoices.paid_amount ELSE invoices.total_amount END) as revenue_total')
            ->groupBy('label')
            ->orderByDesc('revenue_total')
            ->get();
    }

    private function buildSourceRevenue($query)
    {
        $invoices = $query
            ->with(['appointment', 'patientProfile'])
            ->get();

        return $invoices
            ->groupBy(function ($invoice) {
                return $invoice->appointment?->source
                    ?? $invoice->patientProfile?->source
                    ?? 'online';
            })
            ->map(function ($items, $source) {
                return [
                    'label' => $source,
                    'invoice_count' => $items->count(),
                    'revenue_total' => $items->sum(function ($invoice) {
                        return (float) (($invoice->paid_amount ?? 0) > 0
                            ? $invoice->paid_amount
                            : $invoice->total_amount);
                    }),
                ];
            })
            ->values();
    }
}