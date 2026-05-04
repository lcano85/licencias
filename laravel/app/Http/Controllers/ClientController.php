<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\View\View;
use App\Models\Clients;
use App\Models\Activities;
use App\Models\Projects;
use Carbon\Carbon; 
use Auth;
use Session;
use DB; 
use Mail; 
use Str;
use DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use App\Models\ClientCategory;
use App\Models\ClientSubCategory;
use App\Models\UseTypes;
use App\Models\ClientUpgrade;
use App\Models\BasicInfoStatus;

class ClientController extends Controller {

    public function __construct() {
        $this->middleware('permission:create-client|edit-client|delete-client', ['only' => ['index','show']]);
        $this->middleware('permission:create-client', ['only' => ['create','store']]);
        $this->middleware('permission:edit-client', ['only' => ['edit','update']]);
        $this->middleware('permission:delete-client', ['only' => ['destroy']]);

        $this->middleware('permission:list-basic-info', ['only' => ['index','show']]);
        $this->middleware('permission:create-basic-info', ['only' => ['create','store']]);
        $this->middleware('permission:edit-basic-info', ['only' => ['edit','update']]);
        $this->middleware('permission:delete-basic-info', ['only' => ['destroy']]);
    }

    public function index(Request $request): View {
        $pageTitle = 'Clients';
        $categories = ClientCategory::get();
        $useTypes = UseTypes::get();
        $biStatus = BasicInfoStatus::get();
        return view('clients.index', compact('categories', 'useTypes', 'pageTitle', 'biStatus'));
    }

    public function getAjaxData(Request $request){
        if ($request->ajax()) {
            $data = Clients::select('id', 'commercialName', 'legalName', 'categoryID', 'subcategoryID', 'created_at', 'client_acc_ID', 'useTypes', 'client_status')->with(['mainCategory', 'subCategory', 'useTypesRecord', 'basicInfoStatus'])->orderBy('id', 'desc');

            if ($request->categoryID) {
                $data->where('categoryID', $request->categoryID);
            }
            if ($request->useTypes) {
                $data->where('useTypes', $request->useTypes);
            }
        
            return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('created_at', function ($row) {
                if(!empty($row->created_at)){
                    return date('d-m-Y', strtotime($row->created_at));
                }
            })
            ->addColumn('commercialName', function ($row) {
                return $row->commercialName ?: 'N/A';
            })
            ->addColumn('categoryID', function ($row) {
                return $row->mainCategory ? $row->mainCategory->category_name : 'N/A';
            })
            ->addColumn('subcategoryID', function ($row) {
                return $row->subCategory ? $row->subCategory->subcategory_name : 'N/A';
            })
            ->addColumn('useTypes', function ($row) {
                return $row->useTypesRecord ? $row->useTypesRecord->use_types_name : 'N/A';
            })
            ->addColumn('legalName', function ($row) {
                return $row->legalName ?: 'N/A';
            })
            ->addColumn('client_status', function ($row) {
                return $row->basicInfoStatus ? $row->basicInfoStatus->status_name : 'N/A';
            })
            ->addColumn('action', function ($row) {
                $dataId = $row->id;
                $editRoute = route("client.edit", $row->id);
                $viewRoute = route("client.view", $row->id);
                return '<a href="'. $editRoute .'" class="btn btn-soft-primary btn-sm"><iconify-icon icon="solar:pen-new-square-linear" class="align-middle fs-18"></iconify-icon></a> <a href="'. $viewRoute .'" class="btn btn-soft-primary btn-sm"><iconify-icon icon="solar:eye-bold" class="align-middle fs-18"></iconify-icon></a> <a href="javascript:void(0)" class="btn btn-soft-danger btn-sm" onclick="deleteUser('.$dataId.')"><iconify-icon icon="solar:trash-bin-trash-bold" class="align-middle fs-18"></iconify-icon></a>';
            })
            ->rawColumns(['action', 'created_at', 'commercialName', 'legalName', 'categoryID', 'subcategoryID', 'useTypes', 'client_status'])
            ->make(true);
        }
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    public function create() {
        $pageTitle = 'Add Client';
        $categories = ClientCategory::get();
        $useTypes = UseTypes::get();
        $biStatus = BasicInfoStatus::get();
        return view('clients.create', compact('categories', 'useTypes', 'pageTitle', 'biStatus'));
    }

    public function getSubcategories(Request $request, $categoryId){
        $subcategories = ClientSubCategory::where('categoryID', $categoryId)->where('subcategory_status', 1)->get();
        return response()->json($subcategories);
    }

    public function checkDuplicate(Request $request) {
        $legalName = trim($request->legalName ?? '');
        $nit = trim($request->nit ?? '');
        $ignoreId = $request->ignoreId;

        $query = Clients::query();
        if (!empty($ignoreId)) {
            $query->where('id', '!=', $ignoreId);
        }
        $legalNameExists = false;
        $nitExists = false;
        if ($legalName !== '') {
            $legalNameExists = (clone $query)
                ->whereRaw('LOWER(legalName) = ?', [strtolower($legalName)])
                ->exists();
        }
        if ($nit !== '') {
            $nitExists = (clone $query)
                ->where('nit', $nit)
                ->exists();
        }
        return response()->json([
            'legalNameExists' => $legalNameExists,
            'nitExists' => $nitExists,
        ]);
    }

    public function store(Request $request) {
        // dd($request->all());
        $request->validate([
            'commercialName' => 'required',
            'categoryID' => 'required',
            'useTypes' => 'required',
        ]);

        $data = $request->all();
        $accountID = '#MT-' . mt_rand(100000000, 999999999);

        $client = new Clients();
        $client->client_acc_ID = $accountID;
        $client->commercialName = $data['commercialName'] ?? null;
        $client->legalName = $data['legalName'] ?? null;
        $client->nit = $data['nit'] ?? null;
        $client->categoryID = $data['categoryID'] ?? null;
        $client->subcategoryID = $data['subcategoryID'] ?? null;
        $client->useTypes = $data['useTypes'] ?? null;
        $client->website_link = $data['website_link'] ?? null;
        $client->client_status = $data['client_status'] ?? null;
        $client->annotations = $data['annotations'] ?? null;
        $client->judicialNotificationAddress = $data['judicialNotificationAddress'] ?? null;
        $client->companySize = $data['companySize'] ?? null;
        $client->companyType = $data['companyType'] ?? null;
        $client->annualIncome = $data['annualIncome'] ?? null;
        $client->judicialNotification = json_encode($data['judicialNotification'] ?? []);
        $client->licenseInformation = $data['licenseInformation'] ?? null;
        $client->happenClient = $data['happenClient'] ?? null;

        $personalContacts = [];
        if (!empty($data['personalContactData']) && is_array($data['personalContactData'])) {
            foreach ($data['personalContactData'] as $contact) {
                $entry = [
                    'fullName' => $contact['fullName'] ?? null,
                    'designation' => $contact['designation'] ?? null,
                    'contactNumber' => $contact['contactNumber'] ?? null,
                    'companyDate' => $contact['companyDate'] ?? null,
                    'contactEmail' => [],
                ];

                // Handle multiple emails (semicolon-separated)
                if (!empty($contact['contactEmail'])) {
                    $emails = array_filter(array_map('trim', explode(';', $contact['contactEmail'])));
                    foreach ($emails as $email) {
                        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $entry['contactEmail'][] = $email;
                        }
                    }
                }

                $personalContacts[] = $entry;
            }
        }
        $client->personalContactData = json_encode($personalContacts);


        $folderID = str_replace('#MT-', '', $accountID);
        $clientFolder = public_path("uploads/clients/{$folderID}");
        $licenseFolder = $clientFolder . '/licenses';
        $documentFolder = $clientFolder . '/documents';

        if (!file_exists($licenseFolder)) {
            mkdir($licenseFolder, 0755, true);
        }
        if (!file_exists($documentFolder)) {
            mkdir($documentFolder, 0755, true);
        }
        
        // License attachment
        if ($request->hasFile('licenseAttachement')) {
            $file = $request->file('licenseAttachement');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move($licenseFolder, $filename);
            $client->licenseAttachement = $filename;
        }

        // Accounting info
        $client->bankName = $data['bankName'] ?? null;
        $client->bankAccountNumber = $data['bankAccountNumber'] ?? null;
        $client->bankCode = $data['bankCode'] ?? null;

        // Document repository
        $documentRepository = [];
        if (isset($data['documentName']) && isset($data['documentFile'])) {
            foreach ($data['documentName'] as $index => $doc) {
                $document = [];
                $document['name'] = $doc['name'] ?? null;

                if (isset($data['documentFile'][$index]['file'])) {
                    $file = $request->file("documentFile.$index.file");
                    if ($file) {
                        $filename = time() . '_' . $file->getClientOriginalName();
                        $file->move($documentFolder, $filename);
                        $document['file'] = $filename;
                    }
                }

                $documentRepository[] = $document;
            }
        }
        $client->documentRepository = json_encode($documentRepository);

        if ($client->save()) {
            return redirect()->route('clients')->with('success', 'New client is added successfully.');
        } else {
            return redirect()->back()->with('error', 'Something went wrong!');
        }
    }

    public function edit(Request $request, $id) {
        $pageTitle = 'Edit Client';
        $client = Clients::where('id', $id)->first();
        $categories = ClientCategory::get();
        $useTypes = UseTypes::get();
        $subcategories = ClientSubCategory::where('categoryID', $client->categoryID)->get();
        $personalContacts = json_decode($client->personalContactData, true) ?? [];
        $biStatus = BasicInfoStatus::get();
        return view('clients.edit', compact('client', 'categories', 'useTypes', 'subcategories', 'pageTitle', 'personalContacts', 'biStatus'));
    }

    public function update(Request $request, $id) {
        $request->validate([
            'commercialName' => 'required',
            'categoryID' => 'required',
            'useTypes' => 'required',
        ]);
        // dd("123456789");

        $data = $request->all();

        $client = Clients::where('id', $id)->first();
        $client->commercialName = $data['commercialName'] ?? null;
        $client->legalName = $data['legalName'] ?? null;
        $client->nit = $data['nit'] ?? null;
        $client->categoryID = $data['categoryID'] ?? null;
        $client->subcategoryID = $data['subcategoryID'] ?? null;
        $client->useTypes = $data['useTypes'] ?? null;
        $client->website_link = $data['website_link'] ?? null;
        $client->client_status = $data['client_status'] ?? null;
        $client->annotations = $data['annotations'] ?? null;
        $client->judicialNotificationAddress = $data['judicialNotificationAddress'] ?? null;
        $client->companySize = $data['companySize'] ?? null;
        $client->companyType = $data['companyType'] ?? null;
        $client->annualIncome = $data['annualIncome'] ?? null;
        $client->judicialNotification = json_encode($data['judicialNotification'] ?? []);
        $client->licenseInformation = $data['licenseInformation'] ?? null;
        $client->happenClient = $data['happenClient'] ?? null;

        $personalContacts = [];
        if (!empty($data['personalContactData']) && is_array($data['personalContactData'])) {
            foreach ($data['personalContactData'] as $contact) {
                $entry = [
                    'fullName' => $contact['fullName'] ?? null,
                    'designation' => $contact['designation'] ?? null,
                    'contactNumber' => $contact['contactNumber'] ?? null,
                    'companyDate' => $contact['companyDate'] ?? null,
                    'contactEmail' => [],
                ];

                // Handle multiple emails (semicolon-separated)
                if (!empty($contact['contactEmail'])) {
                    $emails = array_filter(array_map('trim', explode(';', $contact['contactEmail'])));
                    foreach ($emails as $email) {
                        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $entry['contactEmail'][] = $email;
                        }
                    }
                }

                $personalContacts[] = $entry;
            }
        }
        $client->personalContactData = json_encode($personalContacts);

        $folderID = str_replace('#MT-', '', $client->client_acc_ID);
        $clientFolder = public_path("uploads/clients/{$folderID}");
        $licenseFolder = $clientFolder . '/licenses';
        $documentFolder = $clientFolder . '/documents';

        if (!file_exists($licenseFolder)) {
            mkdir($licenseFolder, 0755, true);
        }
        if (!file_exists($documentFolder)) {
            mkdir($documentFolder, 0755, true);
        }
        
        // License attachment
        if ($request->hasFile('licenseAttachement')) {
            $file = $request->file('licenseAttachement');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move($licenseFolder, $filename);
            $client->licenseAttachement = $filename;
        }

        // Accounting info
        $client->bankName = $data['bankName'] ?? null;
        $client->bankAccountNumber = $data['bankAccountNumber'] ?? null;
        $client->bankCode = $data['bankCode'] ?? null;

        // Document repository
        $documentRepository = [];
        if (isset($data['documentName']) && isset($data['documentFile'])) {
            foreach ($data['documentName'] as $index => $doc) {
                $document = [];
                $document['name'] = $doc['name'] ?? null;

                if (isset($data['documentFile'][$index]['file'])) {
                    $file = $request->file("documentFile.$index.file");
                    if ($file) {
                        $filename = time() . '_' . $file->getClientOriginalName();
                        $file->move($documentFolder, $filename);
                        $document['file'] = $filename;
                    }
                }

                $documentRepository[] = $document;
            }
        }
        $client->documentRepository = json_encode($documentRepository);

        if ($client->save()) {
            return redirect()->back()->with('success', 'Client is updated successfully!');
        } else {
            return redirect()->back()->with('error', 'Something went wrong!');
        }
    }

    public function addUpgradeComment(Request $request, $id){
        $request->validate([
            'comment' => 'required|string|max:2000',
            'type' => 'sometimes|string|in:comment,upgrade,note'
        ]);

        try {
            Clients::findOrFail($id);

            $upgrade = ClientUpgrade::create([
                'client_id' => $id,
                'user_id' => Auth::id(),
                'comment' => $request->comment,
                'type' => $request->type ?? 'comment'
            ]);

            $upgrade->load('user');

            return response()->json([
                'success' => true,
                'message' => 'Comment added successfully!',
                'upgrade' => $upgrade
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to add comment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getUpgradeComments($id) {
        try {
            $upgrades = ClientUpgrade::where('client_id', $id)
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'upgrades' => $upgrades
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch comments: ' . $e->getMessage()
            ], 500);
        }
    }

    public function view(Request $request, $id) {
        $pageTitle = 'View Client';
        $client = Clients::where('id', $id)->with(['mainCategory', 'subCategory', 'useTypesRecord', 'basicInfoStatus'])->first();
        $activities = Activities::where('clientID', $id)->with('creator')->get();
        $projects = Projects::where('clientID', $id)
            ->with('creator')
            ->withCount([
                'activities as activities_count' => function ($q) {
                    $q->whereNotIn('status', [1, 3]);
                    if (Schema::hasColumn('activities', 'deleted_at')) {
                        $q->whereNull('deleted_at');
                    }
                }
            ])
            ->get();
        $personalContacts = json_decode($client->personalContactData, true) ?? [];
        
        // Load upgrades with user data
        $upgrades = ClientUpgrade::where('client_id', $id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('clients.view', compact('client', 'activities', 'projects', 'pageTitle', 'personalContacts', 'upgrades'));
    }

    public function destroy(Request $request, $id) {
        $client = Clients::where('id', $id)->first();
        if(!empty($client)){
            $client->delete();
            return response()->json(['success' => 'Client is deleted successfully!']);
        }else{
            return response()->json(['error' => 'Record not found!'], 404);
        }
    }

    public function indexBasicInfo(Request $request): View {
        $pageTitle = 'Basic Info Status';
        return view('clients.indexBasicInfo', compact('pageTitle'));
    }

    public function getAjaxDataBasicInfo(Request $request){
        if ($request->ajax()) {
            $data = BasicInfoStatus::select('id', 'status_name', 'status', 'created_at');
            return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('created_at', function ($row) {
                if(!empty($row->created_at)){
                    return date('d-m-Y', strtotime($row->created_at));
                }
            })
            ->addColumn('status_name', function ($row) {
                return $row->status_name ?: __('N/A');
            })
            ->addColumn('action', function ($row) {
                $dataId = $row->id;
                return '
                    <a href="javascript:void(0)" class="btn btn-soft-primary btn-sm me-1 editBtn" data-id="'.$dataId.'">
                        <iconify-icon icon="solar:pen-bold" class="align-middle fs-18"></iconify-icon>
                    </a>
                    <a href="javascript:void(0)" class="btn btn-soft-danger btn-sm" onclick="deleteStatus('.$dataId.')"><iconify-icon icon="solar:trash-bin-trash-bold" class="align-middle fs-18"></iconify-icon>
                    </a>
                ';
            })
            ->rawColumns(['action', 'created_at', 'status_name'])
            ->make(true);
        }
        return response()->json(['error' => __('Unauthorized')], 403);
    }

    public function storeBasicInfo(Request $request) {
        $request->validate([
            'status_name' => 'required',
        ]);
        $biStatus = new BasicInfoStatus();
        $biStatus->status_name   = $request->status_name;
        $biStatus->status = 1;
        if ($biStatus->save()) {
            return response()->json(['success' => true, 'message' => __('Basic Info Status added successfully!')]);
        } else {
            return response()->json(['success' => false, 'message' => __('Something went wrong!')]);
        }
    }

    public function editBasicInfo($id) {
        $cat = BasicInfoStatus::select('id','status_name','status')->find($id);
        if(!$cat){
            return response()->json(['error' => __('Record not found!')], 404);
        }
        return response()->json(['success' => true, 'data' => $cat]);
    }

    public function updateBasicInfo(Request $request, $id) {
        $request->validate([
            'status_name' => 'required',
        ]);
        $biStatus = BasicInfoStatus::find($id);
        if(!$biStatus){
            return response()->json(['success' => false, 'message' => __('Record not found!')], 404);
        }
        $biStatus->status_name = $request->status_name;
        $biStatus->status = 1;
        if ($biStatus->save()) {
            return response()->json(['success' => true, 'message' => __('Basic Info Status updated successfully!')]);
        }
        return response()->json(['success' => false, 'message' => __('Something went wrong!')], 500);
    }

    public function destroyBasicInfo(Request $request, $id) {
        $biStatus = BasicInfoStatus::where('id', $id)->first();
        if(!empty($biStatus)){
            $biStatus->delete();
            return response()->json(['success' => __('Basic Info Status deleted successfully!')]);
        }else{
            return response()->json(['error' => __('Record not found!')], 404);
        }
    }

}
