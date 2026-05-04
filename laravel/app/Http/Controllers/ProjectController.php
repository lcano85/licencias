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
use App\Models\ProjectAttachment;
use Carbon\Carbon; 
use Auth;
use Session;
use DB; 
use Mail; 
use Str;
use DataTables;
use Illuminate\Support\Facades\Storage;
use App\Mail\ProjectAssignedMail;
use App\Models\ProjectHistory;
use App\Models\Clients;
use App\Models\Activities;
use App\Models\ActivitiesAttachment;
use App\Mail\ActivityAssignedMail;
use App\Models\ActivityHistory;
use App\Models\ProjectComment;
use App\Models\ProjectCommentDocument;
use App\Models\ProjectAssignmentByRole;

class ProjectController extends Controller {

    public function __construct() {
        $this->middleware('permission:list-project|create-project|edit-project|delete-project', ['only' => ['index','show']]);
        $this->middleware('permission:create-project', ['only' => ['create','store']]);
        $this->middleware('permission:edit-project', ['only' => ['edit','update']]);
        $this->middleware('permission:delete-project', ['only' => ['destroy']]);
    }

    public function index(Request $request): View {
        $pageTitle = 'Projects';
        return view('projects.index', compact('pageTitle'));
    }

    public function getAjaxData(Request $request){
        if ($request->ajax()) {
            $data = Projects::select('id', 'project_title', 'assign_by', 'created_by', 'status', 'clientID', 'created_at', 'updated_at')
                ->withCount('activities');

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
            ->addColumn('updated_at', function ($row) {
                if(!empty($row->updated_at)){
                    return date('d-m-Y', strtotime($row->updated_at));
                }
            })
            ->addColumn('project_title', function ($row) {
                return $row->project_title ?: __('N/A');
            })
            ->addColumn('assign_by', function ($row) {
                if (!empty($row->assign_by)) {
                    $userID = explode(',', $row->assign_by);
                    $users = User::whereIn('id', $userID)->pluck('name')->toArray();
                    return implode(', ', $users);
                }
                return __('N/A');
            })
            ->addColumn('activities_count', function ($row) {
                return (int) ($row->activities_count ?? 0);
            })
            ->addColumn('created_by', function ($row) {
                if (!empty($row->created_by)) {
                    $creator = User::where('id', $row->created_by)->pluck('name')->first();
                    return $creator;
                }
                return __('N/A');
            })
            ->addColumn('clientID', function ($row) {
                if (!empty($row->clientID)) {
                    $client = Clients::where('id', $row->clientID)->pluck('commercialName')->first();
                    return $client;
                }
                return __('N/A');
            })
            ->addColumn('status', function ($row) {
                if ($row->status == 1) {
                    return '<span class="badge bg-success me-1">' . __('Active') . '</span>';
                } elseif ($row->status == 2) {
                    return '<span class="badge bg-danger me-1">' . __('In Active') . '</span>';
                } elseif ($row->status == 3){
                    return '<span class="badge bg-dark me-1">' . __('Finished') . '</span>';
                } else {
                    return __('N/A');
                }
            })
            ->addColumn('action', function ($row) {
                $dataId = $row->id;
                $editRoute = route("project.edit", $row->id);
                $viewRoute = route("project.view", $row->id);
                $historyRoute = route("project.history", $row->id);
                $commentRoute = route("project.comment", $row->id);
                return '<a href="'. $editRoute .'" class="btn btn-soft-primary btn-sm"><iconify-icon icon="solar:pen-new-square-linear" class="align-middle fs-18"></iconify-icon></a> <a href="'. $viewRoute .'" class="btn btn-soft-primary btn-sm"><iconify-icon icon="solar:eye-bold" class="align-middle fs-18"></iconify-icon></a> <a href="javascript:void(0)" class="btn btn-soft-danger btn-sm" onclick="deleteUser('.$dataId.')"><iconify-icon icon="solar:trash-bin-trash-bold" class="align-middle fs-18"></iconify-icon></a>';
            })
            ->rawColumns(['action', 'created_at', 'project_title', 'assign_by', 'created_by', 'status', 'clientID', 'updated_at'])
            ->make(true);
        }
        return response()->json(['error' => __('Unauthorized')], 403);
    }

    public function create(Request $request) {
        $pageTitle = 'Add Project';
        $roleIds = ProjectAssignmentByRole::pluck('role_id');
        $roleNames = \Spatie\Permission\Models\Role::whereIn('id', $roleIds)->pluck('name');
        $managers = User::role($roleNames)->get();
        $clients = Clients::all();
        $selectedClientId = $request->client_id;
        return view('projects.create', compact('managers', 'clients', 'pageTitle', 'selectedClientId'));
    }

    public function upload(Request $request) {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('projects_attachment', $filename, 'public');

            return response()->json(['file_name' => $filename]);
        }
        return response()->json(['error' => __('No file uploaded')], 400);
    }

    public function store(Request $request) {
        $userID = Auth::user()->id;
        $request->validate([
            'project_title' => 'required|string|max:255',
            'description' => 'required',
            'due_date' => 'required|date|after_or_equal:today',
        ]);

        $assign_byIds = is_array($request->assign_by) ? implode(',', $request->assign_by) : null;

        $project = new Projects();
        $project->project_title  = $request->project_title;
        $project->created_by     = $userID;
        $project->status       = $request->status;
        $project->description  = $request->description;
        $project->comments     = $request->comments;
        $project->due_date     = $request->due_date;
        $project->clientID     = $request->clientID;
        $project->assign_by    = $assign_byIds;
        if ($project->save()) {
            if ($request->has('attachment_file')) {
                foreach ($request->attachment_file as $file) {
                    ProjectAttachment::create([
                        'projectID' => $project->id,
                        'attachment_file'  => $file,
                    ]);
                }
            }

            // Create history entry for project creation
            ProjectHistory::create([
                'project_id' => $project->id,
                'user_id'     => Auth::id(),
                'action'      => 'created',
                'changes'     => null,
            ]);

            // Send Email to assigned users
            if (is_array($request->assign_by) && count($request->assign_by) > 0) {
                $assignedUsers = User::whereIn('id', $request->assign_by)->get();
                foreach ($assignedUsers as $user) {
                    try {
                        Mail::to($user->email)
                            ->send(new ProjectAssignedMail($project));
                        // Small delay to avoid rate limiting
                        usleep(500000); // 0.5 second delay
                    } catch (\Exception $e) {
                        // Log error but don't stop execution
                        \Log::error('Email failed for user: '
                            . $user->email . ' - ' . $e->getMessage());
                    }
                }
            }

            return redirect()->route('projects')->with('success', __('Project is added successfully.'));
        } else {
            return redirect()->back()->with('error', __('Something went wrong!'));
        }
    }

    public function edit(Request $request, $id) {
        $pageTitle = 'Edit Project';
        $project = Projects::findOrFail($id);

        $roleIds = ProjectAssignmentByRole::pluck('role_id');
        $roleNames = \Spatie\Permission\Models\Role::whereIn('id', $roleIds)->pluck('name');
        $managers = User::role($roleNames)->get();

        $attachments = ProjectAttachment::where('projectID', $id)->get();
        $clients = Clients::all();
        return view('projects.edit', compact('project', 'managers', 'attachments', 'clients', 'pageTitle'));
    }

    public function view(Request $request, $id) {
        $pageTitle = 'View Project';
        $project = Projects::findOrFail($id);
        $creator = User::where('id', $project->created_by)->pluck('name')->first();
        $userID = explode(',', $project->assign_by);
        $users = User::whereIn('id', $userID)->pluck('name')->toArray();
        $participants = implode(', ', $users);
        $attachments = ProjectAttachment::where('projectID', $id)->get();
        $managers = User::role('Manager')->get();

        if (!$project) {
            return redirect()->back()->with('error', __('Project not found.'));
        }
        
        // Get all project history
        $history = ProjectHistory::with('user')->where('project_id', $id)->orderBy('created_at', 'desc')->get();
        
        $projectID = $id;
        
        // Get all project comments
        $comments = ProjectComment::where('projectID', $id)->orderBy('created_at', 'desc')->get();
        $commentDocuments = ProjectCommentDocument::where('projectID', $id)->get();

        // INTEGRATED TIMELINE: Combine comments and history
        $integratedTimeline = $this->getIntegratedTimeline($id);

        $authUserId = Auth::id();
        $activities = Activities::where('projectID', $id)
        ->where(function ($query) use ($authUserId) {
            $query->where('created_by', $authUserId)->orWhereRaw("FIND_IN_SET(?, assign_by)", [$authUserId]);
        })
        ->get();

        $roleIds = ProjectAssignmentByRole::pluck('role_id');
        $roleNames = \Spatie\Permission\Models\Role::whereIn('id', $roleIds)->pluck('name');
        $managersnew = User::role($roleNames)->get();
        $clients = Clients::all();

        return view('projects.view', compact('project', 'creator', 'participants', 'attachments', 'managers', 'history', 'projectID', 'comments', 'commentDocuments', 'activities', 'pageTitle', 'integratedTimeline', 'clients', 'managersnew'));
    }

    // NEW METHOD: Get integrated timeline of comments and history
    private function getIntegratedTimeline($projectId) {
        // Get all comments
        $comments = ProjectComment::with('creator')
            ->where('projectID', $projectId)
            ->get()
            ->map(function($comment) {
                return [
                    'type' => 'comment',
                    'id' => $comment->id,
                    'user_id' => $comment->user_id,
                    'user_name' => $comment->creator->name ?? 'Unknown',
                    'content' => $comment->prj_comment,
                    'created_at' => $comment->created_at,
                    'attachments' => ProjectCommentDocument::where('projectCommentID', $comment->id)->get(),
                    'data' => $comment
                ];
            });

        // Get all project history (updates, creation)
        $history = ProjectHistory::with('user')
            ->where('project_id', $projectId)
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

        // Get all activity history (activity created, updated, etc.)
        $activityHistory = ActivityHistory::with('user', 'activity')
            ->whereHas('activity', function($query) use ($projectId) {
                $query->where('projectID', $projectId);
            })
            ->get()
            ->map(function($log) {
                $activityName = $log->activity->activity_name ?? 'Unknown Activity';
                return [
                    'type' => 'activity_history',
                    'id' => $log->id,
                    'user_id' => $log->user_id,
                    'user_name' => $log->user->name ?? 'System',
                    'action' => $log->action,
                    'activity_name' => $activityName,
                    'changes' => $log->changes,
                    'created_at' => $log->created_at,
                    'data' => $log
                ];
            });

        // Merge all timelines and sort by date (newest first)
        $integratedTimeline = $comments->concat($history)->concat($activityHistory)
            ->sortByDesc('created_at')
            ->values();

        return $integratedTimeline;
    }

    public function update(Request $request, $id) {

        $userID = Auth::user()->id;
        $request->validate([
            'project_title' => 'required|string|max:255',
            'description' => 'required',
            'due_date' => 'required|date|after_or_equal:today',
        ]);

        $assign_byIds = is_array($request->assign_by) ? implode(',', $request->assign_by) : null;
        $project = Projects::where('id', $id)->first();

        $oldValues = $project->getOriginal();

        $project->project_title  = $request->project_title;
        $project->created_by     = $userID;
        $project->status       = $request->status;
        $project->description  = $request->description;
        $project->comments     = $request->comments;
        $project->due_date     = $request->due_date;
        $project->clientID     = $request->clientID;
        $project->assign_by    = $assign_byIds;

        if ($project->isDirty()) {
            $newValues = $project->getDirty();
            
            if ($project->save()) {
                if ($request->has('attachment_file')) {
                    foreach ($request->attachment_file as $file) {
                        ProjectAttachment::create([
                            'projectID' => $project->id,
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
                        ProjectHistory::create([
                            'project_id' => $project->id,
                            'user_id'     => Auth::id(),
                            'action'      => 'updated',
                            'changes'     => $formattedChanges,
                        ]);
                    }
                }
                return redirect()->route('projects')->with('success', 'Project is updated successfully.');
            } else {
                return redirect()->back()->with('error', 'Something went wrong!');
            }
        } else {
            return redirect()->route('projects')->with('info', 'No changes were made to the project.');
        }
    }

    public function getDetails($id)
    {
        try {
            $project = Projects::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $project->id,
                    'project_title' => $project->project_title,
                    'description' => $project->description,
                    'comments' => $project->comments,
                    'status' => $project->status,
                    'due_date' => $project->due_date ? date('Y-m-d', strtotime($project->due_date)) : null,
                    'clientID' => $project->clientID,
                    'assign_by' => $project->assign_by, // comma separated ids
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                    'message' => __('Failed to load project details')
            ], 500);
        }
    }


    public function updateViaAjax(Request $request, $id)
    {
        $userID = Auth::user()->id;

        try {
            $request->validate([
                'project_title' => 'required|string|max:255',
                'description'   => 'required',
                'due_date'      => 'required|date|after_or_equal:today',
            ]);

            $assign_byIds = is_array($request->assign_by)
                ? implode(',', $request->assign_by)
                : $request->assign_by;

            $project = Projects::findOrFail($id);
            $oldValues = $project->getOriginal();

            $project->project_title = $request->project_title;
            $project->created_by    = $userID;
            $project->status        = $request->status;
            $project->description   = $request->description;
            $project->comments      = $request->comments;
            $project->due_date      = $request->due_date;
            $project->clientID      = $request->clientID;
            $project->assign_by     = $assign_byIds;

            if ($project->isDirty()) {
                $newValues = $project->getDirty();

                if ($project->save()) {

                    // attachments
                    if ($request->hasFile('attachment_file')) {
                        foreach ($request->file('attachment_file') as $file) {
                            $filename = time() . '_' . $file->getClientOriginalName();
                            $file->storeAs('projects_attachment', $filename, 'public');

                            ProjectAttachment::create([
                                'projectID' => $project->id,
                                'attachment_file' => $filename,
                            ]);
                        }
                    }

                    // history
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
                            ProjectHistory::create([
                                'project_id' => $project->id,
                                'user_id' => Auth::id(),
                                'action' => 'updated',
                                'changes' => $formattedChanges,
                            ]);
                        }
                    }

                    return response()->json([
                        'success' => true,
                        'message' => __('Project updated successfully.')
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => __('Failed to update project.')
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => __('No changes were made to the project.')
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('Validation failed'),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('An error occurred: :message', ['message' => $e->getMessage()])
            ], 500);
        }
    }

    public function destroy(Request $request, $id) {
        $project = Projects::find($id);
        if (!$project) {
            return response()->json(['error' => __('Record not found!')], 404);
        }
        $attachments = ProjectAttachment::where('projectID', $project->id)->get();
        foreach ($attachments as $attachment) {
            $filePath = 'projects_attachment/' . $attachment->attachment_file;
            if ($attachment->attachment_file && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            $attachment->delete();
        }
        $project->delete();
        return response()->json(['success' => __('Project deleted successfully!')]);
    }

    public function uploadRemove(Request $request, $id) {
        $attachment = ProjectAttachment::where('id', $id)->first();
        if(!empty($attachment)){
            if ($attachment->attachment_file && Storage::disk('public')->exists('projects_attachment/' . $attachment->attachment_file)) {
                Storage::disk('public')->delete('projects_attachment/' . $attachment->attachment_file);
            }
            $attachment->delete();
            return response()->json(['success' => __('Attachment is deleted successfully!')]);
        }else{
            return response()->json(['error' => __('Record not found!')], 404);
        }
    }

    public function projectCommentUpload(Request $request) {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('projects_comment_attachment', $filename, 'public');

            return response()->json(['file_name' => $filename]);
        }
        return response()->json(['error' => __('No file uploaded')], 400);
    }

    public function projectCommentStore(Request $request) {
        $userID = Auth::id();
        $request->validate([
            'prj_comment' => 'required|string'
        ]);

        $projectComment = new ProjectComment();
        $projectComment->projectID    = $request->projectID;
        $projectComment->prj_comment  = $request->prj_comment;
        $projectComment->user_id      = $userID;
        if ($projectComment->save()) {
            if ($request->hasFile('attachment_file')) {
                foreach ($request->file('attachment_file') as $file) {
                    $filename = time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->storeAs('projects_comment_attachment', $filename, 'public');
                    ProjectCommentDocument::create([
                        'projectID'         => $request->projectID,
                        'projectCommentID'  => $projectComment->id,
                        'attachment_file'   => $filename,
                    ]);
                }
            }
            return response()->json([
                'success' => true,
                'message' => __('Comment added successfully!'),
            ], 201);
        }
        return response()->json([
            'success' => false,
            'message' => __('Something went wrong!'),
        ], 500);
    }

    public function projectCommentDelete(Request $request, $id){
        $projectCmt = ProjectComment::find($id);
        if (!$projectCmt) {
            return response()->json(['error' => __('Record not found!')], 404);
        }
        $attachments = ProjectCommentDocument::where('projectCommentID', $projectCmt->id)->get();
        foreach ($attachments as $attachment) {
            $filePath = 'projects_comment_attachment/' . $attachment->attachment_file;
            if ($attachment->attachment_file && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            $attachment->delete();
        }
        $projectCmt->delete();
        return response()->json(['success' => __('Comment deleted successfully!')]);
    }

public function addActivity(Request $request, $id)
{
    $pageTitle = 'Add Project';

    $roleIds = ProjectAssignmentByRole::pluck('role_id');
    $roleNames = \Spatie\Permission\Models\Role::whereIn('id', $roleIds)->pluck('name');
    $managers = User::role($roleNames)->get();

    $prjID = $id;

    // ? FIX 1: GET CLIENTS LIST
    $clients = Clients::all();

    // ? FIX 2: GET PROJECTS
    $projects = Projects::where('status', 1)->get();

    // ? FIX 3: GET ONLY clientID VALUE (not full object)
    $clientID = Projects::where('id', $prjID)->value('clientID');

    return view('projects.add-activity', compact(
        'managers',
        'prjID',
        'clients',
        'projects',
        'pageTitle',
        'clientID'
    ));
}
    // Global Function For This Controller
    private function formatHistoryValue($field, $value) {
        if (empty($value)) {
            return __('N/A');
        }
        if ($field === 'assign_by') {
            if (is_string($value)) {
                $userIDs = explode(',', $value);
            } else {
                $userIDs = (array)$value;
            }
            $users = User::whereIn('id', $userIDs)->pluck('name')->toArray();
            return implode(', ', $users);
        }
        elseif ($field === 'status') {
            $statusMap = [
                1 => __('Active'),
                2 => __('In Active'),
                3 => __('Finished')
            ];
            return $statusMap[$value] ?? $value;
        }
        elseif ($field === 'due_date') {
            return date('d M Y', strtotime($value));
        }
        elseif ($field === 'created_by') {
            $user = User::find($value);
            return $user ? $user->name : $value;
        }
        elseif ($field === 'clientID') {
            $client = Clients::find($value);
            return $client ? $client->commercialName : $value;
        }
        return $value;
    }
}
