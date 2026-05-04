<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\View\View;
use App\Models\User;
use Carbon\Carbon; 
use Auth;
use Session;
use DB; 
use Mail; 
use Str;
use DataTables;
use App\Mail\UserCreatedMail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use App\Support\UiText;

class UserController extends Controller {

    public function __construct() {
        $this->middleware('permission:create-user|edit-user|delete-user', ['only' => ['index','show']]);
        $this->middleware('permission:create-user', ['only' => ['create','store']]);
        $this->middleware('permission:edit-user', ['only' => ['edit','update']]);
        $this->middleware('permission:delete-user', ['only' => ['destroy']]);
    }

    public function index(Request $request): View {
        $pageTitle = 'Users';
        return view('users.index', compact('pageTitle'));
    }

    public function getAjaxData(Request $request){
        if ($request->ajax()) {
            $data = User::with('roles')->select('id', 'name', 'email', 'status', 'created_at');

            if ($request->status != '') {
                $data->where('status', $request->status);
            }

            return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('date', function ($row) {
                if(!empty($row->created_at)){
                    return date('d-m-Y', strtotime($row->created_at));
                }
            })
            ->addColumn('name', function ($row) {
                return $row->name ?: __('N/A');
            })
            ->addColumn('email', function ($row) {
                return $row->email ?: __('N/A');
            })
            ->addColumn('role', function ($row) {
                return $row->roles->pluck('name')->map(fn ($roleName) => UiText::role($roleName))->implode(', ') ?: __('No Role');
            })
            ->addColumn('status', function ($row) {
                if ($row->status == 1) {
                    return '<span class="badge bg-success">' . __('Active') . '</span>';
                }

                return '<span class="badge bg-danger">' . __('Inactive') . '</span>';
            })
            ->addColumn('action', function ($row) {
                $dataId = $row->id;
                $editRoute = route("user.edit", $row->id);
                return '<a href="'. $editRoute .'" class="btn btn-soft-primary btn-sm"><iconify-icon icon="solar:pen-2-broken" class="align-middle fs-18"></iconify-icon></a> <a href="javascript:void(0)" class="btn btn-soft-danger btn-sm" onclick="deleteUser('.$dataId.')"><iconify-icon icon="solar:trash-bin-minimalistic-2-broken" class="align-middle fs-18"></iconify-icon></a> <button type="button" class="btn btn-dark btn-sm send-reset-link" data-id="'. $dataId .'"><span>' . __('Sent Reset Password Link') . '</span></button>';
            })
            ->rawColumns(['action', 'date', 'name', 'email', 'role', 'status'])
            ->make(true);
        }
        return response()->json(['error' => __('Unauthorized')], 403);
    }

    public function create() {
        $pageTitle = 'Add User';
        $roles = Role::get();
        return view('users.create', compact('roles', 'pageTitle'));
    }

    public function store(Request $request) {

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'roles' => 'required',
        ]);

        $password = Str::random(10);
        $plainPassword = $password;

        $user = new User();
        $user->name      = $request->name;
        $user->email     = $request->email;
        $user->password  = Hash::make($password);
        if ($user->save()) {
            $user->assignRole($request->roles);
            Mail::to($user->email)->send(new UserCreatedMail($user, $plainPassword));
            return redirect()->route('users')->with('success', __('New user is added successfully.'));
        } else {
            return redirect()->back()->with('error', __('Something went wrong!'));
        }
    }

    public function edit(Request $request, $id) {
        $pageTitle = 'User Edit';
        $user = User::with('roles')->where('id', $id)->first();
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name','name')->all();
        return view('users.edit', compact('user', 'roles', 'userRole', 'pageTitle'));
    }

    public function update(Request $request, $id) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email,' . $id,
            'roles' => 'required',
            'status' => 'required|in:0,1',
        ]);

        $user = User::where('id', $id)->first();
        $user->name      = $request->name;
        $user->email     = $request->email;
        $user->status    = $request->status;
        if ($user->save()) {
            $user->assignRole($request->roles);
            return redirect()->back()->with('success', __('User is updated successfully!'));
        } else {
            return redirect()->back()->with('error', __('Something went wrong!'));
        }
    }

    public function destroy(Request $request, $id) {
        $user = User::where('id', $id)->first();
        if(!empty($user)){
            $user->delete();
            return response()->json(['success' => __('User is deleted successfully!')]);
        }else{
            return response()->json(['error' => __('Record not found!')], 404);
        }
    }

    public function sendResetLink(Request $request) {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);
        $user = User::findOrFail($request->user_id);
        $status = Password::sendResetLink(['email' => $user->email]);
        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['success' => true, 'message' => __($status)]);
        }
        return response()->json(['success' => false, 'message' => __($status)], 500);
    }

}
