<?php

namespace App\Http\Controllers;

use App\Models\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\BankManagement;
use App\Models\CryptoManagement;
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

    // Ensure SubApprover role exists and Approver role can create users
    try {
      $sub = Role::firstOrCreate(['name' => 'SubApprover'], ['guard_name' => 'web']);
      $approverRole = Role::firstOrCreate(['name' => 'Approver'], ['guard_name' => 'web']);
      // Give Approver the ability to create users (used by menu visibility and permission checks)
      Permission::firstOrCreate(['name' => 'user-create']);
      $approverRole->givePermissionTo('user-create');
    } catch (\Exception $e) {
      // ignore if roles cannot be created in some contexts
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

  /**
   * Server-side DataTable for payment requests
   */
  public function dataTable(HttpRequest $request)
  {
    $start = microtime(true);
    $current = Auth::user();
    $requestsQuery = Request::query();

    // Deleted filters
    if ($request->get('only_trashed') === 'true') {
      $requestsQuery = Request::onlyTrashed();
    } elseif ($request->get('include_trashed') === 'true') {
      $requestsQuery = Request::withTrashed();
    }

    // Role-based scope
    if ($current && $current->hasRole('Approver')) {
      $requestsQuery->whereNull('assign_to');
    } elseif ($current && $current->hasRole('SubApprover')) {
      $requestsQuery->where('assign_to', $current->id);
    }

    // Filters
    if ($request->filled('mode') && $request->mode !== 'all') {
      $requestsQuery->where('mode', $request->mode);
    }
    if ($request->filled('status') && $request->status !== 'all') {
      $requestsQuery->where('status', $request->status);
    }
    if ($request->filled('start_date')) {
      $requestsQuery->whereDate('created_at', '>=', $request->start_date);
    }
    if ($request->filled('end_date')) {
      $requestsQuery->whereDate('created_at', '<=', $request->end_date);
    }
    if ($request->filled('search_term')) {
      $term = $request->search_term;
      $requestsQuery->where(function ($q) use ($term) {
        $q->where('utr', 'like', "%$term%")
          ->orWhere('id', 'like', "%$term%")
          ->orWhere('payment_from', 'like', "%$term%")
          ->orWhere('account_upi', 'like', "%$term%");
      });
    }

    return DataTables::of($requestsQuery)
      ->addColumn('trans_id', function ($req) {
        return 'TXN-' . str_pad((string) $req->id, 6, '0', STR_PAD_LEFT);
      })
      ->addColumn('approver_name', function ($req) {
        $userId = $req->accepted_by ?: $req->assign_to;
        if (!$userId)
          return '-';
        $user = User::find($userId);
        if (!$user)
          return '-';
        if (!empty($user->name))
          return $user->name;
        $first = $user->first_name ?? '';
        $last = $user->last_name ?? '';
        return trim($first . ' ' . $last) ?: '-';
      })
      ->addColumn('image', function ($req) {
        if (!$req->image)
          return '<span class="text-muted">-</span>';

        // $src = asset('storage/' . $req->image);
        // dd($req->image);
        $src = '/storage/app/public/' . $req->image;
        return '<img src="' . e($src) . '" alt="img" width="48" height="48" class="rounded border" />';
      })
      ->editColumn('status', function ($req) {
        if (!empty($req->deleted_at)) {
          return '<span class="badge bg-label-danger">Deleted</span>';
        }
        return match ($req->status) {
          'pending' => '<span class="badge bg-label-warning">Pending</span>',
          'accepted' => '<span class="badge bg-label-success">Approved</span>',
          'rejected' => '<span class="badge bg-label-danger">Rejected</span>',
          default => e($req->status ?? '-')
        };
      })
      ->addColumn('action', function ($req) use ($current) {
        $encryptedId = Crypt::encrypt($req->id);
        $actions = '';
        if (!empty($req->deleted_at)) {
          $actions .= '<button class="btn btn-sm btn-success restore-request me-1" data-id="' . $encryptedId . '" title="Restore">'
            . '<i class="ti ti-refresh"></i>'
            . '</button>';
          $actions .= '<button class="btn btn-sm btn-danger force-delete-request" data-id="' . $encryptedId . '" title="Permanent Delete">'
            . '<i class="ti ti-trash-x"></i>'
            . '</button>';
        } else {
          $isApprover = $current && $current->hasRole('Approver');
          if ($isApprover && $req->assign_to === NULL) {
            $actions .= '<button class="btn btn-sm btn-success accept-request me-1" data-id="' . $encryptedId . '" title="Accept">'
              . '<i class="ti ti-check"></i></button>';
          }
          // View for all; delete for owner role
          $actions .= '<a href="' . route('requests.view', $encryptedId) . '" class="btn btn-sm btn-primary me-1" title="View">'
            . '<i class="ti
ti-eye"></i>'
            . '</a>';
          if ($current && $current->hasRole('user')) {
            $actions .= '<button class="btn btn-sm btn-danger delete-request" data-id="' . $encryptedId . '" title="Delete">'
              . '<i class="ti ti-trash"></i>'
              . '</button>';
          }
        }
        return $actions;
      })
      ->rawColumns(['status', 'image', 'action'])
      ->with([
        'cache_status' => 'DATABASE',
        'load_time' => (int) ((microtime(true) - $start) * 1000)
      ])
      ->make(true);
  }

  /**
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
    // // Only for approvers
    // if (!Auth::user() || !Auth::user()->hasRole('Approver')) {
    //   abort(403);
    // }
    return view('content.apps.requests.assigned');
  }

  /**
   * DataTable for requests assigned to the current approver
   */
  public function assignedRequestsDataTable(HttpRequest $request)
  {
    $start = microtime(true);
    $current = Auth::user();
    // if (!$current || !$current->hasRole('Approver')) {
    //   abort(403);
    // }
    // If current user is an Approver, they should see requests assigned to themselves
    // plus requests assigned to any users they created (subordinates)
    if ($current && $current->hasRole('Approver')) {
      $createdIds = User::where('created_by', $current->id)->pluck('id')->toArray();
      $allowed = array_merge([$current->id], $createdIds);
      $requestsQuery = Request::whereIn('assign_to', $allowed);
    } else {
      $requestsQuery = Request::where('assign_to', $current->id);
    }

    // Filters
    if ($request->filled('mode') && $request->mode !== 'all') {
      $requestsQuery->where('mode', $request->mode);
    }
    if ($request->filled('status') && $request->status !== 'all') {
      $requestsQuery->where('status', $request->status);
    }
    if ($request->filled('start_date')) {
      $requestsQuery->whereDate('created_at', '>=', $request->start_date);
    }
    if ($request->filled('end_date')) {
      $requestsQuery->whereDate('created_at', '<=', $request->end_date);
    }
    if ($request->filled('search_term')) {
      $term = $request->search_term;
      $requestsQuery->where(function ($q) use ($term) {
        $q->where('utr', 'like', "%$term%")
          ->orWhere('id', 'like', "%$term%")
          ->orWhere('payment_from', 'like', "%$term%")
          ->orWhere('account_upi', 'like', "%$term%");
      });
    }

    return DataTables::of($requestsQuery)
      ->addColumn('trans_id', function ($req) {
        return 'TXN-' . str_pad((string) $req->id, 6, '0', STR_PAD_LEFT);
      })
      ->addColumn('approver_name', function ($req) {
        $userId = $req->accepted_by ?: $req->assign_to;
        if (!$userId)
          return '-';
        $user = User::find($userId);
        if (!$user)
          return '-';
        if (!empty($user->name))
          return $user->name;
        $first = $user->first_name ?? '';
        $last = $user->last_name ?? '';
        return trim($first . ' ' . $last) ?: '-';
      })
      ->addColumn('image', function ($req) {
        if (!$req->image)

          return '<span class="text-muted">-</span>';
        // dd($req->image);
        // '/storage/app/public/' . $imgPath
        $src = '/storage/app/public/' . $req->image;
        return '<img src="' . e($src) . '" alt="img" width="48" height="48" class="rounded border" />';
      })
      ->editColumn('status', function ($req) {
        return match ($req->status) {
          'pending' => '<span class="badge bg-label-warning">Pending</span>',
          'accepted' => '<span class="badge bg-label-success">Approved</span>',
          'rejected' => '<span class="badge bg-label-danger">Rejected</span>',
          default => e($req->status ?? '-')
        };
      })
      ->addColumn('action', function ($req) {
        $encryptedId = Crypt::encrypt($req->id);
        $actions = '';
        $actions .= '<a href="' . route('requests.edit', $encryptedId) . '" class="text-warning me-1" title="Edit">'
          . 'Edit'
          . '</a>';
        // $actions .= '<a href="' . route('requests.view', $encryptedId) . '" class="btn btn-sm btn-primary me-1" title="View">'
        //   . '<i class="ti ti-eye"></i>'
        //   . '</a>';
        if ($req->status === 'pending') {
          $actions .= '<button class="btn btn-sm mt-1 btn-success assigned-accept-request me-1" data-id="' . $encryptedId . '" title="Accept">Approved</button>';
          $actions .= '<button class="btn btn-sm mt-1 btn-outline-danger assigned-reject-request" data-id="' . $encryptedId . '" title="Reject">Reject</button>';
        }
        return $actions;
      })
      ->rawColumns(['status', 'image', 'action'])
      ->with([
        'cache_status' => 'DATABASE',
        'load_time' => (int) ((microtime(true) - $start) * 1000)
      ])
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

    // Auto-assign to a random SubApprover if available, otherwise an Approver
    $assignTo = null;
    try {
      $subCount = User::role('SubApprover')->count();
      if ($subCount > 0) {
        $offset = random_int(0, max(0, $subCount - 1));
        $sub = User::role('SubApprover')->skip($offset)->first();
        if ($sub)
          $assignTo = $sub->id;
      } else {
        $approverCount = User::role('Approver')->count();
        if ($approverCount > 0) {
          $offset = random_int(0, max(0, $approverCount - 1));
          $approver = User::role('Approver')->skip($offset)->first();
          if ($approver)
            $assignTo = $approver->id;
        }
      }
    } catch (\Exception $e) {
      $assignTo = null;
    }
    $data['assign_to'] = $assignTo;

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
    $currentUser = Auth::user();

    // Role-based validation
    if ($currentUser->hasRole('Approver')) {
      // Approvers can only edit: payment_amount, payment_from, status
      $validator = Validator::make($request->all(), [
        'payment_amount' => 'nullable|numeric|min:0',
        'payment_from' => 'nullable|string|max:255',
        'status' => 'nullable|in:pending,accepted,rejected',
      ]);

      $data = [
        'payment_amount' => $request->payment_amount,
        'payment_from' => $request->payment_from,
        'status' => $request->status,
        'updated_by' => Auth::id(),
      ];
    } else {
      // Regular users can edit all fields
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
        return back()->withErrors(['utr' => 'This UTR already exists.'])->withInput();
      }

      $data = $validator->validated();
      $data['updated_by'] = Auth::id();

      if ($request->hasFile('image')) {
        $data['image'] = $request->file('image')->store('payment_images', 'public');
      }
    }

    $requestModel->update($data);

    $redirectRoute = $currentUser->hasRole('Approver') ? 'requests.assigned' : 'requests.list';
    return redirect()->route($redirectRoute)->with('success', 'Payment request updated successfully.');
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

  /**
   * Get default bank account for the current user
   */
  public function getDefaultBankAccount(HttpRequest $request)
  {
    $type = $request->get('type'); // 'bank' or 'upi'
    $userId = Auth::id();

    $account = BankManagement::where('created_by', $userId)
      ->where('type', $type)
      ->orderBy('created_at', 'desc')
      ->first();

    if ($account) {
      return response()->json(['success' => true, 'account' => $account]);
    }

    return response()->json(['success' => false, 'message' => 'No default account found']);
  }

  /**
   * Get default crypto account for the current user
   */
  public function getDefaultCryptoAccount()
  {
    $userId = Auth::id();

    $account = CryptoManagement::where('created_by', $userId)
      ->orderBy('created_at', 'desc')
      ->first();

    if ($account) {
      return response()->json(['success' => true, 'account' => $account]);
    }

    return response()->json(['success' => false, 'message' => 'No default crypto account found']);
  }
}
