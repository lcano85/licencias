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

class RoleController extends Controller {

    public function __construct() {
        $this->middleware('permission:list-role|create-role|edit-role|delete-role', ['only' => ['index','show']]);
        $this->middleware('permission:create-role', ['only' => ['create','store']]);
        $this->middleware('permission:edit-role', ['only' => ['edit','update']]);
        $this->middleware('permission:delete-role', ['only' => ['destroy']]);
    }

    public function index(Request $request): View {
        $pageTitle = 'Roles';
        return view('roles.index', compact('pageTitle'));
    }

    public function getAjaxData(Request $request){
        if ($request->ajax()) {
            $data = Role::select('id', 'name', 'created_at');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('date', function ($row) {
                    if(!empty($row->created_at)){
                        return date('d-m-Y', strtotime($row->created_at));
                    }
                })
                ->addColumn('name', function ($row) {
                    if(!empty($row->name)){
                        return UiText::role($row->name);
                    } else {
                        return __('N/A');
                    }
                })
                ->addColumn('action', function ($row) {
                    $dataId = $row->id;
                    $editRoute = route("role.edit", $row->id);
                    return '<a href="'. $editRoute .'" class="btn btn-soft-primary btn-sm"><iconify-icon icon="solar:pen-2-broken" class="align-middle fs-18"></iconify-icon></a> <a href="javascript:void(0)" class="btn btn-soft-danger btn-sm" onclick="deleteUser('.$dataId.')"><iconify-icon icon="solar:trash-bin-minimalistic-2-broken" class="align-middle fs-18"></iconify-icon></a>';
                })
                ->rawColumns(['action', 'date', 'name'])
                ->make(true);
        }
        return response()->json(['error' => __('Unauthorized')], 403);
    }

    public function create() {
        $pageTitle = 'Add Role';
        $permissions = Permission::get();
        return view('roles.create', compact('permissions', 'pageTitle'));
    }

    public function store(Request $request) {
        $role = new Role();
        $role->name        = $request->role;
        $role->guard_name  = 'web';
        if ($role->save()) {
            $permissions = Permission::whereIn('id', $request->permission)->get(['name'])->toArray();
            $role->syncPermissions($permissions);
            return redirect()->route('roles')->with('success', 'New role is added successfully.');
        } else {
            return redirect()->back()->with('error', 'Something went wrong!');
        }
    }

    public function show(Role $role): View {
        $rolePermissions = Permission::join("role_has_permissions","permission_id","=","id")
            ->where("role_id",$role->id)
            ->select('name')
            ->get();
        return view('roles.show', [
            'role' => $role,
            'rolePermissions' => $rolePermissions
        ]);
    }

    public function edit(Request $request, $id) {
        $pageTitle = 'Edit Role';
        $role = Role::where('id', $id)->first();
        // if($role->name == 'Master Admin'){
        //     abort(403, 'MASTER ADMIN ROLE CAN NOT BE EDITED');
        // }
        $rolePermissions = DB::table("role_has_permissions")->where("role_id", $role->id)->pluck('permission_id')->all();
        $permissions = Permission::get();
        return view('roles.edit', compact('role', 'permissions', 'rolePermissions', 'pageTitle'));
    }


    public function update(Request $request, $id) {
        $role = Role::where('id', $id)->first();
        $role->name        = $request->role;
        $role->guard_name  = 'web';
        if ($role->save()) {
            $permissions = Permission::whereIn('id', $request->permission)->get(['name'])->toArray();
            $role->syncPermissions($permissions);    
            return redirect()->back()->with('success', 'Role is updated successfully!');
        } else {
            return redirect()->back()->with('error', 'Something went wrong!');
        }
    }

    public function destroy(Request $request, $id) {
        $role = Role::where('id', $id)->first();
        if($role->name == 'Master Admin'){
            abort(403, 'MASTER ADMIN ROLE CAN NOT BE DELETED');
        }
        if(auth()->user()->hasRole($role->name)){
            abort(403, 'CAN NOT DELETE SELF ASSIGNED ROLE');
        }
        if(!empty($role)){
            $role->delete();
            return response()->json(['success' => 'Role is deleted successfully!']);
        }else{
            return response()->json(['error' => 'Record not found!'], 404);
        }
    }
}
