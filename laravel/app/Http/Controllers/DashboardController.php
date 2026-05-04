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
use App\Models\Activities;
use App\Models\ActivitiesAttachment;
use Carbon\Carbon; 
use Auth;
use Session;
use DB; 
use Mail; 
use Str;
use DataTables;
use Illuminate\Support\Facades\Storage;
use App\Mail\ActivityAssignedMail;
use App\Models\ActivityHistory;
use App\Models\Projects;
use App\Models\Clients;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller {

    public function index(Request $request): View {
    // Real metrics
    $totalClients    = \App\Models\Clients::count();
    $activeClients   = \App\Models\Clients::where('client_status', 1)->count();
    $totalLicenses   = \App\Models\LicensesAgreements::count();
    $activeLicenses  = \App\Models\LicensesAgreements::where('status', 1)->count();
    $totalProjects   = \App\Models\Projects::count();
    $totalActivities = \App\Models\Activities::count();

    // Budget / Revenue
    $totalBudgetRevenue = \App\Models\Budget::sum('total');
    $pendingInvoices    = \App\Models\Budget::where('status', 1)->count();
    $paidInvoices       = \App\Models\Budget::where('status', 2)->count();

    // Recent licenses (for a "Recent Licenses" table)
    $recentLicenses = \App\Models\LicensesAgreements::latest()
        ->take(5)
        ->get(['id', 'commercialName', 'licensedConcept', 'status', 'created_at']);

    return view('dashboard', compact(
        'totalClients', 'activeClients',
        'totalLicenses', 'activeLicenses',
        'totalProjects', 'totalActivities',
        'totalBudgetRevenue', 'pendingInvoices', 'paidInvoices',
        'recentLicenses'
    ));
}


    public function getActivitiesData(Request $request){
        if ($request->ajax()) {
            $data = Activities::select('id', 'activity_name', 'activity_type', 'assign_by', 'created_by', 'clientID', 'projectID', 'status', 'created_at', 'due_date', 'updated_at');

            // apply status filter
            if ($request->status) {
                $data->where('status', $request->status);
            }

            return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('created_at', function ($row) {
                if(!empty($row->created_at)){
                    return date('d-m-Y', strtotime($row->created_at));
                }
            })
            ->addColumn('due_date', function ($row) {
                if(!empty($row->due_date)){
                    return date('d-m-Y', strtotime($row->due_date));
                }
            })
            ->addColumn('updated_at', function ($row) {
                if(!empty($row->updated_at)){
                    return date('d-m-Y', strtotime($row->updated_at));
                }
            })
            ->addColumn('activity_name', function ($row) {
                return $row->activity_name ?: 'N/A';
            })
            ->addColumn('activity_type', function ($row) {
                return $row->activity_type ?: 'N/A';
            })
            ->addColumn('projectID', function ($row) {
                if (!empty($row->projectID)) {
                    $project = Projects::where('id', $row->projectID)->pluck('project_title')->first();
                    return $project;
                }
                return 'N/A';
            })
            ->addColumn('clientID', function ($row) {
                if (!empty($row->clientID)) {
                    $client = Clients::where('id', $row->clientID)->pluck('commercialName')->first();
                    return $client;
                }
                return 'N/A';
            })
            ->addColumn('assign_by', function ($row) {
                if (!empty($row->assign_by)) {
                    $userID = explode(',', $row->assign_by);
                    $users = User::whereIn('id', $userID)->pluck('name')->toArray();
                    return implode(', ', $users);
                }
                return 'N/A';
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
                    return '<span class="badge bg-primary me-1">On time</span>';
                } elseif ($row->status == 2) {
                    return '<span class="badge bg-primary me-1">Delayed</span>';
                } elseif ($row->status == 3){
                    return '<span class="badge bg-primary me-1">Completed</span>';
                }elseif ($row->status == 4){
                    return '<span class="badge bg-primary me-1">More than one week</span>';
                }elseif ($row->status == 5){
                    return '<span class="badge bg-primary me-1">Today</span>';
                }elseif ($row->status == 6){
                    return '<span class="badge bg-primary me-1">This week</span>';
                }elseif ($row->status == 7){
                    return '<span class="badge bg-primary me-1">All</span>';
                } else {
                    return 'N/A';
                }
            })
            ->addColumn('action', function ($row) {
                $dataId = $row->id;
                $editRoute = route("activity.edit", $row->id);
                $viewRoute = route("activity.view", $row->id);
                $historyRoute = route("activity.history", $row->id);
                return '<a href="'. $editRoute .'" class="btn btn-soft-primary btn-sm"><iconify-icon icon="solar:pen-new-square-linear" class="align-middle fs-18"></iconify-icon></a> <a href="'. $viewRoute .'" class="btn btn-soft-primary btn-sm"><iconify-icon icon="solar:eye-bold" class="align-middle fs-18"></iconify-icon></a> <a href="'. $historyRoute .'" class="btn btn-soft-primary btn-sm" target="_blank"><iconify-icon icon="solar:history-bold" class="align-middle fs-18"></iconify-icon></a>';
            })
            ->rawColumns(['action', 'created_at', 'activity_name', 'activity_type', 'assign_by', 'created_by', 'status', 'clientID', 'due_date', 'updated_at', 'projectID'])
            ->make(true);
        }
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    public function editProfile(Request $request) {
        $user = Auth::user();
        return view('user-profile', compact('user'));
    }

    public function updateProfile(Request $request) {

        $user = User::where('id', $request->userID)->first();
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:users,name,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('error', 'Please fix the validation errors.');
        }

        try {

            $photoPath = $user->photo;
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $photoName = time() . '_' . $photo->getClientOriginalName();
                $photo->move(public_path('uploads/profile_photos'), $photoName);
                $photoPath = 'uploads/profile_photos/' . $photoName;
            }

            // Update user data
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone_number' => $request->phone_number,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'address' => $request->address,
                'short_description' => $request->short_description,
                'photo' => $photoName,
            ]);

            return redirect()->back()->with('success', 'Profile updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function changePasswordForm(Request $request) {
        $user = Auth::user();
        return view('change-password', compact('user'));
    }

    public function changePassword(Request $request) {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }

        $user = User::where('id', $request->userID)->first();
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Your current password is incorrect.',
            ]);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Password changed successfully!',
        ]);
    }

    public function helpContent(Request $request){
        return view('help');
    }
}
