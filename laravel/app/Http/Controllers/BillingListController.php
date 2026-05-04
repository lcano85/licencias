<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
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
use App\Models\Clients;
use App\Models\RegisterInvoice;
use App\Models\Budget;
use App\Models\BillingList;
use App\Models\UseTypes;
use App\Models\LicensesAgreements;

class BillingListController extends Controller {
    public function __construct() {
        $this->middleware('permission:listing-billing-list', ['only' => ['index']]);
        $this->middleware('permission:create-billing-list', ['only' => ['create','store']]);
        $this->middleware('permission:edit-billing-list', ['only' => ['edit','update']]);
        $this->middleware('permission:delete-billing-list', ['only' => ['destroy']]);
    }

    public function index(Request $request): View {
        $pageTitle = 'Billing List';
        return view('billing-list.index', compact('pageTitle'));
    }

    public function getAjaxData(Request $request){
        if ($request->ajax()) {
            $data = BillingList::select('id', 'user_type', 'company', 'commercialName', 'subTotal', 'vat', 'total', 'invoiceNumber', 'created_at');

            return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('created_at', function ($row) {
                if(!empty($row->created_at)){
                    return date('d-m-Y', strtotime($row->created_at));
                }
            })
            ->addColumn('commercialName', function ($row) {
                return $row->commercialName ?: 'N/A';
            })
            ->addColumn('user_type', function ($row) {
                return $row->user_type ?: 'N/A';
            })
            ->addColumn('company', function ($row) {
                return $row->company ?: 'N/A';
            })
            ->addColumn('invoiceNumber', function ($row) {
                return $row->invoiceNumber ?: 'N/A';
            })
            ->addColumn('subTotal', function ($row) {
                return '₱ '.$row->subTotal ?: 'N/A';
            })
            ->addColumn('vat', function ($row) {
                return $row->vat.'%' ?: 'N/A';
            })
            ->addColumn('total', function ($row) {
                return '₱ '.$row->total ?: 'N/A';
            })
            ->addColumn('action', function ($row) {
                $userId = auth()->id();
                $dataId = $row->id;
                $viewRoute = route("billing-list.view", $row->id);
                $editRoute = route("billing-list.edit", $row->id);
                $buttons = ' <a href="'. $editRoute .'" class="btn btn-soft-primary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                    <iconify-icon icon="solar:pen-new-square-linear" class="align-middle fs-18"></iconify-icon>
                  </a>
                  <a href="' . $viewRoute . '" class="btn btn-soft-primary btn-sm" title="View" data-bs-toggle="tooltip" data-bs-placement="top">
                        <iconify-icon icon="solar:eye-bold" class="align-middle fs-18"></iconify-icon>
                    </a>
                  <a href="javascript:void(0)" class="btn btn-soft-danger btn-sm" onclick="deleteActivity('.$dataId.')" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                    <iconify-icon icon="solar:trash-bin-trash-bold" class="align-middle fs-18"></iconify-icon>
                  </a>';
                return $buttons;
            })
            ->rawColumns(['action', 'created_at', 'user_type', 'commercialName', 'total', 'subTotal', 'vat', 'company', 'invoiceNumber'])
            ->make(true);
        }
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    public function create() {
        $pageTitle = 'Add Billing';
        $clients = Clients::where('client_status', 1)->get();
        return view('billing-list.create', compact('clients', 'pageTitle'));
    }

    public function getUserType($clientId) {
        $client = Clients::find($clientId);
        $useTypes = UseTypes::where('id', $client->useTypes)->first();
        $budget = Budget::where('commercialID', $clientId)->first();
        $license = LicensesAgreements::where('commercialID', $clientId)->first();

        $useTypesVal = '';
        $licensedConcept = '';
        $licensedEnvironment = '';
        if(!empty($license->licensedConcept)){
            if ($license->licensedConcept == 1) {
                $licensedConcept = 'Public Communication of Music Videos';
            } elseif ($license->licensedConcept == 2) {
                $licensedConcept = 'Digital Storage of Phonograms';
            } elseif ($license->licensedConcept == 3) {
                $licensedConcept = 'Reproduction Compensation of Phonograms';
            } else {
                $licensedConcept = '';
            }
        } else {
            $licensedConcept = '';
        }
        if(!empty($license->licensedEnvironment)){
            if ($license->licensedEnvironment == 1) {
                $licensedEnvironment = 'Musical Ambience';
            } elseif ($license->licensedEnvironment == 2) {
                $licensedEnvironment = 'Public Establishments';
            } elseif ($license->licensedEnvironment == 3) {
                $licensedEnvironment = 'Public Events';
            } elseif ($license->licensedEnvironment == 4) {
                $licensedEnvironment = 'Broadcasting';
            } elseif ($license->licensedEnvironment == 5) {
                $licensedEnvironment = 'WebCasting';
            } elseif ($license->licensedEnvironment == 6) {
                $licensedEnvironment = 'SimulCasting';
            } elseif ($license->licensedEnvironment == 7) {
                $licensedEnvironment = 'Subscription TV Operators';
            } elseif ($license->licensedEnvironment == 8) {
                $licensedEnvironment = 'Social Networks';
            } else {
                $licensedEnvironment = 'N/A';
            }
        } else {
            $licensedEnvironment = '';
        }

        if(!empty($useTypes->use_types_name)){
            $useTypesVal = $useTypes->use_types_name;
        } else {
            $useTypesVal = '';
        }

        $company = '';
        if(!empty($budget->company)){
            $company = $budget->company;
        }

        $concept = '';
        if(!empty($budget->concept)){
            $concept = $budget->concept;
        }

        $invoices = [];
        if ($budget) {
            $invoices = RegisterInvoice::where('budgetID', $budget->id)->pluck('invoiceNumber', 'id')->toArray();
        }

        return response()->json(['userType' => $useTypesVal ?? '', 'licensedConcept' => $licensedConcept ?? '', 'licensedEnvironment' => $licensedEnvironment ?? '', 'company' => $company ?? '', 'concept' => $concept ?? '', 'invoices' => $invoices]);
    }

    public function getInvoices($invoiceId) {
        $invoice = RegisterInvoice::where('id', $invoiceId)->first();
        $budget = Budget::where('id', $invoice->budgetID)->first();

        $invoiceDate = '';
        if(!empty($invoice->invoiceDate)){
            $invoiceDate = date('d-m-Y', strtotime($invoice->invoiceDate));
        }

        $criterion = '';
        if(!empty($invoice->criterion)){
            if ($invoice->criterion == 1) {
                $criterion = "Min. Guaranteed, 8% Income";
            } elseif ($invoice->criterion == 2) {
                $criterion = "Min. Guaranteed + 8%";
            } elseif ($invoice->criterion == 3) {
                $criterion = "Monthly Fee";
            } elseif ($invoice->criterion == 4) {
                $criterion = "Annual Fee";
            } else {
                $criterion = "Special Arrangement";
            }
        }

        $period = '';
        if(!empty($invoice->paidPeriod)){
            if ($invoice->paidPeriod == 1) {
                $period = "Month and Year";
            } elseif ($invoice->paidPeriod == 2) {
                $period = "Year Only";
            } else {
                $period = "Multiple Years";
            }
        }

        $periodDetails = '';
        if(!empty($invoice->periodPaid)){
            $periodDetails = $invoice->periodPaid;
        }

        $subTotal = '';
        if(!empty($budget->subTotal)){
            $subTotal = $budget->subTotal;
        }

        $vat = '';
        if(!empty($budget->vat)){
            $vat = $budget->vat;
        }

        $total = '';
        if(!empty($budget->total)){
            $total = $budget->total;
        }

        return response()->json(['invoiceDate' => $invoiceDate ?? '', 'criterion' => $criterion ?? '', 'period' => $period ?? '', 'periodDetails' => $periodDetails ?? '', 'subTotal' => $subTotal ?? '', 'vat' => $vat ?? '', 'total' => $total ?? '']);
    }

    public function store(Request $request) {
        $clientName = Clients::where('id', $request->commercialName)->first();
        $invoice = RegisterInvoice::where('id', $request->invoiceNumber)->first();
        $userID = Auth::user()->id;

        $billing = new BillingList();
        $billing->commercialID  = $request->commercialName;
        $billing->commercialName = $clientName->commercialName;
        $billing->invoiceID  = $request->invoiceNumber;
        $billing->user_type  = $request->user_type;
        $billing->company    = $request->company;
        $billing->concept   = $request->concept;
        $billing->invoiceNumber = $invoice->invoiceNumber;
        $billing->invoiceDate   = $invoice->invoiceDate;
        $billing->periodPaid    = $request->period;
        $billing->paidPeriod  = $request->periodDetails;
        $billing->criterion     = $request->criterion;
        $billing->subTotal     = $request->subTotal;
        $billing->vat     = $request->vat;
        $billing->total     = $request->total;
        $billing->balance     = $request->balance;
        $billing->supportingDocument     = $request->supportingDocument;
        $billing->documentDetail     = $request->documentDetail;
        $billing->licensedConcept    = $request->licensedConcept;
        $billing->licensedEnvironment    = $request->licensedEnvironment;
        $billing->createdBy    = $userID;
        if ($billing->save()) {
            return redirect()->route('billing-list')->with('success', 'Billing is added successfully.');
        } else {
            return redirect()->back()->with('error', 'Something went wrong!');
        }
    }

    public function edit(Request $request, $id) {
        $pageTitle = 'Edit Billing';
        $billing = BillingList::where('id', $id)->first();
        $clients = Clients::where('client_status', 1)->get();
        $invoice = RegisterInvoice::where('id', $billing->invoiceID)->first();
        return view('billing-list.edit', compact('clients', 'pageTitle', 'billing', 'invoice'));
    }

    public function update(Request $request, $id) {
        $clientName = Clients::where('id', $request->commercialName)->first();
        $invoice = RegisterInvoice::where('id', $request->invoiceNumber)->first();
        $userID = Auth::user()->id;

        $billing = BillingList::where('id', $id)->first();
        $billing->commercialID  = $request->commercialName;
        $billing->commercialName = $clientName->commercialName;
        $billing->invoiceID  = $request->invoiceNumber;
        $billing->user_type  = $request->user_type;
        $billing->company    = $request->company;
        $billing->concept   = $request->concept;
        $billing->invoiceNumber = $invoice->invoiceNumber;
        $billing->invoiceDate   = $invoice->invoiceDate;
        $billing->periodPaid    = $request->period;
        $billing->paidPeriod  = $request->periodDetails;
        $billing->criterion     = $request->criterion;
        $billing->subTotal     = $request->subTotal;
        $billing->vat     = $request->vat;
        $billing->total     = $request->total;
        $billing->balance     = $request->balance;
        $billing->supportingDocument     = $request->supportingDocument;
        $billing->documentDetail     = $request->documentDetail;
        $billing->licensedConcept    = $request->licensedConcept;
        $billing->licensedEnvironment    = $request->licensedEnvironment;
        $billing->createdBy    = $userID;
        if ($billing->save()) {
            return redirect()->route('billing-list')->with('success', 'Billing is updated successfully.');
        } else {
            return redirect()->back()->with('error', 'Something went wrong!');
        }
    }

    public function view(Request $request, $id) {
        $pageTitle = 'View Billing';
        $billing = BillingList::where('id', $id)->first();
        $clients = Clients::where('client_status', 1)->get();
        $invoice = RegisterInvoice::where('id', $billing->invoiceID)->first();
        return view('billing-list.view', compact('clients', 'pageTitle', 'billing', 'invoice'));
    }

    public function destroy(Request $request, $id) {
        $billing = BillingList::find($id);
        if (!$billing) {
            return response()->json(['error' => 'Record not found!'], 404);
        }
        $billing->delete();
        return response()->json(['success' => 'Billing deleted successfully!']);
    }
}
