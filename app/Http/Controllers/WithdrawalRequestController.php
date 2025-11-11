<?php

namespace App\Http\Controllers;

use App\Models\WithdrawalRequest;
use App\Models\User;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
// use Request;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

use Spatie\Permission\Models\Permission;

class WithdrawalRequestController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
    $permissions = [
      'withdrawal-list',
      'withdrawal-create',
      'withdrawal-edit',
      'withdrawal-delete'
    ];
    // $permissions = [];

    foreach ($permissions as $permission) {
      Permission::firstOrCreate(['name' => $permission]);
    }

    $this->middleware('permission:withdrawal-list|withdrawal-create|withdrawal-edit|withdrawal-delete', ['only' => ['index', 'show', 'dataTable']]);
    $this->middleware('permission:withdrawal-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:withdrawal-edit', ['only' => ['edit', 'update']]);
    $this->middleware('permission:withdrawal-delete', ['only' => ['softDelete', 'restore', 'forceDelete']]);

  }

  // List view for withdrawal requests
  public function index()
  {
    return view('content.apps.withdrawals.index');
  }

  /**
   * Server-side DataTable for withdrawal requests
   */
  public function dataTable(HttpRequest $request)
  {
    $start = microtime(true);
    $current = Auth::user();
    $query = WithdrawalRequest::query();

    // Trashed filters
    if ($request->get('only_trashed') === 'true') {
      $query = WithdrawalRequest::onlyTrashed();
    } elseif ($request->get('include_trashed') === 'true') {
      $query = WithdrawalRequest::withTrashed();
    }

    // Filters
    if ($request->filled('status') && $request->status !== 'all') {
      $query->where('status', $request->status);
    }
    if ($request->filled('approver_status') && $request->approver_status !== 'all') {
      $query->where('approver_status', $request->approver_status);
    }
    if ($request->filled('start_date')) {
      $query->whereDate('created_at', '>=', $request->start_date);
    }
    if ($request->filled('end_date')) {
      $query->whereDate('created_at', '<=', $request->end_date);
    }
    if ($request->filled('search_term')) {
      $term = $request->search_term;
      $query->where(function ($q) use ($term) {
        $q->where('trans_id', 'like', "%$term%")
          ->orWhere('account_holder_name', 'like', "%$term%")
          ->orWhere('account_number', 'like', "%$term%")
          ->orWhere('ifsc_code', 'like', "%$term%");
      });
    }

    return DataTables::of($query)
      ->addColumn('creator_name', function ($row) {
        $user = User::find($row->created_by);
        return $user ? ($user->name ?? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))) : '-';
      })
      ->addColumn('action', function ($row) {
        $actions = '';
        if (!empty($row->deleted_at)) {
          $actions .= '<button class="btn btn-sm btn-secondary restore-withdrawal me-1" data-id="' . $row->id . '">Restore</button>';
        } else {
          $actions .= '<a href="' . route('withdrawals.edit', $row->id) . '" class="btn btn-sm btn-warning me-1">Edit</a>';
          // $actions .= '<button class="btn btn-sm btn-danger delete-withdrawal" data-id="' . $row->id . '">Delete</button>';
        }
        return $actions;
      })
      ->rawColumns(['action'])
      ->with([
        'cache_status' => 'DATABASE',
        'load_time' => (int) ((microtime(true) - $start) * 1000)
      ])
      ->make(true);
  }

  // Show create form
  public function create()
  {
    // Only Approver may add
    if (!Auth::user() || !Auth::user()->hasRole('Approver')) {
      abort(403, 'Only approvers can create Payout .');
    }
    return view('content.apps.withdrawals.form');
  }

  // Store new withdrawal request
  public function store(Request $request)
  {
    if (!Auth::user() || !Auth::user()->hasRole('Approver')) {
      abort(403, 'Only approvers can create Payout.');
    }

    $validated = $request->validate([
      'account_holder_name' => 'required|string|max:255',
      'account_number' => 'required|string|max:64',
      'confirm_account_number' => 'required|string|max:64|same:account_number',
      'branch_name' => 'nullable|string|max:255',
      'ifsc_code' => 'nullable|string|max:32',
      'amount' => 'required|numeric|min:0.01',
      'status' => 'required|in:active,inactive',
    ]);

    $transId = 'WD' . time() . rand(100, 999);
    $validated['trans_id'] = $transId;
    $validated['approver_status'] = 'pending';
    $validated['created_by'] = Auth::id();

    WithdrawalRequest::create($validated);

    return redirect()->route('withdrawals.index')->with('success', 'Withdrawal request created.');
  }

  // Show edit form
  public function edit($id)
  {
    $item = WithdrawalRequest::findOrFail($id);
    return view('content.apps.withdrawals.form', compact('item'));
  }

  public function update(Request $request, $id)
  {
    $item = WithdrawalRequest::findOrFail($id);
    $validated = $request->validate([
      'account_holder_name' => 'required|string|max:255',
      'account_number' => 'required|string|max:64',
      'confirm_account_number' => 'required|string|max:64|same:account_number',
      'branch_name' => 'nullable|string|max:255',
      'ifsc_code' => 'nullable|string|max:32',
      'amount' => 'required|numeric|min:0.01',
      'status' => 'required|in:active,inactive',
      'approver_status' => 'required|in:approved,pending,rejected',
    ]);

    $validated['updated_by'] = Auth::id();
    $item->update($validated);
    return redirect()->route('withdrawals.index')->with('success', 'Withdrawal request updated.');
  }

  public function destroy($id)
  {
    $item = WithdrawalRequest::findOrFail($id);
    $item->deleted_by = Auth::id();
    $item->save();
    $item->delete();

    if (request()->ajax()) {
      return response()->json(['success' => true]);
    }

    return redirect()->route('withdrawals.index')->with('success', 'Withdrawal request deleted.');
  }

  public function restore($id)
  {
    $item = WithdrawalRequest::withTrashed()->findOrFail($id);
    $item->restore();
    $item->deleted_by = null;
    $item->save();

    if (request()->ajax()) {
      return response()->json(['success' => true]);
    }

    return redirect()->route('withdrawals.index')->with('success', 'Withdrawal request restored.');
  }
}
