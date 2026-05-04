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
use App\Support\UiText;

class PermissionController extends Controller {

    public function __construct() {
        $this->middleware('permission:list-permission|create-permission|edit-permission|delete-permission', ['only' => ['index','show']]);
        $this->middleware('permission:create-permission', ['only' => ['create','store']]);
        $this->middleware('permission:edit-permission', ['only' => ['edit','update']]);
        $this->middleware('permission:delete-permission', ['only' => ['destroy']]);
    }

    public function index(Request $request): View {
        $pageTitle = 'Permissions';
        return view('permissions.index', compact('pageTitle'));
    }

    public function getAjaxData(Request $request){
        if ($request->ajax()) {
            $data = Permission::select('id', 'name', 'created_at');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('date', function ($row) {
                    if(!empty($row->created_at)){
                        return date('d-m-Y', strtotime($row->created_at));
                    }
                })
                ->addColumn('name', function ($row) {
                    if(!empty($row->name)){
                        return UiText::permission($row->name);
                    } else {
                        return __('N/A');
                    }
                })
                ->addColumn('action', function ($row) {
                    $dataId = $row->id;
                    $editRoute = route("permission.edit", $row->id);
                    return '<a href="'. $editRoute .'" class="btn btn-soft-primary btn-sm"><iconify-icon icon="solar:pen-2-broken" class="align-middle fs-18"></iconify-icon></a> <a href="javascript:void(0)" class="btn btn-soft-danger btn-sm" onclick="deleteUser('.$dataId.')"><iconify-icon icon="solar:trash-bin-minimalistic-2-broken" class="align-middle fs-18"></iconify-icon></a>';
                })
                ->rawColumns(['action', 'date', 'name'])
                ->make(true);
        }
        return response()->json(['error' => __('Unauthorized')], 403);
    }

    public function create() {
        $pageTitle = 'Add Permission';
        return view('permissions.create', compact('pageTitle'));
    }

    public function store(Request $request) {
        $permission = new Permission();
        $permission->name        = $request->permission;
        $permission->guard_name  = 'web';
        if ($permission->save()) {
            return redirect()->route('permissions')->with('success', 'New permission is added successfully.');
        } else {
            return redirect()->back()->with('error', 'Something went wrong!');
        }
    }

    public function edit(Request $request, $id) {
        $pageTitle = 'Edit Permission';
        $permission = Permission::where('id', $id)->first();
        return view('permissions.edit', compact('permission', 'pageTitle'));
    }


    public function update(Request $request, $id) {
        $permission = Permission::where('id', $id)->first();
        $permission->name        = $request->permission;
        $permission->guard_name  = 'web';
        if ($permission->save()) {
            return redirect()->back()->with('success', 'Permission is updated successfully!');
        } else {
            return redirect()->back()->with('error', 'Something went wrong!');
        }
    }

    public function destroy(Request $request, $id) {
        $permission = Permission::where('id', $id)->first();
        if(!empty($permission)){
            $permission->delete();
            return response()->json(['success' => 'Permission is deleted successfully!']);
        }else{
            return response()->json(['error' => 'Record not found!'], 404);
        }
    }
}
