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
use App\Models\Bank;
use App\Models\BudgetCriterion;
use App\Models\InvoiceConsecutive;

class InvoiceConsecutiveController extends Controller {
    public function __construct() {
        $this->middleware('permission:list-invoice-consecutive', ['only' => ['index','show']]);
        $this->middleware('permission:create-invoice-consecutive', ['only' => ['create','store']]);
        $this->middleware('permission:edit-invoice-consecutive', ['only' => ['edit','update']]);
        $this->middleware('permission:delete-invoice-consecutive', ['only' => ['destroy']]);
    }

    public function index(Request $request): View {
        $pageTitle = 'Invoice Consecutive List';
        return view('invoice-consecutive.index', compact('pageTitle'));
    }

    public function getAjaxData(Request $request){
        if ($request->ajax()) {
            $data = InvoiceConsecutive::select('id', 'consecutive_name', 'next_number', 'status', 'created_at');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('created_at', function ($row) {
                    if(!empty($row->created_at)){
                        return date('d-m-Y', strtotime($row->created_at));
                    }
                })
                ->addColumn('consecutive_name', function ($row) {
                    if(!empty($row->consecutive_name)){
                        return $row->consecutive_name;
                    } else {
                        return 'N/A';
                    }
                })
                ->addColumn('next_number', function ($row) {
                    return $row->next_number ?? 'N/A';
                })
                ->addColumn('action', function ($row) {
                    $dataId = $row->id;
                    return '<a href="javascript:void(0)" class="btn btn-soft-primary btn-sm me-1 editBtn" data-id="'.$dataId.'">
                                <iconify-icon icon="solar:pen-bold" class="align-middle fs-18"></iconify-icon>
                            </a>
                            <a href="javascript:void(0)" class="btn btn-soft-danger btn-sm" onclick="deleteStatus('.$dataId.')"><iconify-icon icon="solar:trash-bin-trash-bold" class="align-middle fs-18"></iconify-icon>
                            </a>';
                })
                ->rawColumns(['action', 'created_at', 'consecutive_name', 'next_number'])
                ->make(true);
        }
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    public function store(Request $request) {
        $request->validate([
            'consecutive_name' => 'required|string',
            'next_number' => 'nullable|integer|min:1',
        ]);
        InvoiceConsecutive::create([
            'consecutive_name' => $request->consecutive_name,
            'next_number' => $request->next_number ?: 1,
            'status' => 1
        ]);
        return response()->json(['success'=>true, 'message'=>'Invoice Consecutive created successfully']);
    }

    public function edit($id) {
        $consecutive = InvoiceConsecutive::findOrFail($id);
        return response()->json(['success' => true, 'data' => $consecutive]);
    }

    public function update(Request $request, $id) {
        $request->validate([
            'consecutive_name' => 'required|string',
            'next_number' => 'nullable|integer|min:1',
        ]);
        $consecutive = InvoiceConsecutive::findOrFail($id);
        $consecutive->update([
            'consecutive_name' => $request->consecutive_name,
            'next_number' => $request->next_number ?: ($consecutive->next_number ?: 1),
        ]);
        return response()->json(['success'=>true, 'message'=>'Invoice Consecutive updated successfully']);
    }

    public function destroy($id) {
        $consecutive = InvoiceConsecutive::findOrFail($id);
        $consecutive->delete();
        return response()->json(['success'=>'Invoice Consecutive deleted successfully!']);
    }
}
