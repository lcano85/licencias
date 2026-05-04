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
use App\Models\ActivityComment;
use App\Models\ActivityCommentDocument;
use App\Models\Calendar;
use App\Models\ProjectAssignmentByRole;

class ActivitiesController extends Controller {

    public function __construct() {
        $this->middleware('permission:list-activities|create-activities|edit-activities|delete-activities', ['only' => ['index','show']]);
        $this->middleware('permission:create-activities', ['only' => ['create','store']]);
        $this->middleware('permission:edit-activities', ['only' => ['edit','update']]);
        $this->middleware('permission:delete-activities', ['only' => ['destroy']]);
    }

    public function index(Request $request): View {
        $pageTitle = 'Activities';
        return view('activities.index', compact('pageTitle'));
    }

    public function getAjaxData(Request $request){
        if ($request->ajax()) {
            $data = Activities::select('id', 'activity_name', 'activity_type', 'assign_by', 'created_by', 'clientID', 'projectID', 'status', 'created_at', 'due_date', 'updated_at', 'sub_status')->whereNotNull('clientID');

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
                    return '<span class="badge bg-dark me-1">On time</span>';
                } elseif ($row->status == 2) {
                    return '<span class="badge bg-secondary me-1">Delayed</span>';
                } elseif ($row->status == 3){
                    return '<span class="badge bg-warning me-1">Priority</span>';
                } elseif ($row->status == 4){
                    return '<span class="badge bg-success me-1">Completed</span>';
                } else {
                    return 'N/A';
                }
            })
            ->addColumn('sub_status', function ($row) {
                if ($row->sub_status == 1) {
                    return '<span class="badge bg-success me-1">Completed</span>';
                } elseif ($row->sub_status == 2) {
                    return '<span class="badge bg-secondary me-1">Reject</span>';
                } elseif ($row->sub_status == 3){
                    return '<span class="badge bg-warning me-1">Review</span>';
                } elseif ($row->sub_status == 4){
                    return '<span class="badge bg-dark me-1">Cancel</span>';
                } elseif ($row->sub_status == 5){
                    return '<span class="badge bg-dark me-1">Created</span>';
                } else {
                    return 'N/A';
                }
            })
            ->addColumn('action', function ($row) {
                $dataId = $row->id;
                $viewRoute = route("activity.view", $row->id);
                $historyRoute = route("activity.history", $row->id);
                $commentRoute = route("activity.comment", $row->id);

                $userId = auth()->id(); // current logged-in user ID
                $buttons = '';
                if (!empty($row->assign_by)) {
                    $assignedUserIds = explode(',', $row->assign_by); // convert string to array
                    if (in_array($userId, $assignedUserIds)) {
                        $buttons = '<a href="' . $viewRoute . '" class="btn btn-soft-primary btn-sm" title="View" data-bs-toggle="tooltip" data-bs-placement="top"><iconify-icon icon="solar:eye-bold" class="align-middle fs-18"></iconify-icon></a>';
                    }
                }

                if ($row->created_by == $userId) {
                    // Activity creator gets Edit and Delete buttons
                    $editRoute = route("activity.edit", $row->id);
                    $buttons .= ' <a href="'. $editRoute .'" class="btn btn-soft-primary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                        <iconify-icon icon="solar:pen-new-square-linear" class="align-middle fs-18"></iconify-icon>
                      </a>
                      <a href="' . $viewRoute . '" class="btn btn-soft-primary btn-sm" title="View" data-bs-toggle="tooltip" data-bs-placement="top">
                            <iconify-icon icon="solar:eye-bold" class="align-middle fs-18"></iconify-icon>
                        </a>
                      <a href="javascript:void(0)" class="btn btn-soft-danger btn-sm" onclick="deleteActivity('.$dataId.')" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                        <iconify-icon icon="solar:trash-bin-trash-bold" class="align-middle fs-18"></iconify-icon>
                      </a>';
                }

                return $buttons;
            })
            ->rawColumns(['action', 'created_at', 'activity_name', 'activity_type', 'assign_by', 'created_by', 'status', 'clientID', 'due_date', 'updated_at', 'projectID', 'sub_status'])
            ->make(true);
        }
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    public function getAjaxDataPersonal(Request $request){
        if ($request->ajax()) {
            $authId = auth()->id();
            $data = Activities::select('id', 'activity_name', 'activity_type', 'assign_by', 'created_by', 'clientID', 'projectID', 'status', 'created_at', 'due_date', 'updated_at', 'sub_status')->whereNull('assign_by')->orWhereNotNull('assign_by')->whereNull('clientID')->orWhereNotNull('clientID')->whereNull('projectID')->where('created_by', $authId); 

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
                    return '<span class="badge bg-dark me-1">On time</span>';
                } elseif ($row->status == 2) {
                    return '<span class="badge bg-secondary me-1">Delayed</span>';
                } elseif ($row->status == 3){
                    return '<span class="badge bg-warning me-1">Priority</span>';
                } elseif ($row->status == 4){
                    return '<span class="badge bg-success me-1">Completed</span>';
                } else {
                    return 'N/A';
                }
            })
            ->addColumn('sub_status', function ($row) {
                if ($row->sub_status == 1) {
                    return '<span class="badge bg-success me-1">Completed</span>';
                } elseif ($row->sub_status == 2) {
                    return '<span class="badge bg-secondary me-1">Reject</span>';
                } elseif ($row->sub_status == 3){
                    return '<span class="badge bg-warning me-1">Review</span>';
                } elseif ($row->sub_status == 4){
                    return '<span class="badge bg-dark me-1">Cancel</span>';
                } elseif ($row->sub_status == 5){
                    return '<span class="badge bg-dark me-1">Created</span>';
                } else {
                    return 'N/A';
                }
            })
            ->addColumn('action', function ($row) {
                $dataId = $row->id;
                $viewRoute = route("activity.view", $row->id);
                $historyRoute = route("activity.history", $row->id);
                $commentRoute = route("activity.comment", $row->id);

                $userId = auth()->id(); // current logged-in user ID
                $buttons = '';
                if ($row->created_by == $userId) {
                    // Activity creator gets Edit and Delete buttons
                    $editRoute = route("activity.edit", $row->id);
                    $buttons .= ' <a href="'. $editRoute .'" class="btn btn-soft-primary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                        <iconify-icon icon="solar:pen-new-square-linear" class="align-middle fs-18"></iconify-icon>
                      </a>
                      <a href="' . $viewRoute . '" class="btn btn-soft-primary btn-sm" title="View" data-bs-toggle="tooltip" data-bs-placement="top">
                            <iconify-icon icon="solar:eye-bold" class="align-middle fs-18"></iconify-icon>
                        </a>
                      <a href="javascript:void(0)" class="btn btn-soft-danger btn-sm" onclick="deleteActivity('.$dataId.')" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                        <iconify-icon icon="solar:trash-bin-trash-bold" class="align-middle fs-18"></iconify-icon>
                      </a>';
                }

                return $buttons;
            })
            ->rawColumns(['action', 'created_at', 'activity_name', 'activity_type', 'assign_by', 'created_by', 'status', 'clientID', 'due_date', 'updated_at', 'projectID', 'sub_status'])
            ->make(true);
        }
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    public function create(Request $request) {
        $pageTitle = 'Add Activity';
        $roleIds = ProjectAssignmentByRole::pluck('role_id');
        $roleNames = \Spatie\Permission\Models\Role::whereIn('id', $roleIds)->pluck('name');
        $managers = User::role($roleNames)->get();
        $projects = Projects::where('status', 1)->get();
        $clients = Clients::all();
        $selectedClientId = $request->client_id;
        return view('activities.create', compact('managers', 'projects', 'clients', 'pageTitle', 'selectedClientId'));
    }

    public function upload(Request $request) {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('activities_attachment', $filename, 'public');
            return response()->json(['file_name' => $filename]);
        }
        return response()->json(['error' => 'No file uploaded'], 400);
    }

    public function getProjectClient($projectId) {
        $project = Projects::find($projectId);

        if ($project && $project->clientID) {
            return response()->json([
                'success'  => true,
                'clientID' => $project->clientID
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No client found for this project'
        ]);
    }


    public function store(Request $request) {
        $userID = Auth::user()->id;
        $request->validate([
            'activity_name'      => 'required|string|max:255',
            'due_date'           => 'required|date|after_or_equal:today',
        ]);

        $assign_byIds = is_array($request->assign_by) ? implode(',', $request->assign_by) : null;
        $project = Projects::where('id', $request->projectID)->first();
if(empty($request->status)){
    $request->merge(['status' => 1]);
}

        $activity = new Activities();
        $activity->activity_name  = $request->activity_name;
        $activity->activity_type  = $request->activity_type;
        $activity->created_by     = $userID;
        $activity->status = $request->status ?? 1;
        $activity->main_description  = $request->main_description;
        $activity->due_date  = $request->due_date;
        $activity->short_description  = $request->short_description;
        $activity->comments     = $request->comments;
        $activity->projectID    = $request->projectID;
        $activity->clientID     = $request->clientID;
        $activity->assign_by    = $assign_byIds;
        $activity->sub_status   = 5;
        if ($activity->save()) {
            if ($request->has('attachment_file')) {
                foreach ($request->attachment_file as $file) {
                    ActivitiesAttachment::create([
                        'activityID' => $activity->id,
                        'attachment_file'  => $file,
                    ]);
                }
            }

            ActivityHistory::create([
                'activity_id' => $activity->id,
                'user_id'     => Auth::id(),
                'action'      => 'created',
                'changes'     => null, // No changes for creation, just record that it was created
            ]);

            // Send Email to assigned users
            // $assignedUsers = User::whereIn('id', $request->assign_by)->get();
            // foreach ($assignedUsers as $user) {
            //     Mail::to($user->email)->send(new ActivityAssignedMail($activity));
            // }

            return redirect()->route('activities')->with('success', 'Activity is added successfully.');
        } else {
            return redirect()->back()->with('error', 'Something went wrong!');
        }
    }

    public function edit(Request $request, $id) {
        $pageTitle = 'Edit Activity';
        $activity = Activities::findOrFail($id);
        $managers = User::role('Manager')->get();
        $attachments = ActivitiesAttachment::where('activityID', $id)->get();
        $projects = Projects::where('status', 1)->get();
        $clients = Clients::all();
        return view('activities.edit', compact('activity', 'managers', 'attachments', 'projects', 'clients', 'pageTitle'));
    }

    public function view(Request $request, $id) {
        $pageTitle = 'View Activity';
        $activity = Activities::findOrFail($id);
        
        if (!$activity) {
            return redirect()->back()->with('error', 'Activity not found.');
        }

        // Activity details
        $creator = User::where('id', $activity->created_by)->pluck('name')->first();
        $userID = explode(',', $activity->assign_by);
        $users = User::whereIn('id', $userID)->pluck('name')->toArray();
        $participants = implode(', ', $users);
        
        // Attachments and project
        $attachments = ActivitiesAttachment::where('activityID', $id)->get();
        $project = Projects::where('id', $activity->projectID)->pluck('project_title')->first();
        
        // History and comments
        $history = ActivityHistory::with('user')->where('activity_id', $id)->orderBy('created_at', 'desc')->get();
        $activityID = $id;
        $comments = ActivityComment::where('activityID', $id)->get();
        $commentDocuments = ActivityCommentDocument::where('activityID', $id)->get();
        
        // Project details
        $projectdetail = Projects::where('id', $activity->projectID)->first();
        $projectCreator = $projectdetail ? User::where('id', $projectdetail->created_by)->pluck('name')->first() : null;
        $projectParticipants = '';
        
        if ($projectdetail && !empty($projectdetail->assign_by)) {
            $projectuserID = explode(',', $projectdetail->assign_by);
            $projectusers = User::whereIn('id', $projectuserID)->pluck('name')->toArray();
            $projectParticipants = implode(', ', $projectusers);
        }
        
        // Integrated timeline
        $integratedTimeline = $this->getIntegratedTimeline($id);
        
        return view('activities.view', compact(
            'activity', 'creator', 'participants', 'attachments', 'project', 
            'history', 'activityID', 'comments', 'commentDocuments', 
            'projectdetail', 'projectCreator', 'projectParticipants', 
            'pageTitle', 'integratedTimeline'
        ));
    }

    // NEW METHOD: Get integrated timeline of comments and history for activities
    private function getIntegratedTimeline($activityId) {
        // Get all activity comments
        $comments = ActivityComment::with('creator')
            ->where('activityID', $activityId)
            ->get()
            ->map(function($comment) {
                return [
                    'type' => 'comment',
                    'id' => $comment->id,
                    'user_id' => $comment->user_id,
                    'user_name' => $comment->creator->name ?? 'Unknown',
                    'content' => $comment->act_comment,
                    'commentTypes' => $comment->commentTypes,
                    'created_at' => $comment->created_at,
                    'attachments' => ActivityCommentDocument::where('activityCommentID', $comment->id)->get(),
                    'data' => $comment
                ];
            });

        // Get all activity history (updates, creation)
        $history = ActivityHistory::with('user')
            ->where('activity_id', $activityId)
            ->get()
            ->map(function($log) {
                return [
                    'type' => 'history',
                    'id' => $log->id,
                    'user_id' => $log->user_id,
                    'user_name' => $log->user->name ?? 'System',
                    'action' => $log->action,
                    'changes' => $log->changes,
                    'created_at' => $log->created_at,
                    'data' => $log
                ];
            });

        // Merge both timelines and sort by date (newest first)
        $integratedTimeline = $comments->concat($history)
            ->sortByDesc('created_at')
            ->values();

        return $integratedTimeline;
    }

    public function update(Request $request, $id) {

        $userID = Auth::user()->id;
        $request->validate([
            'activity_name'      => 'required|string|max:255',
            'due_date'           => 'required|date|after_or_equal:today',
        ]);

        $assign_byIds = is_array($request->assign_by) ? implode(',', $request->assign_by) : null;

        $activity = Activities::where('id', $id)->first();
        $project = Projects::where('id', $request->projectID)->first();
        
        // Store original values before updating
        $oldValues = $activity->getOriginal();

        $activity->activity_name  = $request->activity_name;
        $activity->activity_type  = $request->activity_type;
        $activity->created_by     = $userID;
        $activity->status       = $request->status;
        $activity->main_description  = $request->main_description;
        $activity->due_date  = $request->due_date;
        $activity->short_description  = $request->short_description;
        $activity->comments     = $request->comments;
        $activity->projectID    = $request->projectID;
        $activity->clientID     = $request->clientID;
        $activity->assign_by    = $assign_byIds;
        if ($activity->isDirty()) {
            $newValues = $activity->getDirty();
            
            if ($activity->save()) {
                if ($request->has('attachment_file')) {
                    foreach ($request->attachment_file as $file) {
                        ActivitiesAttachment::create([
                            'activityID' => $activity->id,
                            'attachment_file'  => $file,
                        ]);
                    }
                }

                if (!empty($newValues)) {
                    $formattedChanges = [];
                    
                    foreach ($newValues as $field => $newValue) {
                        $oldValue = $oldValues[$field] ?? '';
                        
                        $formattedOldValue = $this->formatHistoryValue($field, $oldValue);
                        $formattedNewValue = $this->formatHistoryValue($field, $newValue);
                        
                        if ($formattedOldValue !== $formattedNewValue) {
                            $formattedChanges[$field] = [
                                'old' => $formattedOldValue,
                                'new' => $formattedNewValue
                            ];
                        }
                    }
                    
                    if (!empty($formattedChanges)) {
                        ActivityHistory::create([
                            'activity_id' => $activity->id,
                            'user_id'     => Auth::id(),
                            'action'      => 'updated',
                            'changes'     => $formattedChanges,
                        ]);
                    }
                }
                return redirect()->route('activities')->with('success', 'Activity is updated successfully.');
            } else {
                return redirect()->back()->with('error', 'Something went wrong!');
            }
        } else {
            // No changes were made
            return redirect()->route('activities')->with('info', 'No changes were made to the activity.');
        }
    }

    public function destroy(Request $request, $id) {
        $activity = Activities::find($id);
        if (!$activity) {
            return response()->json(['error' => 'Record not found!'], 404);
        }
        $attachments = ActivitiesAttachment::where('activityID', $activity->id)->get();
        foreach ($attachments as $attachment) {
            $filePath = 'activities_attachment/' . $attachment->attachment_file;
            if ($attachment->attachment_file && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            $attachment->delete();
        }
        $activity->delete();
        return response()->json(['success' => 'Activity deleted successfully!']);
    }

    public function uploadRemove(Request $request, $id) {
        $attachment = ActivitiesAttachment::where('id', $id)->first();
        if(!empty($attachment)){
            if ($attachment->attachment_file && Storage::disk('public')->exists('activities_attachment/' . $attachment->attachment_file)) {
                Storage::disk('public')->delete('activities_attachment/' . $attachment->attachment_file);
            }
            $attachment->delete();
            return response()->json(['success' => 'Attachment is deleted successfully!']);
        }else{
            return response()->json(['error' => 'Record not found!'], 404);
        }
    }

    public function activityHistory(Request $request, $id){
        $activity = Activities::find($id);
        if (!$activity) {
            return redirect()->back()->with('error', 'Activity not found.');
        }
        $history = ActivityHistory::with('user')->where('activity_id', $id)->orderBy('created_at', 'desc')->get();
        return view('activities.history', compact('history'));
    }

    public function activityComment(Request $request, $id){
        $activityID = $id;
        $comments = ActivityComment::where('activityID', $id)->get();
        $commentDocuments = ActivityCommentDocument::where('activityID', $id)->get();
        return view('activities.comment', compact('activityID', 'comments', 'commentDocuments'));
    }

    public function activityCommentUpload(Request $request) {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('activities_comment_attachment', $filename, 'public');

            return response()->json(['file_name' => $filename]);
        }
        return response()->json(['error' => 'No file uploaded'], 400);
    }

    public function activityCommentStore(Request $request) {
        $userId = Auth::id();
        $request->validate([
            'act_comment'  => 'required|string',
        ]);

        $activityComment = ActivityComment::create([
            'activityID'   => $request->activityID,
            'act_comment'  => $request->act_comment,
            'commentTypes' => $request->commentTypes,
            'user_id'      => $userId,
        ]);

        if ($activityComment) {

            $activity = Activities::where('id', $request->activityID)->first();
            $oldStatus = $activity->status;
            $oldSubStatus = $activity->sub_status;

            if($request->commentTypes == 1){
                $activity->status = 4;
                $activity->sub_status = $request->commentTypes;
            } else {
                $activity->sub_status = $request->commentTypes;
            }
            $activity->save();

            // Log status / sub-status changes into ActivityHistory
            $changes = [];
            if ($oldStatus != $activity->status) {
                $changes['status'] = [
                    'old' => $this->formatHistoryValue('status', $oldStatus),
                    'new' => $this->formatHistoryValue('status', $activity->status),
                ];
            }
            if ($oldSubStatus != $activity->sub_status) {
                $subStatusMap = [
                    1 => 'Completed',
                    2 => 'Reject',
                    3 => 'Review',
                    4 => 'Cancel',
                    5 => 'Created',
                ];

                $changes['sub_status'] = [
                    'old' => $subStatusMap[$oldSubStatus] ?? ($oldSubStatus ?? 'N/A'),
                    'new' => $subStatusMap[$activity->sub_status] ?? $activity->sub_status,
                ];
            }

            if (!empty($changes)) {
                ActivityHistory::create([
                    'activity_id' => $activity->id,
                    'user_id'     => $userId,
                    'action'      => 'updated',
                    'changes'     => $changes,
                ]);
            }

            // Handle attachments if provided
            if ($request->hasFile('attachment_file')) {
                foreach ($request->file('attachment_file') as $file) {
                    $filename = time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('activities_comment_attachment', $filename, 'public');

                    ActivityCommentDocument::create([
                        'activityID'          => $request->activityID,
                        'activityCommentID'   => $activityComment->id,
                        'attachment_file'     => $filename, // store path instead of raw file
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Comment added successfully!',
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => 'Something went wrong!',
        ], 500);
    }

    public function activityCommentDelete(Request $request, $id){
        $activityCmt = ActivityComment::find($id);
        if (!$activityCmt) {
            return response()->json(['error' => 'Record not found!'], 404);
        }
        $attachments = ActivityCommentDocument::where('activityCommentID', $activityCmt->id)->get();
        foreach ($attachments as $attachment) {
            $filePath = 'activities_comment_attachment/' . $attachment->attachment_file;
            if ($attachment->attachment_file && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            $attachment->delete();
        }
        $activityCmt->delete();
        return response()->json(['success' => 'Comment deleted successfully!']);
    }

    // Global Function For This Controller
    private function formatHistoryValue($field, $value) {
        if ($value === null || $value === '') {
            return 'N/A';
        }

        if ($field === 'assign_by') {
            if (is_string($value)) {
                $userIDs = explode(',', $value);
            } else {
                $userIDs = (array) $value;
            }

            $users = User::whereIn('id', $userIDs)->pluck('name')->toArray();
            return implode(', ', $users);
        }

        if ($field === 'status') {
            // Keep this in sync with the status badges used in the list & view
            $statusMap = [
                1 => 'On time',
                2 => 'Delayed',
                3 => 'Priority',
                4 => 'Completed',
            ];

            return $statusMap[$value] ?? $value;
        }

        if ($field === 'sub_status') {
            $subStatusMap = [
                1 => 'Completed',
                2 => 'Reject',
                3 => 'Review',
                4 => 'Cancel',
                5 => 'Created',
            ];

            return $subStatusMap[$value] ?? $value;
        }

        if ($field === 'due_date') {
            return date('d M Y', strtotime($value));
        }

        if ($field === 'created_by') {
            $user = User::find($value);
            return $user ? $user->name : $value;
        }

        return $value;
    }

}
