<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\Projects;
use Carbon\Carbon; 
use Auth;
use Session;
use DB; 
use Mail; 
use Str;
use DataTables;
use Illuminate\Support\Facades\Storage;
use App\Models\ProjectAssignmentByRole;

class ProjectAssignmentByRoleController extends Controller {

    public function __construct() {
        $this->middleware('permission:list-assign-by-role-project|create-assign-by-role-project|delete-assign-by-role-project', ['only' => ['index','getAjaxData']]);
        $this->middleware('permission:create-assign-by-role-project', ['only' => ['create','store']]);
        $this->middleware('permission:delete-assign-by-role-project', ['only' => ['destroy']]);
    }

    public function index(Request $request): View {
        $pageTitle = 'Manage By Roles Project Assignment';
        $roles = Role::get();
        return view('assign-by-role-project.index', compact('roles', 'pageTitle'));
    }

    public function getAjaxData(Request $request){
        if ($request->ajax()) {
            $data = ProjectAssignmentByRole::select('id', 'role_id', 'created_at');
            return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('created_at', function ($row) {
                if(!empty($row->created_at)){
                    return date('d-m-Y', strtotime($row->created_at));
                }
            })
            ->addColumn('role_id', function ($row) {
                if (!empty($row->role_id)) {
                    $users = Role::where('id', $row->role_id)->pluck('name')->first();
                    return $users;
                }
                return 'N/A';
            })
            ->addColumn('action', function ($row) {
                $dataId = $row->id;
                return ' <a href="javascript:void(0)" class="btn btn-soft-danger btn-sm" onclick="deleteUser('.$dataId.')"><iconify-icon icon="solar:trash-bin-trash-bold" class="align-middle fs-18"></iconify-icon></a>';
            })
            ->rawColumns(['action', 'created_at', 'role_id'])
            ->make(true);
        }
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    public function store(Request $request) {
        $request->validate([
            'roles' => 'required',
        ]);
        $assignByRole = new ProjectAssignmentByRole();
        $assignByRole->role_id  = $request->roles;
        if ($assignByRole->save()) {
            return response()->json(['success' => true, 'message' => 'Role assigned successfully!']);
        } else {
            return response()->json(['success' => false, 'message' => 'Something went wrong!']);
        }
    }

    public function destroy(Request $request, $id) {
        $assignByRole = ProjectAssignmentByRole::where('id', $id)->first();
        if(!empty($assignByRole)){
            $assignByRole->delete();
            return response()->json(['success' => 'Role assigned deleted successfully!']);
        }else{
            return response()->json(['error' => 'Record not found!'], 404);
        }
    }
}
