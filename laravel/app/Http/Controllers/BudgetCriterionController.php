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

class BudgetCriterionController extends Controller {
    public function __construct() {
        $this->middleware('permission:list-criterion', ['only' => ['index','show']]);
        $this->middleware('permission:create-criterion', ['only' => ['create','store']]);
        $this->middleware('permission:edit-criterion', ['only' => ['edit','update']]);
        $this->middleware('permission:delete-criterion', ['only' => ['destroy']]);
    }

    public function index(Request $request): View {
        $pageTitle = 'Criterion List';
        return view('criterions.index', compact('pageTitle'));
    }

    public function getAjaxData(Request $request){
        if ($request->ajax()) {
            $data = BudgetCriterion::select('id', 'criterion_name', 'criterion_status', 'created_at');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('created_at', function ($row) {
                    if(!empty($row->created_at)){
                        return date('d-m-Y', strtotime($row->created_at));
                    }
                })
                ->addColumn('criterion_name', function ($row) {
                    if(!empty($row->criterion_name)){
                        return $row->criterion_name;
                    } else {
                        return __('N/A');
                    }
                })
                ->addColumn('action', function ($row) {
                    $dataId = $row->id;
                    return '<a href="javascript:void(0)" class="btn btn-soft-primary btn-sm me-1 editBtn" data-id="'.$dataId.'">
                                <iconify-icon icon="solar:pen-bold" class="align-middle fs-18"></iconify-icon>
                            </a>
                            <a href="javascript:void(0)" class="btn btn-soft-danger btn-sm" onclick="deleteStatus('.$dataId.')"><iconify-icon icon="solar:trash-bin-trash-bold" class="align-middle fs-18"></iconify-icon>
                            </a>';
                })
                ->rawColumns(['action', 'created_at', 'criterion_name'])
                ->make(true);
        }
        return response()->json(['error' => __('Unauthorized')], 403);
    }

    public function store(Request $request) {
        $request->validate([
            'criterion_name' => 'required|string'
        ]);
        BudgetCriterion::create([
            'criterion_name' => $request->criterion_name,
            'criterion_status' => 1
        ]);
        return response()->json(['success'=>true, 'message'=>__('Criterion created successfully')]);
    }

    public function edit($id) {
        $criterion = BudgetCriterion::findOrFail($id);
        return response()->json(['success'=>true, 'data'=>$criterion]);
    }

    public function update(Request $request, $id) {
        $request->validate([
            'criterion_name' => 'required|string'
        ]);
        $criterion = BudgetCriterion::findOrFail($id);
        $criterion->update([
            'criterion_name' => $request->criterion_name
        ]);
        return response()->json(['success'=>true, 'message'=>__('Criterion updated successfully')]);
    }

    public function destroy($id) {
        $criterion = BudgetCriterion::findOrFail($id);
        $criterion->delete();
        return response()->json(['success'=>__('Criterion deleted successfully!')]);
    }
}
