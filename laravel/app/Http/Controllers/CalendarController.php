<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\View\View;
use App\Models\Clients;
use App\Models\Activities;
use App\Models\Projects;
use App\Models\User;
use App\Models\Calendar;
use App\Models\CalendarAttachment;
use App\Models\CalendarComment;
use App\Models\CalendarCommentAttachment;
use Carbon\Carbon; 
use Auth;
use Session;
use DB; 
use Mail; 
use Str;
use DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class CalendarController extends Controller {

    public function __construct() {
        $this->middleware('permission:create-client|edit-client|delete-client', ['only' => ['index','show']]);
        $this->middleware('permission:create-client', ['only' => ['create','store']]);
        $this->middleware('permission:edit-client', ['only' => ['edit','update']]);
        $this->middleware('permission:delete-client', ['only' => ['destroy']]);
    }
    
    public function index(Request $request): View {
        $pageTitle = 'Calendars';
        $users = User::get();
        return view('calendar.index', compact('users', 'pageTitle'));
    }

    public function calendarList(Request $request) {
        $pageTitle = 'Calendars';
        return view('calendar.list', compact('pageTitle'));
    }

    public function getAjaxData(Request $request) {
        if ($request->ajax()) {

            $data = Calendar::query()
                ->leftJoin('users', 'users.id', '=', 'calendar.creator')
                ->select([
                    'calendar.id',
                    'calendar.schedule_title',
                    'calendar.type',
                    'calendar.start',
                    'calendar.location',
                    'calendar.creator',
                    'calendar.created_at',
                    DB::raw('users.name as creator_name'),
                ]);

            return DataTables::of($data)
                ->addIndexColumn()

                // Schedule Title
                ->editColumn('schedule_title', function ($row) {
                    return $row->schedule_title ?: __('N/A');
                })

                // Type Badge (sorting will still work because DB column is calendar.type)
                ->editColumn('type', function ($row) {
                    if ($row->type == 1) {
                        return '<span class="badge bg-primary me-1">' . __('Activity') . '</span>';
                    } elseif ($row->type == 2) {
                        return '<span class="badge bg-info me-1">' . __('Meeting') . '</span>';
                    } elseif ($row->type == 3) {
                        return '<span class="badge bg-success me-1">' . __('Hearing') . '</span>';
                    } elseif ($row->type == 4) {
                        return '<span class="badge bg-secondary me-1">' . __('Deadline') . '</span>';
                    }
                    return __('N/A');
                })

                // Start Date
                ->editColumn('start', function ($row) {
                    return $row->start ? date('d-m-Y H:i', strtotime($row->start)) : '';
                })

                // Location
                ->editColumn('location', function ($row) {
                    return $row->location ?: __('N/A');
                })

                // Creator Name
                ->editColumn('creator_name', function ($row) {
                    return $row->creator_name ?: __('N/A');
                })

                // Created Date
                ->editColumn('created_at', function ($row) {
                    return $row->created_at ? date('d-m-Y', strtotime($row->created_at)) : '';
                })

                // Action Buttons
                ->addColumn('action', function ($row) {
                    $viewRoute = route("calendar.view", $row->id);
                    $commentRoute = route("calendar.comment", $row->id);

                    return '
                        <a href="' . $viewRoute . '" class="btn btn-soft-primary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="' . __('View Schedule') . '">
                            <iconify-icon icon="solar:eye-bold" class="align-middle fs-18"></iconify-icon>
                        </a>
                        <a href="' . $commentRoute . '" class="btn btn-soft-primary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="' . __('Add Comment') . '" target="_blank">
                            <iconify-icon icon="solar:bookmark-circle-bold" class="align-middle fs-18"></iconify-icon>
                        </a>
                    ';
                })

                // IMPORTANT: Fix ordering for formatted date columns
                ->orderColumn('created_at', function ($query, $order) {
                    $query->orderBy('calendar.created_at', $order);
                })
                ->orderColumn('start', function ($query, $order) {
                    $query->orderBy('calendar.start', $order);
                })
                ->orderColumn('creator_name', function ($query, $order) {
                    $query->orderBy('users.name', $order);
                })

                ->rawColumns(['type', 'action'])
                ->make(true);
        }

        return response()->json(['error' => __('Unauthorized')], 403);
    }


    public function getEvents() {
        $events = Calendar::all()->map(function ($event) {
            $category = $this->mapCategory($event->type);
            $class    = $this->getClassByType($category);
            $guests = [];

            if (!empty($event->guests)) {
                $decoded = json_decode($event->guests, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $guests = is_array($decoded) ? $decoded : [$decoded];
                } else {
                    $guests = explode(',', $event->guests);
                }
            }
            return [
                'id'          => $event->id,
                'title'       => $event->schedule_title,
                'start'       => $event->start,
                'end'         => $event->end,
                'location'    => $event->location,
                'description' => $event->description,
                'guests'      => array_map('intval', $guests),
                'className'   => $class,
                'category'    => $event->type,
            ];
        });
        return response()->json($events);
    }

    public function calendarView(Request $request, $id) {
        $pageTitle = 'View Calendar';
        $event = Calendar::where('id', $id)->first();
        $creator = User::where('id', $event->creator)->pluck('name')->first();

        $userIds = explode(',', $event->guests);
        $guestNames = User::whereIn('id', $userIds)->pluck('name')->implode(', ');

        $attachments = CalendarAttachment::where('calendarID', $id)->get();
        return view('calendar.view', compact('event', 'creator', 'guestNames', 'attachments', 'pageTitle'));
    }

    public function store(Request $request){
        $data = $request->all();

        $validated = $request->validate([
            'title' => 'required|string',
            'category' => 'required',
            'due_date' => 'required|date',
            'location' => 'nullable|string',
            'description' => 'nullable|string',
            'guests' => 'nullable|array',
        ]);

        $userID = Auth::user()->id;
        $assign_byIds = implode(',', $data['guests']);

        $calendar = new Calendar();
        $calendar->schedule_title = $data['title'];
        $calendar->type = $data['category'];
        $calendar->start = $data['due_date'];
        $calendar->end = null;
        $calendar->location = $data['location'] ?? null;
        $calendar->description = $data['description'] ?? null;
        $calendar->creator = $userID;
        $calendar->guests = $assign_byIds ?? null;
        if($calendar->save()){
            return response()->json(['status' => 'success','event' => $calendar]);
        } else {
            return response()->json(['status' => 'error','event' => $calendar]);
        }
    }

    public function update(Request $request, $schedule) {
        $data = $request->all();
        $validated = $request->validate([
            'title' => 'required|string',
            'category' => 'required',
            'due_date' => 'required|date',
            'location' => 'nullable|string',
            'description' => 'nullable|string',
            'guests' => 'nullable|array',
        ]);

        $userID = Auth::user()->id;
        $assign_byIds = implode(',', $data['guests']);

        $calendar = Calendar::findOrFail($schedule);
        $calendar->schedule_title = $data['title'];
        $calendar->type = $data['category'];
        $calendar->start = $data['due_date'];
        $calendar->end = null;
        $calendar->location = $data['location'] ?? null;
        $calendar->description = $data['description'] ?? null;
        $calendar->guests = $assign_byIds ?? null;
        if($calendar->save()){
            return response()->json(['status' => 'success','event' => $calendar]);
        } else {
            return response()->json(['status' => 'error','event' => $calendar]);
        }
    }

    public function destroy(Request $request, $schedule) {
        $calendar = Calendar::where('id', $schedule)->first();
        if($calendar->delete()){
            return response()->json(['status' => 'success']);
        } else {
            return response()->json(['status' => 'error']);
        }
    }

    private function getClassByType($type) {
        return match($type) {
            'Activity' => 'bg-primary',
            'Meeting' => 'bg-info',
            'Hearing' => 'bg-success',
            'Deadline' => 'bg-warning',
            default => 'bg-secondary'
        };
    }

    private function mapCategory($value) {
        return match($value) {
            '1' => 'Activity',
            '2' => 'Meeting',
            '3' => 'Hearing',
            '4' => 'Deadline',
            default => 'Activity'
        };
    }

    public function upload(Request $request) {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('calendar_attachment', $filename, 'public');
            return response()->json(['file_name' => $filename]);
        }
        return response()->json(['error' => 'No file uploaded'], 400);
    }

    public function uploadStore(Request $request) {

        if (isset($request->calendarID)) {
            if ($request->has('attachment_file')) {
                foreach ($request->attachment_file as $file) {
                    CalendarAttachment::create([
                        'calendarID' => $request->calendarID,
                        'attachment_file'  => $file,
                    ]);
                }
            }
            return response()->json([
                'success' => true,
                'message' => 'Attachment has been uploaded successfully!',
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong!',
            ], 500);
        }
    }

    public function uploadRemove(Request $request, $id) {
        $attachment = CalendarAttachment::where('id', $id)->first();
        if(!empty($attachment)){
            if ($attachment->attachment_file && Storage::disk('public')->exists('calendar_attachment/' . $attachment->attachment_file)) {
                Storage::disk('public')->delete('calendar_attachment/' . $attachment->attachment_file);
            }
            $attachment->delete();
            return response()->json(['success' => 'Attachment is deleted successfully!']);
        }else{
            return response()->json(['error' => 'Record not found!'], 404);
        }
    }

    public function calendarComment(Request $request, $id){
        $pageTitle = 'Add Comment';
        $calendarID = $id;
        $comments = CalendarComment::where('calendarID', $id)->get();
        $commentDocuments = CalendarCommentAttachment::where('calendarID', $id)->get();
        return view('calendar.comment', compact('calendarID', 'comments', 'commentDocuments', 'pageTitle'));
    }

    public function calendarCommentUpload(Request $request) {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('calendar_comment_attachment', $filename, 'public');

            return response()->json(['file_name' => $filename]);
        }
        return response()->json(['error' => 'No file uploaded'], 400);
    }

    public function calendarCommentStore(Request $request) {
        $userID = Auth::user()->id;
        $request->validate([
            'act_comment'      => 'required|string',
        ]);

        $calendarCmt = new CalendarComment();
        $calendarCmt->calendarID  = $request->calendarID;
        $calendarCmt->act_comment  = $request->act_comment;
        $calendarCmt->user_id     = $userID;

        if ($calendarCmt->save()) {
            if ($request->hasFile('attachment_file')) {
                foreach ($request->file('attachment_file') as $file) {
                    $filename = time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('calendar_comment_attachment', $filename, 'public');

                    CalendarCommentAttachment::create([
                        'calendarID'          => $request->calendarID,
                        'calendarCommentID'   => $calendarCmt->id,
                        'attachment_file'     => $filename,
                    ]);
                }
            }
            return response()->json([
                'success' => true,
                'message' => 'Calendar added successfully!',
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong!',
            ], 500);
        }
    }

    public function calendarCommentDelete(Request $request, $id){
        $calendarCmt = CalendarComment::find($id);
        if (!$calendarCmt) {
            return response()->json(['error' => 'Record not found!'], 404);
        }
        $attachments = CalendarCommentAttachment::where('calendarCommentID', $calendarCmt->id)->get();
        foreach ($attachments as $attachment) {
            $filePath = 'calendar_comment_attachment/' . $attachment->attachment_file;
            if ($attachment->attachment_file && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            $attachment->delete();
        }
        $calendarCmt->delete();
        return response()->json(['success' => 'Comment deleted successfully!']);
    }

}
