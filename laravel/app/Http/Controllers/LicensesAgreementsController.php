<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\LicensesAgreements;
use App\Models\Environment;
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
use App\Models\ClientCategory;
use App\Models\ClientSubCategory;
use App\Models\Budget;
use App\Models\LicenseAttachment;
use App\Models\LicenseComment;

class LicensesAgreementsController extends Controller {

    public function __construct() {
        $this->middleware('permission:list-licenses-agreements|create-licenses-agreements|edit-licenses-agreements|delete-licenses-agreements', ['only' => ['index','show']]);
        $this->middleware('permission:create-licenses-agreements', ['only' => ['create','store']]);
        $this->middleware('permission:edit-licenses-agreements', ['only' => ['edit','update']]);
        $this->middleware('permission:delete-licenses-agreements', ['only' => ['destroy']]);
    }

    public function index(Request $request): View {
        $pageTitle = 'Licenses / Agreements';
        $environments = Environment::pluck('name', 'id')->toArray();
        return view('licenses_agreements.index', compact('pageTitle', 'environments'));
    }

    public function getAjaxData(Request $request){
        if ($request->ajax()) {
            $this->syncExpiredLicenses();

            $today = Carbon::today()->toDateString();
            $soonLimit = Carbon::today()->addDays(30)->toDateString();

            $data = LicensesAgreements::select(
                'id', 'commercialID', 'commercialName', 'userType', 'licensedConcept',
                'licensedEnvironment', 'startDate', 'endDate', 'monthlyValue', 'annualValue',
                'status', 'created_at', 'created_by', 'billing_frequency', 'category', 'subcategory', 'origin'
            );

            // Advanced filters
            if ($request->filled('frequencyFilter')) {
                $data->where('billing_frequency', $request->frequencyFilter);
            }
            if ($request->filled('commercialNameFilter')) {
                $data->where('commercialName', 'like', '%'.$request->commercialNameFilter.'%');
            }
            if ($request->filled('categoryFilter')) {
                $data->where('category', 'like', '%'.$request->categoryFilter.'%');
            }
            if ($request->filled('subcategoryFilter')) {
                $data->where('subcategory', 'like', '%'.$request->subcategoryFilter.'%');
            }
            if ($request->filled('conceptFilter')) {
                $data->where('licensedConcept', 'like', '%'.$request->conceptFilter.'%');
            }
            if ($request->filled('environmentFilter')) {
                $val = (string)$request->environmentFilter;
                $data->where(function ($q) use ($val) {
                    $q->whereJsonContains('licensedEnvironment', $val)
                      ->orWhere('licensedEnvironment', $val)
                      ->orWhereRaw('JSON_VALID(licensedEnvironment) AND JSON_CONTAINS(licensedEnvironment, JSON_QUOTE(?))', [$val]);
                });
            }
            if ($request->filled('originFilter')) {
                $data->where('origin', $request->originFilter);
            }
            if ($request->filled('statusFilter')) {
                if ($request->statusFilter == 1) {
                    $data->where('status', 1)
                        ->where(function ($q) use ($today) {
                            $q->whereNull('endDate')
                                ->orWhereDate('endDate', '>=', $today);
                        });
                } elseif ($request->statusFilter == 4) {
                    $data->where(function ($q) use ($today) {
                        $q->where('status', 4)
                            ->orWhere(function ($subQ) use ($today) {
                                $subQ->where('status', 1)
                                    ->whereDate('endDate', '<', $today);
                            });
                    });
                } else {
                    $data->where('status', $request->statusFilter);
                }
            }
            if ($request->filled('expirationFilter')) {
                if ($request->expirationFilter === 'expired') {
                    $data->whereDate('endDate', '<', $today);
                } elseif ($request->expirationFilter === 'valid') {
                    $data->whereDate('endDate', '>=', $today);
                } elseif ($request->expirationFilter === 'expiring_30') {
                    $data->whereBetween(DB::raw('DATE(endDate)'), [$today, $soonLimit]);
                } elseif ($request->expirationFilter === 'no_end_date') {
                    $data->whereNull('endDate');
                }
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('created_at', function ($row) {
                    return !empty($row->created_at) ? date('d-m-Y', strtotime($row->created_at)) : null;
                })
                ->addColumn('startDate', function ($row) {
                    return !empty($row->startDate) ? date('d-m-Y', strtotime($row->startDate)) : __('N/A');
                })
                ->addColumn('endDate', function ($row) {
                    return !empty($row->endDate) ? date('d-m-Y', strtotime($row->endDate)) : __('N/A');
                })
                ->addColumn('commercialName', function ($row) {
                    return $row->commercialName ?: __('N/A');
                })
                ->addColumn('category', function ($row) {
                    return $row->category ?: __('N/A');
                })
                ->addColumn('subcategory', function ($row) {
                    return $row->subcategory ?: __('N/A');
                })
                ->addColumn('status', function ($row) {
                    $isExpiredByDate = !empty($row->endDate) && Carbon::parse($row->endDate)->lt(Carbon::today());

                    if ($row->status == 1 && $isExpiredByDate) {
                        return '<span class="badge bg-secondary">' . __('Expired') . '</span>';
                    } elseif ($row->status == 1) {
                        return '<span class="badge bg-success">' . __('Active') . '</span>';
                    } elseif ($row->status == 2) {
                        return '<span class="badge bg-danger">' . __('Canceled') . '</span>';
                    } elseif ($row->status == 3) {
                        return '<span class="badge bg-warning">' . __('Suspended') . '</span>';
                    } elseif ($row->status == 4) {
                        return '<span class="badge bg-secondary">' . __('Expired') . '</span>';
                    } else {
                        return __('N/A');
                    }
                })
                ->addColumn('billing_frequency', function ($row) {
                    if ($row->billing_frequency == 'Monthly' || $row->billing_frequency == 1) return __('Monthly');
                    if ($row->billing_frequency == 'Quarterly' || $row->billing_frequency == 2) return __('Quarterly');
                    if ($row->billing_frequency == 'Annual' || $row->billing_frequency == 3) return __('Annual');
                    if ($row->billing_frequency == 'One-Time Payment' || $row->billing_frequency == 4) return __('One-Time Payment');
                    return $row->billing_frequency ?: __('N/A');
                })
                ->addColumn('licensedConcept', function ($row) {
                    return $row->licensedConcept ?: __('N/A');
                })
                ->addColumn('annualValue', function ($row) {
                    return '$ ' . $row->annualValue ?: 'N/A';
                })
                ->addColumn('licensedEnvironment', function ($row) {
                    if (empty($row->licensedEnvironment)) return __('N/A');

                    $envMap = Environment::pluck('name', 'id')->toArray();
                    $vals = [];
                    if (is_array($row->licensedEnvironment)) {
                        $vals = $row->licensedEnvironment;
                    } else {
                        $vals = json_decode($row->licensedEnvironment, true);
                    }

                    if (!is_array($vals)) {
                        $vals = [];
                    }
                    $labels = [];
                    foreach ($vals as $id) {
                        $key = (int)$id;
                        if (isset($envMap[$key])) {
                            $labels[] = __($envMap[$key]);
                        }
                    }
                    return $labels ? implode(', ', $labels) : __('N/A');
                })
                ->addColumn('origin', function ($row) {
                    $origins = [
                        'License' => 'License',
                        'Transaction' => 'Transaction',
                        'Conciliation' => 'Conciliation',
                        'Sentences' => 'Sentences'
                    ];
                    return isset($origins[$row->origin]) ? __($origins[$row->origin]) : __('N/A');
                })
                ->addColumn('action', function ($row) {
                    $dataId = $row->id;
                    $viewRoute = route("licenses-agreements.view", $row->id);
                    $editRoute = route("licenses-agreements.edit", $row->id);
                    $buttons = '';
                    $buttons .= ' <a href="'. $editRoute .'" class="btn btn-soft-primary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="' . __('Edit') . '">
                        <iconify-icon icon="solar:pen-new-square-linear" class="align-middle fs-18"></iconify-icon>
                      </a>
                      <a href="' . $viewRoute . '" class="btn btn-soft-primary btn-sm" title="' . __('View') . '" data-bs-toggle="tooltip" data-bs-placement="top">
                            <iconify-icon icon="solar:eye-bold" class="align-middle fs-18"></iconify-icon>
                        </a>
                      <a href="javascript:void(0)" class="btn btn-soft-danger btn-sm" onclick="deleteActivity('.$dataId.')" data-bs-toggle="tooltip" data-bs-placement="top" title="' . __('Delete') . '">
                        <iconify-icon icon="solar:trash-bin-trash-bold" class="align-middle fs-18"></iconify-icon>
                      </a>';

                    return $buttons;
                })
                ->rawColumns(['action', 'created_at', 'licensedConcept', 'commercialName', 'startDate', 'endDate', 'licensedEnvironment', 'status', 'billing_frequency', 'origin', 'category', 'subcategory', 'annualValue'])
                ->make(true);
        }
        return response()->json(['error' => __('Unauthorized')], 403);
    }

    private function syncExpiredLicenses(): void {
        LicensesAgreements::where('status', 1)
            ->whereDate('endDate', '<', Carbon::today())
            ->update(['status' => 4]);
    }

    public function create() {
        $pageTitle = 'Add Licenses / Agreements';
        $clients = Clients::all();
        $environments = Environment::pluck('name', 'id')->toArray();
        return view('licenses_agreements.create', compact('clients', 'environments', 'pageTitle'));
    }

    public function getUserType($clientId) {
        $client = Clients::find($clientId);
        $useTypes = UseTypes::where('id', $client->useTypes)->first();
        $useTypesVal = $useTypes->use_types_name ?? '';

        $category = ClientCategory::where('id', $client->categoryID)->first();
        $categoryVal = $category->category_name ?? '';

        $subcategory = ClientSubCategory::where('id', $client->subcategoryID)->first();
        $subcategoryVal = $subcategory->subcategory_name ?? '';

        return response()->json([
            'userType' => $useTypesVal,
            'categoryVal' => $categoryVal,
            'subcategoryVal' => $subcategoryVal,
        ]);
    }

    public function store(Request $request) {
        $userID = Auth::user()->id;

        $request->validate([
            'commercialName'      => 'required',
            'category'            => 'required',
            'subcategory'         => 'required',
            'licensedConcept'     => 'required',
            'licensedEnvironment' => 'required',
            'startDate'           => 'required|date',
            'endDate'             => 'required|date|after:startDate',
            'billing_frequency'   => 'required',
            'begin_month'         => 'required|integer|min:1|max:12',
            'begin_year'          => 'required|integer|min:2000|max:2100',
            'attachments.*'       => 'nullable|file|max:10240', // 10MB max per file
        ]);
        
        $clientName = Clients::where('id', $request->commercialName)->first();

        // $vatPercent = $request->vat;
        // $vatAmount  = $request->monthlyValue * ($vatPercent / 100);
        // $total      = $request->monthlyValue + $vatAmount;

        $monthlyValue = $this->parseCurrencyValue($request->monthlyValue);
        $annualValue  = $this->parseCurrencyValue($request->annualValue);
        $billingStart = Carbon::create((int) $request->begin_year, (int) $request->begin_month, 1);
        $monthsTotal = $this->calculateBillingMonths(
            $request->billing_frequency,
            (int) $request->begin_month,
            (int) $request->begin_year,
            $request->finish_month ? (int) $request->finish_month : null,
            $request->finish_year ? (int) $request->finish_year : null
        );
        $billingEnd = $this->resolveBillingEnd(
            (int) $request->begin_month,
            (int) $request->begin_year,
            $request->finish_month ? (int) $request->finish_month : null,
            $request->finish_year ? (int) $request->finish_year : null,
            $monthsTotal
        );

        $duplicate = $this->findOverlappingLicense((int) $request->commercialName, $billingStart, $billingEnd);
        if ($duplicate) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'commercialName' => $this->duplicateLicenseMessage($duplicate, $billingStart, $billingEnd),
                ]);
        }

        $subTotal = $monthlyValue ?: ($annualValue ? round($annualValue / $monthsTotal, 2) : 0);
        if ($annualValue <= 0 && $subTotal > 0) {
            $annualValue = round($subTotal * $monthsTotal, 2);
        }

        $vatPercent = $this->parsePercentValue($request->vat ?? 0);
        $vatAmount  = $subTotal * ($vatPercent / 100);
        $total      = $subTotal + $vatAmount;

        $licenses = new LicensesAgreements();
        $licenses->commercialID  = $request->commercialName;
        $licenses->commercialName  = $clientName->commercialName ?? '';
        $licenses->userType  = $request->licensedConcept;
        $licenses->created_by     = $userID;
        $licenses->licensedConcept = $request->licensedConcept;
        $licenses->licensedEnvironment = array_map('strval', (array) $request->licensedEnvironment);
        $licenses->startDate = $request->startDate;
        $licenses->endDate   = $request->endDate;
        $licenses->billing_frequency = $request->billing_frequency;
        $licenses->begin_month = $request->begin_month;
        $licenses->begin_year  = $request->begin_year;
        $licenses->finish_month = $billingEnd->month;
        $licenses->finish_year  = $billingEnd->year;
        $licenses->monthlyValue = $this->formatCurrencyValue($subTotal);
        $licenses->annualValue  = $this->formatCurrencyValue($annualValue);
        $licenses->status       = $request->status;
        $licenses->category     = $request->category;
        $licenses->subcategory  = $request->subcategory;
        $licenses->origin       = $request->origin;
        $licenses->vat  = $vatPercent;
        $licenses->month_total_value  = $total;

        if ($licenses->save()) {
            if ($request->hasFile('attachments')) {
                $this->saveAttachments($request->file('attachments'), $request->attachment_descriptions ?? [], $licenses->id, $userID);
            }
            $this->autoCreateBudget($licenses, $clientName, $userID);
            return redirect()->route('licenses-agreements')->with('success', 'Licenses / Agreements is added successfully.');
        }
        return redirect()->back()->with('error', 'Something went wrong!');
    }

    private function autoCreateBudget($license, $client, $userID) {
        // Parse values
        $monthlyValue = $this->parseCurrencyValue($license->monthlyValue);
        $annualValue  = $this->parseCurrencyValue($license->annualValue);

        $start = Carbon::create($license->begin_year, $license->begin_month, 1);
        $monthsTotal = $this->calculateBillingMonths(
            $license->billing_frequency,
            (int) $license->begin_month,
            (int) $license->begin_year,
            $license->finish_month ? (int) $license->finish_month : null,
            $license->finish_year ? (int) $license->finish_year : null
        );
        $end = $this->resolveBillingEnd(
            (int) $license->begin_month,
            (int) $license->begin_year,
            $license->finish_month ? (int) $license->finish_month : null,
            $license->finish_year ? (int) $license->finish_year : null,
            $monthsTotal
        );

        // Monthly calculation
        $subTotal = $monthlyValue ?: ($annualValue ? round($annualValue / $monthsTotal, 2) : 0);
        if ($annualValue <= 0 && $subTotal > 0) {
            $annualValue = round($subTotal * $monthsTotal, 2);
        }
        $vatPercent = isset($license->vat) ? $this->parsePercentValue($license->vat) : 12;
        $vatAmount  = $subTotal * ($vatPercent / 100);
        $total      = $subTotal + $vatAmount;

        // Licensed environment text
        $licensedEnvironment = '';
        if (!empty($license->licensedEnvironment)) {
            $environments = [
                1 => 'Musical Ambience',
                2 => 'Public Establishments',
                3 => 'Public Events',
                4 => 'Broadcasting',
                5 => 'WebCasting',
                6 => 'SimulCasting',
                7 => 'Subscription TV Operators',
                8 => 'Social Networks'
            ];

            $envs = is_array($license->licensedEnvironment)
                ? $license->licensedEnvironment
                : [$license->licensedEnvironment];

            $licensedEnvironment = implode(', ', array_map(
                fn($id) => $environments[$id] ?? '',
                $envs
            ));
        }

        /* ===============================
           ðŸ”¥ CREATE BUDGET PER MONTH ðŸ”¥
        ================================ */
        while ($start <= $end) {

            $budget = new Budget();
            $budget->commercialID   = $license->commercialID;
            $budget->commercialName = $license->commercialName;
            $budget->user_type      = $license->userType;
            $budget->company        = $client->legalName ?? '';
            $budget->created_by     = $userID;

            // License info
            $budget->licensedEnvironment = $licensedEnvironment;
            $budget->category            = $license->category;
            $budget->subcategory         = $license->subcategory;
            $budget->licensedConcept     = $license->userType;

            // Period info
            $budget->billing_frequency = $license->billing_frequency;
            $budget->begin_month       = $license->begin_month;
            $budget->begin_year        = $license->begin_year;
            $budget->finish_month      = $end->month;
            $budget->finish_year       = $end->year;

            // ðŸ”¥ CURRENT MONTH
            $budget->budget_month = $start->month;
            $budget->budget_year  = $start->year;

            // Financials
            $budget->annual_value  = $annualValue;
            $budget->monthly_value = $subTotal;
            $budget->total_months  = $monthsTotal;
            $budget->subTotal      = $subTotal;
            $budget->vat           = $vatPercent;
            $budget->total         = $total;

            // Status
            $budget->condition = 2; // New Agreement
            $budget->status    = 1; // Pending
            $budget->concept   = 'Auto-generated from License';

            $budget->save();
            $start->addMonth();
        }
    }


    private function calculateBillingMonths($frequency, int $beginMonth, int $beginYear, ?int $finishMonth, ?int $finishYear): int {
        $start = Carbon::create($beginYear, $beginMonth, 1);

        if ($finishMonth && $finishYear) {
            $end = Carbon::create($finishYear, $finishMonth, 1);
            return max(1, $start->diffInMonths($end) + 1);
        }

        $frequency = strtolower(trim((string) $frequency));
        return match ($frequency) {
            'quarterly', '2' => 3,
            'annual', '3' => 12,
            'one-time payment', '4' => 1,
            default => 12,
        };
    }

    private function resolveBillingEnd(int $beginMonth, int $beginYear, ?int $finishMonth, ?int $finishYear, int $monthsTotal): Carbon {
        if ($finishMonth && $finishYear) {
            return Carbon::create($finishYear, $finishMonth, 1);
        }

        return Carbon::create($beginYear, $beginMonth, 1)->addMonths(max(1, $monthsTotal) - 1);
    }

    private function findOverlappingLicense(int $clientId, Carbon $newStart, Carbon $newEnd, ?int $ignoreId = null): ?LicensesAgreements {
        $query = LicensesAgreements::where('commercialID', $clientId)
            ->whereNotNull('begin_month')
            ->whereNotNull('begin_year');

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        foreach ($query->get() as $license) {
            $existingStart = Carbon::create((int) $license->begin_year, (int) $license->begin_month, 1);
            $existingMonths = $this->calculateBillingMonths(
                $license->billing_frequency,
                (int) $license->begin_month,
                (int) $license->begin_year,
                $license->finish_month ? (int) $license->finish_month : null,
                $license->finish_year ? (int) $license->finish_year : null
            );
            $existingEnd = $this->resolveBillingEnd(
                (int) $license->begin_month,
                (int) $license->begin_year,
                $license->finish_month ? (int) $license->finish_month : null,
                $license->finish_year ? (int) $license->finish_year : null,
                $existingMonths
            );

            if ($existingStart->lte($newEnd) && $existingEnd->gte($newStart)) {
                return $license;
            }
        }

        return null;
    }

    private function duplicateLicenseMessage(LicensesAgreements $license, Carbon $newStart, Carbon $newEnd): string {
        $existingStart = Carbon::create((int) $license->begin_year, (int) $license->begin_month, 1);
        $existingMonths = $this->calculateBillingMonths(
            $license->billing_frequency,
            (int) $license->begin_month,
            (int) $license->begin_year,
            $license->finish_month ? (int) $license->finish_month : null,
            $license->finish_year ? (int) $license->finish_year : null
        );
        $existingEnd = $this->resolveBillingEnd(
            (int) $license->begin_month,
            (int) $license->begin_year,
            $license->finish_month ? (int) $license->finish_month : null,
            $license->finish_year ? (int) $license->finish_year : null,
            $existingMonths
        );

        return sprintf(
            'Este cliente ya tiene la licencia #%d para el periodo %s a %s. El periodo solicitado %s a %s se cruza con esa licencia.',
            $license->id,
            $existingStart->format('F Y'),
            $existingEnd->format('F Y'),
            $newStart->format('F Y'),
            $newEnd->format('F Y')
        );
    }

    private function parsePercentValue($value) {
        if ($value === null || $value === '') return 0;
        return (float) preg_replace('/[^0-9,.]/', '', str_replace(',', '.', (string) $value));
    }

    private function formatCurrencyValue($value) {
        return number_format((float) $value, 2, ',', '.');
    }

    private function parseCurrencyValue($value) {
        if (empty($value)) return 0;
        $value = preg_replace('/[^0-9,.]/', '', $value);
        if (strpos($value, ',') !== false && strpos($value, '.') !== false) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (strpos($value, ',') !== false) {
            $value = str_replace(',', '.', $value);
        }
        return (float) $value;
    }

    public function edit(Request $request, $id) {
        $pageTitle = 'Edit Licenses / Agreements';
        $clients = Clients::all();
        $licenses = LicensesAgreements::findOrFail($id);
        $environments = Environment::pluck('name', 'id')->toArray();
        return view('licenses_agreements.edit', compact('licenses', 'clients', 'environments', 'pageTitle'));
    }

    public function view(Request $request, $id) {
        $pageTitle = 'View Licenses / Agreements';
        $clients = Clients::all();
        $licensesAgreements = LicensesAgreements::with('user')->findOrFail($id);
        $licensesID = $id;
        $licensesComments = LicenseComment::where('license_id', $id)->get();
        return view('licenses_agreements.view', compact('licensesAgreements', 'clients', 'pageTitle', 'licensesID', 'licensesComments'));
    }

    public function update(Request $request, $id) {
        $userID = Auth::user()->id;

        $request->validate([
            'commercialName'      => 'required',
            'licensedConcept'     => 'required',
            'licensedEnvironment' => 'required',
            'startDate'           => 'required|date',
            'endDate'             => 'required|date|after:startDate',
            'billing_frequency'   => 'required',
            'begin_month'         => 'required|integer|min:1|max:12',
            'begin_year'          => 'required|integer|min:2000|max:2100',
        ]);
        
        $clientName = Clients::where('id', $request->commercialName)->first();
        $licenses = LicensesAgreements::where('id', $id)->firstOrFail();

        $licenses->commercialID    = $request->commercialName;
        $licenses->commercialName  = $clientName->commercialName ?? '';
        $licenses->userType        = $request->userType;
        $licenses->created_by      = $userID;
        $licenses->licensedConcept = $request->licensedConcept;

        $licenses->licensedEnvironment = array_map('strval', (array) $request->licensedEnvironment);

        $licenses->startDate = $request->startDate;
        $licenses->endDate   = $request->endDate;

        $licenses->billing_frequency = $request->billing_frequency;
        $licenses->begin_month = $request->begin_month;
        $licenses->begin_year  = $request->begin_year;

        $monthlyValue = $this->parseCurrencyValue($request->monthlyValue);
        $annualValue  = $this->parseCurrencyValue($request->annualValue);
        $monthsTotal = $this->calculateBillingMonths(
            $request->billing_frequency,
            (int) $request->begin_month,
            (int) $request->begin_year,
            $request->finish_month ? (int) $request->finish_month : null,
            $request->finish_year ? (int) $request->finish_year : null
        );
        $billingStart = Carbon::create((int) $request->begin_year, (int) $request->begin_month, 1);
        $billingEnd = $this->resolveBillingEnd(
            (int) $request->begin_month,
            (int) $request->begin_year,
            $request->finish_month ? (int) $request->finish_month : null,
            $request->finish_year ? (int) $request->finish_year : null,
            $monthsTotal
        );

        $duplicate = $this->findOverlappingLicense((int) $request->commercialName, $billingStart, $billingEnd, (int) $licenses->id);
        if ($duplicate) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'commercialName' => $this->duplicateLicenseMessage($duplicate, $billingStart, $billingEnd),
                ]);
        }

        $subTotal = $monthlyValue ?: ($annualValue ? round($annualValue / $monthsTotal, 2) : 0);
        if ($annualValue <= 0 && $subTotal > 0) {
            $annualValue = round($subTotal * $monthsTotal, 2);
        }

        $licenses->finish_month = $billingEnd->month;
        $licenses->finish_year  = $billingEnd->year;
        $licenses->monthlyValue = $this->formatCurrencyValue($subTotal);
        $licenses->annualValue  = $this->formatCurrencyValue($annualValue);
        $licenses->status       = $request->status;
        $licenses->category     = $request->category;
        $licenses->subcategory  = $request->subcategory;
        $licenses->origin       = $request->origin;

        if ($licenses->save()) {
            if ($request->hasFile('attachments')) {
                $this->saveAttachments($request->file('attachments'), $request->attachment_descriptions ?? [], $licenses->id, $userID);
            }
            return redirect()->route('licenses-agreements')->with('success', 'Licenses / Agreements is updated successfully.');
        }
        return redirect()->back()->with('error', 'Something went wrong!');
    }

    public function destroy(Request $request, $id) {
        $licenses = LicensesAgreements::find($id);
        if (!$licenses) {
            return response()->json(['error' => 'Record not found!'], 404);
        }
        $licenses->delete();
        return response()->json(['success' => 'Licenses / Agreements deleted successfully!']);
    }

    public function deleteAttachment(Request $request, $id) {
        $attachment = LicenseAttachment::findOrFail($id);
        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }
        $attachment->delete();
        return response()->json(['success' => 'Attachment deleted successfully!']);
    }

    public function downloadAttachment($id) {
        $attachment = LicenseAttachment::findOrFail($id);
        $filePath = storage_path('app/public/' . $attachment->file_path);
        
        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }
        return response()->download($filePath, $attachment->original_name);
    }

    private function saveAttachments($files, $descriptions, $licenseId, $userId) {
        foreach ($files as $index => $file) {
            if ($file && $file->isValid()) {
                $originalName = $file->getClientOriginalName();
                $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('licenses/attachments', $fileName, 'public');
                LicenseAttachment::create([
                    'license_id' => $licenseId,
                    'original_name' => $originalName,
                    'file_name' => $fileName,
                    'file_path' => $filePath,
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => round($file->getSize() / 1024), // Convert to KB
                    'description' => $descriptions[$index] ?? null,
                    'uploaded_by' => $userId,
                ]);
            }
        }
    }

    public function licensesCommentStore(Request $request) {
        $userId = Auth::id();
        $request->validate([
            'lic_comment'  => 'required|string',
        ]);

        $licenseComment = new LicenseComment();
        $licenseComment->license_id = $request->licensesID;
        $licenseComment->lic_comment = $request->lic_comment;
        $licenseComment->user_id = $userId;
        if ($licenseComment->save()) {
            return response()->json([
                'success' => true,
                'message' => 'Comment added successfully!',
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong!',
            ], 500);
        }
    }

    public function licensesCommentDelete(Request $request, $id){
        $licenseComment = LicenseComment::find($id);
        if (!$licenseComment) {
            return response()->json(['error' => 'Record not found!'], 404);
        }
        $licenseComment->delete();
        return response()->json(['success' => 'Comment deleted successfully!']);
    }
}
