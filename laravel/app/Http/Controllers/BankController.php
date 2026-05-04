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

class BankController extends Controller {
    public function __construct() {
        $this->middleware('permission:list-bank|create-bank|edit-bank|delete-bank', ['only' => ['index','show']]);
        $this->middleware('permission:create-bank', ['only' => ['create','store']]);
        $this->middleware('permission:edit-bank', ['only' => ['edit','update']]);
        $this->middleware('permission:delete-bank', ['only' => ['destroy']]);
    }

    public function index(Request $request): View {
        $pageTitle = 'Banks List';
        return view('banks.index', compact('pageTitle'));
    }

    public function getAjaxData(Request $request){
        if ($request->ajax()) {
            $data = Bank::select('id', 'bank_name', 'bank_code', 'status', 'created_at');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('date', function ($row) {
                    if(!empty($row->created_at)){
                        return date('d-m-Y', strtotime($row->created_at));
                    }
                })
                ->addColumn('bank_name', function ($row) {
                    if(!empty($row->bank_name)){
                        return $row->bank_name;
                    } else {
                        return 'N/A';
                    }
                })
                ->addColumn('bank_code', function ($row) {
                    if(!empty($row->bank_code)){
                        return $row->bank_code;
                    } else {
                        return 'N/A';
                    }
                })
                ->addColumn('action', function ($row) {
                    $dataId = $row->id;
                    $editRoute = route("bank.edit", $row->id);
                    return '<a href="'. $editRoute .'" class="btn btn-soft-primary btn-sm"><iconify-icon icon="solar:pen-2-broken" class="align-middle fs-18"></iconify-icon></a> <a href="javascript:void(0)" class="btn btn-soft-danger btn-sm" onclick="deleteBank('.$dataId.')"><iconify-icon icon="solar:trash-bin-minimalistic-2-broken" class="align-middle fs-18"></iconify-icon></a>';
                })
                ->rawColumns(['action', 'date', 'bank_name', 'bank_code'])
                ->make(true);
        }
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    public function create() {
        $pageTitle = 'Add Bank';
        return view('banks.create', compact('pageTitle'));
    }

    public function store(Request $request) {
        $bank = new Bank();
        $bank->bank_name   = $request->bank_name;
        $bank->bank_code   = $request->bank_code;
        $bank->status  = 1;
        if ($bank->save()) {
            return redirect()->route('banks')->with('success', 'New bank is added successfully.');
        } else {
            return redirect()->back()->with('error', 'Something went wrong!');
        }
    }

    public function edit(Request $request, $id) {
        $pageTitle = 'Edit Bank';
        $bank = Bank::where('id', $id)->first();
        return view('banks.edit', compact('bank', 'pageTitle'));
    }

    public function update(Request $request, $id) {
        $bank = Bank::where('id', $id)->first();
        $bank->bank_name   = $request->bank_name;
        $bank->bank_code   = $request->bank_code;
        $bank->status  = 1;
        if ($bank->save()) { 
            return redirect()->back()->with('success', 'Bank is updated successfully!');
        } else {
            return redirect()->back()->with('error', 'Something went wrong!');
        }
    }

    public function destroy(Request $request, $id) {
        $bank = Bank::where('id', $id)->first();
        if(!empty($bank)){
            $bank->delete();
            return response()->json(['success' => 'Bank is deleted successfully!']);
        }else{
            return response()->json(['error' => 'Record not found!'], 404);
        }
    }
}
