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
use Illuminate\Support\Facades\Storage;
use App\Models\ClientCategory;
use App\Models\ClientSubCategory;
use App\Models\UseTypes;

class CategoryController extends Controller {

    public function __construct() {
        $this->middleware('permission:create-category|list-category|delete-category', ['only' => ['index', 'getAjaxData']]);
        $this->middleware('permission:create-category', ['only' => ['store']]);
        $this->middleware('permission:delete-category', ['only' => ['destroy']]);

        $this->middleware('permission:create-subcategory|list-subcategory|delete-subcategory', ['only' => ['indexSubCategory', 'getAjaxDataSubCategory']]);
        $this->middleware('permission:create-subcategory', ['only' => ['storeSubCategory']]);
        $this->middleware('permission:delete-subcategory', ['only' => ['destroySubCategory']]);

        $this->middleware('permission:create-usetypes|list-usetypes|delete-usetypes', ['only' => ['indexUseTypes', 'getAjaxDataUseTypes']]);
        $this->middleware('permission:create-usetypes', ['only' => ['storeUseTypes']]);
        $this->middleware('permission:delete-usetypes', ['only' => ['destroyUseTypes']]);
    }

    public function index(Request $request): View {
        $pageTitle = 'Categories';
        return view('client-category.index', compact('pageTitle'));
    }

    public function getAjaxData(Request $request){
        if ($request->ajax()) {
            $data = ClientCategory::select('id', 'category_name', 'category_status', 'created_at');
            return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('created_at', function ($row) {
                if(!empty($row->created_at)){
                    return date('d-m-Y', strtotime($row->created_at));
                }
            })
            ->addColumn('category_name', function ($row) {
                return $row->category_name ?: __('N/A');
            })
            ->addColumn('category_status', function ($row) {
                if ($row->category_status == 1) {
                    return '<span class="badge bg-success me-1">' . __('Active') . '</span>';
                } elseif ($row->category_status == 2) {
                    return '<span class="badge bg-danger me-1">' . __('In Active') . '</span>';
                } else {
                    return __('N/A');
                }
            })
            ->addColumn('action', function ($row) {
                $dataId = $row->id;
                return '
                    <a href="javascript:void(0)" class="btn btn-soft-primary btn-sm me-1 editBtn" data-id="'.$dataId.'">
                        <iconify-icon icon="solar:pen-bold" class="align-middle fs-18"></iconify-icon>
                    </a>
                    <a href="javascript:void(0)" class="btn btn-soft-danger btn-sm" onclick="deleteUser('.$dataId.')"><iconify-icon icon="solar:trash-bin-trash-bold" class="align-middle fs-18"></iconify-icon>
                    </a>
                ';
            })
            ->rawColumns(['action', 'created_at', 'category_name', 'category_status'])
            ->make(true);
        }
        return response()->json(['error' => __('Unauthorized')], 403);
    }

    public function store(Request $request) {
        $request->validate([
            'category_name' => 'required',
        ]);
        $category = new ClientCategory();
        $category->category_name   = $request->category_name;
        $category->category_status = 1;
        if ($category->save()) {
            return response()->json(['success' => true, 'message' => __('Category added successfully!')]);
        } else {
            return response()->json(['success' => false, 'message' => __('Something went wrong!')]);
        }
    }

    public function edit($id) {
        $cat = ClientCategory::select('id','category_name','category_status')->find($id);
        if(!$cat){
            return response()->json(['error' => __('Record not found!')], 404);
        }
        return response()->json(['success' => true, 'data' => $cat]);
    }

    public function update(Request $request, $id) {
        $request->validate([
            'category_name' => 'required|string|max:255',
        ]);
        $cat = ClientCategory::find($id);
        if(!$cat){
            return response()->json(['success' => false, 'message' => __('Record not found!')], 404);
        }
        $cat->category_name = $request->category_name;
        $cat->category_status = 1;
        if ($cat->save()) {
            return response()->json(['success' => true, 'message' => __('Category updated successfully!')]);
        }
        return response()->json(['success' => false, 'message' => __('Something went wrong!')], 500);
    }

    public function destroy(Request $request, $id) {
        $category = ClientCategory::where('id', $id)->first();
        if(!empty($category)){
            $category->delete();
            return response()->json(['success' => __('Category deleted successfully!')]);
        }else{
            return response()->json(['error' => __('Record not found!')], 404);
        }
    }

    public function indexSubCategory(Request $request): View {
        $pageTitle = 'Sub Categories';
        $categories = ClientCategory::get();
        return view('client-category.index-sub-category', compact('categories', 'pageTitle'));
    }

    public function getAjaxDataSubCategory(Request $request){
        if ($request->ajax()) {
            $data = ClientSubCategory::with('mainCategory')->select('id', 'categoryID', 'subcategory_name', 'subcategory_status', 'created_at');
            return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('created_at', function ($row) {
                if(!empty($row->created_at)){
                    return date('d-m-Y', strtotime($row->created_at));
                }
            })
            ->addColumn('categoryID', function ($row) {
                return $row->mainCategory ? $row->mainCategory->category_name : __('N/A');
            })
            ->addColumn('subcategory_name', function ($row) {
                return $row->subcategory_name ?: __('N/A');
            })
            ->addColumn('subcategory_status', function ($row) {
                if ($row->subcategory_status == 1) {
                    return '<span class="badge bg-success me-1">' . __('Active') . '</span>';
                } elseif ($row->subcategory_status == 2) {
                    return '<span class="badge bg-danger me-1">' . __('In Active') . '</span>';
                } else {
                    return __('N/A');
                }
            })
            ->addColumn('action', function ($row) {
                $dataId = $row->id;
                return '
                    <a href="javascript:void(0)" class="btn btn-soft-primary btn-sm me-1 editBtn" data-id="'.$dataId.'">
                        <iconify-icon icon="solar:pen-bold" class="align-middle fs-18"></iconify-icon>
                    </a>
                    <a href="javascript:void(0)" class="btn btn-soft-danger btn-sm" onclick="deleteUser('.$dataId.')"><iconify-icon icon="solar:trash-bin-trash-bold" class="align-middle fs-18"></iconify-icon>
                    </a>';
            })
            ->rawColumns(['action', 'created_at', 'subcategory_name', 'subcategory_status', 'categoryID'])
            ->make(true);
        }
        return response()->json(['error' => __('Unauthorized')], 403);
    }

    public function storeSubCategory(Request $request) {
        $request->validate([
            'subcategory_name' => 'required',
        ]);
        $subCategory = new ClientSubCategory();
        $subCategory->categoryID   = $request->categoryID;
        $subCategory->subcategory_name = $request->subcategory_name;
        $subCategory->subcategory_status = 1;
        if ($subCategory->save()) {
            return response()->json(['success' => true, 'message' => __('Sub Category added successfully!')]);
        } else {
            return response()->json(['success' => false, 'message' => __('Something went wrong!')]);
        }
    }

    public function editSubCategory($id) {
        $sc = ClientSubCategory::with('mainCategory')
            ->select('id','categoryID','subcategory_name','subcategory_status')
            ->find($id);
        if (!$sc) {
            return response()->json(['error' => __('Record not found!')], 404);
        }
        return response()->json(['success' => true, 'data' => $sc]);
    }

    public function updateSubCategory(Request $request, $id) {
        $request->validate([
            'categoryID'        => 'required',
            'subcategory_name'  => 'required',
        ]);
        $sc = ClientSubCategory::find($id);
        if (!$sc) {
            return response()->json(['success' => false, 'message' => __('Record not found!')], 404);
        }
        $sc->categoryID = $request->categoryID;
        $sc->subcategory_name = $request->subcategory_name;
        $sc->subcategory_status = 1;
        if ($sc->save()) {
            return response()->json(['success' => true, 'message' => __('Sub Category updated successfully!')]);
        }
        return response()->json(['success' => false, 'message' => __('Something went wrong!')], 500);
    }

    public function destroySubCategory(Request $request, $id) {
        $subCategory = ClientSubCategory::where('id', $id)->first();
        if(!empty($subCategory)){
            $subCategory->delete();
            return response()->json(['success' => __('Sub Category deleted successfully!')]);
        }else{
            return response()->json(['error' => __('Record not found!')], 404);
        }
    }

    public function indexUseTypes(Request $request): View {
        $pageTitle = 'Use Types';
        return view('client-category.use-types', compact('pageTitle'));
    }

    public function getAjaxDataUseTypes(Request $request){
        if ($request->ajax()) {
            $data = UseTypes::select('id', 'use_types_name', 'use_types_status', 'created_at');
            return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('created_at', function ($row) {
                if(!empty($row->created_at)){
                    return date('d-m-Y', strtotime($row->created_at));
                }
            })
            ->addColumn('use_types_name', function ($row) {
                return $row->use_types_name ?: __('N/A');
            })
            ->addColumn('use_types_status', function ($row) {
                if ($row->use_types_status == 1) {
                    return '<span class="badge bg-success me-1">' . __('Active') . '</span>';
                } elseif ($row->use_types_status == 2) {
                    return '<span class="badge bg-danger me-1">' . __('In Active') . '</span>';
                } else {
                    return __('N/A');
                }
            })
            ->addColumn('action', function ($row) {
                $dataId = $row->id;
                return ' <a href="javascript:void(0)" class="btn btn-soft-danger btn-sm" onclick="deleteUser('.$dataId.')"><iconify-icon icon="solar:trash-bin-trash-bold" class="align-middle fs-18"></iconify-icon></a>';
            })
            ->rawColumns(['action', 'created_at', 'use_types_name', 'use_types_status'])
            ->make(true);
        }
        return response()->json(['error' => __('Unauthorized')], 403);
    }

    public function storeUseTypes(Request $request) {
        $request->validate([
            'use_types_name' => 'required',
        ]);
        $use_types = new UseTypes();
        $use_types->use_types_name   = $request->use_types_name;
        $use_types->use_types_status = $request->use_types_status;
        if ($use_types->save()) {
            return response()->json(['success' => true, 'message' => __('Use types added successfully!')]);
        } else {
            return response()->json(['success' => false, 'message' => __('Something went wrong!')]);
        }
    }

    public function destroyUseTypes(Request $request, $id) {
        $use_types = UseTypes::where('id', $id)->first();
        if(!empty($use_types)){
            $use_types->delete();
            return response()->json(['success' => __('Use types deleted successfully!')]);
        }else{
            return response()->json(['error' => __('Record not found!')], 404);
        }
    }

}
