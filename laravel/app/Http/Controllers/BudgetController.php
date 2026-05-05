<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Budget;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;
use Auth;
use Session;
use DB;
use Mail;
use Str;
use DataTables;
use Illuminate\Support\Facades\Storage;
use App\Mail\ActivityAssignedMail;
use App\Models\Clients;
use App\Models\User;
use App\Models\UseTypes;
use App\Models\RegisterInvoice;
use App\Models\LicensesAgreements;
use App\Models\Environment;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\ClientCategory;
use App\Models\ClientSubCategory;
use App\Models\CreditNote;
use App\Models\CashReceipt;
use App\Models\IncomeRecord;
use App\Models\Bank;
use App\Models\BudgetCriterion;
use App\Models\Validation; // Add this
use App\Models\ValidationItem; // Add this
use App\Models\Distribution;
use App\Models\Settlement;
use App\Models\PortfolioComment;
use App\Models\AssignSettlement;
use App\Models\InvoiceConsecutive;
use App\Models\ReceiptConsecutive;

class BudgetController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:list-budget|create-budget|edit-budget|delete-budget', ['only' => ['index', 'show']]);
        $this->middleware('permission:create-budget', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit-budget', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete-budget', ['only' => ['destroy']]);
    }

    public function index(Request $request): View
    {
        $pageTitle = 'Budgets';
        $clients = Clients::all();
        $month = $request->month ?? date('n');
        $year  = $request->year ?? date('Y');
        $startDate = $request->start_date ?? null;
        $endDate = $request->end_date ?? null;

        $banks = Bank::get();
        $criterions = BudgetCriterion::get();
        $clientCategories = ClientCategory::where('category_status', 1)->get();
        $receiptConsecutives = ReceiptConsecutive::where('status', 1)->get();
        $environments = Environment::pluck('name', 'name')->toArray();

        $totals = $this->calculateTotalsByConcept($month, $year, $startDate, $endDate);

        return view('budget.index', compact('pageTitle', 'totals', 'clients', 'month', 'year', 'banks', 'criterions', 'clientCategories', 'receiptConsecutives', 'environments'));
    }

    /** Timeframe overlap filter helper */
    private function applyTimeframeFilter($query, ?string $startDate, ?string $endDate)
    {
        if ($startDate || $endDate) {
            $query->where(function ($q) use ($startDate, $endDate) {
                if ($startDate && $endDate) {
                    // Both dates provided - budget period must overlap with filter range
                    $q->where(function ($subQ) use ($startDate, $endDate) {
                        // Budget begins before or during filter range AND ends after or during filter range
                        $subQ->whereRaw("CONCAT(begin_year, '-', LPAD(begin_month, 2, '0')) <= ?", [$endDate])
                            ->whereRaw("CONCAT(finish_year, '-', LPAD(finish_month, 2, '0')) >= ?", [$startDate]);
                    });
                } elseif ($startDate) {
                    // Only start date - budget must end on or after start date
                    $q->whereRaw("CONCAT(finish_year, '-', LPAD(finish_month, 2, '0')) >= ?", [$startDate]);
                } elseif ($endDate) {
                    // Only end date - budget must begin on or before end date
                    $q->whereRaw("CONCAT(begin_year, '-', LPAD(begin_month, 2, '0')) <= ?", [$endDate]);
                }
            });
        }
    }

    /** Inclusive month difference */
    private function monthsDiffInclusive(int $bMonth, int $bYear, int $fMonth, int $fYear): int
    {
        $begin  = Carbon::create($bYear, $bMonth, 1);
        $finish = Carbon::create($fYear, $fMonth, 1);
        if ($finish->lt($begin)) {
            while ($finish->lt($begin)) {
                $finish->addYear();
            }
        }
        return $begin->diffInMonths($finish) + 1;
    }

    /** Core calculator for budget values */
    private function buildCalculatedBudget(?string $frequency, ?float $annualValue, int $bMonth, int $bYear, ?int $fMonth, ?int $fYear, float $vatPercent): array
    {
        $frequency = $frequency ?: 'Monthly';

        // Default finish for Monthly: 12 months inclusive
        if ($frequency === 'Monthly' && (!$fMonth || !$fYear)) {
            $finish = Carbon::create($bYear, $bMonth, 1)->addMonths(11);
            $fMonth = (int)$finish->month;
            $fYear  = (int)$finish->year;
        }

        $monthsTotal = $this->monthsDiffInclusive($bMonth, $bYear, $fMonth ?? $bMonth, $fYear ?? $bYear);

        $monthlyAmount = null;
        if ($annualValue !== null && $annualValue > 0) {
            $monthlyAmount = round($annualValue / $monthsTotal, 2);
        }

        $subTotal = $monthlyAmount ?? 0.0;
        $total    = $subTotal + ($subTotal * ($vatPercent / 100));

        return [
            'frequency'      => $frequency,
            'finish_month'   => $fMonth,
            'finish_year'    => $fYear,
            'months_total'   => $monthsTotal,
            'monthly_amount' => $monthlyAmount,
            'subTotal'       => $subTotal,
            'total'          => $total,
        ];
    }

    /** Format currency as $1,000.00 */
    private function formatCurrency($amount)
    {
        return '$' . number_format((float)$amount, 2, '.', ',');
    }

    // Helper function to parse formatted numbers
    private function parseFormattedNumber($value)
    {
        if ($value === null || $value === '') return 0;

        $raw = trim((string) $value);
        $isNegative = str_starts_with($raw, '-');
        $value = preg_replace('/[^0-9,.]/', '', $raw);

        if ($value === '') return 0;

        if (strpos($value, ',') !== false && strpos($value, '.') !== false) {
            $lastComma = strrpos($value, ',');
            $lastDot = strrpos($value, '.');

            if ($lastComma > $lastDot) {
                // 1.234,56
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                // 1,234.56
                $value = str_replace(',', '', $value);
            }
        } elseif (strpos($value, ',') !== false) {
            $parts = explode(',', $value);
            if (count($parts) === 2 && strlen($parts[1]) <= 2) {
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        } elseif (substr_count($value, '.') > 1) {
            $value = str_replace('.', '', $value);
        }

        $parsed = (float) $value;
        return $isNegative ? -$parsed : $parsed;
    }

    public function getAjaxData(Request $request)
    {
        if ($request->has('get_totals')) {
            $month  = $request->month ?: null;
            $year   = $request->year ?: null;
            $start  = $request->start_date ?: null;
            $end    = $request->end_date ?: null;
            $conceptFilter = $request->conceptFilter ?: null;
            $conditionFilter = $request->conditionFilter ?: null;

            $totals = $this->calculateTotalsByConcept($month, $year, $start, $end, $conceptFilter, $conditionFilter);
            return response()->json([
                'success'    => true,
                'sections'   => $totals['sections'],
                'grandTotal' => $totals['grandTotal']
            ]);
        }

        if ($request->ajax()) {
            $data = Budget::select('id', 'commercialID', 'user_type', 'company', 'commercialName', 'concept', 'subTotal', 'vat', 'total', 'condition', 'status', 'created_by', 'created_at', 'licensedConcept', 'licensedEnvironment', 'budget_month', 'budget_year', 'begin_month', 'begin_year', 'finish_month', 'finish_year', 'billing_frequency', 'annual_value', 'total_months', 'monthly_value', 'license_pdf', 'category', 'subcategory');

            // Advanced filters
            if ($request->filled('commercialName')) {
                $data->where('commercialName', 'like', '%' . $request->commercialName . '%');
            }
            if ($request->filled('company')) {
                $data->where('company', 'like', '%' . $request->company . '%');
            }
            if ($request->filled('category')) {
                $data->where('category', $request->category);
            }
            if ($request->filled('subcategory')) {
                $data->where('subcategory', $request->subcategory);
            }
            if ($request->filled('licensedConcept')) {
                $data->where('licensedConcept', $request->licensedConcept);
            }
            if ($request->filled('licensedEnvironment')) {
                $data->where('licensedEnvironment', $request->licensedEnvironment);
            }
            if ($request->filled('statusFilter')) {
                $data->where('status', $request->statusFilter);
            }

            // Month/Year filters - only apply if they have values
            if ($request->filled('month') && $request->month != '') {
                $data->where('budget_month', $request->month);
            }
            if ($request->filled('year') && $request->year != '') {
                $data->where('budget_year', $request->year);
            }

            // *** CONCEPT FILTER (User Type) ***
            if ($request->filled('conceptFilter') && $request->conceptFilter != '') {
                $data->where('user_type', 'like', '%' . $request->conceptFilter . '%');
            }

            // *** CONDITION FILTER ***
            if ($request->filled('conditionFilter') && $request->conditionFilter != '') {
                $data->where('condition', $request->conditionFilter);
            }

            // Apply timeframe filter (Initial/Final months)
            $this->applyTimeframeFilter($data, $request->start_date, $request->end_date);

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('created_at', function ($row) {
                    if (!empty($row->created_at)) {
                        return date('d-m-Y', strtotime($row->created_at));
                    }
                })
                ->addColumn('company', fn($row) => $row->company ?: 'N/A')
                ->addColumn('commercialName', fn($row) => $row->commercialName ?: 'N/A')
                ->addColumn('user_type', fn($row) => $row->user_type ?: 'N/A')
                ->addColumn('concept', fn($row) => $row->concept ? Str::limit($row->concept, 50) : 'N/A')
                ->addColumn('subTotal', fn($row) => $this->formatCurrency($row->subTotal))
                ->addColumn('vat', fn($row) => $row->vat . '%')
                ->addColumn('total', fn($row) => $this->formatCurrency($row->total))
                ->addColumn('annual_value', fn($row) => $this->formatCurrency($row->annual_value))
                ->addColumn('condition', function ($row) {
                    $badges = [
                        1 => '<span class="badge bg-primary">Portfolio</span>',
                        2 => '<span class="badge bg-primary">New Agreement</span>',
                        3 => '<span class="badge bg-primary">Awaiting Purchase Order</span>',
                        4 => '<span class="badge bg-primary">Acuerdos</span>',
                        5 => '<span class="badge bg-primary">Others</span>'
                    ];
                    return $badges[$row->condition] ?? '<span class="badge bg-secondary">N/A</span>';
                })
                ->addColumn('created_by', function ($row) {
                    if (!empty($row->created_by)) {
                        $creator = User::where('id', $row->created_by)->pluck('name')->first();
                        return $creator;
                    }
                    return 'N/A';
                })
                ->addColumn('status', function ($row) {
                    if ($row->status == 1) {
                        return '<span class="badge bg-warning">Pending</span>';
                    } elseif ($row->status == 2) {
                        return '<span class="badge bg-success">Invoiced</span>';
                    } elseif ($row->status == 3) {
                        return '<span class="badge bg-danger">Discarded</span>';
                    } else {
                        return '<span class="badge bg-secondary">N/A</span>';
                    }
                })
                ->addColumn('action', function ($row) {
                    $dataId = $row->id;
                    $viewRoute = route("budget.view", $row->id);
                    $editRoute = route("budget.edit", $row->id);
                    $isInvoiced = $row->status == 2;
                    $invoiceIconColor = $isInvoiced ? 'text-secondary' : '';
                    $invoiceDisabled = $isInvoiced ? 'disabled' : '';
                    $invoiceTitle = $isInvoiced ? 'Already Invoiced' : 'Register Invoice';

                    $pdfBtn = '';

                    $buttons = '<div class="btn-group" role="group">
                        <a href="' . $editRoute . '" class="btn btn-soft-primary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                            <iconify-icon icon="solar:pen-new-square-linear" class="fs-18"></iconify-icon>
                        </a>
                        <a href="' . $viewRoute . '" class="btn btn-soft-info btn-sm" title="View" data-bs-toggle="tooltip" data-bs-placement="top">
                            <iconify-icon icon="solar:eye-bold" class="fs-18"></iconify-icon>
                        </a>'
                        . $pdfBtn .
                        '<a href="javascript:void(0)" class="btn btn-soft-danger btn-sm" onclick="deleteActivity(' . $dataId . ')" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                            <iconify-icon icon="solar:trash-bin-trash-bold" class="fs-18"></iconify-icon>
                        </a>
                        <a href="javascript:void(0)" class="btn btn-soft-success btn-sm ' . $invoiceDisabled . '" onclick="' . ($isInvoiced ? 'return false;' : 'invoiceGenerate(' . $dataId . ')') . ' " data-bs-toggle="tooltip" data-bs-placement="top" title="' . $invoiceTitle . '">
                            <iconify-icon icon="solar:file-download-outline" class="fs-18 ' . $invoiceIconColor . '"></iconify-icon>
                        </a>
                    </div>';

                    return $buttons;
                })
                ->addColumn('row_class', function ($row) {
                    $endOfMonth = Carbon::now()->endOfMonth();
                    $daysUntilEndOfMonth = Carbon::now()->diffInDays($endOfMonth, false);
                    if ($row->status != 2 && $daysUntilEndOfMonth <= 10 && $daysUntilEndOfMonth >= 0) {
                        return 'table-danger';
                    }
                    return '';
                })
                ->rawColumns(['action', 'created_at', 'status', 'condition', 'created_by', 'user_type', 'commercialName', 'total', 'subTotal', 'vat', 'company', 'concept'])
                ->make(true);
        }

        return response()->json(['error' => 'Unauthorized'], 403);
    }

    public function create()
    {
        $pageTitle = 'Add Budget';
        $clients = Clients::all();
        $userTypes = UseTypes::where('use_types_status', 1)->get();
        return view('budget.create', compact('clients', 'pageTitle', 'userTypes'));
    }

    public function getUserType($clientId)
    {
        $client = Clients::find($clientId);
        if (!$client) {
            return response()->json([
                'userType' => '',
                'licensedConcept' => '',
                'licensedConceptId' => '',
                'licensedEnvironment' => '',
                'licensedEnvironmentId' => '',
                'billingFrequency' => '',
            ]);
        }
        $useTypes = UseTypes::where('id', $client->useTypes)->first();
        $license  = LicensesAgreements::where('commercialID', $clientId)->first();
        $useTypesVal = '';
        $licensedEnvironment = '';
        $licensedEnvironmentId = '';

        if ($license && !empty($license->licensedEnvironment)) {
            $licensedEnvironmentId = $license->licensedEnvironment;
            // Handle multipick (array or single value)
            $envMap = Environment::pluck('name', 'id')->toArray();
            if (is_array($license->licensedEnvironment)) {
                $names = array_map(fn($id) => $envMap[$id] ?? '', $license->licensedEnvironment);
                $licensedEnvironment = implode(', ', $names);
            } else {
                $decoded = json_decode($license->licensedEnvironment, true);
                if (is_array($decoded)) {
                    $names = array_map(fn($id) => $envMap[$id] ?? '', $decoded);
                    $licensedEnvironment = implode(', ', $names);
                } else {
                    $licensedEnvironment = $envMap[$license->licensedEnvironment] ?? '';
                }
            }
        }

        if ($useTypes && !empty($useTypes->use_types_name)) {
            $useTypesVal = $useTypes->use_types_name;
        }
        $category = ClientCategory::where('id', $client->categoryID)->first();
        $categoryVal = '';
        if (!empty($category->category_name)) {
            $categoryVal = $category->category_name;
        }

        $subcategory = ClientSubCategory::where('id', $client->subcategoryID)->first();
        $subcategoryVal = '';
        if (!empty($subcategory->subcategory_name)) {
            $subcategoryVal = $subcategory->subcategory_name;
        }
        $billingFrequency = $license->billing_frequency ?? 'Monthly';

        $companyVal = '';
        if (!empty($client->legalName)) {
            $companyVal = $client->legalName;
        }

        return response()->json([
            'userType' => $useTypesVal,
            'licensedEnvironment' => $licensedEnvironment,
            'licensedEnvironmentId' => $licensedEnvironmentId,
            'billingFrequency' => $billingFrequency,
            'categoryVal' => $categoryVal ?? '',
            'subcategoryVal' => $subcategoryVal ?? '',
            'companyVal' => $companyVal ?? '',
            // NEW: Return budget dates from license
            'beginMonth' => $license->begin_month ?? null,
            'beginYear' => $license->begin_year ?? null,
            'finishMonth' => $license->finish_month ?? null,
            'finishYear' => $license->finish_year ?? null,
        ]);
    }

    public function store(Request $request)
    {
        $userID = Auth::id();

        $request->validate([
            'commercialName' => 'required',
            'user_type'      => 'required',
            'company'        => 'required',
            'begin_month'    => 'required|integer|min:1|max:12',
            'begin_year'     => 'required|integer|min:2000|max:2100',
            'finish_month'   => 'required|integer|min:1|max:12',
            'finish_year'    => 'required|integer|min:2000|max:2100',
        ]);

        $client  = Clients::findOrFail($request->commercialName);
        $license = LicensesAgreements::where('commercialID', $client->id)->first();

        $annualValue = $request->annual_value
            ? $this->parseFormattedNumber($request->annual_value)
            : null;

        $vatPercent = (float) ($request->vat ?? 0);
        $beginMonth  = (int) $request->begin_month;
        $beginYear   = (int) $request->begin_year;
        $finishMonth = (int) $request->finish_month;
        $finishYear  = (int) $request->finish_year;

        $monthsTotal = (($finishYear - $beginYear) * 12)
            + ($finishMonth - $beginMonth) + 1;

        $monthlyValue = $annualValue
            ? round($annualValue / $monthsTotal, 2)
            : $this->parseFormattedNumber($request->subTotal);

        $vatAmount = $monthlyValue * ($vatPercent / 100);
        $total     = $monthlyValue + $vatAmount;
        $baseData = [
            'commercialID'   => $client->id,
            'commercialName' => $client->commercialName,
            'user_type'      => $request->user_type,
            'company'        => $request->company,
            'created_by'     => $userID,

            'begin_month' => $beginMonth,
            'begin_year'  => $beginYear,
            'finish_month' => $finishMonth,
            'finish_year' => $finishYear,

            'billing_frequency' => $request->newFrequency,
            'annual_value'      => $annualValue,
            'total_months'      => $monthsTotal,
            'monthly_value'     => $monthlyValue,

            'subTotal' => $monthlyValue,
            'vat'      => $vatPercent,
            'total'    => $total,

            'condition' => $request->condition,
            'status'    => $request->status,

            'licensedConcept'     => $request->user_type,
            'licensedEnvironment' => $request->licensedEnvironment ?? ($license->licensedEnvironment ?? null),
            'category'            => $request->category ?? ($license->category ?? null),
            'subcategory'         => $request->subcategory ?? ($license->subcategory ?? null),
            'concept'             => 'Auto-generated from License',
        ];

        $this->createMonthlyBudgets(
            $baseData,
            $beginMonth,
            $beginYear,
            $finishMonth,
            $finishYear
        );
        return redirect()->route('budgets')->with('success', 'Budget created for all months successfully.');
    }

    public function edit(Request $request, $id)
    {
        $pageTitle = 'Edit Budget';
        $budget = Budget::findOrFail($id);
        $clients = Clients::all();
        $userTypes = UseTypes::where('use_types_status', 1)->get();
        return view('budget.edit', compact('clients', 'pageTitle', 'userTypes', 'budget'));
    }

    public function update(Request $request, $id)
    {
        $userID = Auth::user()->id;

        $request->validate([
            'commercialName' => 'required',
            'user_type'      => 'required',
            'company'        => 'required',
            'frequency'      => 'nullable|in:Monthly,Quarterly,Annual',
            'begin_month'    => 'nullable|integer|min:1|max:12',
            'begin_year'     => 'nullable|integer|min:2000|max:2100',
            'finish_month'   => 'nullable|integer|min:1|max:12',
            'finish_year'    => 'nullable|integer|min:2000|max:2100',
            'annual_value'   => 'nullable|numeric|min:0',
            'license_pdf'    => 'nullable|file|mimes:pdf|max:20480',
        ]);

        // Parse formatted numbers
        $annualValue = $request->annual_value !== null ? $this->parseFormattedNumber($request->annual_value) : null;
        $subTotal = $request->subTotal ? $this->parseFormattedNumber($request->subTotal) : null;
        $total = $request->total ? $this->parseFormattedNumber($request->total) : null;

        $clientName = Clients::where('id', $request->commercialName)->first();
        $budget = Budget::where('id', $id)->firstOrFail();

        $budget->commercialID   = $request->commercialName;
        $budget->commercialName = $clientName->commercialName;
        $budget->user_type      = $request->user_type;
        $budget->company        = $request->company;

        $vatPercent = (float)($request->vat ?? 0);

        $bMonth = (int)($request->begin_month ?? $budget->begin_month ?? date('n'));
        $bYear  = (int)($request->begin_year  ?? $budget->begin_year  ?? date('Y'));

        $calc = $this->buildCalculatedBudget(
            $request->frequency ?? $budget->billing_frequency,
            $annualValue !== null ? $annualValue : $budget->annual_value,
            $bMonth,
            $bYear,
            $request->finish_month ? (int)$request->finish_month : ($budget->finish_month ?? null),
            $request->finish_year  ? (int)$request->finish_year  : ($budget->finish_year ?? null),
            $vatPercent
        );

        $budget->budget_month = $request->budget_month ?? $bMonth;
        $budget->budget_year  = $request->budget_year  ?? $bYear;

        $budget->begin_month    = $bMonth;
        $budget->begin_year     = $bYear;
        $budget->finish_month   = $calc['finish_month'];
        $budget->finish_year    = $calc['finish_year'];
        $budget->billing_frequency = $calc['frequency'];
        $budget->annual_value   = $annualValue !== null ? $annualValue : $budget->annual_value;
        $budget->total_months   = $calc['months_total'];
        $budget->monthly_value = $calc['monthly_amount'];

        if ($annualValue !== null) {
            $budget->subTotal = $calc['subTotal'];
            $budget->vat      = $vatPercent;
            $budget->total    = $calc['total'];
        } else {
            $budget->subTotal = $subTotal ?? $budget->subTotal;
            $budget->vat      = $vatPercent;
            $budget->total    = $total ?? ($budget->subTotal + ($budget->subTotal * $vatPercent / 100));
        }

        $budget->condition           = $request->condition;
        $budget->status              = $request->status;
        $budget->licensedConcept     = $request->licensedConcept ?? $budget->licensedConcept;
        $budget->licensedEnvironment = $request->licensedEnvironment ?? $budget->licensedEnvironment;
        $budget->category = $request->category ?? $budget->category;
        $budget->subcategory = $request->subcategory ?? $budget->subcategory;

        if ($budget->save()) {
            return redirect()->route('budgets')->with('success', 'Budget is updated successfully.');
        } else {
            return redirect()->back()->with('error', 'Something went wrong!');
        }
    }

    private function createMonthlyBudgets(
        array $baseData,
        int $beginMonth,
        int $beginYear,
        int $finishMonth,
        int $finishYear
    ) {
        $start = Carbon::create($beginYear, $beginMonth, 1);
        $end   = Carbon::create($finishYear, $finishMonth, 1);

        while ($start <= $end) {

            Budget::create(array_merge($baseData, [
                'budget_month' => (int) $start->month,
                'budget_year'  => (int) $start->year,
            ]));

            $start->addMonth();
        }
    }


    public function view(Request $request, $id)
    {
        $pageTitle = 'View Budget';

        $budget = Budget::findOrFail($id);

        $client           = Clients::find($budget->commercialID);
        $userTypeName     = UseTypes::where('id', $budget->user_type)->value('use_types_name');
        $creatorName      = User::where('id', $budget->created_by)->value('name');
        $categoryName     = ClientCategory::where('id', $client?->categoryID)->value('category_name');
        $subcategoryName  = ClientSubCategory::where('id', $client?->subcategoryID)->value('subcategory_name');

        $conditionMap = [
            1 => 'Awaiting Purchase Order',
            2 => 'Invoiced',
            3 => 'New Agreement',
            4 => 'Portfolio',
        ];
        $statusMap = [
            1 => 'Pending',
            2 => 'Invoiced',
            3 => 'Discarded',
        ];

        $conditionLabel = $conditionMap[$budget->condition] ?? 'N/A';
        $statusLabel    = $statusMap[$budget->status] ?? 'N/A';

        $envMap = [
            1 => 'Musical Ambience',
            2 => 'Public Establishments',
            3 => 'Public Events',
            4 => 'Broadcasting',
            5 => 'WebCasting',
            6 => 'SimulCasting',
            7 => 'Subscription TV Operators',
            8 => 'Social Networks',
        ];
        $licensedEnvironmentDisplay = '';
        if (is_array($budget->licensedEnvironment)) {
            $licensedEnvironmentDisplay = implode(', ', array_map(fn($id) => $envMap[$id] ?? $id, $budget->licensedEnvironment));
        } elseif (is_string($budget->licensedEnvironment) && preg_match('/^\d+(,\d+)*$/', $budget->licensedEnvironment)) {
            $licensedEnvironmentDisplay = collect(explode(',', $budget->licensedEnvironment))
                ->map(fn($id) => $envMap[(int)$id] ?? $id)->implode(', ');
        } else {
            $licensedEnvironmentDisplay = $envMap[$budget->licensedEnvironment] ?? ($budget->licensedEnvironment ?: 'N/A');
        }

        $beginYm   = $budget->begin_year && $budget->begin_month
            ? sprintf('%s %s', date('F', mktime(0, 0, 0, (int)$budget->begin_month, 1)), $budget->begin_year)
            : 'N/A';
        $finishYm  = $budget->finish_year && $budget->finish_month
            ? sprintf('%s %s', date('F', mktime(0, 0, 0, (int)$budget->finish_month, 1)), $budget->finish_year)
            : 'N/A';
        $budgetYm  = ($budget->budget_month && $budget->budget_year)
            ? sprintf('%s %s', date('F', mktime(0, 0, 0, (int)$budget->budget_month, 1)), $budget->budget_year)
            : 'N/A';

        $fmt = fn($n) => '$' . number_format((float)$n, 2, ',', '.');
        $display = [
            'commercialName'      => $budget->commercialName ?? 'N/A',
            'company'             => $budget->company ?? ($client->legalName ?? 'N/A'),
            'licensedConcept'     => $budget->licensedConcept ?: ($userTypeName ?: 'N/A'),
            'licensedEnvironment' => $licensedEnvironmentDisplay,
            'category'            => $budget->category ?: ($categoryName ?: 'N/A'),
            'subcategory'         => $budget->subcategory ?: ($subcategoryName ?: 'N/A'),
            'concept'             => $budget->concept ?: 'N/A',

            'frequency'           => $budget->billing_frequency ?: 'monthly',
            'annual_value'        => $budget->annual_value ? $fmt($budget->annual_value) : 'N/A',
            'total_months'        => $budget->total_months ?: 'N/A',
            'monthly_value'       => $budget->monthly_value ? $fmt($budget->monthly_value) : 'N/A',

            'subTotal'            => $fmt($budget->subTotal),
            'vat'                 => is_numeric($budget->vat) ? $budget->vat . '%' : 'N/A',
            'total'               => $fmt($budget->total),

            'condition'           => $conditionLabel,
            'status'              => $statusLabel,

            'begin_period'        => $beginYm,
            'finish_period'       => $finishYm,
            'budget_period'       => $budgetYm,

            'created_by'          => $creatorName ?: 'N/A',
            'created_at'          => $budget->created_at ? $budget->created_at->format('d-m-Y') : 'N/A',
        ];
        return view('budget.view', compact('pageTitle', 'budget', 'display'));
    }

    public function destroy(Request $request, $id)
    {
        $budget = Budget::find($id);
        if (!$budget) {
            return response()->json(['error' => 'Record not found!'], 404);
        }

        // Ã°Å¸â€Â¥ CASCADE DELETE: Delete all connected records

        // 1. Get all invoices for this budget
        $invoices = RegisterInvoice::where('budgetID', $id)->get();

        foreach ($invoices as $invoice) {
            // Delete cash receipts for this invoice
            CashReceipt::where('invoice_id', $invoice->id)->delete();

            // Delete credit notes for this invoice
            CreditNote::where('invoice_id', $invoice->id)->delete();

            // Delete portfolio comments for this invoice
            PortfolioComment::where('invoice_id', $invoice->id)->delete();

            // Delete validation items for this invoice
            ValidationItem::where('item_type', 'invoice')
                ->where('item_id', $invoice->id)
                ->delete();

            // Delete income records linked to this invoice
            $incomeRecords = IncomeRecord::where('invoice_id', $invoice->id)->get();
            foreach ($incomeRecords as $income) {
                // Delete distributions linked to this income
                Distribution::where('income_id', $income->id)->delete();

                // Delete the income record
                $income->delete();
            }

            // Delete the invoice itself
            $invoice->delete();
        }

        // 2. Delete validation items for this budget
        ValidationItem::where('item_type', 'budget')
            ->where('item_id', $id)
            ->delete();

        // 3. Delete the budget
        $budget->delete();

        return response()->json(['success' => 'Budget and all connected records deleted successfully!']);
    }


    public function getBudgetRecord(Request $request, $id)
    {
        $budget = Budget::find($id);
        if (!$budget) {
            return response()->json(['error' => 'Budget not found'], 404);
        }
        $userTypeName = $budget->user_type;
        $creator = User::where('id', $budget->created_by)->pluck('name')->first();
        $conditions = [
            1 => 'Awaiting Purchase Order',
            2 => 'Invoiced',
            3 => 'New Agreement',
            4 => 'Portfolio'
        ];
        $condition = $conditions[$budget->condition] ?? 'N/A';
        $statuses = [
            1 => 'Pending',
            2 => 'Invoiced',
            3 => 'Discarded'
        ];
        $status = $statuses[$budget->status] ?? 'N/A';
        $licensedConceptName = $budget->licensedConcept ?? 'N/A';
        $licensedEnvironmentName = $budget->licensedEnvironment ?? 'N/A';

        $criterions = BudgetCriterion::get();
        $consecutives = InvoiceConsecutive::get();

        return response()->json([
            'budgetID' => $budget->id,
            'userTypeName' => $userTypeName,
            'company' => $budget->company,
            'commercialName' => $budget->commercialName,
            'concept' => $budget->concept,
            'subTotal' => number_format($budget->subTotal, 2, ',', '.'),
            'vat' => $budget->vat,
            'total' => number_format($budget->total, 2, ',', '.'),
            'creator' => $creator,
            'condition' => $condition,
            'status' => $status,
            'licensedConcept' => $licensedConceptName,
            'licensedEnvironment' => $licensedEnvironmentName,
            'category' => $budget->category,
            'subcategory' => $budget->subcategory,
            'criterions' => $criterions,
            'consecutives' => $consecutives,
        ]);
    }

    public function generateInvoice(Request $request)
    {
        $validated = $request->validate([
            'budgetID' => 'required',
            'criterion' => 'required',
            'invoiceConsecutive' => 'required|exists:invoice_consecutive,id',
            'invoice_date' => 'nullable|date',
        ]);

        try {
            DB::beginTransaction();

            $budget = Budget::where('id', $request->budgetID)->first();
            if (!$budget) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Budget not found.',
                ], 404);
            }

            // Allocate invoice number based on selected consecutive (supports manual starting number)
            $consecutive = InvoiceConsecutive::where('id', $request->invoiceConsecutive)
                ->lockForUpdate()
                ->first();

            if (!$consecutive) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Invoice consecutive not found.',
                ], 404);
            }

            $prefix = trim((string) $consecutive->consecutive_name);
            $nextNumber = (int) ($consecutive->next_number ?: 1);

            $invoiceNumber = ($prefix !== '' ? $prefix . '-' : 'INV-') . $nextNumber;
            $consecutive->next_number = $nextNumber + 1;
            $consecutive->save();

            $invoiceDate = $request->invoice_date
                ? Carbon::parse($request->invoice_date)
                : now();

            $invoice = RegisterInvoice::create([
                'budgetID' => $request->budgetID,
                'invoiceConsecutive' => $request->invoiceConsecutive,
                'invoiceNumber' => $invoiceNumber,
                'invoiceDate' => $invoiceDate,
                'periodPaid' => $request->periodPaid,
                'paidPeriod' => $request->paidPeriod,
                'criterion' => $request->criterion,
                'licensedConcept' => $budget->licensedConcept,
                'licensedEnvironment' => $budget->licensedEnvironment,
                'commercialID' => $budget->commercialID,
                'user_type' => $budget->user_type,
                'company' => $budget->company,
                'commercialName' => $budget->commercialName,
                'subTotal' => $budget->subTotal,
                'vat' => $budget->vat,
                'total' => $budget->total,
                'created_by' => auth()->id(),
            ]);

            // Auto-create billing_list record for new invoice
            DB::table('billing_list')->insert([
                'invoiceID'            => $invoice->id,
                'commercialID'         => $invoice->commercialID,
                'user_type'            => $invoice->user_type,
                'company'              => $invoice->company,
                'commercialName'       => $invoice->commercialName,
                'concept'              => $invoice->licensedConcept,
                'licensedConcept'      => $invoice->licensedConcept,
                'licensedEnvironment'  => $invoice->licensedEnvironment,
                'invoiceNumber'        => (string) ($invoiceNumber ?? $invoice->invoiceNumber),
                'invoiceDate'          => $invoice->invoiceDate,
                'periodPaid'           => $invoice->periodPaid,
                'paidPeriod'           => $invoice->paidPeriod,
                'criterion'            => $invoice->criterion,
                'subTotal'             => $invoice->subTotal,
                'vat'                  => $invoice->vat,
                'total'                => $invoice->total,
                'balance'              => $invoice->total,
                'createdBy'            => auth()->id(),
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);

            $budget->status = 2;
            $budget->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Invoice generated and registered successfully! Invoice Number: ' . $invoiceNumber,
                'invoice_id' => $invoice->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Unable to generate the invoice. Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getInvoiceData(Request $request)
    {
        if ($request->ajax()) {
            $start = $request->get('start_date');
            $end   = $request->get('end_date');
            $status = $request->get('status');
            $commercial_name = $request->get('commercial_name');
            $client_category = $request->get('client_category');
            $concept = $request->get('concept');
            $criterion = $request->get('criterion');

            $data = RegisterInvoice::select(
                'register_invoice.id',
                'register_invoice.budgetID',
                'register_invoice.invoiceNumber',
                'register_invoice.invoiceConsecutive',
                'register_invoice.invoiceDate',
                'register_invoice.periodPaid',
                'register_invoice.criterion',
                'register_invoice.created_by',
                'register_invoice.created_at',
                'register_invoice.subTotal',
                'register_invoice.vat',
                'register_invoice.total',
                'register_invoice.licensedConcept',
                'budget.commercialName',
                'budget.commercialID',
                'budget.category',
                'budget.subcategory'
            )
                ->leftJoin('budget', 'register_invoice.budgetID', '=', 'budget.id') // Fixed table name
                ->when($start, fn($q) => $q->whereDate('register_invoice.invoiceDate', '>=', $start))
                ->when($end, fn($q) => $q->whereDate('register_invoice.invoiceDate', '<=', $end))
                ->when($status, function ($q) use ($status) {
                    if ($status === 'P') {
                        $q->whereHas('cashReceipts', function ($query) {
                            $query->whereNotNull('receipt_no');
                        });
                    } elseif ($status === 'C') {
                        $q->whereHas('creditNotes', function ($query) {
                            $query->whereNotNull('cn_number');
                        });
                    } elseif ($status === 'B') {
                        $q->whereHas('cashReceipts', function ($query) {
                            $query->whereNotNull('receipt_no');
                        })->whereRaw('(SELECT COALESCE(SUM(amount), 0) FROM cash_receipts WHERE invoice_id = register_invoice.id) < register_invoice.total');
                    } elseif ($status === 'D') {
                        $q->whereDate('register_invoice.invoiceDate', '<', now()->subDays(30))
                            ->whereDoesntHave('cashReceipts', function ($query) {
                                $query->whereNotNull('receipt_no');
                            });
                    } elseif ($status === 'On') {
                        $q->whereDate('register_invoice.invoiceDate', '>=', now()->subDays(30))
                            ->whereDoesntHave('cashReceipts', function ($query) {
                                $query->whereNotNull('receipt_no');
                            })
                            ->whereDoesntHave('creditNotes', function ($query) {
                                $query->whereNotNull('cn_number');
                            });
                    }
                })
                ->when($commercial_name, function ($q) use ($commercial_name) {
                    $q->where('budget.commercialName', 'like', '%' . $commercial_name . '%'); // Fixed table name
                })
                ->when($client_category, function ($q) use ($client_category) {
                    $q->where('budget.category', $client_category); // Fixed table name
                })
                ->when($concept, function ($q) use ($concept) {
                    $q->where('register_invoice.licensedConcept', 'like', '%' . $concept . '%');
                })
                ->when($criterion, function ($q) use ($criterion) {
                    $q->where('register_invoice.criterion', $criterion);
                });

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('invoiceNumber', fn($row) => $row->invoiceNumber ?: 'N/A')
                ->addColumn('invoiceDate', function ($row) {
                    if (!empty($row->invoiceDate)) {
                        return date('d-m-Y', strtotime($row->invoiceDate));
                    }
                    return 'N/A';
                })
                ->addColumn('invoiceConsecutive', fn($row) => $row->invoiceConsecutive ?: 'N/A')
                ->addColumn('created_at', function ($row) {
                    if (!empty($row->created_at)) {
                        return date('d-m-Y', strtotime($row->created_at));
                    }
                })
                ->addColumn('commercialName', function ($row) {
                    return $row->commercialName ?? 'N/A';
                })
                ->addColumn('criterion', function ($row) {
                    $criterion = \App\Models\BudgetCriterion::where('id', $row->criterion)->first();

                    if ($criterion && isset($criterion->criterion_name)) {
                        return $criterion->criterion_name;
                    }

                    return 'N/A';
                })
                ->addColumn('subTotal', fn($row) => $this->formatCurrency($row->subTotal))
                // ->addColumn('vat', fn($row) => $row->vat.'%')
                ->addColumn('vat', function ($row) {
                    if (!empty($row->subTotal) && !empty($row->total)) {
                        return $this->formatCurrency(round($row->total - $row->subTotal));
                    } else {
                        return 'N/A';
                    }
                })
                ->addColumn('total', fn($row) => $this->formatCurrency($row->total))
                ->addColumn('created_by', function ($row) {
                    if (!empty($row->created_by)) {
                        $creator = User::where('id', $row->created_by)->pluck('name')->first();
                        return $creator;
                    }
                    return 'N/A';
                })
                ->addColumn('client_category', function ($row) {
                    $budget  = Budget::findOrFail($row->budgetID);
                    $client  = Clients::find($budget->commercialID);
                    $categoryName = ClientCategory::where('id', $client?->categoryID)->value('category_name');
                    return $categoryName ?? 'N/A';
                })
                ->addColumn('sub_category', function ($row) {
                    $budget  = Budget::findOrFail($row->budgetID);
                    $client  = Clients::find($budget->commercialID);
                    $subcategoryName = ClientSubCategory::where('id', $client?->subcategoryID)->value('subcategory_name');
                    return $subcategoryName ?? 'N/A';
                })
                ->addColumn('period', function ($row) {
                    return $row->periodPaid ?? 'N/A';
                })
                ->addColumn('concept', function ($row) {
                    return $row->licensedConcept ?? 'N/A';
                })
                ->addColumn('status', function ($row) {
                    $cashReceipts = CashReceipt::where('invoice_id', $row->id)->get();
                    $creditNotes = CreditNote::where('invoice_id', $row->id)->get();

                    $totalPaid = $cashReceipts->sum('amount');
                    $hasCreditNote = $creditNotes->count() > 0;
                    $hasCashReceipt = $cashReceipts->count() > 0;
                    $isOverdue = $row->invoiceDate && now()->diffInDays($row->invoiceDate) > 30;

                    if ($hasCreditNote) {
                        return '<span class="badge bg-secondary">Canceled</span>';
                    } elseif ($totalPaid >= $row->total) {
                        return '<span class="badge bg-primary">Paid</span>';
                    } elseif ($hasCashReceipt && $totalPaid > 0) {
                        return '<span class="badge bg-info">Balance</span>';
                    } elseif ($isOverdue && !$hasCashReceipt) {
                        return '<span class="badge bg-warning">Delay</span>';
                    } else {
                        // NEW: If RC exists, show "Paid" instead of "On Time"
                        if ($hasCashReceipt) {
                            return '<span class="badge bg-primary">Paid</span>';
                        }
                        return '<span class="badge bg-success">On Time</span>';
                    }
                })
                ->addColumn('rc_nc', function ($row) {
                    $cashReceipts = CashReceipt::where('invoice_id', $row->id)->get();
                    $creditNotes = CreditNote::where('invoice_id', $row->id)->get();

                    $rcNumbers = $cashReceipts->pluck('receipt_no')->filter()->map(function ($rc) {
                        return 'RC ' . $rc;
                    })->implode(', ');

                    $ncNumbers = $creditNotes->pluck('cn_number')->filter()->map(function ($nc) {
                        return 'NC ' . $nc;
                    })->implode(', ');

                    if ($rcNumbers && $ncNumbers) {
                        return $rcNumbers . ', ' . $ncNumbers;
                    } elseif ($rcNumbers) {
                        return $rcNumbers;
                    } elseif ($ncNumbers) {
                        return $ncNumbers;
                    } else {
                        return '-';
                    }
                })
                ->addColumn('action', function ($row) {
                    $creditNote = CreditNote::where('invoice_id', $row->id)->first();
                    if ($creditNote) {
                        $buttons = '<button type="button" class="btn btn-soft-secondary btn-sm" disabled
                            title="Credit Note already registered">
                            Credit Note
                        </button>';
                    } else {
                        $buttons = '<button type="button" class="btn btn-soft-warning btn-sm"
                            onclick="openCreditNoteModal(' . $row->id . ')"
                            title="Register Credit Note"><iconify-icon icon="solar:add-circle-linear" class="fs-18"></iconify-icon>
                        </button>';
                    }
                    $viewRoute = route('get-invoice.view', $row->id);
                    $buttons .= '<a href="' . $viewRoute . '" class="btn btn-soft-primary btn-sm"
                        title="View" data-bs-toggle="tooltip" data-bs-placement="top" target="_blank">
                        <iconify-icon icon="solar:eye-bold" class="fs-18"></iconify-icon>
                    </a>';
                    return $buttons;
                })
                ->rawColumns(['action', 'created_at', 'invoiceDate', 'invoiceNumber', 'invoiceConsecutive', 'commercialName', 'subTotal', 'vat', 'total', 'status'])
                ->make(true);
        }
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    public function viewGetInvoice(Request $request, $id)
    {
        $pageTitle = 'View Invoice';

        $invoice = RegisterInvoice::findOrFail($id);
        $budget  = Budget::findOrFail($invoice->budgetID);
        $client  = Clients::find($budget->commercialID);

        // Lookups
        $userTypeName    = UseTypes::where('id', $budget->user_type)->value('use_types_name');
        $creatorName     = User::where('id', $invoice->created_by)->value('name') ?: User::where('id', $budget->created_by)->value('name');
        $categoryName    = ClientCategory::where('id', $client?->categoryID)->value('category_name');
        $subcategoryName = ClientSubCategory::where('id', $client?->subcategoryID)->value('subcategory_name');

        // Mappings
        $criterionMap = [
            1 => 'Min. Guaranteed, 8% Income',
            2 => 'Min. Guaranteed + 8%',
            3 => 'Monthly Fee',
            4 => 'Annual Fee',
            5 => 'Special Arrangement',
        ];
        $paidPeriodMap = [
            1 => 'Month and Year',
            2 => 'Year Only',
            3 => 'Multiple Years',
        ];
        $statusMap = [
            1 => 'Pending',
            2 => 'Invoiced',
            3 => 'Discarded',
        ];
        $conditionMap = [
            1 => 'Awaiting Purchase Order',
            2 => 'Invoiced',
            3 => 'New Agreement',
            4 => 'Portfolio',
        ];
        $envMap = [
            1 => 'Musical Ambience',
            2 => 'Public Establishments',
            3 => 'Public Events',
            4 => 'Broadcasting',
            5 => 'WebCasting',
            6 => 'SimulCasting',
            7 => 'Subscription TV Operators',
            8 => 'Social Networks',
        ];

        // License environment display: supports array / CSV / plain text
        $licensedEnvironmentDisplay = '';
        $le = $budget->licensedEnvironment;
        if (is_array($le)) {
            $licensedEnvironmentDisplay = implode(', ', array_map(fn($id) => $envMap[$id] ?? $id, $le));
        } elseif (is_string($le) && preg_match('/^\d+(,\d+)*$/', $le)) {
            $licensedEnvironmentDisplay = collect(explode(',', $le))
                ->map(fn($id) => $envMap[(int)$id] ?? $id)->implode(', ');
        } else {
            $licensedEnvironmentDisplay = $envMap[$le] ?? ($le ?: 'N/A');
        }

        // Dates
        $invoiceDate = $invoice->invoiceDate ? Carbon::parse($invoice->invoiceDate)->format('d-m-Y') : 'N/A';
        $createdAt   = $invoice->created_at ? $invoice->created_at->format('d-m-Y') : 'N/A';
        $beginYm     = ($budget->begin_year && $budget->begin_month)
            ? sprintf('%s %s', date('F', mktime(0, 0, 0, (int)$budget->begin_month, 1)), $budget->begin_year)
            : 'N/A';
        $finishYm    = ($budget->finish_year && $budget->finish_month)
            ? sprintf('%s %s', date('F', mktime(0, 0, 0, (int)$budget->finish_month, 1)), $budget->finish_year)
            : 'N/A';

        // Currency formatter ($1,000.00)
        $fmt = fn($n) => '$' . number_format((float)$n, 2, ',', '.');

        // Calculated VAT amount (total - subtotal)
        $vatAmount = (float)$invoice->total - (float)$invoice->subTotal;

        // Frequency label normalizer
        $freqRaw = $budget->billing_frequency ?? null;
        $freqKey = is_string($freqRaw) ? strtolower(trim($freqRaw)) : (string)$freqRaw;
        $freqMap = ['1' => 'Monthly', '2' => 'Quarterly', '3' => 'Annual', '4' => 'One-Time Payment', 'monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'annual' => 'Annual', 'One-Time Payment' => 'One-Time Payment'];
        $frequencyText = $freqMap[$freqKey] ?? ($freqRaw ?: 'N/A');

        // Build one clean array for the blade
        $display = [
            // Invoice core
            'invoiceNumber'      => $invoice->invoiceNumber ?? 'N/A',
            'invoiceConsecutive' => $invoice->invoiceConsecutive ?? 'N/A',
            'invoiceDate'        => $invoiceDate,
            'paidPeriod'         => $paidPeriodMap[$invoice->paidPeriod] ?? 'N/A',
            'periodPaid'         => $invoice->periodPaid ?? 'N/A',
            'criterion'          => $criterionMap[$invoice->criterion] ?? 'N/A',
            'created_by'         => $creatorName ?: 'N/A',
            'created_at'         => $createdAt,

            // Client / license
            'commercialName'      => $budget->commercialName ?? 'N/A',
            'company'             => $budget->company ?? ($client->legalName ?? 'N/A'),
            'userType'            => $userTypeName ?: $budget->user_type ?: 'N/A',
            'licensedConcept'     => $budget->licensedConcept ?: ($userTypeName ?: 'N/A'),
            'licensedEnvironment' => $licensedEnvironmentDisplay,
            'category'            => $budget->category ?: ($categoryName ?: 'N/A'),
            'subcategory'         => $budget->subcategory ?: ($subcategoryName ?: 'N/A'),

            // Period / frequency (from budget)
            'begin_period'   => $beginYm,
            'finish_period'  => $finishYm,
            'frequency'      => $frequencyText,
            'total_months'   => $budget->total_months ?: 'N/A',

            // Amounts (from invoice row to reflect the exact booked values)
            'subTotal'   => $fmt($invoice->subTotal),
            'vat_rate'   => is_numeric($invoice->vat) ? $invoice->vat . '%' : 'N/A',
            'vat_amount' => $fmt($vatAmount),
            'total'      => $fmt($invoice->total),

            // Budget status/condition at the time
            'status'     => $statusMap[$budget->status] ?? 'N/A',
            'condition'  => $conditionMap[$budget->condition] ?? 'N/A',

            // Long text
            'concept'    => $budget->concept ?: 'N/A',
        ];

        return view('budget.invoice-view', compact('pageTitle', 'invoice', 'budget', 'display'));
    }

    public function invoiceConceptTotals(Request $request)
    {

        $start = $request->get('start_date');
        $end = $request->get('end_date');
        $status = $request->get('status');
        $commercialName = $request->get('commercial_name');
        $clientCategory = $request->get('client_category');
        $concept = $request->get('concept');
        $criterion = $request->get('criterion');

        $query = DB::table('register_invoice')
            ->select(
                'licensedConcept',
                DB::raw('SUM(subTotal) as subTotal'),
                DB::raw('SUM(total) as total')
            );

        if ($start) {
            $query->whereDate('invoiceDate', '>=', $start);
        }
        if ($end) {
            $query->whereDate('invoiceDate', '<=', $end);
        }

        if ($commercialName) {
            $query->where('commercialName', 'like', '%' . $commercialName . '%');
        }

        if ($clientCategory) {
            $query->join('budget', 'register_invoice.budgetID', '=', 'budget.id')
                ->where('budget.category', $clientCategory);
        }

        if ($concept) {
            $query->where('licensedConcept', 'like', '%' . $concept . '%');
        }

        if ($criterion) {
            $query->where('criterion', $criterion);
        }

        if ($status) {
            if ($status === 'P') {
                $query->whereExists(function ($q) {
                    $q->selectRaw('1')
                        ->from('cash_receipts')
                        ->whereColumn('cash_receipts.invoice_id', 'register_invoice.id')
                        ->havingRaw('COALESCE(SUM(cash_receipts.amount), 0) >= register_invoice.total');
                });
            } elseif ($status === 'C') {
                $query->whereExists(function ($q) {
                    $q->selectRaw('1')
                        ->from('credit_notes')
                        ->whereColumn('credit_notes.invoice_id', 'register_invoice.id');
                });
            } elseif ($status === 'B') {
                $query->whereExists(function ($q) {
                    $q->selectRaw('1')
                        ->from('cash_receipts')
                        ->whereColumn('cash_receipts.invoice_id', 'register_invoice.id')
                        ->havingRaw('COALESCE(SUM(cash_receipts.amount), 0) < register_invoice.total')
                        ->havingRaw('COALESCE(SUM(cash_receipts.amount), 0) > 0');
                });
            } elseif ($status === 'D') {
                $query->whereDate('invoiceDate', '<', now()->subDays(30))
                    ->whereNotExists(function ($q) {
                        $q->selectRaw('1')
                            ->from('cash_receipts')
                            ->whereColumn('cash_receipts.invoice_id', 'register_invoice.id');
                    });
            } elseif ($status === 'On') {
                $query->whereDate('invoiceDate', '>=', now()->subDays(30))
                    ->whereNotExists(function ($q) {
                        $q->selectRaw('1')
                            ->from('cash_receipts')
                            ->whereColumn('cash_receipts.invoice_id', 'register_invoice.id');
                    })
                    ->whereNotExists(function ($q) {
                        $q->selectRaw('1')
                            ->from('credit_notes')
                            ->whereColumn('credit_notes.invoice_id', 'register_invoice.id');
                    });
            }
        }

        $data = $query->groupBy('licensedConcept')->get();
        $data = $data->map(function ($row) {
            $row->vat = $row->total - $row->subTotal;
            return $row;
        });

        $grandSubTotal = $data->sum('subTotal');
        $grandTotal = $data->sum('total');
        $grandVat = $grandTotal - $grandSubTotal;

        $grand = [
            'subTotal' => $grandSubTotal,
            'vat' => $grandVat,
            'total' => $grandTotal
        ];

        return response()->json([
            'concepts' => $data,
            'grandTotal' => $grand
        ]);
    }

    public function invoiceDownload(Request $request)
    {
        $start = $request->get('start_date');
        $end   = $request->get('end_date');
        $status = $request->get('status');
        $commercial_name = $request->get('commercial_name');
        $client_category = $request->get('client_category');
        $concept = $request->get('concept');
        $criterion = $request->get('criterion');

        $data = RegisterInvoice::select(
            'register_invoice.id',
            'register_invoice.budgetID',
            'register_invoice.invoiceNumber',
            'register_invoice.invoiceConsecutive',
            'register_invoice.invoiceDate',
            'register_invoice.periodPaid',
            'register_invoice.criterion',
            'register_invoice.created_by',
            'register_invoice.created_at',
            'register_invoice.subTotal',
            'register_invoice.vat',
            'register_invoice.total',
            'register_invoice.licensedConcept',
            'budget.commercialName',
            'budget.commercialID',
            'budget.category',
            'budget.subcategory'
        )
            ->leftJoin('budget', 'register_invoice.budgetID', '=', 'budget.id')
            ->when($start, fn($q) => $q->whereDate('register_invoice.invoiceDate', '>=', $start))
            ->when($end, fn($q) => $q->whereDate('register_invoice.invoiceDate', '<=', $end))
            ->when($status, function ($q) use ($status) {
                if ($status === 'P') {
                    $q->whereHas('cashReceipts', function ($query) {
                        $query->whereNotNull('receipt_no');
                    });
                } elseif ($status === 'C') {
                    $q->whereHas('creditNotes', function ($query) {
                        $query->whereNotNull('cn_number');
                    });
                } elseif ($status === 'B') {
                    $q->whereHas('cashReceipts', function ($query) {
                        $query->whereNotNull('receipt_no');
                    })->whereRaw('(SELECT COALESCE(SUM(amount), 0) FROM cash_receipts WHERE invoice_id = register_invoice.id) < register_invoice.total');
                } elseif ($status === 'D') {
                    $q->whereDate('register_invoice.invoiceDate', '<', now()->subDays(30))
                        ->whereDoesntHave('cashReceipts', function ($query) {
                            $query->whereNotNull('receipt_no');
                        });
                } elseif ($status === 'On') {
                    $q->whereDate('register_invoice.invoiceDate', '>=', now()->subDays(30))
                        ->whereDoesntHave('cashReceipts', function ($query) {
                            $query->whereNotNull('receipt_no');
                        })
                        ->whereDoesntHave('creditNotes', function ($query) {
                            $query->whereNotNull('cn_number');
                        });
                }
            })
            ->when($commercial_name, function ($q) use ($commercial_name) {
                $q->where('budget.commercialName', 'like', '%' . $commercial_name . '%');
            })
            ->when($client_category, function ($q) use ($client_category) {
                $q->where('budget.category', $client_category);
            })
            ->when($concept, function ($q) use ($concept) {
                $q->where('register_invoice.licensedConcept', 'like', '%' . $concept . '%');
            })
            ->when($criterion, function ($q) use ($criterion) {
                $q->where('register_invoice.criterion', $criterion);
            })
            ->orderBy('register_invoice.invoiceConsecutive', 'asc')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Register Invoice');

        // Header styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];

        // Headers
        $headers = [
            'Client Category',
            'Sub Category',
            'Commercial Name',
            'Invoice Number',
            'Invoice Date',
            'Concept',
            'Period',
            'Criterion',
            'Subtotal',
            'VAT %',
            'Total',
            'Status',
            'RC/NC'
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->applyFromArray($headerStyle);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        // Data rows
        $row = 2;
        $totalInvoiced = 0;

        foreach ($data as $invoice) {
            $budget = Budget::find($invoice->budgetID);
            $client = Clients::find($budget?->commercialID);

            $categoryName = ClientCategory::where('id', $client?->categoryID)->value('category_name') ?? 'N/A';
            $subcategoryName = ClientSubCategory::where('id', $client?->subcategoryID)->value('subcategory_name') ?? 'N/A';

            $criteriaMap = [
                1 => 'Min. Guaranteed, 8% Income',
                2 => 'Min. Guaranteed + 8%',
                3 => 'Monthly Fee',
                4 => 'Annual Fee',
                5 => 'Special Arrangement'
            ];
            $criterion = $criteriaMap[$invoice->criterion] ?? 'N/A';

            // Calculate status
            $cashReceipts = CashReceipt::where('invoice_id', $invoice->id)->get();
            $creditNotes = CreditNote::where('invoice_id', $invoice->id)->get();

            $totalPaid = $cashReceipts->sum('amount');
            $hasCreditNote = $creditNotes->count() > 0;
            $hasCashReceipt = $cashReceipts->count() > 0;
            $isOverdue = $invoice->invoiceDate && now()->diffInDays($invoice->invoiceDate) > 30;

            if ($hasCreditNote) {
                $status = 'Canceled';
            } elseif ($totalPaid >= $invoice->total) {
                $status = 'Payed';
            } elseif ($hasCashReceipt && $totalPaid > 0) {
                $status = 'Balance';
            } elseif ($isOverdue && !$hasCashReceipt) {
                $status = 'Delay';
            } else {
                $status = 'On Time';
            }

            // RC/NC
            $rcNumbers = $cashReceipts->pluck('receipt_no')->filter()->map(function ($rc) {
                return 'RC ' . $rc;
            })->implode(', ');

            $ncNumbers = $creditNotes->pluck('cn_number')->filter()->map(function ($nc) {
                return 'NC ' . $nc;
            })->implode(', ');

            $rcNc = '';
            if ($rcNumbers && $ncNumbers) {
                $rcNc = $rcNumbers . ', ' . $ncNumbers;
            } elseif ($rcNumbers) {
                $rcNc = $rcNumbers;
            } elseif ($ncNumbers) {
                $rcNc = $ncNumbers;
            } else {
                $rcNc = '-';
            }

            $sheet->setCellValue('A' . $row, $categoryName);
            $sheet->setCellValue('B' . $row, $subcategoryName);
            $sheet->setCellValue('C' . $row, $invoice->commercialName ?? 'N/A');
            $sheet->setCellValue('D' . $row, $invoice->invoiceNumber ?? 'N/A');
            $sheet->setCellValue('E' . $row, $invoice->invoiceDate ? Carbon::parse($invoice->invoiceDate)->format('d-m-Y') : 'N/A');
            $sheet->setCellValue('F' . $row, $invoice->licensedConcept ?? 'N/A');
            $sheet->setCellValue('G' . $row, $invoice->periodPaid ?? 'N/A');
            $sheet->setCellValue('H' . $row, $criterion);
            $sheet->setCellValue('I' . $row, (float)$invoice->subTotal);
            $sheet->setCellValue('J' . $row, $invoice->vat);
            $sheet->setCellValue('K' . $row, (float)$invoice->total);
            $sheet->setCellValue('L' . $row, $status);
            $sheet->setCellValue('M' . $row, $rcNc);

            // Format numbers
            $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('0.00"%"');
            $sheet->getStyle('K' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

            $totalInvoiced += (float)$invoice->total;
            $row++;
        }

        // Add total row
        if ($row > 2) {
            $sheet->setCellValue('H' . $row, 'TOTAL:');
            $sheet->setCellValue('K' . $row, $totalInvoiced);
            $sheet->getStyle('H' . $row . ':K' . $row)->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E7E6E6']]
            ]);
            $sheet->getStyle('K' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        }

        $filename = 'Register_Invoice_' . ($start ? Carbon::parse($start)->format('Ymd') : 'all') . '_to_' . ($end ? Carbon::parse($end)->format('Ymd') : 'all') . '.xlsx';

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    // Helper currency like $1,000.00
    private function money($n)
    {
        return '$' . number_format((float)$n, 2, '.', ',');
    }

    public function billingList(Request $request)
    {
        $start = $request->get('start_date'); // Y-m-d
        $end   = $request->get('end_date');   // Y-m-d

        $q = RegisterInvoice::query()
            ->when($start, fn($x) => $x->whereDate('invoiceDate', '>=', $start))
            ->when($end,   fn($x) => $x->whereDate('invoiceDate', '<=', $end))
            ->orderBy('invoiceConsecutive', 'asc');

        return DataTables::of($q)
            ->addIndexColumn()
            ->addColumn('user_type', function ($row) {
                $name = UseTypes::where('id', $row->user_type)->value('use_types_name');
                return $name ?: ($row->user_type ?? 'N/A');
            })
            ->addColumn('company', fn($r) => $r->company ?: 'N/A')
            ->addColumn('commercialName', fn($r) => $r->commercialName ?: 'N/A')
            ->addColumn('concept', function ($r) {
                $b = Budget::find($r->budgetID);
                return $b?->concept ?? 'N/A';
            })
            ->addColumn('invoice_no', fn($r) => $r->invoiceNumber ?: 'N/A')
            ->addColumn('invoice_date', fn($r) => $r->invoiceDate ? Carbon::parse($r->invoiceDate)->format('d-m-Y') : 'N/A')
            ->addColumn('period', function ($r) {
                $map = [1 => 'Month and Year', 2 => 'Year Only', 3 => 'Multiple Years'];
                $label = $map[$r->paidPeriod] ?? 'N/A';
                return $label . ($r->periodPaid ? ' (' . $r->periodPaid . ')' : '');
            })
            ->addColumn('criterion', function ($r) {
                $criteria = [1 => 'Min. Guaranteed, 8% Income', 2 => 'Min. Guaranteed + 8%', 3 => 'Monthly Fee', 4 => 'Annual Fee', 5 => 'Special Arrangement'];
                return $criteria[$r->criterion] ?? 'N/A';
            })
            ->addColumn('subtotal', fn($r) => $this->money($r->subTotal))
            ->addColumn('vat', fn($r) => is_numeric($r->vat) ? $r->vat . '%' : 'N/A')
            ->addColumn('total', fn($r) => $this->money($r->total))
            ->addColumn('balance', function ($r) {
                $paid = (float) CashReceipt::where('invoice_id', $r->id)->sum('amount');
                $cn   = CreditNote::where('invoice_id', $r->id)->first();
                $cnTot = $cn ? (float)$cn->total : 0.0;
                $bal  = (float)$r->total - $paid - $cnTot;
                return $this->money(max($bal, 0));
            })
            ->addColumn('supporting_doc', function ($r) {
                $receipts = CashReceipt::where('invoice_id', $r->id)->orderBy('receipt_date')->get();
                $cn = CreditNote::where('invoice_id', $r->id)->first();

                $parts = [];
                foreach ($receipts as $rc) {
                    $parts[] = 'CR ' . $rc->receipt_no . ' (' . $this->money($rc->amount) . ')';
                }
                if ($cn) {
                    $parts[] = 'CN ' . $cn->cn_number . ' (' . $this->money($cn->total) . ')';
                }
                return $parts ? implode(' Ã‚Â· ', $parts) : '-';
            })
            ->addColumn('action', function ($r) {
                $cn = CreditNote::where('invoice_id', $r->id)->first();
                $cnBtn = $cn
                    ? '<button type="button" class="btn btn-soft-secondary btn-sm" disabled title="Credit Note already registered">Credit Note</button>'
                    : '<button type="button" class="btn btn-soft-warning btn-sm" onclick="openCreditNoteModal(' . $r->id . ')" title="Register Credit Note">Register Credit Note<i class="ri-file-reduce-line"></i></button>';

                $view = route('get-invoice.view', $r->id);
                return '<div class="btn-group" role="group">
                            ' . $cnBtn . '
                            <a href="' . $view . '" class="btn btn-soft-primary btn-sm" title="View Invoice" target="_blank">View</a>
                        </div>';
            })
            ->rawColumns(['action'])
            ->with(['totals' => $this->billingTotals($start, $end)])
            ->make(true);
    }

    private function billingTotals(?string $start, ?string $end): array
    {
        $inv = RegisterInvoice::query()
            ->when($start, fn($x) => $x->whereDate('invoiceDate', '>=', $start))
            ->when($end,   fn($x) => $x->whereDate('invoiceDate', '<=', $end))
            ->get();

        $totalBilling = (float)$inv->sum('total');

        $ids = $inv->pluck('id');
        $cnSum = (float) CreditNote::whereIn('invoice_id', $ids)->sum('total');
        $rcSum = (float) CashReceipt::whereIn('invoice_id', $ids)->sum('amount');

        $portfolio = $totalBilling - $rcSum - $cnSum;

        return [
            'billing'     => $this->money($totalBilling),
            'creditNotes' => $this->money($cnSum),
            'portfolio'   => $this->money(max($portfolio, 0)),
        ];
    }

    public function creditNotesList(Request $request)
    {
        $start = $request->get('start_date');
        $end   = $request->get('end_date');

        $q = DB::table('credit_notes as cn')
            ->leftJoin('billing_list as bl', 'cn.invoice_id', '=', 'bl.invoiceID')
            ->leftJoin('register_invoice as ri', 'cn.invoice_id', '=', 'ri.id')
            ->select(
                'cn.id',
                'cn.cn_number',
                'cn.cn_date',
                'cn.subTotal',
                'cn.vat',
                'cn.total',
                DB::raw('COALESCE(cn.supporting_doc, "-") as supporting_doc'),
                'ri.user_type',
                DB::raw('COALESCE(bl.company, ri.company) as company'),
                DB::raw('COALESCE(bl.commercialName, ri.commercialName) as commercialName'),
                DB::raw('COALESCE(cn.concept, cn.reason, "Auto-generated from License") as concept'),
                DB::raw('COALESCE(cn.period, "N/A") as period'),
                DB::raw('COALESCE(cn.criterion, "N/A") as criterion')
            )
            ->when($start, fn($x) => $x->whereDate('cn.cn_date', '>=', $start))
            ->when($end,   fn($x) => $x->whereDate('cn.cn_date', '<=', $end))
            ->orderBy('cn_number');

        return DataTables::of($q)
            ->addIndexColumn()
            ->addColumn('user_type', function ($r) {
                $name = UseTypes::where('id', $r->user_type)->value('use_types_name');
                return $name ?: ($r->user_type ?? 'N/A');
            })
            ->addColumn('company', fn($r) => $r->company ?? 'N/A')
            ->addColumn('commercialName', fn($r) => $r->commercialName ?? 'N/A')
            ->addColumn('concept', fn($r) => $r->concept ?? 'N/A')
            ->addColumn('cn_no', fn($r) => $r->cn_number)
            ->addColumn('cn_date', fn($r) => $r->cn_date ? Carbon::parse($r->cn_date)->format('d-m-Y') : 'N/A')
            ->addColumn('period', fn($r) => $r->period ?? 'N/A')
            ->addColumn('criterion', fn($r) => $r->criterion ?? 'N/A')
            ->addColumn('subtotal', fn($r) => $this->money($r->subTotal))
            ->addColumn('vat', fn($r) => is_numeric($r->vat) ? $r->vat . '%' : 'N/A')
            ->addColumn('total', fn($r) => $this->money($r->total))
            ->addColumn('supporting_doc', function ($r) {
                if ($r->supporting_doc && $r->supporting_doc !== '-') {
                    $url = Storage::url($r->supporting_doc);
                    $fileName = basename($r->supporting_doc);
                    return '<a href="' . $url . '" target="_blank" class="btn btn-soft-info btn-sm"><iconify-icon icon="solar:file-download-bold" class="fs-18"></iconify-icon></a>';
                }
                return '-';
                return '-';
            })
            ->addColumn('action', fn($r) => '<a href="javascript:void(0)" class="btn btn-soft-danger btn-sm" onclick="deleteCN(' . $r->id . ')" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete"><iconify-icon icon="solar:trash-bin-trash-bold" class="fs-18"></iconify-icon></a>')
            ->rawColumns(['action', 'supporting_doc'])
            ->make(true);
    }

    public function storeCreditNote(Request $request)
    {
        $request->validate([
            'invoice_id'     => 'required|exists:register_invoice,id',
            'cn_number'      => 'required|unique:credit_notes,cn_number',
            'cn_date'        => 'required|date',
            'reason'         => 'nullable|string|max:255',
            'subTotal'       => 'required|numeric|min:0',
            'vat'            => 'required|numeric|min:0',
            'total'          => 'required|numeric|min:0',
            'supporting_doc' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120', // 5MB max
        ]);

        try {
            DB::beginTransaction();

            if (CreditNote::where('invoice_id', $request->invoice_id)->exists()) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'A credit note already exists for this invoice.'
                ], 422);
            }

            // Handle file upload
            $supportingDocPath = null;
            if ($request->hasFile('supporting_doc')) {
                $supportingDocPath = $request->file('supporting_doc')->store('credit_notes', 'public');
            }

            $invoiceRecord = RegisterInvoice::find(
                $request->invoice_id
            );

            $cn = CreditNote::create([
                'invoice_id'     => $request->invoice_id,
                'cn_number'      => $request->cn_number,
                'cn_date'        => $request->cn_date,
                'reason'         => $request->reason,
                'period'         => $invoiceRecord->periodPaid ?? $invoiceRecord->paidPeriod ?? null,
                'criterion'      => $invoiceRecord->criterion ?? null,
                'concept'        => $invoiceRecord->licensedConcept ?? null,
                'subTotal'       => $request->subTotal,
                'vat'            => $request->vat,
                'total'          => $request->total,
                'supporting_doc' => $supportingDocPath, // Store file path instead of text
                'created_by'     => auth()->id(),
            ]);

            // Update billing_list balance after credit note
            if ($request->invoice_id) {
                $invoice = RegisterInvoice::find(
                    $request->invoice_id
                );
                if ($invoice) {
                    // Get total paid via cash receipts
                    $totalPaidSoFar = CashReceipt::where(
                        'invoice_id',
                        $invoice->id
                    )->sum('amount');

                    // Get total credit notes for this invoice
                    $totalCreditNotes = CreditNote::where(
                        'invoice_id',
                        $invoice->id
                    )->sum('total');

                    // Calculate new balance
                    $newBalance = max(
                        (float)$invoice->total
                            - $totalPaidSoFar
                            - $totalCreditNotes,
                        0
                    );

                    // Determine status
                    if ($newBalance <= 0) {
                        $invoiceStatus = 'Cancelled';
                    } elseif (
                        $totalPaidSoFar > 0
                        || $totalCreditNotes > 0
                    ) {
                        $invoiceStatus = 'Partial';
                    } else {
                        $invoiceStatus = 'On time';
                    }

                    DB::table('billing_list')
                        ->where('invoiceID', $invoice->id)
                        ->update([
                            'balance'    => $newBalance,
                            'estado'     => $invoiceStatus,
                            'updated_at' => now()
                        ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Credit Note saved successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error saving Credit Note: '
                    . $e->getMessage()
            ], 500);
        }
    }

    public function deleteCreditNote($id)
    {
        return DB::transaction(function () use ($id) {
            $cn = CreditNote::findOrFail($id);

            $invoiceId = $cn->invoice_id ?? $cn->invoiceID ?? null;
            $amount = $cn->total ?? $cn->amount ?? 0;

            if ($invoiceId && $amount > 0) {
                DB::table('billing_list')
                    ->where('invoiceID', $invoiceId)
                    ->increment('balance', $amount);
            }

            $cn->delete();
            return response()->json(['status' => true, 'message' => 'Credit Note deleted']);
        });
    }

    public function quickRegisterInvoice(Request $request)
    {
        $request->validate([
            'commercialID'   => 'required|exists:clients,id',
            'company'        => 'required|string',
            'commercialName' => 'required|string',
            'user_type'      => 'required',
            'subTotal'       => 'required|numeric|min:0',
            'vat'            => 'required|numeric|min:0',
            'total'          => 'required|numeric|min:0',
            'paidPeriod'     => 'required|in:1,2,3',
            'periodPaid'     => 'required|string',
            'criterion'      => 'required|in:1,2,3,4,5',
        ]);

        $invoiceNumber = 'IN-' . strtoupper(uniqid());
        $inv = RegisterInvoice::create([
            'budgetID'           => null,
            'invoiceConsecutive' => $invoiceNumber,
            'invoiceNumber'      => $invoiceNumber,
            'invoiceDate'        => now(),
            'periodPaid'         => $request->periodPaid,
            'paidPeriod'         => $request->paidPeriod,
            'criterion'          => $request->criterion,
            'licensedConcept'    => null,
            'licensedEnvironment' => null,
            'commercialID'       => $request->commercialID,
            'user_type'          => $request->user_type,
            'company'            => $request->company,
            'commercialName'     => $request->commercialName,
            'subTotal'           => $request->subTotal,
            'vat'                => $request->vat,
            'total'              => $request->total,
            'created_by'         => auth()->id(),
        ]);

        return response()->json(['status' => true, 'message' => 'Invoice registered', 'id' => $inv->id]);
    }

    public function billingDownload(Request $request)
    {
        $start = $request->get('start_date');
        $end   = $request->get('end_date');
        $invoices = RegisterInvoice::query()->when($start, fn($q) => $q->whereDate('invoiceDate', '>=', $start))->when($end, fn($q) => $q->whereDate('invoiceDate', '<=', $end))->orderBy('invoiceConsecutive', 'asc')->get();

        $creditNotes = CreditNote::query()->with('invoice')->when($start, fn($q) => $q->whereHas('invoice', fn($x) => $x->whereDate('invoiceDate', '>=', $start)))->when($end, fn($q) => $q->whereHas('invoice', fn($x) => $x->whereDate('invoiceDate', '<=', $end)))->orderBy('cn_number', 'asc')->get();

        $spreadsheet = new Spreadsheet();
        $invoiceSheet = $spreadsheet->getActiveSheet();
        $invoiceSheet->setTitle('Invoices');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];

        $invoiceHeaders = [
            'User Type',
            'Company',
            'Commercial Name',
            'Concept',
            'Invoice No.',
            'Invoice Date',
            'Period',
            'Criterion',
            'Subtotal',
            'VAT %',
            'VAT Amount',
            'Total',
            'Balance',
            'Supporting Doc'
        ];

        $col = 'A';
        foreach ($invoiceHeaders as $header) {
            $invoiceSheet->setCellValue($col . '1', $header);
            $invoiceSheet->getStyle($col . '1')->applyFromArray($headerStyle);
            $invoiceSheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        $row = 2;
        $totalInvoiced = 0;
        $totalVatAmount = 0;

        foreach ($invoices as $invoice) {
            $budget = Budget::find($invoice->budgetID);
            $userTypeName = UseTypes::where('id', $invoice->user_type)->value('use_types_name') ?? $invoice->user_type;
            $paidPeriodMap = [1 => 'Month and Year', 2 => 'Year Only', 3 => 'Multiple Years'];
            $period = ($paidPeriodMap[$invoice->paidPeriod] ?? 'N/A') . ($invoice->periodPaid ? ' (' . $invoice->periodPaid . ')' : '');

            $criteriaMap = [
                1 => 'Min. Guaranteed, 8% Income',
                2 => 'Min. Guaranteed + 8%',
                3 => 'Monthly Fee',
                4 => 'Annual Fee',
                5 => 'Special Arrangement'
            ];
            $criterion = $criteriaMap[$invoice->criterion] ?? 'N/A';

            $paid = (float) CashReceipt::where('invoice_id', $invoice->id)->sum('amount');
            $cn = CreditNote::where('invoice_id', $invoice->id)->first();
            $cnTotal = $cn ? (float)$cn->total : 0.0;
            $balance = max((float)$invoice->total - $paid - $cnTotal, 0);

            $vatAmount = (float)$invoice->total - (float)$invoice->subTotal;
            $receipts = CashReceipt::where('invoice_id', $invoice->id)->orderBy('receipt_date')->get();
            $supportingDocs = [];
            foreach ($receipts as $rc) {
                $supportingDocs[] = 'CR ' . $rc->receipt_no . ' ($' . number_format($rc->amount, 2) . ')';
            }
            if ($cn) {
                $supportingDocs[] = 'CN ' . $cn->cn_number . ' ($' . number_format($cn->total, 2) . ')';
            }
            $supportingDocText = $supportingDocs ? implode(' Ã‚Â· ', $supportingDocs) : '-';
            $invoiceSheet->setCellValue('A' . $row, $userTypeName);
            $invoiceSheet->setCellValue('B' . $row, $invoice->company ?? 'N/A');
            $invoiceSheet->setCellValue('C' . $row, $invoice->commercialName ?? 'N/A');
            $invoiceSheet->setCellValue('D' . $row, $budget?->concept ?? 'N/A');
            $invoiceSheet->setCellValue('E' . $row, $invoice->invoiceNumber ?? 'N/A');
            $invoiceSheet->setCellValue('F' . $row, $invoice->invoiceDate ? Carbon::parse($invoice->invoiceDate)->format('d-m-Y') : 'N/A');
            $invoiceSheet->setCellValue('G' . $row, $period);
            $invoiceSheet->setCellValue('H' . $row, $criterion);
            $invoiceSheet->setCellValue('I' . $row, (float)$invoice->subTotal);
            $invoiceSheet->setCellValue('J' . $row, $invoice->vat);
            $invoiceSheet->setCellValue('K' . $row, $vatAmount);
            $invoiceSheet->setCellValue('L' . $row, (float)$invoice->total);
            $invoiceSheet->setCellValue('M' . $row, $balance);
            $invoiceSheet->setCellValue('N' . $row, $supportingDocText);
            $invoiceSheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $invoiceSheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('0.00"%"');
            $invoiceSheet->getStyle('K' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $invoiceSheet->getStyle('L' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $invoiceSheet->getStyle('M' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $totalInvoiced += (float)$invoice->total;
            $totalVatAmount += $vatAmount;
            $row++;
        }

        if ($row > 2) {
            $invoiceSheet->setCellValue('H' . $row, 'TOTAL:');
            $invoiceSheet->setCellValue('K' . $row, $totalVatAmount);
            $invoiceSheet->setCellValue('L' . $row, $totalInvoiced);
            $invoiceSheet->getStyle('H' . $row . ':L' . $row)->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E7E6E6']]
            ]);
            $invoiceSheet->getStyle('K' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $invoiceSheet->getStyle('L' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        }
        $cnSheet = $spreadsheet->createSheet();
        $cnSheet->setTitle('Credit Notes');

        $cnHeaders = [
            'User Type',
            'Company',
            'Commercial Name',
            'Concept',
            'CN No.',
            'CN Date',
            'Period',
            'Criterion',
            'Reason',
            'Subtotal',
            'VAT %',
            'VAT Amount',
            'Total',
            'Supporting Doc'
        ];

        $col = 'A';
        foreach ($cnHeaders as $header) {
            $cnSheet->setCellValue($col . '1', $header);
            $cnSheet->getStyle($col . '1')->applyFromArray($headerStyle);
            $cnSheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        $row = 2;
        $totalCN = 0;
        $totalCNVat = 0;
        foreach ($creditNotes as $cn) {
            $invoice = $cn->invoice;
            $budget = Budget::find($invoice?->budgetID);
            $userTypeName = UseTypes::where('id', $invoice?->user_type)->value('use_types_name') ?? $invoice?->user_type ?? 'N/A';
            $paidPeriodMap = [1 => 'Month and Year', 2 => 'Year Only', 3 => 'Multiple Years'];
            $period = ($paidPeriodMap[$invoice?->paidPeriod] ?? 'N/A') . ($invoice?->periodPaid ? ' (' . $invoice->periodPaid . ')' : '');
            $criteriaMap = [
                1 => 'Min. Guaranteed, 8% Income',
                2 => 'Min. Guaranteed + 8%',
                3 => 'Monthly Fee',
                4 => 'Annual Fee',
                5 => 'Special Arrangement'
            ];
            $criterion = $criteriaMap[$invoice?->criterion] ?? 'N/A';
            $vatAmount = (float)$cn->total - (float)$cn->subTotal;
            $supportingDoc = $cn->supporting_doc ? 'Attached' : '-';

            $cnSheet->setCellValue('A' . $row, $userTypeName);
            $cnSheet->setCellValue('B' . $row, $invoice?->company ?? 'N/A');
            $cnSheet->setCellValue('C' . $row, $invoice?->commercialName ?? 'N/A');
            $cnSheet->setCellValue('D' . $row, $budget?->concept ?? 'N/A');
            $cnSheet->setCellValue('E' . $row, $cn->cn_number);
            $cnSheet->setCellValue('F' . $row, $cn->cn_date ? Carbon::parse($cn->cn_date)->format('d-m-Y') : 'N/A');
            $cnSheet->setCellValue('G' . $row, $period);
            $cnSheet->setCellValue('H' . $row, $criterion);
            $cnSheet->setCellValue('I' . $row, $cn->reason ?? '-');
            $cnSheet->setCellValue('J' . $row, (float)$cn->subTotal);
            $cnSheet->setCellValue('K' . $row, $cn->vat);
            $cnSheet->setCellValue('L' . $row, $vatAmount);
            $cnSheet->setCellValue('M' . $row, (float)$cn->total);
            $cnSheet->setCellValue('N' . $row, $supportingDoc);
            $cnSheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $cnSheet->getStyle('K' . $row)->getNumberFormat()->setFormatCode('0.00"%"');
            $cnSheet->getStyle('L' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $cnSheet->getStyle('M' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $totalCN += (float)$cn->total;
            $totalCNVat += $vatAmount;
            $row++;
        }

        if ($row > 2) {
            $cnSheet->setCellValue('H' . $row, 'TOTAL:');
            $cnSheet->setCellValue('L' . $row, $totalCNVat);
            $cnSheet->setCellValue('M' . $row, $totalCN);
            $cnSheet->getStyle('H' . $row . ':M' . $row)->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFE699']]
            ]);
            $cnSheet->getStyle('L' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $cnSheet->getStyle('M' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        }

        $spreadsheet->setActiveSheetIndex(0);
        $filename = 'Billing_Report_' . ($start ? Carbon::parse($start)->format('Ymd') : 'all') . '_to_' . ($end ? Carbon::parse($end)->format('Ymd') : 'all') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function billingUpload(Request $r)
    {
        return response()->json(['status' => true, 'message' => 'Upload stub. Parse Excel and insert invoices/receipts.']);
    }

    public function billingReport(Request $request)
    {
        $start = $request->get('start_date');
        $end   = $request->get('end_date');

        $invoices = RegisterInvoice::query()->when($start, fn($q) => $q->whereDate('invoiceDate', '>=', $start))->when($end, fn($q) => $q->whereDate('invoiceDate', '<=', $end))->orderBy('invoiceConsecutive', 'asc')->get();
        $creditNotes = CreditNote::query()->with('invoice')->when($start, fn($q) => $q->whereHas('invoice', fn($x) => $x->whereDate('invoiceDate', '>=', $start)))->when($end, fn($q) => $q->whereHas('invoice', fn($x) => $x->whereDate('invoiceDate', '<=', $end)))->orderBy('cn_number', 'asc')->get();

        $totalBilling = (float)$invoices->sum('total');
        $totalCN = (float)$creditNotes->sum('total');
        $cashReceipts = CashReceipt::whereIn('invoice_id', $invoices->pluck('id'))->sum('amount');
        $portfolio = $totalBilling - $cashReceipts - $totalCN;
        $data = [
            'start_date' => $start ? Carbon::parse($start)->format('d-m-Y') : 'All',
            'end_date' => $end ? Carbon::parse($end)->format('d-m-Y') : 'All',
            'invoices' => $invoices,
            'creditNotes' => $creditNotes,
            'totalBilling' => $totalBilling,
            'totalCN' => $totalCN,
            'totalReceipts' => $cashReceipts,
            'portfolio' => max($portfolio, 0),
            'generated_at' => Carbon::now()->format('d-m-Y H:i:s')
        ];

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('budget.billing-report-pdf', $data);
        $pdf->setPaper('A4', 'landscape');

        $filename = 'Billing_Report_' . ($start ? Carbon::parse($start)->format('Ymd') : 'all') . '_to_' . ($end ? Carbon::parse($end)->format('Ymd') : 'all') . '.pdf';
        return $pdf->download($filename);
    }

    public function calculateTotalsByConcept($month = null, $year = null, $startDate = null, $endDate = null, $conceptFilter = null, $conditionFilter = null)
    {
        $concepts = \App\Models\Budget::select('licensedConcept')
            ->distinct()
            ->pluck('licensedConcept')
            ->filter()
            ->values();

        $sections = [
            'others' => [
                'title' => 'Others',
                'conditions' => [5] // Others,
            ],
            'acuerdos' => [
                'title' => 'Acuerdos',
                'conditions' => [4] // Acuerdos,
            ],
            'awaiting_purchase_order' => [
                'title' => 'Awaiting Purchase Order',
                'conditions' => [3] // Awaiting Purchase Order,
            ],
            'new_agreement' => [
                'title' => 'New Agreement',
                'conditions' => [2] // New Agreement
            ],
            'portfolio' => [
                'title' => 'Portfolio',
                'conditions' => [1] // Portfolio
            ]
        ];

        $totals = [];
        $grandTotal = ['subTotal' => 0, 'vat' => 0, 'total' => 0];

        foreach ($sections as $sectionKey => $section) {
            $sectionTotals = [];
            $sectionGrand = ['subTotal' => 0, 'vat' => 0, 'total' => 0];

            foreach ($concepts as $conceptName) {
                $budgets = Budget::where('licensedConcept', $conceptName)
                    ->whereIn('condition', $section['conditions']);

                // Apply month/year filters only if provided
                if ($month) {
                    $budgets->where('budget_month', $month);
                }
                if ($year) {
                    $budgets->where('budget_year', $year);
                }

                // Apply concept filter if provided
                if ($conceptFilter) {
                    $budgets->where('user_type', 'like', '%' . $conceptFilter . '%');
                }

                // Apply condition filter if provided
                if ($conditionFilter) {
                    $budgets->where('condition', $conditionFilter);
                }

                // Apply timeframe filter if provided
                $this->applyTimeframeFilter($budgets, $startDate, $endDate);

                $budgets = $budgets->get();

                $conceptSubTotal = $budgets->sum('subTotal') ?? 0;
                $conceptVat      = $budgets->sum(fn($b) => ($b->subTotal * (float)$b->vat) / 100) ?? 0;
                $conceptTotal    = $budgets->sum('total') ?? 0;

                $sectionTotals[] = [
                    'name' => $conceptName,
                    'subTotal' => $conceptSubTotal,
                    'vat' => $conceptVat,
                    'total' => $conceptTotal
                ];

                // Add to section totals
                $sectionGrand['subTotal'] += $conceptSubTotal;
                $sectionGrand['vat']      += $conceptVat;
                $sectionGrand['total']    += $conceptTotal;

                // Add to overall grand total
                $grandTotal['subTotal'] += $conceptSubTotal;
                $grandTotal['vat']      += $conceptVat;
                $grandTotal['total']    += $conceptTotal;
            }

            $totals[$sectionKey] = [
                'title' => $section['title'],
                'concepts' => $sectionTotals,
                'sectionTotal' => $sectionGrand
            ];
        }

        return [
            'sections' => $totals,
            'grandTotal' => $grandTotal
        ];
    }

    public function checkDeadlineAlert()
    {
        $endOfMonth = Carbon::now()->endOfMonth();
        $daysUntilEndOfMonth = (int) Carbon::now()->diffInDays($endOfMonth);

        if ($daysUntilEndOfMonth <= 10 && $daysUntilEndOfMonth >= 0) {
            $pendingBudgets = Budget::where('status', '!=', 2)->count();
            if ($pendingBudgets > 0) {
                $dayText = $daysUntilEndOfMonth === 1 ? 'day' : 'days';
                return response()->json([
                    'alert' => true,
                    'message' => " Alert: {$pendingBudgets} budget(s) not invoiced. Only {$daysUntilEndOfMonth} {$dayText} remaining until month end!",
                    'count' => $pendingBudgets,
                    'days' => $daysUntilEndOfMonth
                ]);
            }
        }
        return response()->json(['alert' => false]);
    }

    public function getIncomeData(Request $request)
    {
        $start = $request->get('start_date');
        $end   = $request->get('end_date');

        // FIXED: Group by RC number and filter only valid RC records
        $query = IncomeRecord::query()
            ->select([
                'rc_number',
                DB::raw('MAX(id) as id'),
                DB::raw('MAX(mode) as mode'),
                DB::raw('MAX(bank_code) as bank_code'),
                DB::raw('MIN(income_date) as income_date'),
                DB::raw('MAX(rc_date) as rc_date'),
                DB::raw('MAX(company) as company'),
                DB::raw('MAX(commercial_name) as commercial_name'),

                DB::raw('SUM(income_amount) as total_income'),
                DB::raw('SUM(other_amounts) as total_other'),

                DB::raw('MAX(total_paid) as total_paid'),
                DB::raw('MAX(balance) as total_balance'),

                DB::raw('GROUP_CONCAT(DISTINCT invoice_number) as invoices'),

                DB::raw('MAX(invoice_date) as invoice_date'),
                DB::raw('GROUP_CONCAT(DISTINCT concept) as concept'),
                DB::raw('GROUP_CONCAT(DISTINCT invoice_period) as invoice_period'),

                DB::raw('SUM(invoice_value) as total_invoice_value')
            ])
            ->whereNotNull('rc_number')
            ->where('rc_number', '!=', '')
            ->where('rc_number', '!=', '-')
            ->groupBy('rc_number');

        if ($start && $end) {
            $query->whereBetween('income_date', [$start, $end]);
        } elseif ($start) {
            $query->whereDate('income_date', '>=', $start);
        } elseif ($end) {
            $query->whereDate('income_date', '<=', $end);
        }
        $query->orderBy('income_date', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('mode', fn($row) => $row->mode)
            ->addColumn('bank_code', function ($row) {
                $bank = Bank::find($row->bank_code);
                return $bank ? $bank->bank_code . ' - ' . $bank->bank_name : '-';
            })
            ->addColumn('company', fn($row) => $row->company ?? '-')
            ->addColumn('commercial_name', fn($row) => $row->commercial_name ?? '-')
            ->addColumn('income_date', function ($row) {
                return $row->income_date
                    ? Carbon::parse($row->income_date)->format('d-m-Y')
                    : '-';
            })
            ->addColumn('income_amount', fn($row) => $this->money($row->total_income))
            ->addColumn('other_amounts', fn($row) => $this->money($row->total_other))
            ->addColumn('total_paid', fn($row) => $this->money($row->total_paid))
            ->addColumn('invoice_number', fn($row) => $row->invoices ?? '-')
            ->addColumn('invoice_ids', function ($row) {
                if ($row->invoices) {
                    return explode(',', $row->invoices);
                }
                return [];
            })
            ->addColumn('invoice_date', function ($row) {
                return $row->invoice_date
                    ? Carbon::parse($row->invoice_date)->format('d-m-Y')
                    : '-';
            })
            ->addColumn('concept', fn($row) => $row->concept ?? '-')
            ->addColumn('invoice_period', fn($row) => $row->invoice_period ?? '-')
            ->addColumn('invoice_value', fn($row) => $this->money($row->total_invoice_value))
            ->addColumn('balance', fn($row) => $this->money($row->total_balance))
            ->addColumn('rc_number', fn($row) => $row->rc_number ?? '-')
            ->addColumn('rc_date', function ($row) {
                return $row->rc_date
                    ? Carbon::parse($row->rc_date)->format('d-m-Y')
                    : '-';
            })
            ->addColumn('action', function ($row) {
                return '<div class="btn-group" role="group">
                    <button type="button" class="btn btn-soft-primary btn-sm" onclick="editIncome(' . $row->id . ')" title="Edit">
                        <iconify-icon icon="solar:pen-new-square-linear" class="fs-18"></iconify-icon>
                    </button>
                    <button type="button" class="btn btn-soft-danger btn-sm" onclick="deleteIncome(' . $row->id . ')" title="Delete">
                        <iconify-icon icon="solar:trash-bin-trash-bold" class="fs-18"></iconify-icon>
                    </button>
                </div>';
            })
            ->with(['totals' => $this->calculateIncomeTotals($start, $end)])
            ->rawColumns(['action'])
            ->make(true);
    }

    public function deleteIncome($id)
    {
        return DB::transaction(function () use ($id) {
            $income = IncomeRecord::findOrFail($id);
            $rcNumber = $income->rc_number;

            // If no RC number, only delete this one
            if (!$rcNumber || $rcNumber === '-') {
                $relatedIncomes = collect([$income]);
            } else {
                // Find all related income records for this payment transaction
                $relatedIncomes = IncomeRecord::where('rc_number', $rcNumber)
                    ->where('income_date', $income->income_date)
                    ->get();
            }

            foreach ($relatedIncomes as $record) {
                // Find the linked invoice in billing_list
                $invoice = DB::table('billing_list')->where('invoiceID', $record->invoice_id)->first();

                if ($invoice) {
                    // Restore the balance by adding back the paid amount and any other amounts
                    $restoredAmount = (float)($record->income_amount ?? 0) + (float)($record->other_amounts ?? 0);
                    $newBalance = (float)$invoice->balance + $restoredAmount;

                    // Determine original status (On time/Pending)
                    $invoiceTotal = (float)$invoice->total;
                    $status = 'On time'; // Default in this system
                    if ($newBalance > 0 && $newBalance < $invoiceTotal) {
                        $status = 'Partial';
                    } elseif ($newBalance <= 0) {
                        $status = 'Paid';
                    }

                    // Update invoice balance and status
                    DB::table('billing_list')
                        ->where('invoiceID', $record->invoice_id)
                        ->update([
                            'balance' => $newBalance,
                            'estado' => $status,
                            'updated_at' => now()
                        ]);
                }

                // Delete the individual cash receipt
                if ($record->rc_number && $record->invoice_id) {
                    // Try exact match first
                    CashReceipt::where('invoice_id', $record->invoice_id)
                        ->where('receipt_no', $record->rc_number)
                        ->delete();

                    // Also try matching the potential suffixed receipt
                    CashReceipt::where('invoice_id', $record->invoice_id)
                        ->where('receipt_no', 'LIKE', $record->rc_number . '-%')
                        ->delete();
                }
            }

            // Delete all related income records in this batch
            if ($rcNumber && $rcNumber !== '-') {
                IncomeRecord::where('rc_number', $rcNumber)
                    ->where('income_date', $income->income_date)
                    ->delete();

                // Final safety: ensuring all cash receipts for this RC are gone
                CashReceipt::where('receipt_no', $rcNumber)->delete();
                CashReceipt::where('receipt_no', 'LIKE', $rcNumber . '-%')->delete();
            } else {
                $income->delete();
            }

            return response()->json([
                'status' => true,
                'message' => 'Income record(s) and associated balance(s) restored successfully.'
            ]);
        });
    }

    // storeIncome, updateIncome, and getCompanyInvoices methods
    // public function storeIncome(Request $request) {

    //     $request->validate([
    //         'mode' => 'required|in:Transfer,Deposit',
    //         'bank_code' => 'required|string',
    //         'income_date' => 'required|date',
    //         'income_amount' => 'required|numeric',
    //         'other_amounts' => 'nullable|numeric',
    //         'company_id' => 'nullable|exists:clients,id',
    //         'invoice_id' => 'nullable|exists:register_invoice,id',
    //         'rc_date' => 'nullable|date',
    //         'surplus_invoice_id' => 'nullable|exists:register_invoice,id',
    //         'surplus_amount' => 'nullable|numeric|min:0',
    //     ]);

    //     try {
    //         DB::beginTransaction();

    //         $otherAmounts = $this->parseFormattedNumber($request->other_amounts ?? '0');
    //         $incomeAmount = $this->parseFormattedNumber($request->income_amount);
    //         $totalPaid = $incomeAmount + $otherAmounts;

    //         // Main income record data
    //         $data = [
    //             'mode' => $request->mode,
    //             'bank_code' => $request->bank_code,
    //             'company_id' => $request->company_id,
    //             'company' => $request->company,
    //             'commercial_name' => $request->commercial_name,
    //             'income_date' => $request->income_date,
    //             'income_amount' => $incomeAmount,
    //             'other_amounts' => $otherAmounts,
    //             'total_paid' => $totalPaid,
    //             'invoice_id' => $request->invoice_id,
    //             'invoice_number' => $request->invoice_number,
    //             'invoice_date' => $request->invoice_date,
    //             'concept' => $request->concept,
    //             'invoice_period' => $request->invoice_period,
    //             'invoice_value' => $request->invoice_value ? $this->parseFormattedNumber($request->invoice_value) : null,
    //             'balance' => null,
    //             'rc_number' => $request->rc_number,
    //             'rc_date' => $request->rc_date,
    //             'created_by' => auth()->id(),
    //         ];

    //         // Calculate balance for main invoice
    //         if ($request->invoice_id && $request->invoice_value) {
    //             $invoiceValue = $this->parseFormattedNumber($request->invoice_value);

    //             // Get previous payments for this invoice
    //             $previousPayments = IncomeRecord::where('invoice_id', $request->invoice_id)
    //                 ->where('rc_number', '!=', null)
    //                 ->sum('total_paid');

    //             // Calculate balance after this payment
    //             $newBalance = $invoiceValue - $previousPayments - $totalPaid;

    //             // If payment + other amounts >= invoice value, mark as fully paid
    //             if ($totalPaid >= $invoiceValue - $previousPayments) {
    //                 $data['balance'] = 0;

    //                 // Calculate surplus if payment exceeds remaining balance
    //                 $surplus = $totalPaid - ($invoiceValue - $previousPayments);

    //                 // If there's a surplus and a second invoice is selected
    //                 if ($surplus > 0 && $request->surplus_invoice_id && $request->surplus_amount) {
    //                     $surplusAmount = $this->parseFormattedNumber($request->surplus_amount);

    //                     // Validate surplus amount doesn't exceed actual surplus
    //                     if ($surplusAmount > $surplus) {
    //                         throw new \Exception('Surplus amount cannot exceed the actual surplus of $' . number_format($surplus, 2));
    //                     }

    //                     // Create separate income record for surplus invoice
    //                     $surplusInvoice = RegisterInvoice::findOrFail($request->surplus_invoice_id);
    //                     $surplusPreviousPayments = IncomeRecord::where('invoice_id', $request->surplus_invoice_id)
    //                         ->where('rc_number', '!=', null)
    //                         ->sum('total_paid');

    //                     $surplusBalance = (float)$surplusInvoice->total - $surplusPreviousPayments - $surplusAmount;

    //                     $surplusData = [
    //                         'mode' => $request->mode,
    //                         'bank_code' => $request->bank_code,
    //                         'company_id' => $request->company_id,
    //                         'company' => $request->company,
    //                         'commercial_name' => $request->commercial_name,
    //                         'income_date' => $request->income_date,
    //                         'income_amount' => 0, // Surplus from main payment
    //                         'other_amounts' => $surplusAmount,
    //                         'total_paid' => $surplusAmount,
    //                         'invoice_id' => $request->surplus_invoice_id,
    //                         'invoice_number' => $surplusInvoice->invoiceNumber,
    //                         'invoice_date' => $surplusInvoice->invoiceDate,
    //                         'concept' => $request->surplus_concept,
    //                         'invoice_period' => $request->surplus_period,
    //                         'invoice_value' => (float)$surplusInvoice->total,
    //                         'balance' => max($surplusBalance, 0),
    //                         'rc_number' => $request->surplus_rc_number,
    //                         'rc_date' => $request->rc_date,
    //                         'created_by' => auth()->id(),
    //                     ];

    //                     IncomeRecord::create($surplusData);

    //                     // Create cash receipt for surplus invoice
    //                     if ($request->surplus_rc_number) {
    //                         CashReceipt::create([
    //                             'invoice_id' => $request->surplus_invoice_id,
    //                             'receipt_no' => $request->surplus_rc_number,
    //                             'receipt_date' => $request->rc_date ?? now(),
    //                             'amount' => $surplusAmount,
    //                             'payment_method' => $request->mode,
    //                             'bank_code' => $request->bank_code,
    //                             'created_by' => auth()->id(),
    //                         ]);
    //                     }
    //                 }
    //             } else {
    //                 // Partial payment
    //                 $data['balance'] = max($newBalance, 0);
    //             }
    //         }

    //         // Create main income record
    //         $income = IncomeRecord::create($data);

    //         // Create cash receipt for main invoice if RC number is provided
    //         if ($request->rc_number && $request->invoice_id) {
    //             CashReceipt::create([
    //                 'invoice_id' => $request->invoice_id,
    //                 'receipt_no' => $request->rc_number,
    //                 'receipt_date' => $request->rc_date ?? now(),
    //                 'amount' => $totalPaid,
    //                 'payment_method' => $request->mode,
    //                 'bank_code' => $request->bank_code,
    //                 'created_by' => auth()->id(),
    //             ]);
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Income registered successfully',
    //             'id' => $income->id
    //         ]);

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Error: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    // public function storeIncome(Request $request) {

    //     $request->validate([
    //         'mode' => 'required|in:Transfer,Deposit',
    //         'bank_code' => 'required|string',
    //         'income_date' => 'required|date',
    //         'income_amount' => 'required|numeric',
    //         'other_amounts' => 'nullable|numeric',
    //         'company_id' => 'nullable|exists:clients,id',
    //         'invoice_id' => 'nullable|exists:register_invoice,id',
    //         'rc_date' => 'nullable|date',
    //         'surplus_invoice_id' => 'nullable|exists:register_invoice,id',
    //         'surplus_amount' => 'nullable|numeric|min:0',
    //     ]);

    //     try {
    //         DB::beginTransaction();

    //         $otherAmounts = $this->parseFormattedNumber($request->other_amounts ?? '0');
    //         $incomeAmount = $this->parseFormattedNumber($request->income_amount);
    //         $totalPaid = $incomeAmount + $otherAmounts;

    //         // Main income record data
    //         $data = [
    //             'mode' => $request->mode,
    //             'bank_code' => $request->bank_code,
    //             'company_id' => $request->company_id,
    //             'company' => $request->company,
    //             'commercial_name' => $request->commercial_name,
    //             'income_date' => $request->income_date,
    //             'income_amount' => $incomeAmount,
    //             'other_amounts' => $otherAmounts,
    //             'total_paid' => $totalPaid,
    //             'invoice_id' => $request->invoice_id,
    //             'invoice_number' => $request->invoice_number,
    //             'invoice_date' => $request->invoice_date,
    //             'concept' => $request->concept,
    //             'invoice_period' => $request->invoice_period,
    //             'invoice_value' => $request->invoice_value ? $this->parseFormattedNumber($request->invoice_value) : null,
    //             'balance' => null,
    //             'rc_number' => $request->rc_number,
    //             'rc_date' => $request->rc_date,
    //             'created_by' => auth()->id(),
    //         ];

    //         // Calculate balance for main invoice
    //         if ($request->invoice_id && $request->invoice_value) {
    //             $invoiceValue = $this->parseFormattedNumber($request->invoice_value);

    //             // Get previous payments for this invoice
    //             $previousPayments = IncomeRecord::where('invoice_id', $request->invoice_id)
    //                 ->where('rc_number', '!=', null)
    //                 ->sum('total_paid');

    //             // Calculate balance after this payment
    //             $newBalance = $invoiceValue - $previousPayments - $totalPaid;

    //             // If payment + other amounts >= invoice value, mark as fully paid
    //             if ($totalPaid >= $invoiceValue - $previousPayments) {
    //                 $data['balance'] = 0;

    //                 // Calculate surplus if payment exceeds remaining balance
    //                 $surplus = $totalPaid - ($invoiceValue - $previousPayments);

    //                 // If there's a surplus and a second invoice is selected
    //                 if ($surplus > 0 && $request->surplus_invoice_id && $request->surplus_amount) {
    //                     $surplusAmount = $this->parseFormattedNumber($request->surplus_amount);

    //                     // Validate surplus amount doesn't exceed actual surplus
    //                     if ($surplusAmount > $surplus) {
    //                         throw new \Exception('Surplus amount cannot exceed the actual surplus of $' . number_format($surplus, 2));
    //                     }

    //                     // Create separate income record for surplus invoice
    //                     $surplusInvoice = RegisterInvoice::findOrFail($request->surplus_invoice_id);
    //                     $surplusPreviousPayments = IncomeRecord::where('invoice_id', $request->surplus_invoice_id)
    //                         ->where('rc_number', '!=', null)
    //                         ->sum('total_paid');

    //                     $surplusBalance = (float)$surplusInvoice->total - $surplusPreviousPayments - $surplusAmount;

    //                     $surplusData = [
    //                         'mode' => $request->mode,
    //                         'bank_code' => $request->bank_code,
    //                         'company_id' => $request->company_id,
    //                         'company' => $request->company,
    //                         'commercial_name' => $request->commercial_name,
    //                         'income_date' => $request->income_date,
    //                         'income_amount' => 0, // Surplus from main payment
    //                         'other_amounts' => $surplusAmount,
    //                         'total_paid' => $surplusAmount,
    //                         'invoice_id' => $request->surplus_invoice_id,
    //                         'invoice_number' => $surplusInvoice->invoiceNumber,
    //                         'invoice_date' => $surplusInvoice->invoiceDate,
    //                         'concept' => $request->surplus_concept,
    //                         'invoice_period' => $request->surplus_period,
    //                         'invoice_value' => (float)$surplusInvoice->total,
    //                         'balance' => max($surplusBalance, 0),
    //                         'rc_number' => $request->surplus_rc_number,
    //                         'rc_date' => $request->rc_date,
    //                         'created_by' => auth()->id(),
    //                     ];

    //                     IncomeRecord::create($surplusData);

    //                     // Create cash receipt for surplus invoice
    //                     if ($request->surplus_rc_number) {
    //                         CashReceipt::create([
    //                             'invoice_id' => $request->surplus_invoice_id,
    //                             'receipt_no' => $request->surplus_rc_number,
    //                             'receipt_date' => $request->rc_date ?? now(),
    //                             'amount' => $surplusAmount,
    //                             'payment_method' => $request->mode,
    //                             'bank_code' => $request->bank_code,
    //                             'created_by' => auth()->id(),
    //                         ]);
    //                     }
    //                 }
    //             } else {
    //                 // Partial payment
    //                 $data['balance'] = max($newBalance, 0);
    //             }
    //         }

    //         // Create main income record
    //         $income = IncomeRecord::create($data);

    //         // Create cash receipt for main invoice if RC number is provided
    //         if ($request->rc_number && $request->invoice_id) {
    //             CashReceipt::create([
    //                 'invoice_id' => $request->invoice_id,
    //                 'receipt_no' => $request->rc_number,
    //                 'receipt_date' => $request->rc_date ?? now(),
    //                 'amount' => $totalPaid,
    //                 'payment_method' => $request->mode,
    //                 'bank_code' => $request->bank_code,
    //                 'created_by' => auth()->id(),
    //             ]);
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Income registered successfully',
    //             'id' => $income->id,
    //             'invoice_updated' => $request->invoice_id ? true : false
    //         ]);

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Error: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function storeIncome(Request $request)
    {
        // Pre-sanitize numeric fields to support comma-formatted values
        if ($request->has('invoice_amounts')) {
            $cleanAmounts = array_map(function ($amt) {
                return (string) $this->parseFormattedNumber($amt);
            }, $request->invoice_amounts);
            $request->merge(['invoice_amounts' => $cleanAmounts]);
        }

        foreach (['income_amount', 'other_amounts', 'surplus_amount'] as $field) {
            if ($request->has($field)) {
                $request->merge([
                    $field => (string) $this->parseFormattedNumber($request->input($field))
                ]);
            }
        }

        $request->validate([
            'mode' => 'required|in:Transfer,Deposit',
            'bank_code' => 'required|string',
            'income_date' => 'required|date',
            'income_amount' => 'required|numeric',
            'other_amounts' => 'nullable|numeric',
            'company_id' => 'nullable|exists:clients,id',
            'invoice_id' => 'nullable|exists:register_invoice,id',
            'invoice_ids'       => 'nullable|array',
            'invoice_ids.*'     => 'exists:register_invoice,id',
            'invoice_amounts'   => 'nullable|array',
            'invoice_amounts.*' => 'nullable|numeric|min:0',
            'rc_number' => 'nullable|string|max:255',
            'rc_date' => 'nullable|date',
            'receipt_consecutive_id' => 'nullable|exists:receipt_consecutive,id',
            'surplus_invoice_id' => 'nullable|exists:register_invoice,id',
            'surplus_amount' => 'nullable|numeric|min:0',
        ]);

        // Multi-invoice distribution validation
        $totalIncome = $this->parseFormattedNumber($request->income_amount ?? 0);
        $distributed = collect($request->invoice_amounts ?? [])->map(function ($amt) {
            return $this->parseFormattedNumber($amt);
        })->sum();

        // Multi-invoice distribution validation with rounding tolerance (0.01)
        if ($distributed > 0 && abs($distributed - $totalIncome) > 0.01) {
            return response()->json([
                'status' => false,
                'message' => 'Total distributed amount must match income amount'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $otherAmounts = $this->parseFormattedNumber($request->other_amounts ?? '0');
            $incomeAmount = $this->parseFormattedNumber($request->income_amount);
            $totalPaid = $incomeAmount + $otherAmounts;

            $invoiceVal = $request->invoice_id ? RegisterInvoice::where('id', $request->invoice_id)->first() : null;
            $invoiceTotal = $invoiceVal ? (float) $invoiceVal->total : 0.0;
            $balanceVal = $invoiceVal ? ($invoiceTotal - $totalPaid) : 0.0;

            // Auto-generate RC number if not provided and a receipt consecutive is selected
            $rcNumber = $request->rc_number;
            $rcAutoGenerated = false;
            if ((!$rcNumber || trim((string)$rcNumber) === '') && $request->receipt_consecutive_id) {
                $receiptConsecutive = ReceiptConsecutive::where('id', $request->receipt_consecutive_id)
                    ->lockForUpdate()
                    ->first();

                if (!$receiptConsecutive) {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => 'Receipt consecutive not found.',
                    ], 404);
                }

                $rcPrefix = trim((string) $receiptConsecutive->consecutive_name);
                $rcNext = (int) ($receiptConsecutive->next_number ?: 1);

                $rcNumber = ($rcPrefix !== '' ? $rcPrefix . '-' : 'RC-') . $rcNext;
                $receiptConsecutive->next_number = $rcNext + 1;
                $receiptConsecutive->save();
                $rcAutoGenerated = true;
            }

            // Prepare invoice IDs and amounts
            $invoiceIds = $request->input('invoice_ids', []);
            $invoiceAmounts = $request->input('invoice_amounts', []);

            // Fallback for single invoice
            if (empty($invoiceIds) && $request->invoice_id) {
                $invoiceIds = [$request->invoice_id];
                $invoiceAmounts = [$incomeAmount];
            }

            if (empty($invoiceIds)) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'No invoices selected to distribute the income.'
                ], 422);
            }

            $income = null;
            $count = count($invoiceIds);

            foreach ($invoiceIds as $index => $invoiceId) {
                $distributedAmount = isset($invoiceAmounts[$index]) ? (float)$invoiceAmounts[$index] : 0;

                if ($count === 1 && $distributedAmount === 0) {
                    $distributedAmount = $incomeAmount;
                }

                $inv = RegisterInvoice::find($invoiceId);
                if (!$inv) continue;

                // Determine RC Number for this specific invoice (Suffix only for Cash Receipt if multiple)
                $receiptNo = $count > 1 ? $rcNumber . '-' . ($index + 1) : $rcNumber;

                // CREATE CASH RECEIPT FIRST
                if (!empty($receiptNo)) {
                    CashReceipt::create([
                        'invoice_id'     => $inv->id,
                        'receipt_no'     => $receiptNo,
                        'receipt_date'   => $request->rc_date ?? now(),
                        'amount'         => $distributedAmount,
                        'payment_method' => $request->mode,
                        'bank_code'      => $request->bank_code,
                        'created_by'     => auth()->id(),
                    ]);
                }

                // Sync balance and status
                $totalPaidSoFar = CashReceipt::where('invoice_id', $inv->id)->sum('amount');
                $totalCreditNotes = \App\Models\CreditNote::where('invoice_id', $inv->id)->sum('total');
                $newBalance = max((float)$inv->total - $totalPaidSoFar - $totalCreditNotes, 0);

                if ($newBalance <= 0) {
                    $invoiceStatus = 'Paid';
                } elseif ($totalPaidSoFar > 0 || $totalCreditNotes > 0) {
                    $invoiceStatus = 'Partial';
                } else {
                    $invoiceStatus = 'On time';
                }

                // CREATE INCOME RECORD
                $income = IncomeRecord::create([
                    'mode' => $request->mode,
                    'bank_code' => $request->bank_code,
                    'company_id' => $request->company_id,
                    'company' => $request->company,
                    'commercial_name' => $request->commercial_name,
                    'income_date' => $request->income_date,
                    'income_amount' => $distributedAmount,
                    'other_amounts' => ($index === 0) ? $otherAmounts : 0,
                    'total_paid' => $distributedAmount + (($index === 0) ? $otherAmounts : 0),
                    'invoice_id' => $inv->id,
                    'invoice_number' => $inv->invoiceNumber,
                    'invoice_date' => $inv->invoiceDate,
                    'concept' => $request->concept,
                    'invoice_period' => $inv->periodPaid ?? $inv->paidPeriod,
                    'invoice_value' => (float)$inv->total,
                    'balance' => $newBalance,
                    'rc_number' => $rcNumber, // Use the BASE RC number for all records in the batch
                    'rc_date' => $request->rc_date,
                    'created_by' => auth()->id(),
                ]);

                // UPDATE BILLING LIST
                $billingExists = DB::table('billing_list')->where('invoiceID', $inv->id)->exists();
                if ($billingExists) {
                    DB::table('billing_list')
                        ->where('invoiceID', $inv->id)
                        ->update([
                            'balance'    => $newBalance,
                            'estado'     => $invoiceStatus,
                            'updated_at' => now()
                        ]);
                } else {
                    DB::table('billing_list')->insert([
                        'invoiceID'           => $inv->id,
                        'commercialID'        => $inv->commercialID,
                        'user_type'           => $inv->user_type,
                        'company'             => $inv->company,
                        'commercialName'      => $inv->commercialName,
                        'concept'             => $inv->licensedConcept,
                        'licensedConcept'     => $inv->licensedConcept,
                        'licensedEnvironment' => $inv->licensedEnvironment,
                        'invoiceNumber'       => $inv->invoiceNumber,
                        'invoiceDate'         => $inv->invoiceDate,
                        'periodPaid'          => $inv->periodPaid,
                        'paidPeriod'          => $inv->paidPeriod,
                        'criterion'           => $inv->criterion,
                        'subTotal'            => $inv->subTotal,
                        'vat'                 => $inv->vat,
                        'total'               => $inv->total,
                        'balance'             => $newBalance,
                        'estado'              => $invoiceStatus,
                        'createdBy'           => auth()->id(),
                        'created_at'          => now(),
                        'updated_at'          => now(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => $rcAutoGenerated
                    ? ('Income registered successfully. RC Number: ' . $rcNumber)
                    : 'Income registered successfully',
                'id' => $income->id,
                'invoice_updated' => $request->invoice_id ? true : false
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getCompanyInvoices($companyId, Request $request)
    {
        // Get income date from request if provided
        $incomeDate = $request->get('income_date');

        // Get all invoices for this company with remaining balance
        $invoices = RegisterInvoice::where('commercialID', $companyId)
            ->with('budget')
            ->when($incomeDate, function ($query) use ($incomeDate) {
                // If income date is provided, filter invoices with date <= income date
                return $query->whereDate('invoiceDate', '<=', $incomeDate);
            })
            ->orderBy('invoiceDate', 'desc')
            ->get()
            ->map(function ($invoice) {
                // Calculate amounts paid via cash receipts
                $totalPaid = (float) CashReceipt::where('invoice_id', $invoice->id)->sum('amount');

                // Calculate credit note amount
                $cnTotal = (float) CreditNote::where(
                    'invoice_id',
                    $invoice->id
                )->sum('total');

                // Calculate remaining balance
                $balance = max((float) $invoice->total - $totalPaid - $cnTotal, 0);

                // Only return invoices with remaining balance > 0.01 (to account for rounding)
                if ($balance < 0.01) {
                    return null;
                }

                $budget = Budget::find($invoice->budgetID);

                return [
                    'id'              => $invoice->id,
                    'invoice_number'  => $invoice->invoiceNumber,
                    'invoice_date'    => $invoice->invoiceDate
                        ? Carbon::parse($invoice->invoiceDate)->format('Y-m-d')
                        : null,
                    'concept'         => $budget?->licensedConcept ?? $invoice->licensedConcept ?? 'N/A',
                    'invoice_period'  => $invoice->periodPaid ?? 'N/A',
                    'invoice_value'   => (float) $invoice->total,
                    'balance'         => $balance,
                    'total_paid'      => $totalPaid,
                    'commercial_name' => $invoice->commercialName,
                    'company'         => $invoice->company,
                    'criterion'       => $invoice->criterion,
                ];
            })
            ->filter() // Remove null entries (fully paid invoices)
            ->values();

        return response()->json($invoices);
    }

    public function getIncome($id)
    {
        $income = IncomeRecord::findOrFail($id);

        $client = null;
        if ($income->company_id) {
            $client = Clients::find($income->company_id);
        }

        $invoice = null;
        if ($income->invoice_id) {
            $invoice = RegisterInvoice::find($income->invoice_id);
        }

        return response()->json([
            'id' => $income->id,
            'mode' => $income->mode,
            'bank_code' => $income->bank_code,
            'company_id' => $income->company_id,
            'company' => $income->company,
            'commercial_name' => $income->commercial_name,
            'income_date' => $income->income_date,
            'income_amount' => (float) $income->income_amount,
            'other_amounts' => (float) $income->other_amounts,
            'total_paid' => (float) $income->total_paid,
            'invoice_id' => $income->invoice_id,
            'invoice_number' => $income->invoice_number,
            'invoice_date' => $income->invoice_date,
            'concept' => $income->concept,
            'invoice_period' => $income->invoice_period,
            'invoice_value' => $income->invoice_value ? (float) $income->invoice_value : null,
            'balance' => $income->balance !== null ? (float) $income->balance : null,
            'rc_number' => $income->rc_number,
            'rc_date' => $income->rc_date,
            'has_company' => $income->company_id ? true : false,
            'has_invoice' => $income->invoice_id ? true : false,
        ]);
    }

    public function updateIncome(Request $request, $id)
    {
        $income = IncomeRecord::findOrFail($id);

        $request->validate([
            'mode'          => 'required|in:Transfer,Deposit',
            'bank_code'     => 'required|string',
            'income_date'   => 'required|date',
            'income_amount' => 'required',
            'other_amounts' => 'nullable',
            'rc_number'     => 'nullable|string',
            'rc_date'       => 'nullable|date',
            'invoice_value' => 'nullable',
        ]);

        try {
            DB::beginTransaction();

            // Auto-generate RC number if consecutive selected
            // but no manual RC number provided
            $rcNumber = $request->rc_number;
            $rcAutoGenerated = false;

            if (
                (!$rcNumber || trim((string)$rcNumber) === '')
                && $request->receipt_consecutive_id
            ) {
                $receiptConsecutive = ReceiptConsecutive::where(
                    'id',
                    $request->receipt_consecutive_id
                )
                    ->lockForUpdate()
                    ->first();

                if ($receiptConsecutive) {
                    $rcPrefix = trim(
                        (string) $receiptConsecutive->consecutive_name
                    );
                    $rcNext = (int) (
                        $receiptConsecutive->next_number ?: 1
                    );
                    $rcNumber = (
                        $rcPrefix !== ''
                        ? $rcPrefix . '-'
                        : 'RC-'
                    ) . $rcNext;
                    $receiptConsecutive->next_number = $rcNext + 1;
                    $receiptConsecutive->save();
                    $rcAutoGenerated = true;
                }
            }

            $incomeAmount = $this->parseFormattedNumber($request->income_amount);
            $otherAmounts = $this->parseFormattedNumber($request->other_amounts ?? '0');
            $totalPaid    = $incomeAmount + $otherAmounts;

            // Fix invoice_value parsing - remove $ and spaces before parsing
            $rawInvoiceValue = $request->invoice_value
                ? preg_replace('/[^0-9,.]/', '', trim($request->invoice_value))
                : null;
            $invoiceValue = $rawInvoiceValue
                ? $this->parseFormattedNumber($rawInvoiceValue)
                : (float) $income->invoice_value;

            // Prefer the RC record's stored invoice value; only fallback when the value is missing.
            if ($invoiceValue <= 0 && $request->invoice_id) {
                $invoiceRecord = RegisterInvoice::find($request->invoice_id);
                if ($invoiceRecord) {
                    $invoiceValue = (float) $invoiceRecord->total;
                }
            }

            $income->mode            = $request->mode;
            $income->bank_code       = $request->bank_code;
            $income->company_id      = $request->company_id;
            $income->company         = $request->company;
            $income->commercial_name = $request->commercial_name;
            $income->income_date     = $request->income_date;
            $income->income_amount   = $incomeAmount;
            $income->other_amounts   = $otherAmounts;
            $income->total_paid      = $totalPaid;
            $income->invoice_value   = $invoiceValue;
            $income->invoice_id      = $request->invoice_id;
            $income->invoice_number  = $request->invoice_number;
            $income->invoice_date    = $request->invoice_date;
            $income->concept         = $request->concept;
            $income->invoice_period  = $request->invoice_period;
            $income->rc_number       = $rcNumber;
            $income->rc_date         = $request->rc_date;

            // Calculate balance correctly
            if ($income->invoice_id && $invoiceValue) {
                $previousPayments = IncomeRecord::where('invoice_id', $income->invoice_id)
                    ->where('id', '!=', $income->id)
                    ->whereNotNull('rc_number')
                    ->sum('total_paid');
                $newBalance = $invoiceValue - $previousPayments - $totalPaid;
                $income->balance = max($newBalance, 0);
            }

            $income->save();

            // Handle CashReceipt creation or update
            if ($rcNumber && $request->invoice_id) {
                $existingRC = CashReceipt::where(
                    'invoice_id',
                    $request->invoice_id
                )
                    ->where('receipt_no', $rcNumber)
                    ->first();

                if ($existingRC) {
                    // Update existing cash receipt
                    $existingRC->update([
                        'receipt_no'     => $rcNumber,
                        'amount'         => $totalPaid,
                        'receipt_date'   => $request->rc_date ?? now(),
                        'payment_method' => $request->mode,
                        'bank_code'      => $request->bank_code,
                    ]);
                } else {
                    // Create new cash receipt if none exists
                    CashReceipt::create([
                        'invoice_id'     => $request->invoice_id,
                        'receipt_no'     => $rcNumber,
                        'receipt_date'   => $request->rc_date ?? now(),
                        'amount'         => $totalPaid,
                        'payment_method' => $request->mode,
                        'bank_code'      => $request->bank_code,
                        'created_by'     => auth()->id(),
                    ]);
                }
            }

            // Sync billing_list after update
            if ($request->invoice_id) {
                $invoice = RegisterInvoice::find($request->invoice_id);
                if ($invoice) {
                    // Get total paid via cash receipts
                    $totalPaidSoFar = CashReceipt::where(
                        'invoice_id',
                        $invoice->id
                    )->sum('amount');

                    // Get total credit notes for this invoice
                    $totalCreditNotes = \App\Models\CreditNote::where(
                        'invoice_id',
                        $invoice->id
                    )->sum('total');

                    // Calculate balance including credit notes
                    $newBalance = max(
                        (float)$invoice->total
                            - $totalPaidSoFar
                            - $totalCreditNotes,
                        0
                    );

                    // Determine correct status
                    if ($newBalance <= 0) {
                        $invoiceStatus = 'Paid';
                    } elseif (
                        $totalPaidSoFar > 0
                        || $totalCreditNotes > 0
                    ) {
                        $invoiceStatus = 'Partial';
                    } else {
                        $invoiceStatus = 'On time';
                    }

                    // Check if billing_list row exists
                    $billingExists = DB::table('billing_list')
                        ->where('invoiceID', $invoice->id)
                        ->exists();

                    if ($billingExists) {
                        // Update existing record
                        DB::table('billing_list')
                            ->where('invoiceID', $invoice->id)
                            ->update([
                                'balance'    => $newBalance,
                                'estado'     => $invoiceStatus,
                                'updated_at' => now()
                            ]);
                    } else {
                        // Create missing billing_list record
                        DB::table('billing_list')->insert([
                            'invoiceID'           => $invoice->id,
                            'commercialID'        => $invoice->commercialID,
                            'user_type'           => $invoice->user_type,
                            'company'             => $invoice->company,
                            'commercialName'      => $invoice->commercialName,
                            'concept'             => $invoice->licensedConcept,
                            'licensedConcept'     => $invoice->licensedConcept,
                            'licensedEnvironment' => $invoice->licensedEnvironment,
                            'invoiceNumber'       => $invoice->invoiceNumber,
                            'invoiceDate'         => $invoice->invoiceDate,
                            'periodPaid'          => $invoice->periodPaid,
                            'paidPeriod'          => $invoice->paidPeriod,
                            'criterion'           => $invoice->criterion,
                            'subTotal'            => $invoice->subTotal,
                            'vat'                 => $invoice->vat,
                            'total'               => $invoice->total,
                            'balance'             => $newBalance,
                            'estado'              => $invoiceStatus,
                            'createdBy'           => auth()->id(),
                            'created_at'          => now(),
                            'updated_at'          => now(),
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => $rcAutoGenerated
                    ? 'Income updated successfully. RC Number: '
                    . $rcNumber
                    : 'Income updated successfully',
                'rc_number' => $rcNumber,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function downloadIncomeReport(Request $request)
    {

        $start = $request->get('start_date');
        $end   = $request->get('end_date');

        // Same filters as getIncomeData()
        $incomes = IncomeRecord::query()
            ->when($start, fn($q) => $q->whereDate('income_date', '>=', $start))
            ->when($end,   fn($q) => $q->whereDate('income_date', '<=', $end))
            ->orderBy('income_date', 'desc')
            ->get();

        // Create spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Income');

        // Header styling (same style as other exports)
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
            ],
        ];

        // Columns for the Income report
        $headers = [
            'Mode',
            'Bank',
            'Company',
            'Commercial Name',
            'Income Date',
            'Income Amount',
            'Other Amounts',
            'Total Paid',
            'Invoice Number',
            'Invoice Date',
            'Concept',
            'Invoice Period',
            'Invoice Value',
            'Balance',
            'RC Number',
            'RC Date',
        ];

        // Set header row
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->applyFromArray($headerStyle);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        $row = 2;
        $totalIncomeAmount = 0;
        $totalOtherAmounts = 0;
        $totalPaidAmount   = 0;

        foreach ($incomes as $income) {
            // Bank: code + name
            $bank = $income->bank_code ? Bank::find($income->bank_code) : null;
            $bankLabel = $bank ? ($bank->bank_code . ' - ' . $bank->bank_name) : '-';

            $sheet->setCellValue('A' . $row, $income->mode);
            $sheet->setCellValue('B' . $row, $bankLabel);
            $sheet->setCellValue('C' . $row, $income->company ?? 'N/A');
            $sheet->setCellValue('D' . $row, $income->commercial_name ?? 'N/A');
            $sheet->setCellValue(
                'E' . $row,
                $income->income_date
                    ? Carbon::parse($income->income_date)->format('d-m-Y')
                    : 'N/A'
            );

            $sheet->setCellValue('F' . $row, (float) $income->income_amount);
            $sheet->setCellValue('G' . $row, (float) $income->other_amounts);
            $sheet->setCellValue('H' . $row, (float) $income->total_paid);

            $sheet->setCellValue('I' . $row, $income->invoice_number ?? 'N/A');
            $sheet->setCellValue(
                'J' . $row,
                $income->invoice_date
                    ? Carbon::parse($income->invoice_date)->format('d-m-Y')
                    : 'N/A'
            );
            $sheet->setCellValue('K' . $row, $income->concept ?? 'N/A');
            $sheet->setCellValue('L' . $row, $income->invoice_period ?? 'N/A');

            $sheet->setCellValue('M' . $row, $income->invoice_value !== null ? (float) $income->invoice_value : 0);
            $sheet->setCellValue('N' . $row, $income->balance !== null ? (float) $income->balance : 0);

            $sheet->setCellValue('O' . $row, $income->rc_number ?? 'N/A');
            $sheet->setCellValue(
                'P' . $row,
                $income->rc_date
                    ? Carbon::parse($income->rc_date)->format('d-m-Y')
                    : 'N/A'
            );

            // Number formats for amounts
            $sheet->getStyle('F' . $row . ':H' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('M' . $row . ':N' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

            // Totals
            $totalIncomeAmount += (float) $income->income_amount;
            $totalOtherAmounts += (float) $income->other_amounts;
            $totalPaidAmount   += (float) $income->total_paid;

            $row++;
        }

        // Totals row
        if ($row > 2) {
            $sheet->setCellValue('E' . $row, 'TOTALS:');
            $sheet->setCellValue('F' . $row, $totalIncomeAmount);
            $sheet->setCellValue('G' . $row, $totalOtherAmounts);
            $sheet->setCellValue('H' . $row, $totalPaidAmount);

            $sheet->getStyle('E' . $row . ':H' . $row)->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E7E6E6']
                ],
            ]);
            $sheet->getStyle('F' . $row . ':H' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        }

        // File name like your other exports
        $filename = 'Income_Report_' .
            ($start ? Carbon::parse($start)->format('Ymd') : 'all') .
            '_to_' .
            ($end ? Carbon::parse($end)->format('Ymd') : 'all') .
            '.xlsx';

        $writer = new Xlsx($spreadsheet);

        // Output headers
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function getInvoiceDetails($invoiceId)
    {
        $invoice = RegisterInvoice::with('budget')->find($invoiceId);

        if (!$invoice) {
            return response()->json(['error' => 'Invoice not found'], 404);
        }

        $budget = Budget::find($invoice->budgetID);

        // Calculate amounts paid via cash receipts
        $totalPaid = (float) CashReceipt::where('invoice_id', $invoice->id)->sum('amount');

        // Calculate credit note amount
        $cn = CreditNote::where('invoice_id', $invoice->id)->first();
        $cnTotal = $cn ? (float) $cn->total : 0.0;

        // Calculate remaining balance
        $balance = max((float) $invoice->total - $totalPaid - $cnTotal, 0);

        return response()->json([
            'id'              => $invoice->id,
            'invoice_number'  => $invoice->invoiceNumber,
            'invoice_date'    => $invoice->invoiceDate
                ? Carbon::parse($invoice->invoiceDate)->format('Y-m-d')
                : null,
            'concept'         => $budget?->concept ?? $invoice->licensedConcept ?? 'N/A',
            'invoice_period'  => $invoice->periodPaid ?? 'N/A',
            'invoice_value'   => (float) $invoice->total,
            'balance'         => $balance,
            'total_paid'      => $totalPaid,
            'commercial_name' => $invoice->commercialName,
            'company'         => $invoice->company,
            'criterion'       => $invoice->criterion,
        ]);
    }

    private function calculateIncomeTotals($start = null, $end = null)
    {
        $query = IncomeRecord::query()
            ->when($start, fn($q) => $q->whereDate('income_date', '>=', $start))
            ->when($end, fn($q) => $q->whereDate('income_date', '<=', $end));

        $incomes = $query->get();

        $totalIncome = $incomes->sum('income_amount');
        $totalOther = $incomes->sum('other_amounts');
        $totalPaid = $incomes->sum('total_paid');

        // Group by concept
        $byConceptQuery = IncomeRecord::query()
            ->select('concept', DB::raw('SUM(total_paid) as total'))
            ->whereNotNull('concept')
            ->when($start, fn($q) => $q->whereDate('income_date', '>=', $start))
            ->when($end, fn($q) => $q->whereDate('income_date', '<=', $end))
            ->groupBy('concept')
            ->get();

        $conceptTotals = $byConceptQuery->mapWithKeys(function ($item) {
            return [$item->concept => (float)$item->total];
        })->toArray();

        return [
            'total_income' => $totalIncome,
            'total_other' => $totalOther,
            'total_paid' => $totalPaid,
            'by_concept' => $conceptTotals,
        ];
    }

    public function getIncomeTotalsByConcept(Request $request)
    {
        $start = $request->start_date;
        $end   = $request->end_date;
        $query = IncomeRecord::query()
            ->leftJoin('register_invoice as ri', 'income_records.invoice_id', '=', 'ri.id')
            ->leftJoin('budget as b', 'ri.budgetID', '=', 'b.id');

        if ($start && $end) {
            $query->whereBetween('income_records.income_date', [$start, $end]);
        } elseif ($start) {
            $query->whereDate('income_records.income_date', '>=', $start);
        } elseif ($end) {
            $query->whereDate('income_records.income_date', '<=', $end);
        }
        $rows = $query
            ->select(
                DB::raw('COALESCE(b.user_type, "Without Concept") as concept'),
                DB::raw('SUM(income_records.income_amount) as income'),
                DB::raw('SUM(income_records.other_amounts) as other'),
                DB::raw('SUM(income_records.total_paid) as paid')
            )
            ->groupBy('concept')
            ->get();
        $response = [
            'rows' => [],
            'summary' => [
                'income' => 0,
                'other' => 0,
                'paid' => 0,
            ]
        ];
        foreach ($rows as $row) {
            $response['rows'][] = [
                'concept' => $row->concept,
                'income'  => (float) $row->income,
                'other'   => (float) $row->other,
                'paid'    => (float) $row->paid,
            ];

            $response['summary']['income'] += $row->income;
            $response['summary']['other']  += $row->other;
            $response['summary']['paid']   += $row->paid;
        }
        return response()->json($response);
    }


    // ==================== VALIDATION METHODS ====================
    public function getValidationData(Request $request)
    {
        $query = Validation::with(['accountant', 'management', 'creator']);

        if ($request->filled('report_type')) {
            $query->where('report_type', $request->report_type);
        }

        if ($request->filled('status')) {
            if ($request->status === 'pending_accountant') {
                $query->where('accountant_status', 'pending');
            } elseif ($request->status === 'pending_management') {
                $query->where('accountant_status', 'approved')
                    ->where('management_status', 'pending');
            } elseif ($request->status === 'approved') {
                $query->where('accountant_status', 'approved')
                    ->where('management_status', 'approved');
            } elseif ($request->status === 'rejected') {
                $query->where(function ($q) {
                    $q->where('accountant_status', 'rejected')
                        ->orWhere('management_status', 'rejected');
                });
            }
        }

        if ($request->filled('period_start')) {
            $query->where('period_start', '>=', $request->period_start);
        }

        if ($request->filled('period_end')) {
            $query->where('period_end', '<=', $request->period_end);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('report_type', function ($row) {
                $badges = [
                    'billing' => '<span class="badge bg-primary">Billing</span>',
                    'income' => '<span class="badge bg-success">Income</span>'
                ];
                return $badges[$row->report_type] ?? '<span class="badge bg-secondary">N/A</span>';
            })
            ->addColumn('period', function ($row) {
                return date('M d, Y', strtotime($row->period_start)) . ' - ' .
                    date('M d, Y', strtotime($row->period_end));
            })
            ->addColumn('status', function ($row) {
                $status = $row->status;
                $badges = [
                    'Pending Accountant Validation' => 'warning',
                    'Pending Management Validation' => 'info',
                    'Approved' => 'success',
                    'Rejected by Accountant' => 'danger',
                    'Rejected by Management' => 'danger'
                ];

                $color = $badges[$status] ?? 'secondary';
                return '<span class="badge bg-' . $color . '">' . $status . '</span>';
            })
            ->addColumn('accountant', function ($row) {
                if ($row->accountant) {
                    $html = '<div>' . $row->accountant->name . '</div>';
                    if ($row->accountant_validated_at) {
                        $html .= '<small class="text-muted">' .
                            $row->accountant_validated_at->format('M d, Y H:i') .
                            '</small>';
                    }
                    return $html;
                }
                return '<span class="text-muted">Pending</span>';
            })
            ->addColumn('management', function ($row) {
                if ($row->management) {
                    $html = '<div>' . $row->management->name . '</div>';
                    if ($row->management_validated_at) {
                        $html .= '<small class="text-muted">' .
                            $row->management_validated_at->format('M d, Y H:i') .
                            '</small>';
                    }
                    return $html;
                }
                return '<span class="text-muted">Pending</span>';
            })
            ->addColumn('totals', function ($row) {
                $data = $row->concepts_data;
                $total = 0;
                if (isset($data['grandTotal'])) {
                    $total = $data['grandTotal']['total'] ?? 0;
                }
                return $this->formatCurrency($total);
            })
            ->addColumn('creator_name', function ($row) {
                return $row->creator ? $row->creator->name : 'N/A';
            })
            ->addColumn('created_at', function ($row) {
                if (!empty($row->created_at)) {
                    return date('d-m-Y', strtotime($row->created_at));
                }
            })
            ->addColumn('action', function ($row) {
                $buttons = '<div class="btn-group" role="group">';

                // View/Validate button
                $buttons .= '<button type="button" class="btn btn-soft-primary btn-sm" onclick="viewValidation(' . $row->id . ')" title="View/Validate">
                    <iconify-icon icon="solar:eye-bold" class="fs-18"></iconify-icon>
                </button>';

                // Edit button (only for creator or admin when pending)
                if (($row->created_by == auth()->id() || auth()->user()->hasRole('admin')) &&
                    $row->accountant_status == 'pending' && !$row->is_locked
                ) {
                    $buttons .= '<button type="button" class="btn btn-soft-info btn-sm" onclick="editValidation(' . $row->id . ')" title="Edit">
                        <iconify-icon icon="solar:pen-new-square-linear" class="fs-18"></iconify-icon>
                    </button>';
                }

                // Delete button (only for creator or admin when not approved)
                if (($row->created_by == auth()->id() || auth()->user()->hasRole('admin')) &&
                    !($row->accountant_status === 'approved' && $row->management_status === 'approved')
                ) {
                    $buttons .= '<button type="button" class="btn btn-soft-danger btn-sm" onclick="deleteValidation(' . $row->id . ')" title="Delete">
                        <iconify-icon icon="solar:trash-bin-trash-bold" class="fs-18"></iconify-icon>
                    </button>';
                }

                // Resend button (admin only for rejected reports)
                if (
                    auth()->user()->hasRole('admin') &&
                    ($row->accountant_status === 'rejected' || $row->management_status === 'rejected')
                ) {
                    $buttons .= '<button type="button" class="btn btn-soft-warning btn-sm" onclick="resendValidation(' . $row->id . ')" title="Resend">
                        <iconify-icon icon="solar:restart-bold" class="fs-18"></iconify-icon>
                    </button>';
                }

                $buttons .= '</div>';
                return $buttons;
            })
            ->rawColumns(['report_type', 'status', 'accountant', 'management', 'action'])
            ->make(true);
    }

    public function createValidation(Request $request)
    {
        $request->validate([
            'report_type' => 'required|in:billing,income',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'title' => 'nullable|string|max:255'
        ]);

        // Check if validation already exists for this period and type
        $existing = Validation::where('report_type', $request->report_type)
            ->where('period_start', $request->period_start)
            ->where('period_end', $request->period_end)
            ->first();

        if ($existing) {
            return response()->json([
                'status' => false,
                'message' => 'A validation report already exists for this period and type'
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Gather data based on report type
            $conceptsData = [];
            $items = [];

            if ($request->report_type === 'billing') {
                $invoices = RegisterInvoice::whereBetween('invoiceDate', [
                    $request->period_start,
                    $request->period_end
                ])->get();

                if ($invoices->isEmpty()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'No invoices found for the selected period'
                    ], 422);
                }

                // Group by concept
                $grouped = $invoices->groupBy('licensedConcept')->map(function ($group) {
                    return [
                        'subTotal' => $group->sum('subTotal'),
                        'vat' => $group->sum(function ($inv) {
                            return $inv->total - $inv->subTotal;
                        }),
                        'total' => $group->sum('total'),
                        'count' => $group->count()
                    ];
                });

                $conceptsData = [
                    'sections' => [
                        'invoices' => [
                            'title' => 'Invoices by Concept',
                            'concepts' => $grouped->map(function ($data, $concept) {
                                return [
                                    'name' => $concept ?: 'Uncategorized',
                                    'subTotal' => $data['subTotal'],
                                    'vat' => $data['vat'],
                                    'total' => $data['total'],
                                    'count' => $data['count']
                                ];
                            })->values()->toArray()
                        ]
                    ],
                    'grandTotal' => [
                        'subTotal' => $grouped->sum('subTotal'),
                        'vat' => $grouped->sum('vat'),
                        'total' => $grouped->sum('total'),
                        'count' => $invoices->count()
                    ]
                ];

                // Prepare validation items
                foreach ($invoices as $invoice) {
                    $items[] = [
                        'item_type' => 'invoice',
                        'item_id' => $invoice->id,
                        'concept' => $invoice->licensedConcept,
                        'original_amount' => $invoice->total,
                        'accountant_status' => 'pending',
                        'management_status' => 'pending'
                    ];
                }
            } elseif ($request->report_type === 'income') {
                $incomes = IncomeRecord::whereBetween('income_date', [
                    $request->period_start,
                    $request->period_end
                ])->get();

                if ($incomes->isEmpty()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'No income records found for the selected period'
                    ], 422);
                }

                // Group by concept
                $grouped = $incomes->groupBy('concept')->map(function ($group) {
                    return [
                        'total' => $group->sum('total_paid'),
                        'count' => $group->count()
                    ];
                });

                $conceptsData = [
                    'sections' => [
                        'income' => [
                            'title' => 'Income by Concept',
                            'concepts' => $grouped->map(function ($data, $concept) {
                                return [
                                    'name' => $concept ?: 'Uncategorized',
                                    'total' => $data['total'],
                                    'count' => $data['count']
                                ];
                            })->values()->toArray()
                        ]
                    ],
                    'grandTotal' => [
                        'total' => $grouped->sum('total'),
                        'count' => $incomes->count()
                    ]
                ];

                // Prepare validation items
                foreach ($incomes as $income) {
                    $items[] = [
                        'item_type' => 'income',
                        'item_id' => $income->id,
                        'concept' => $income->concept,
                        'original_amount' => $income->total_paid,
                        'accountant_status' => 'pending',
                        'management_status' => 'pending'
                    ];
                }
            }

            // Create validation record
            $validation = Validation::create([
                'report_type' => $request->report_type,
                'period_start' => $request->period_start,
                'period_end' => $request->period_end,
                'title' => $request->title,
                'concepts_data' => $conceptsData,
                'accountant_status' => 'pending',
                'management_status' => 'pending',
                'created_by' => auth()->id(),
            ]);

            // Create validation items
            foreach ($items as $item) {
                $validation->items()->create($item);
            }

            // Send notification to accountants
            $this->sendValidationNotification($validation, 'Contador');

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Validation report created successfully',
                'id' => $validation->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error creating validation: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getValidation($id)
    {
        $validation = Validation::with([
            'items.invoice',
            'items.income',
            'accountant',
            'management',
            'creator'
        ])->findOrFail($id);

        $userRole = $this->getUserValidationRole();
        $canValidate = $this->canUserValidate($validation, $userRole);

        // Get items grouped by concept
        $itemsByConcept = $validation->items->groupBy('concept');

        return response()->json([
            'status' => true,
            'validation' => $validation,
            'items_by_concept' => $itemsByConcept,
            'can_edit' => ($validation->created_by == auth()->id() || auth()->user()->hasRole('admin')) &&
                $validation->accountant_status == 'pending',
            'user_role' => $userRole,
            'can_validate' => $canValidate
        ]);
    }

    private function getValidationReportSummary(Validation $validation)
    {
        if ($validation->report_type === 'billing') {
            return DB::table('billing_list')
                ->whereNotNull('invoiceNumber')
                ->where('total', '>', 0)
                ->whereBetween('invoiceDate', [
                    $validation->period_start,
                    $validation->period_end
                ])
                ->select(
                    'concept',
                    DB::raw('COUNT(*) as count'),
                    DB::raw('COALESCE(SUM(subTotal), 0) as subtotal'),
                    DB::raw('COALESCE(SUM(vat), 0) as vat'),
                    DB::raw('COALESCE(SUM(total), 0) as total')
                )
                ->groupBy('concept')
                ->orderBy('concept')
                ->limit(50)
                ->get();
        }

        return $validation->items
            ->groupBy(function ($item) {
                return $item->concept ?: 'Uncategorized';
            })
            ->map(function ($items, $concept) {
                return (object) [
                    'concept' => $concept,
                    'count' => $items->count(),
                    'subtotal' => 0,
                    'vat' => 0,
                    'total' => $items->sum('original_amount'),
                ];
            })
            ->values();
    }

    private function getValidationReportDetails(Validation $validation)
    {
        // ALL report types use income-only data — the billing early return that
        // injected 'invoiceNumber as no_fc' has been removed to prevent FC data
        // from ever appearing in the No. RC column.

        $results = [];

        foreach ($validation->items as $item) {
            // STRICT gate 1: skip anything that is not an income record
            if ($item->item_type !== 'income') {
                continue;
            }

            $income = $item->income;

            // STRICT gate 2: skip if relationship failed to resolve
            if (!$income) {
                continue;
            }

            // RC Number — enforce RC- prefix, use 'RC-UNKNOWN' if field is empty
            $rc = $income->rc_number;
            if (!empty($rc) && trim((string)$rc) !== '') {
                $number = str_starts_with((string)$rc, 'RC-') ? $rc : 'RC-' . $rc;
            } else {
                $number = 'RC-UNKNOWN';
            }

            // Client — never 'N/A', never an internal ID
            $client = $income->company
                ?? $income->commercial_name
                ?? 'UNKNOWN CLIENT';

            // Date
            $date = $income->income_date ?? '-';

            // Concept comes from validation_items (captured at report creation time)
            $concept = $item->concept ?? '-';

            // Value — prefer validated amount, then original, then income total_paid
            $value = $item->validated_amount
                ?? $item->original_amount
                ?? $income->total_paid
                ?? 0;

            $results[] = (object) [
                'no_fc'   => $number,   // Always RC-xxx, never FC/invoiceNumber/raw ID
                'date'    => $date,
                'client'  => $client,
                'concept' => $concept,
                'value'   => $value,
            ];
        }

        return collect($results);
    }

    public function viewValidation($id)
    {
        $validation = Validation::with([
            'items' => function ($query) {
                // Income-only filter at DB level — no invoice rows ever loaded
                $query->where('item_type', 'income')
                    ->with([
                        // Include all fields needed for rendering
                        'income:id,rc_number,company,commercial_name,income_date,total_paid',
                    ]);
            },
            'accountant',
            'management',
            'creator'
        ])->findOrFail($id);

        $userRole = $this->getUserValidationRole();
        $canValidate = $this->canUserValidate($validation, $userRole);

        // Group items by concept
        $itemsByConcept = $validation->items->groupBy('concept');
        $summary = $this->getValidationReportSummary($validation);
        $details = $this->getValidationReportDetails($validation);

        // Return HTML for modal (AJAX request)
        if (request()->ajax()) {
            return view('budget.validations.modal-content', compact(
                'validation',
                'userRole',
                'canValidate',
                'itemsByConcept',
                'summary',
                'details'
            ))->render();
        }

        // Return full page view
        $pageTitle = 'Validation Report';
        return view('budget.validations.view', compact(
            'pageTitle',
            'validation',
            'userRole',
            'canValidate',
            'itemsByConcept',
            'summary',
            'details'
        ));
    }

    public function updateValidation(Request $request, $id)
    {
        $validation = Validation::findOrFail($id);

        // Check permissions
        if ($validation->created_by !== auth()->id() && !auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to edit this report'
            ], 403);
        }

        // Check if validation can be edited
        if ($validation->accountant_status !== 'pending' || $validation->is_locked) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot edit validation that has been processed or is locked'
            ], 422);
        }

        $request->validate([
            'title' => 'nullable|string|max:255',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start'
        ]);

        $validation->update([
            'title' => $request->title,
            'period_start' => $request->period_start,
            'period_end' => $request->period_end
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Validation report updated successfully'
        ]);
    }

    public function submitValidation(Request $request, $id)
    {
        $validation = Validation::with('items')->findOrFail($id);
        $userRole = $this->getUserValidationRole();

        if (!$this->canUserValidate($validation, $userRole)) {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to validate this report'
            ], 403);
        }

        $request->validate([
            'action' => 'required|in:approve,reject',
            'notes' => 'required_if:action,reject|nullable|string|max:1000',
            'items' => 'sometimes|array',
            'items.*.status' => 'required|in:approved,rejected,pending',
            'items.*.notes' => 'nullable|string|max:500',
            'items.*.validated_amount' => 'nullable|numeric|min:0'
        ]);

        DB::beginTransaction();

        try {
            $hasRejectedItems = false;

            // Process individual items first
            if ($request->has('items')) {
                foreach ($request->items as $itemId => $itemData) {
                    $validationItem = $validation->items()->find($itemId);
                    if (!$validationItem) continue;

                    if ($userRole === 'Contador') {
                        $updateData = [
                            'accountant_status' => $itemData['status'],
                            'accountant_notes' => $itemData['notes'] ?? null
                        ];

                        if (isset($itemData['validated_amount']) && $itemData['validated_amount'] !== null) {
                            $updateData['validated_amount'] = $itemData['validated_amount'];
                        }

                        if ($itemData['status'] === 'rejected') {
                            $hasRejectedItems = true;
                        }

                        $validationItem->update($updateData);
                    } elseif ($userRole === 'Gerencia') {
                        $validationItem->update([
                            'management_status' => $itemData['status'],
                            'management_notes' => $itemData['notes'] ?? null
                        ]);

                        if ($itemData['status'] === 'rejected') {
                            $hasRejectedItems = true;
                        }
                    } elseif ($userRole === 'admin' || $userRole === 'Admin' || $userRole === 'master admin' || $userRole === 'Master Admin') {
                        $validationItem->update([
                            'management_status' => $itemData['status'],
                            'management_notes' => $itemData['notes'] ?? null
                        ]);

                        $validation->update([
                            'accountant_status' => $itemData['status'],
                            'management_status' => $itemData['status']
                        ]);

                        if ($itemData['status'] === 'rejected') {
                            $hasRejectedItems = true;
                        }
                    }
                }
            }

            // If any items are rejected, overall action must be reject
            if ($hasRejectedItems && $request->action === 'approve') {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Cannot approve validation when some items are rejected. Please reject the entire report.'
                ], 422);
            }

            // Update validation record
            if ($userRole === 'Contador') {
                $validation->accountant_id = auth()->id();
                $validation->accountant_validated_at = now();
                $validation->accountant_notes = $request->notes;
                $validation->accountant_status = $request->action === 'approve' ? 'approved' : 'rejected';

                // Update overall status
                if ($request->action === 'approve') {
                    $validation->status = 'Pending Management Validation';
                    // Notify management for next validation
                    $this->sendValidationNotification($validation, 'Gerencia');
                } else {
                    $validation->status = 'Rejected by Accountant';
                    // Notify admin about rejection
                    $this->sendValidationNotification($validation, 'admin', 'rejected_by_accountant');
                }
            } elseif ($userRole === 'Gerencia') {
                // Check if accountant approved first
                if ($validation->accountant_status !== 'approved') {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => 'Accountant must approve before management validation'
                    ], 400);
                }

                $validation->management_id = auth()->id();
                $validation->management_validated_at = now();
                $validation->management_notes = $request->notes;
                $validation->management_status = $request->action === 'approve' ? 'approved' : 'rejected';

                if ($request->action === 'approve') {
                    // Lock the validation and update status
                    $validation->is_locked = true;
                    $validation->status = 'Approved';
                    // Notify creator and admin
                    $this->sendValidationNotification($validation, 'admin', 'fully_approved');
                } else {
                    $validation->status = 'Rejected by Management';
                    // Notify accountant to review again
                    $this->sendValidationNotification($validation, 'Contador', 'rejected_by_management');
                }
            }

            $validation->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Validation ' . ($request->action === 'approve' ? 'approved' : 'rejected') . ' successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Validation submission error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteValidation($id)
    {
        $validation = Validation::findOrFail($id);

        // Check permissions
        if ($validation->created_by !== auth()->id() && !auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to delete this report'
            ], 403);
        }

        // Check if validation can be deleted
        if ($validation->accountant_status === 'approved' && $validation->management_status === 'approved') {
            return response()->json([
                'status' => false,
                'message' => 'Cannot delete fully approved validation report'
            ], 422);
        }

        if ($validation->is_locked && !auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot delete locked validation report'
            ], 422);
        }

        $validation->delete();

        return response()->json([
            'status' => true,
            'message' => 'Validation report deleted successfully'
        ]);
    }

    public function previewValidation(Request $request)
    {
        $request->validate([
            'report_type' => 'required|in:billing,income',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
        ]);

        $html = '';
        $totalCount = 0;
        $totalAmount = 0;

        if ($request->report_type === 'billing') {
            $invoices = RegisterInvoice::whereBetween('invoiceDate', [
                $request->period_start,
                $request->period_end
            ])->get();

            if ($invoices->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No invoices found for the selected period'
                ]);
            }

            $grouped = $invoices->groupBy('licensedConcept');

            foreach ($grouped as $concept => $items) {
                $conceptTotal = $items->sum('total');
                $count = $items->count();
                $totalCount += $count;
                $totalAmount += $conceptTotal;

                $html .= '<tr>';
                $html .= '<td>' . ($concept ?: 'Uncategorized') . '</td>';
                $html .= '<td class="text-end">' . $count . '</td>';
                $html .= '<td class="text-end">' . $this->formatCurrency($conceptTotal) . '</td>';
                $html .= '</tr>';
            }
        } elseif ($request->report_type === 'income') {
            $incomes = IncomeRecord::whereBetween('income_date', [
                $request->period_start,
                $request->period_end
            ])->get();

            if ($incomes->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No income records found for the selected period'
                ]);
            }

            $grouped = $incomes->groupBy('concept');

            foreach ($grouped as $concept => $items) {
                $conceptTotal = $items->sum('total_paid');
                $count = $items->count();
                $totalCount += $count;
                $totalAmount += $conceptTotal;

                $html .= '<tr>';
                $html .= '<td>' . ($concept ?: 'Uncategorized') . '</td>';
                $html .= '<td class="text-end">' . $count . '</td>';
                $html .= '<td class="text-end">' . $this->formatCurrency($conceptTotal) . '</td>';
                $html .= '</tr>';
            }
        }

        // Add totals row
        $html .= '<tr class="fw-bold table-active">';
        $html .= '<td>Total</td>';
        $html .= '<td class="text-end">' . $totalCount . '</td>';
        $html .= '<td class="text-end">' . $this->formatCurrency($totalAmount) . '</td>';
        $html .= '</tr>';

        return response()->json([
            'status' => true,
            'html' => $html,
            'total_count' => $totalCount,
            'total_amount' => $totalAmount
        ]);
    }

    public function resendValidation(Request $request, $id)
    {
        $validation = Validation::findOrFail($id);

        // Only admin can resend
        if (!auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => false,
                'message' => 'Only administrators can resend validation reports'
            ], 403);
        }

        $request->validate([
            'resend_to' => 'required|in:Accountant,Management',
            'unlock' => 'sometimes|boolean'
        ]);

        if ($request->resend_to === 'Contador') {
            $validation->accountant_status = 'pending';
            $validation->accountant_id = null;
            $validation->accountant_validated_at = null;

            // Keep notes for reference but reset status
            $validation->items()->update([
                'accountant_status' => 'pending',
                'validated_amount' => null
            ]);
        } elseif ($request->resend_to === 'Gerencia') {
            $validation->management_status = 'pending';
            $validation->management_id = null;
            $validation->management_validated_at = null;

            $validation->items()->update([
                'management_status' => 'pending'
            ]);
        }

        // Unlock if requested
        if ($request->unlock) {
            $validation->is_locked = false;
        }

        $validation->save();

        // Send notification
        $this->sendValidationNotification($validation, $request->resend_to, 'resent_for_review');

        return response()->json([
            'status' => true,
            'message' => 'Validation report resent successfully'
        ]);
    }

    // Helper methods
    private function getUserValidationRole()
    {
        $user = auth()->user();

        if ($user->hasRole('Contador') || $user->hasRole('Contador') || $user->hasRole('Contador')) {
            return 'Contador';
        } elseif ($user->hasRole('Gerencia') || $user->hasRole('Gerencia') || $user->hasRole('Gerencia')) {
            return 'Gerencia';
        } elseif ($user->hasRole('admin') || $user->hasRole('Admin')) {
            return 'admin';
        } elseif ($user->hasRole('Master Admin') || $user->hasRole('master admin')) {
            return 'master admin';
        }

        return null;
    }

    private function canUserValidate(Validation $validation, $userRole)
    {
        if ($userRole === 'admin') {
            return true;
        }

        if ($userRole === 'Contador' && $validation->accountant_status === 'pending') {
            return true;
        }

        if (
            $userRole === 'Gerencia' &&
            $validation->accountant_status === 'approved' &&
            $validation->management_status === 'pending'
        ) {
            return true;
        }

        return false;
    }

    private function sendValidationNotification(Validation $validation, $toRole, $type = 'new')
    {
        $users = User::role($toRole)->get();

        $messages = [
            'new' => 'A new validation report requires your attention',
            'rejected_by_accountant' => 'A validation report was rejected by the accountant',
            'rejected_by_management' => 'A validation report was rejected by management',
            'fully_approved' => 'A validation report has been fully approved',
            'resent_for_review' => 'A validation report has been resent for your review'
        ];

        $title = $type === 'new' ? 'Validation Required' : 'Validation Update';
        $message = $messages[$type] ?? $messages['new'];

        // foreach ($users as $user) {
        //     Notification::create([
        //         'user_id' => $user->id,
        //         'type' => 'validation',
        //         'title' => $title,
        //         'message' => $message,
        //         'data' => [
        //             'validation_id' => $validation->id,
        //             'report_type' => $validation->report_type,
        //             'period' => $validation->period_start->format('M d, Y') . ' - ' . $validation->period_end->format('M d, Y')
        //         ],
        //         'read_at' => null
        //     ]);
        // }
    }


    // Add these methods to your BudgetController class
    // ==================== DISTRIBUTIONS METHODS ====================

    public function getDistributionsData(Request $request)
    {
        $statusFilter = $request->get('status', 'validated'); // validated, distributable, settled

        $query = Distribution::query()
            ->with(['validation', 'income', 'creator'])
            ->orderBy('created_at', 'desc');

        // Apply status filters
        if ($statusFilter === 'validated') {
            $query->where('status', 'pending');
        } elseif ($statusFilter === 'distributable') {
            $query->where('status', 'distributed');
        } elseif ($statusFilter === 'settled') {
            $query->where('status', 'settled');
        } elseif ($statusFilter === 'paid') {
            $query->where('status', 'paid');
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('distribution_no', fn($row) => $row->distribution_no ?? 'N/A')
            ->addColumn('origin', fn($row) => $row->origin ?? 'N/A')
            ->addColumn('concept', fn($row) => $row->concept ?? 'N/A')
            ->addColumn('distribution_date', function ($row) {
                return $row->distribution_date ? Carbon::parse($row->distribution_date)->format('d-m-Y') : 'N/A';
            })
            ->addColumn('invoice_no', fn($row) => $row->invoice_no ?? 'N/A')
            ->addColumn('rc_no', fn($row) => $row->rc_no ?? 'N/A')
            ->addColumn('base_value', fn($row) => $this->money($row->base_value))
            ->addColumn('vat', fn($row) => $this->money($row->vat))
            ->addColumn('associate_subtotal', fn($row) => $this->money($row->associate_subtotal))
            ->addColumn('admin_subtotal', fn($row) => $this->money($row->admin_subtotal))
            ->addColumn('admin_vat', fn($row) => $this->money($row->admin_vat))
            ->addColumn('admin_total', fn($row) => $this->money($row->admin_total))
            ->addColumn('total_to_pay', fn($row) => $this->money($row->total_to_pay))
            ->addColumn('balance', fn($row) => $this->money($row->balance))
            ->addColumn('status', function ($row) {
                $badges = [
                    'pending' => 'warning',
                    'distributed' => 'info',
                    'settled' => 'success',
                    'paid' => 'primary'
                ];
                $color = $badges[$row->status] ?? 'secondary';
                return '<span class="badge bg-' . $color . '">' . ucfirst($row->status) . '</span>';
            })
            ->addColumn('created_by_name', fn($row) => $row->creator->name ?? 'N/A')
            ->addColumn('action', function ($row) use ($statusFilter) {
                $buttons = '<div class="btn-group" role="group">';

                if ($statusFilter === 'validated') {
                    // Checkbox for selection
                    $buttons .= '<input type="checkbox" class="form-check-input distribution-checkbox" 
                        value="' . $row->id . '" data-concept="' . ($row->concept ?? '') . '">';
                }

                if ($statusFilter === 'distributable' && $row->status === 'distributed') {
                    // Settle button
                    $buttons .= '<button type="button" class="btn btn-soft-primary btn-sm" onclick="openSettlementModal(' . $row->id . ')" 
                        title="Settle Distribution">
                        <iconify-icon icon="solar:money-bag-bold" class="fs-18"></iconify-icon>
                    </button>';
                }

                if ($statusFilter === 'settled' && $row->status === 'settled') {
                    // View settlement details
                    $buttons .= '<button type="button" class="btn btn-soft-info btn-sm" onclick="viewSettlement(' . $row->id . ')" 
                        title="View Settlement">
                        <iconify-icon icon="solar:eye-bold" class="fs-18"></iconify-icon>
                    </button>';
                }

                if (in_array($row->status, ['pending', 'distributed']) && auth()->user()->hasRole(['admin', 'Contador'])) {
                    // Edit button
                    $buttons .= '<button type="button" class="btn btn-soft-warning btn-sm" onclick="editDistribution(' . $row->id . ')" 
                        title="Edit">
                        <iconify-icon icon="solar:pen-new-square-linear" class="fs-18"></iconify-icon>
                    </button>';
                }

                if ($row->status === 'pending' && auth()->user()->hasRole(['admin', 'Contador'])) {
                    // Delete button
                    $buttons .= '<button type="button" class="btn btn-soft-danger btn-sm" onclick="deleteDistribution(' . $row->id . ')" 
                        title="Delete">
                        <iconify-icon icon="solar:trash-bin-trash-bold" class="fs-18"></iconify-icon>
                    </button>';
                }

                $buttons .= '</div>';
                return $buttons;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function getValidatedIncomes(Request $request)
    {
        // Get validated incomes from validation items
        $query = ValidationItem::query()
            ->with(['validation', 'invoice', 'income'])
            ->whereHas('validation', function ($q) {
                $q->where('accountant_status', 'approved')
                    ->where('management_status', 'approved');
            })
            ->where(function ($q) {
                $q->whereNotNull('item_id');
            })
            ->orderBy('created_at', 'desc');

        // Group by concept for totals
        $conceptTotals = clone $query;
        $conceptTotals = $conceptTotals->select(
            'concept',
            DB::raw('SUM(CASE WHEN validated_amount IS NOT NULL THEN validated_amount ELSE original_amount END) as total_amount'),
            DB::raw('COUNT(*) as item_count')
        )->groupBy('concept')->get();

        // Return DataTables response
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('company', function ($row) {
                if ($row->invoice) {
                    return $row->invoice->company ?? 'N/A';
                } elseif ($row->income) {
                    return $row->income->company ?? 'N/A';
                }
                return 'N/A';
            })
            ->addColumn('commercial_name', function ($row) {
                if ($row->invoice) {
                    return $row->invoice->commercialName ?? 'N/A';
                } elseif ($row->income) {
                    return $row->income->commercial_name ?? 'N/A';
                }
                return 'N/A';
            })
            ->addColumn('concept', fn($row) => $row->concept ?? 'N/A')
            ->addColumn('invoice_no', function ($row) {
                if ($row->invoice) {
                    return $row->invoice->invoiceNumber ?? 'N/A';
                }
                return 'N/A';
            })
            ->addColumn('invoice_date', function ($row) {
                if ($row->invoice) {
                    return $row->invoice->invoiceDate ? Carbon::parse($row->invoice->invoiceDate)->format('d-m-Y') : 'N/A';
                }
                return 'N/A';
            })
            ->addColumn('rc_no', function ($row) {
                if ($row->income) {
                    return $row->income->rc_number ?? 'N/A';
                }
                return 'N/A';
            })
            ->addColumn('rc_date', function ($row) {
                if ($row->income) {
                    return $row->income->rc_date ? Carbon::parse($row->income->rc_date)->format('d-m-Y') : 'N/A';
                }
                return 'N/A';
            })
            ->addColumn('base_value', fn($row) => $this->money($row->original_amount))
            ->addColumn('vat', function ($row) {
                // Calculate VAT based on invoice if available
                if ($row->invoice) {
                    $vatAmount = $row->invoice->total - $row->invoice->subTotal;
                    return $this->money($vatAmount);
                }
                return $this->money(0);
            })
            ->addColumn('amount', fn($row) => $this->money($row->validated_amount ?? $row->original_amount))
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="form-check-input validated-income-checkbox" 
                    value="' . $row->id . '" data-concept="' . ($row->concept ?? '') . '"
                    data-amount="' . ($row->validated_amount ?? $row->original_amount) . '">';
            })
            ->rawColumns(['checkbox'])
            ->with(['concept_totals' => $conceptTotals])
            ->make(true);
    }

    public function createDistributions(Request $request)
    {
        $request->validate([
            'item_ids' => 'required|array',
            'item_ids.*' => 'exists:validation_items,id',
            'distribution_date' => 'required|date',
        ]);

        DB::beginTransaction();

        try {
            $items = ValidationItem::whereIn('id', $request->item_ids)
                ->with(['invoice', 'income'])
                ->get();

            // Group by concept for multiple distributions
            $groupedItems = $items->groupBy('concept');

            $createdDistributions = [];

            foreach ($groupedItems as $concept => $conceptItems) {
                // Generate distribution number
                $lastDist = Distribution::orderBy('id', 'desc')->first();
                $distNo = 'DIST-' . str_pad($lastDist ? $lastDist->id + 1 : 1, 6, '0', STR_PAD_LEFT);

                // Calculate totals
                $totalAmount = $conceptItems->sum(function ($item) {
                    return $item->validated_amount ?? $item->original_amount;
                });

                // For now, use simple calculations - adjust based on your business logic
                $baseValue = $totalAmount;
                $vat = $conceptItems->sum(function ($item) {
                    if ($item->invoice) {
                        return $item->invoice->total - $item->invoice->subTotal;
                    }
                    return 0;
                });

                $associateSubtotal = $baseValue * 0.80; // Example: 80% to associates
                $adminSubtotal = $baseValue * 0.20; // Example: 20% admin
                $adminVat = $vat;
                $adminTotal = $adminSubtotal + $adminVat;
                $totalToPay = $associateSubtotal;
                $balance = $associateSubtotal; // Initially full balance

                $distribution = Distribution::create([
                    'distribution_no' => $distNo,
                    'origin' => 'Validation System',
                    'concept' => $concept,
                    'distribution_date' => $request->distribution_date,
                    'invoice_no' => $conceptItems->first()->invoice->invoiceNumber ?? null,
                    'rc_no' => $conceptItems->first()->income->rc_number ?? null,
                    'base_value' => $baseValue,
                    'vat' => $vat,
                    'associate_subtotal' => $associateSubtotal,
                    'admin_subtotal' => $adminSubtotal,
                    'admin_vat' => $adminVat,
                    'admin_total' => $adminTotal,
                    'total_to_pay' => $totalToPay,
                    'balance' => $balance,
                    'status' => 'distributed',
                    'validation_id' => $conceptItems->first()->validation_id,
                    'income_id' => $conceptItems->first()->income_id,
                    'created_by' => auth()->id(),
                    'metadata' => [
                        'item_ids' => $conceptItems->pluck('id')->toArray(),
                        'item_count' => $conceptItems->count(),
                    ],
                ]);

                $createdDistributions[] = $distribution;
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Distributions created successfully',
                'distributions' => $createdDistributions,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error creating distributions: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function createSettlement(Request $request)
    {
        try {
            $request->validate([
                'distribution_id' => 'required|exists:distributions,id',
                'origin' => 'required|string|max:255',
                'income_month_start' => 'required|date',
                'income_month_end' => 'nullable|date|after_or_equal:income_month_start',
                'period_covered' => 'nullable|string|max:255',
                'distribution_formula' => 'nullable|string|max:500',
                'distribution_type' => 'required|in:ownership,manual',
            ]);

            // Additional validation for manual distribution
            if ($request->distribution_type === 'manual') {
                $request->validate([
                    'associates' => 'required|array|min:1',
                    'associates.*.id' => 'required|exists:users,id',
                    'associates.*.type' => 'required|in:percentage,fixed',
                    'associates.*.value' => 'required|numeric|min:0',
                ]);
            }

            DB::beginTransaction();

            $distribution = Distribution::findOrFail($request->distribution_id);

            // Generate settlement number
            $lastSettlement = Settlement::orderBy('id', 'desc')->first();
            $settlementNo = 'SETT-' . str_pad($lastSettlement ? $lastSettlement->id + 1 : 1, 6, '0', STR_PAD_LEFT);

            $totalToDistribute = $distribution->total_to_pay;
            $amountToPay = $totalToDistribute;

            $settlementData = [
                'distribution_type' => $request->distribution_type,
                'associates' => [],
            ];

            $associatesData = [];

            if ($request->distribution_type === 'ownership') {
                throw new \Exception('Ownership distribution type is not yet implemented. Please use manual distribution.');
            } else {
                // Manual distribution
                $totalPercentage = 0;
                $totalFixed = 0;

                foreach ($request->associates as $associate) {
                    if ($associate['type'] === 'percentage') {
                        $totalPercentage += $associate['value'];
                        $calculatedAmount = $totalToDistribute * ($associate['value'] / 100);
                    } else {
                        $totalFixed += $associate['value'];
                        $calculatedAmount = $associate['value'];
                    }

                    $associatesData[] = [
                        'associate_id' => $associate['id'],
                        'percentage' => $associate['type'] === 'percentage' ? $associate['value'] : null,
                        'fixed_amount' => $associate['type'] === 'fixed' ? $associate['value'] : null,
                        'calculated_amount' => $calculatedAmount,
                    ];
                }

                // Validate totals
                $hasPercentages = collect($request->associates)->where('type', 'percentage')->isNotEmpty();
                if ($hasPercentages) {
                    if (abs($totalPercentage - 100) > 0.01) {
                        throw new \Exception('Total percentage must equal 100%. Current total: ' . $totalPercentage . '%');
                    }
                }

                $totalCalculated = collect($associatesData)->sum('calculated_amount');
                if (abs($totalCalculated - $totalToDistribute) > 0.01) {
                    throw new \Exception('Total calculated amount ($' . number_format($totalCalculated, 2) . ') must equal amount to distribute ($' . number_format($totalToDistribute, 2) . ')');
                }
            }

            // Check if we have associates data
            if (empty($associatesData)) {
                throw new \Exception('No associates data available for settlement');
            }

            $settlement = Settlement::create([
                'settlement_no' => $settlementNo,
                'origin' => $request->origin,
                'concept' => $distribution->concept,
                'income_month_start' => $request->income_month_start,
                'income_month_end' => $request->income_month_end,
                'period_covered' => $request->period_covered,
                'distribution_formula' => $request->distribution_formula,
                'total_to_distribute' => $totalToDistribute,
                'amount_to_pay' => $amountToPay,
                'distribution_type' => $request->distribution_type,
                'distribution_data' => $settlementData,
                'associates_data' => $associatesData,
                'status' => 'settled',
                'distribution_id' => $distribution->id,
                'created_by' => auth()->id(),
            ]);

            // Attach associates
            foreach ($associatesData as $associateData) {
                $settlement->associates()->attach($associateData['associate_id'], [
                    'percentage' => $associateData['percentage'],
                    'fixed_amount' => $associateData['fixed_amount'],
                    'calculated_amount' => $associateData['calculated_amount'],
                    'status' => 'pending',
                ]);
            }

            // Update distribution status
            $distribution->update(['status' => 'settled']);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Settlement created successfully',
                'settlement_id' => $settlement->id,
                'settlement_no' => $settlementNo,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Validation error: ' . implode(', ', $e->errors()),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error creating settlement: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getSettlementsData(Request $request)
    {
        $query = Settlement::query()
            ->with(['distribution', 'creator', 'associates'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('concept')) {
            $query->where('concept', 'like', '%' . $request->concept . '%');
        }

        if ($request->filled('period_start')) {
            $query->whereDate('income_month_start', '>=', $request->period_start);
        }

        if ($request->filled('period_end')) {
            $query->whereDate('income_month_end', '<=', $request->period_end);
        }

        // Add payment status filter if provided
        if ($request->filled('payment_status')) {
            $query->where('status', $request->payment_status);
        }

        // Add associate filter if provided
        if ($request->filled('associate_id')) {
            $query->whereHas('associates', function ($q) use ($request) {
                $q->where('users.id', $request->associate_id);
            });
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('settlement_no', fn($row) => $row->settlement_no ?? 'N/A')
            ->addColumn('origin', fn($row) => $row->origin ?? 'N/A')
            ->addColumn('concept', fn($row) => $row->concept ?? 'N/A')
            ->addColumn('income_month', function ($row) {
                if ($row->income_month_start && $row->income_month_end) {
                    return Carbon::parse($row->income_month_start)->format('M Y') . ' - ' .
                        Carbon::parse($row->income_month_end)->format('M Y');
                } elseif ($row->income_month_start) {
                    return Carbon::parse($row->income_month_start)->format('M Y');
                }
                return 'N/A';
            })
            ->addColumn('period_covered', fn($row) => $row->period_covered ?? 'N/A')
            ->addColumn('distribution_formula', fn($row) => $row->distribution_formula ?? 'N/A')
            ->addColumn('total_to_distribute', fn($row) => $this->money($row->total_to_distribute))
            ->addColumn('amount_to_pay', fn($row) => $this->money($row->amount_to_pay))
            ->addColumn('distribution_type', function ($row) {
                $badges = [
                    'ownership' => 'primary',
                    'manual' => 'info'
                ];
                $color = $badges[$row->distribution_type] ?? 'secondary';
                return '<span class="badge bg-' . $color . '">' . ucfirst($row->distribution_type) . '</span>';
            })
            ->addColumn('status', function ($row) {
                $badges = [
                    'pending' => 'warning',
                    'settled' => 'info',
                    'paid' => 'success'
                ];
                $color = $badges[$row->status] ?? 'secondary';
                return '<span class="badge bg-' . $color . '">' . ucfirst($row->status) . '</span>';
            })
            ->addColumn('action', function ($row) {
                $buttons = '<div class="btn-group" role="group">';

                // View details
                $buttons .= '<button type="button" class="btn btn-soft-info btn-sm" onclick="viewSettlementDetails(' . $row->id . ')" 
                    title="View Details">
                    <iconify-icon icon="solar:eye-bold" class="fs-18"></iconify-icon>
                </button>';

                // View associates
                $buttons .= '<button type="button" class="btn btn-soft-primary btn-sm" onclick="viewSettlementAssociates(' . $row->id . ')" 
                    title="View Associates">
                    <iconify-icon icon="solar:users-group-two-rounded-bold" class="fs-18"></iconify-icon>
                </button>';

                // Only show "Mark as Paid" button for settled settlements
                if ($row->status === 'settled' && auth()->user()->hasRole(['admin', 'Contador'])) {
                    $buttons .= '<button type="button" class="btn btn-soft-success btn-sm" onclick="markSettlementPaid(' . $row->id . ')" 
                        title="Mark as Paid">
                        <iconify-icon icon="solar:check-circle-bold" class="fs-18"></iconify-icon>
                    </button>';
                }

                // Download report (available for both settled and paid)
                $buttons .= '<button type="button" class="btn btn-soft-secondary btn-sm" onclick="downloadSettlementReport(' . $row->id . ')" 
                    title="Download Report">
                    <iconify-icon icon="solar:download-bold" class="fs-18"></iconify-icon>
                </button>';

                $buttons .= '</div>';
                return $buttons;
            })
            ->rawColumns(['distribution_type', 'status', 'action'])
            ->make(true);
    }

    public function viewSettlement($id)
    {
        $settlement = Settlement::with(['distribution', 'creator', 'associates'])->findOrFail($id);

        return response()->json([
            'status' => true,
            'settlement' => $settlement,
            'associates' => $settlement->associates->map(function ($associate) {
                return [
                    'id' => $associate->id,
                    'name' => $associate->name,
                    'email' => $associate->email,
                    'percentage' => $associate->pivot->percentage,
                    'fixed_amount' => $associate->pivot->fixed_amount,
                    'calculated_amount' => $associate->pivot->calculated_amount,
                    'status' => $associate->pivot->status,
                    'paid_date' => $associate->pivot->paid_date,
                ];
            }),
        ]);
    }

    public function markSettlementPaid(Request $request, $id)
    {
        $request->validate([
            'paid_date' => 'required|date',
        ]);

        DB::beginTransaction();

        try {
            $settlement = Settlement::findOrFail($id);

            // Update settlement
            $settlement->update([
                'status' => 'paid',
                'paid_date' => $request->paid_date,
            ]);

            // Update all associates
            $settlement->associates()->updateExistingPivot(
                $settlement->associates->pluck('id'),
                [
                    'status' => 'paid',
                    'paid_date' => $request->paid_date,
                ]
            );

            // Update distribution status
            if ($settlement->distribution) {
                $settlement->distribution->update(['status' => 'paid']);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Settlement marked as paid successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getAssociatesForSettlement()
    {

        $roleIds = AssignSettlement::pluck('role_id')->toArray();
        if (empty($roleIds)) {
            return response()->json([]);
        }

        $roleNames = \Spatie\Permission\Models\Role::whereIn('id', $roleIds)->pluck('name')->toArray();
        $associates = User::role($roleNames)->get(['id', 'name', 'email']);

        return response()->json($associates);
    }

    public function downloadSettlementReport($id)
    {
        $settlement = Settlement::with(['distribution', 'associates'])->findOrFail($id);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Settlement Report');

        // Header styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];

        // Settlement Details
        $sheet->setCellValue('A1', 'Settlement Report');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ]);

        $details = [
            ['Settlement No:', $settlement->settlement_no],
            ['Date:', $settlement->created_at->format('d-m-Y')],
            ['Origin:', $settlement->origin],
            ['Concept:', $settlement->concept],
            ['Period:', $settlement->period_covered],
            ['Distribution Formula:', $settlement->distribution_formula],
            ['Total to Distribute:', $this->money($settlement->total_to_distribute)],
            ['Amount to Pay:', $this->money($settlement->amount_to_pay)],
            ['Status:', ucfirst($settlement->status)],
        ];

        $row = 3;
        foreach ($details as $detail) {
            $sheet->setCellValue('A' . $row, $detail[0]);
            $sheet->setCellValue('B' . $row, $detail[1]);
            $row++;
        }

        // Associates Table
        $row += 2;
        $sheet->setCellValue('A' . $row, 'Associates Distribution');
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ]);

        $row++;
        $associateHeaders = ['Associate', 'Email', 'Percentage', 'Fixed Amount', 'Calculated Amount', 'Status'];
        $col = 'A';
        foreach ($associateHeaders as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->applyFromArray($headerStyle);
            $col++;
        }

        $row++;
        foreach ($settlement->associates as $associate) {
            $sheet->setCellValue('A' . $row, $associate->name);
            $sheet->setCellValue('B' . $row, $associate->email);
            $sheet->setCellValue('C' . $row, $associate->pivot->percentage ? $associate->pivot->percentage . '%' : '-');
            $sheet->setCellValue('D' . $row, $associate->pivot->fixed_amount ? $this->money($associate->pivot->fixed_amount) : '-');
            $sheet->setCellValue('E' . $row, $this->money($associate->pivot->calculated_amount));
            $sheet->setCellValue('F' . $row, ucfirst($associate->pivot->status));
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'F') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'Settlement_Report_' . $settlement->settlement_no . '.xlsx';

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function getDistribution($id)
    {
        $distribution = Distribution::with(['validation', 'income', 'creator'])->findOrFail($id);

        return response()->json([
            'status' => true,
            'distribution' => $distribution
        ]);
    }

    public function updateDistribution(Request $request, $id)
    {
        $distribution = Distribution::findOrFail($id);

        // Check permissions
        if (!auth()->user()->hasRole(['admin', 'Contador'])) {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to edit distributions'
            ], 403);
        }

        $request->validate([
            'origin' => 'nullable|string|max:255',
            'concept' => 'nullable|string|max:255',
            'distribution_date' => 'nullable|date',
            'base_value' => 'nullable|numeric|min:0',
            'vat' => 'nullable|numeric|min:0',
            'associate_subtotal' => 'nullable|numeric|min:0',
            'admin_subtotal' => 'nullable|numeric|min:0',
            'admin_vat' => 'nullable|numeric|min:0',
            'admin_total' => 'nullable|numeric|min:0',
            'total_to_pay' => 'nullable|numeric|min:0',
            'balance' => 'nullable|numeric|min:0',
        ]);

        $distribution->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Distribution updated successfully'
        ]);
    }

    public function deleteDistribution($id)
    {
        $distribution = Distribution::findOrFail($id);

        // Check permissions
        if (!auth()->user()->hasRole(['admin', 'Contador'])) {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to delete distributions'
            ], 403);
        }

        // Only allow deletion of pending distributions
        if ($distribution->status !== 'pending') {
            return response()->json([
                'status' => false,
                'message' => 'Cannot delete distributions that are not in pending status'
            ], 422);
        }

        $distribution->delete();

        return response()->json([
            'status' => true,
            'message' => 'Distribution deleted successfully'
        ]);
    }


    /** Portfolio (aging) data **/
    public function getPortfolioData(Request $request)
    {
        $periodMonth = $request->get('period_month', date('m'));
        $periodYear = $request->get('period_year', date('Y'));
        $clientFilter = $request->get('client_filter');
        $agingFilter = $request->get('aging_filter'); // '1-30', '31-90', '90+'

        // Calculate period end date
        $periodEndDate = Carbon::create($periodYear, $periodMonth, 1)->endOfMonth();

        // Get all unpaid/partially paid invoices up to period end
        $query = RegisterInvoice::query()
            ->with(['budget.client', 'cashReceipts', 'creditNotes'])
            ->whereDate('invoiceDate', '<=', $periodEndDate)
            ->where(function ($q) {
                // Not fully paid and not canceled
                $q->whereDoesntHave('creditNotes')
                    ->orWhereHas('creditNotes', function ($cn) {
                        $cn->whereNull('cn_number');
                    });
            });

        // Client filter
        if ($clientFilter) {
            $query->where('commercialName', 'like', '%' . $clientFilter . '%');
        }

        $invoices = $query->orderBy('commercialName')->orderBy('invoiceDate')->get();

        // Process invoices to calculate aging and balances
        $portfolioData = [];

        foreach ($invoices as $invoice) {
            // Calculate total paid
            $totalPaid = (float) $invoice->cashReceipts->sum('amount');

            // Calculate credit notes
            $creditNoteTotal = 0;
            if ($invoice->creditNotes->isNotEmpty()) {
                $creditNoteTotal = (float) $invoice->creditNotes->sum('total');
            }

            // Calculate balance
            $balance = (float) $invoice->total - $totalPaid - $creditNoteTotal;

            // Skip if fully paid
            if ($balance <= 0.01) {
                continue;
            }

            // Calculate aging (days from invoice date to period end)
            $invoiceDate = Carbon::parse($invoice->invoiceDate);
            $daysOld = $invoiceDate->diffInDays($periodEndDate);

            // Categorize by aging
            $aging_1_30 = 0;
            $aging_31_90 = 0;
            $aging_90_plus = 0;

            if ($daysOld <= 30) {
                $aging_1_30 = $balance;
            } elseif ($daysOld <= 90) {
                $aging_31_90 = $balance;
            } else {
                $aging_90_plus = $balance;
            }

            // Apply aging filter if set
            if ($agingFilter) {
                $matchesFilter = false;
                if ($agingFilter === '1-30' && $aging_1_30 > 0) $matchesFilter = true;
                if ($agingFilter === '31-90' && $aging_31_90 > 0) $matchesFilter = true;
                if ($agingFilter === '90+' && $aging_90_plus > 0) $matchesFilter = true;

                if (!$matchesFilter) continue;
            }

            // Get client ID
            $clientId = $invoice->commercialID;
            $clientName = $invoice->commercialName;

            // Get or create client entry
            if (!isset($portfolioData[$clientId])) {
                $portfolioData[$clientId] = [
                    'client_id' => $clientId,
                    'client_name' => $clientName,
                    'invoices' => [],
                    'totals' => [
                        '1_30' => 0,
                        '31_90' => 0,
                        '90_plus' => 0,
                        'total' => 0
                    ]
                ];
            }

            // Get comment for this invoice/client/period
            $comment = PortfolioComment::where('client_id', $clientId)
                ->where('invoice_id', $invoice->id)
                ->forPeriod($periodMonth, $periodYear)
                ->first();

            // Add invoice data
            $portfolioData[$clientId]['invoices'][] = [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoiceNumber,
                'invoice_date' => $invoice->invoiceDate,
                'invoice_total' => $invoice->total,
                'total_paid' => $totalPaid,
                'balance' => $balance,
                'days_old' => $daysOld,
                'aging_1_30' => $aging_1_30,
                'aging_31_90' => $aging_31_90,
                'aging_90_plus' => $aging_90_plus,
                'comment' => $comment ? [
                    'id' => $comment->id,
                    'text' => $comment->comment,
                    'status' => $comment->status,
                    'created_by' => $comment->creator->name ?? 'N/A',
                    'approved_by' => $comment->approver->name ?? null,
                    'approved_at' => $comment->approved_at ? $comment->approved_at->format('Y-m-d H:i') : null,
                    'can_modify' => $comment->canBeModified(),
                    'can_approve' => $comment->canBeApproved(auth()->user())
                ] : null
            ];

            // Update totals
            $portfolioData[$clientId]['totals']['1_30'] += $aging_1_30;
            $portfolioData[$clientId]['totals']['31_90'] += $aging_31_90;
            $portfolioData[$clientId]['totals']['90_plus'] += $aging_90_plus;
            $portfolioData[$clientId]['totals']['total'] += $balance;
        }

        // Sort by client name
        usort($portfolioData, function ($a, $b) {
            return strcmp($a['client_name'], $b['client_name']);
        });

        // Calculate grand totals
        $grandTotals = [
            '1_30' => 0,
            '31_90' => 0,
            '90_plus' => 0,
            'total' => 0
        ];

        foreach ($portfolioData as $client) {
            $grandTotals['1_30'] += $client['totals']['1_30'];
            $grandTotals['31_90'] += $client['totals']['31_90'];
            $grandTotals['90_plus'] += $client['totals']['90_plus'];
            $grandTotals['total'] += $client['totals']['total'];
        }

        return response()->json([
            'status' => true,
            'data' => array_values($portfolioData),
            'grand_totals' => $grandTotals,
            'period' => [
                'month' => $periodMonth,
                'year' => $periodYear,
                'end_date' => $periodEndDate->format('Y-m-d')
            ]
        ]);
    }

    public function storePortfolioComment(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'invoice_id' => 'required|exists:register_invoice,id',
            'period_month' => 'required|numeric|min:1|max:12',
            'period_year' => 'required|numeric|min:2000',
            'comment' => 'required|string|max:1000'
        ]);

        // Check if comment already exists for this combination
        $existing = PortfolioComment::where('client_id', $request->client_id)
            ->where('invoice_id', $request->invoice_id)
            ->forPeriod($request->period_month, $request->period_year)
            ->first();

        if ($existing) {
            if ($existing->status === 'approved') {
                return response()->json([
                    'status' => false,
                    'message' => 'Cannot modify approved comments'
                ], 422);
            }

            // Update existing pending comment
            $existing->update([
                'comment' => $request->comment,
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Comment updated successfully',
                'comment' => $existing
            ]);
        }

        // Create new comment
        $comment = PortfolioComment::create([
            'client_id' => $request->client_id,
            'invoice_id' => $request->invoice_id,
            'period_month' => $request->period_month,
            'period_year' => $request->period_year,
            'comment' => $request->comment,
            'status' => 'pending',
            'created_by' => auth()->id()
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Comment added successfully',
            'comment' => $comment
        ]);
    }

    public function approvePortfolioComment(Request $request, $id)
    {
        $comment = PortfolioComment::findOrFail($id);

        if (!$comment->canBeApproved(auth()->user())) {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to approve this comment'
            ], 403);
        }

        $request->validate([
            'action' => 'required|in:approve,reject',
            'rejection_reason' => 'required_if:action,reject|nullable|string|max:500'
        ]);

        if ($request->action === 'approve') {
            $comment->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'rejection_reason' => null
            ]);

            $message = 'Comment approved successfully';
        } else {
            $comment->update([
                'status' => 'rejected',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'rejection_reason' => $request->rejection_reason
            ]);

            $message = 'Comment rejected';
        }

        return response()->json([
            'status' => true,
            'message' => $message,
            'comment' => $comment->fresh(['creator', 'approver'])
        ]);
    }

    public function deletePortfolioComment($id)
    {
        $comment = PortfolioComment::findOrFail($id);

        if (!$comment->canBeModified()) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot delete approved or rejected comments'
            ], 422);
        }

        if ($comment->created_by !== auth()->id() && !auth()->user()->hasRole(['admin', 'Admin'])) {
            return response()->json([
                'status' => false,
                'message' => 'You can only delete your own comments'
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'status' => true,
            'message' => 'Comment deleted successfully'
        ]);
    }

    public function exportPortfolioReport(Request $request)
    {
        $periodMonth = $request->get('period_month', date('m'));
        $periodYear = $request->get('period_year', date('Y'));

        // Get portfolio data
        $response = $this->getPortfolioData($request);
        $portfolioData = $response->getData()->data;
        $grandTotals = $response->getData()->grand_totals;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Portfolio Aging');

        // Header styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];

        // Title
        $sheet->setCellValue('A1', 'Portfolio (Aging) Report');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ]);

        // Period info
        $periodLabel = date('F Y', mktime(0, 0, 0, $periodMonth, 1, $periodYear));
        $sheet->setCellValue('A2', 'Period: ' . $periodLabel);
        $sheet->mergeCells('A2:H2');

        // Headers
        $row = 4;
        $headers = ['Company', 'Inv. No.', 'Inv. Date', '1-30 days', '31-90 days', '90+ days', 'Total Balance', 'Comment'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->applyFromArray($headerStyle);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        $row++;

        // Data
        foreach ($portfolioData as $client) {
            foreach ($client->invoices as $invoice) {
                $sheet->setCellValue('A' . $row, $client->client_name);
                $sheet->setCellValue('B' . $row, $invoice->invoice_number);
                $sheet->setCellValue('C' . $row, date('d-m-Y', strtotime($invoice->invoice_date)));
                $sheet->setCellValue('D' . $row, $invoice->aging_1_30);
                $sheet->setCellValue('E' . $row, $invoice->aging_31_90);
                $sheet->setCellValue('F' . $row, $invoice->aging_90_plus);
                $sheet->setCellValue('G' . $row, $invoice->balance);
                $sheet->setCellValue('H' . $row, $invoice->comment ? $invoice->comment->text : '');

                // Format numbers
                $sheet->getStyle('D' . $row . ':G' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

                $row++;
            }

            // Client subtotal
            $sheet->setCellValue('A' . $row, 'Subtotal: ' . $client->client_name);
            $sheet->setCellValue('D' . $row, $client->totals->{'1_30'});
            $sheet->setCellValue('E' . $row, $client->totals->{'31_90'});
            $sheet->setCellValue('F' . $row, $client->totals->{'90_plus'});
            $sheet->setCellValue('G' . $row, $client->totals->total);

            $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E7E6E6']]
            ]);
            $sheet->getStyle('D' . $row . ':G' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

            $row++;
        }

        // Grand total
        $row++;
        $sheet->setCellValue('A' . $row, 'GRAND TOTAL');
        $sheet->setCellValue('D' . $row, $grandTotals->{'1_30'});
        $sheet->setCellValue('E' . $row, $grandTotals->{'31_90'});
        $sheet->setCellValue('F' . $row, $grandTotals->{'90_plus'});
        $sheet->setCellValue('G' . $row, $grandTotals->total);

        $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFD966']]
        ]);
        $sheet->getStyle('D' . $row . ':G' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

        $filename = 'Portfolio_Aging_' . $periodLabel . '.xlsx';

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}
