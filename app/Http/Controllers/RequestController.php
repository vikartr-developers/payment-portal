<?php

namespace App\Http\Controllers;

use App\Models\Request;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\DataTables;

class RequestController extends Controller
{
  public function __construct()
  {
    $permissions = ['request-list', 'request-create', 'request-edit', 'request-delete', 'approver-request-list', 'approver-request-create', 'approver-request-edit', 'approver-request-delete'];
    // $permissions = [];

    foreach ($permissions as $permission) {
      Permission::firstOrCreate(['name' => $permission]);
    }

    $this->middleware('permission:request-list|request-create|request-edit|request-delete', ['only' => ['index', 'show', 'dataTable']]);
    $this->middleware('permission:request-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:request-edit', ['only' => ['edit', 'update']]);
    $this->middleware('permission:request-delete', ['only' => ['softDelete', 'restore', 'forceDelete']]);


    // $this->middleware('permission:approver-request-list|approver-request-create|approver-request-edit|approver-request-delete', ['only' => ['index', 'show', 'dataTable']]);
    // $this->middleware('permission:approver-request-create', ['only' => ['create', 'store']]);
    // $this->middleware('permission:approver-request-edit', ['only' => ['edit', 'update']]);
    // $this->middleware('permission:approver-request-delete', ['only' => ['softDelete', 'restore', 'forceDelete']]);
  }

  public function index()
  {
    return view('content.apps.requests.list');
  }

  public function dataTable(HttpRequest $request)
  {
    $requests_data = Request::query();
    $current = Auth::user();

    if ($current->hasRole('Approver')) {
      $requests = $requests_data->whereNull('assign_to');
    }
    if ($current->hasRole('user')) {
      $requests = $requests_data->where('created_by', $current->id);
    }
    return DataTables::of($requests)
      ->addColumn('action', function ($req) {
        $encryptedId = Crypt::encrypt($req->id);
        $actions = '';
        if ($req->deleted_at) {
          $actions .= '<button class="btn btn-sm btn-success restore-request" data-id="' . $encryptedId . '" title="Restore">'
            . '<i class="ti ti-refresh"></i>'
            . '</button> ';
          $actions .= '<button class="btn btn-sm btn-danger force-delete-request" data-id="' . $encryptedId . '" title="Permanent Delete">'
            . '<i class="ti ti-trash-x"></i>'
            . '</button>';
        } else {
          // Edit/Delete for owners/admins
  
          // $actions .= '<button class="btn btn-sm btn-danger delete-request" data-id="' . $encryptedId . '" title="Delete">'
          //   . '<i class="ti ti-trash"></i>'
          //   . '</button>';
  
          // If current user is an approver (role name 'Approver') or has permission to edit, show Approve/Reject for pending requests
          $current = Auth::user();
          $isApprover = $current && ($current->hasRole('Approver'));

          if ($isApprover && $req->assign_to === NULL) {
            $actions .= ' <button class="btn btn-sm btn-success accept-request" data-id="' . $encryptedId . '" title="Accept"><i class="ti ti-check"></i></button>';
            // $actions .= ' <button class="btn btn-sm btn-outline-danger reject-request" data-id="' . $encryptedId . '" title="Reject"><i class="ti ti-x"></i></button>';
          }
          if ($current && ($current->hasRole('user'))) {
            $actions .= '<a href="' . route('requests.view', $encryptedId) . '" class="btn btn-sm btn-primary me-1" title="View">'
              . '<i class="ti ti-eye"></i>'
              . '</a> ';
            $actions .= '<a href="' . route('requests.edit', $encryptedId) . '" class="btn btn-sm btn-primary me-1" title="Edit">'
              . '<i class="ti ti-edit"></i>'
              . '</a> ';
            $actions .= '<button class="btn btn-sm btn-danger delete-request" data-id="' . $encryptedId . '" title="Delete">'
              . '<i class="ti ti-trash"></i>'
              . '</button>';
          }
        }
        return $actions;
      })
      ->editColumn('status', function ($req) {
        if ($req->deleted_at) {
          return '<span class="badge bg-label-danger">Deleted</span>';
        }
        return match ($req->status) {
          'pending' => '<span class="badge bg-label-warning">Pending</span>',
          'accepted' => '<span class="badge bg-label-success">Accepted</span>',
          'rejected' => '<span class="badge bg-label-danger">Rejected</span>',
          default => $req->status,
        };
      })
      ->rawColumns(['action', 'status'])
      ->make(true);
  }  /**
     * View a single payment request
     */
  public function view(string $id)
  {
    $id = Crypt::decrypt($id);
    $requestModel = Request::findOrFail($id);
    return view('content.apps.requests.view', compact('requestModel'));
  }

  /**
   * Show list of requests assigned to the current approver
   */
  public function assignedRequests()
  {
    // Only for approvers
    if (!Auth::user() || !Auth::user()->hasRole('Approver')) {
      abort(403);
    }
    return view('content.apps.requests.assigned');
  }

  /**
   * DataTable for requests assigned to the current approver
   */
  public function assignedRequestsDataTable(HttpRequest $request)
  {
    $current = Auth::user();
    if (!$current || !$current->hasRole('Approver')) {
      abort(403);
    }
    $requests = Request::where('assign_to', $current->id);
    // ->where('status', 'pending');
    return DataTables::of($requests)
      ->addColumn('action', function ($req) {
        $encryptedId = Crypt::encrypt($req->id);
        $actions = '';
        $actions .= '<a href="' . route('requests.view', $encryptedId) . '" class="btn btn-sm btn-primary me-1" title="View">'
          . '<i class="ti ti-eye"></i>'
          . '</a> ';
        $actions .= ' <button class="btn btn-sm btn-success assigned-accept-request" data-id="' . $encryptedId . '"
  title="Accept"><i class="ti ti-check"></i></button>';
        $actions .= ' <button class="btn btn-sm btn-outline-danger assigned-reject-request" data-id="' . $encryptedId . '"
  title="Reject"><i class="ti ti-x"></i></button>';
        return $actions;
      })
      ->editColumn('status', function ($req) {
        return match ($req->status) {
          'pending' => '<span class="badge bg-label-warning">Pending</span>',
          'accepted' => '<span class="badge bg-label-success">Accepted</span>',
          'rejected' => '<span class="badge bg-label-danger">Rejected</span>',
          default => $req->status,
        };
      })
      ->rawColumns(['action', 'status'])
      ->make(true);
  }
  public function create()
  {
    return view('content.apps.requests.add');
  }

  public function store(HttpRequest $request)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string|max:255',
      'mode' => 'required|string|max:100',
      'amount' => 'required|numeric|min:0',
      'payment_amount' => 'nullable|numeric|min:0',
      'utr' => 'nullable|string|max:100',
      'payment_from' => 'nullable|string|max:255',
      'account_upi' => 'nullable|string|max:255',
      'image' => 'nullable|image|max:2048',
      'status' => 'nullable|in:pending,accepted,rejected',
    ]);

    if ($request->utr && Request::where('utr', $request->utr)->exists()) {
      return back()->withErrors(['utr' => 'This UTR already exists. Payment request auto-rejected.'])->withInput();
    }

    $data = $validator->validated();
    $data['created_by'] = Auth::id();

    if ($request->hasFile('image')) {
      $data['image'] = $request->file('image')->store('payment_images', 'public');
    }

    Request::create($data);

    return redirect()->route('requests.list')->with('success', 'Payment request created successfully.');
  }

  public function edit(string $id)
  {
    $id = Crypt::decrypt($id);
    $requestModel = Request::findOrFail($id);

    return view('content.apps.requests.edit', compact('requestModel'));
  }

  public function update(HttpRequest $request, string $id)
  {
    $id = Crypt::decrypt($id);
    $requestModel = Request::findOrFail($id);

    $validator = Validator::make($request->all(), [
      'name' => 'required|string|max:255',
      'mode' => 'required|string|max:100',
      'amount' => 'required|numeric|min:0',
      'payment_amount' => 'nullable|numeric|min:0',
      'utr' => 'nullable|string|max:100',
      'payment_from' => 'nullable|string|max:255',
      'account_upi' => 'nullable|string|max:255',
      'image' => 'nullable|image|max:2048',
      'status' => 'nullable|in:pending,accepted,rejected',
    ]);

    if ($request->utr && Request::where('utr', $request->utr)->where('id', '!=', $id)->exists()) {
      return back()->withErrors(['utr' => 'This UTR already exists. Payment request auto-rejected.'])->withInput();
    }

    $data = $validator->validated();
    $data['updated_by'] = Auth::id();

    if ($request->hasFile('image')) {
      $data['image'] = $request->file('image')->store('payment_images', 'public');
    }

    $requestModel->update($data);

    return redirect()->route('requests.list')->with('success', 'Payment request updated successfully.');
  }

  public function softDelete(string $id)
  {
    $id = Crypt::decrypt($id);

    $requestModel = Request::findOrFail($id);

    $requestModel->delete();

    return response()->json(['success' => true]);
  }

  public function restore(string $id)
  {
    $id = Crypt::decrypt($id);
    $requestModel = Request::withTrashed()->findOrFail($id);

    $requestModel->restore();

    return response()->json(['success' => true]);
  }

  public function forceDelete(string $id)
  {
    $id = Crypt::decrypt($id);
    $requestModel = Request::withTrashed()->findOrFail($id);

    $requestModel->forceDelete();

    return response()->json(['success' => true]);
  }

  /**
   * Approve a payment request (mark accepted)
   */
  public function accept(string $id)
  {
    $id = Crypt::decrypt($id);
    $requestModel = Request::findOrFail($id);

    // Authorization: only approver or users with 'request-edit' permission
    if (!Auth::user() || (!Auth::user()->hasRole('Approver') && !Auth::user()->can('request-edit'))) {
      return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    if ($requestModel->status !== 'pending') {
      return response()->json(['success' => false, 'message' => 'Request is not pending'], 422);
    }

    // $requestModel->status = 'accepted';
    // $requestModel->accepted_at = now();
    $requestModel->assign_to = Auth::id();
    $requestModel->save();

    return response()->json(['success' => true, 'message' => 'Request accepted']);
  }
  public function accept_payment(string $id)
  {
    $id = Crypt::decrypt($id);
    $requestModel = Request::findOrFail($id);

    // Authorization: only approver or users with 'request-edit' permission
    if (!Auth::user() || (!Auth::user()->hasRole('Approver') && !Auth::user()->can('request-edit'))) {
      return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    if ($requestModel->status !== 'pending') {
      return response()->json(['success' => false, 'message' => 'Request is not pending'], 422);
    }

    $requestModel->status = 'accepted';
    $requestModel->accepted_at = now();
    $requestModel->accepted_by = Auth::id();
    $requestModel->save();

    return response()->json(['success' => true, 'message' => 'Request accepted']);
  }

  /**
   * Reject a payment request
   */
  public function reject(string $id)
  {
    $id = Crypt::decrypt($id);
    $requestModel = Request::findOrFail($id);

    // Authorization: only approver or users with 'request-edit' permission
    if (!Auth::user() || (!Auth::user()->hasRole('Approver') && !Auth::user()->can('request-edit'))) {
      return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    if ($requestModel->status !== 'pending') {
      return response()->json(['success' => false, 'message' => 'Request is not pending'], 422);
    }

    $requestModel->status = 'rejected';
    $requestModel->rejected_at = now();
    $requestModel->rejected_by = Auth::id();
    $requestModel->save();

    return response()->json(['success' => true, 'message' => 'Request rejected']);
  }
}
