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
    $this->middleware('auth')->except(['createFrontend', 'storeFrontend']);
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
      ->addColumn('screenshot', function ($row) {
        if ($row->screenshot) {
          return '<a href="' . asset('storage/' . $row->screenshot) . '" target="_blank" class="btn btn-sm btn-info">
                    <i class="ti ti-photo me-1"></i>View
                  </a>';
        }
        return '<button class="btn btn-sm btn-outline-primary upload-screenshot-btn" data-id="' . $row->id . '">
                  <i class="ti ti-upload me-1"></i>Upload
                </button>';
      })
      ->addColumn('action', function ($row) {
        $actions = '';
        if (!empty($row->deleted_at)) {
          $actions .= '<button class="btn btn-sm btn-secondary restore-withdrawal me-1" data-id="' . $row->id . '">Restore</button>';
        } else {
          // Three status buttons
          $approvedClass = $row->approver_status === 'approved' ? 'btn-success' : 'btn-outline-success';
          $pendingClass = $row->approver_status === 'pending' ? 'btn-warning' : 'btn-outline-warning';
          $rejectedClass = $row->approver_status === 'rejected' ? 'btn-danger' : 'btn-outline-danger';

          $actions .= '<button class="btn btn-sm ' . $approvedClass . ' me-1 change-status-btn" data-id="' . $row->id . '" data-field="approver_status" data-value="approved" title="Approve">
                        <i class="ti ti-check"></i>
                      </button>';
          $actions .= '<button class="btn btn-sm ' . $pendingClass . ' me-1 change-status-btn" data-id="' . $row->id . '" data-field="approver_status" data-value="pending" title="Pending">
                        <i class="ti ti-clock"></i>
                      </button>';
          $actions .= '<button class="btn btn-sm ' . $rejectedClass . ' me-1 change-status-btn" data-id="' . $row->id . '" data-field="approver_status" data-value="rejected" title="Reject">
                        <i class="ti ti-x"></i>
                      </button>';
          $actions .= '<button class="btn btn-sm btn-primary edit-withdrawal-btn" data-id="' . $row->id . '" title="Edit">
                        <i class="ti ti-edit"></i>
                      </button>';
        }
        return $actions;
      })
      ->rawColumns(['screenshot', 'action'])
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
    // if (!Auth::user() || !Auth::user()->hasRole('Approver')) {
    //   abort(403, 'Only approvers can create Payout .');
    // }
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

  /**
   * Export withdrawal requests (full dataset respecting current filters) as CSV
   */
  public function show(HttpRequest $request)
  {
    // dd('ll');
    return view('content.apps.withdrawals.index');

  }
  public function export(HttpRequest $request)
  {
    // dd('ll');
    $start = microtime(true);
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

    $rows = $query->orderBy('id', 'desc')->get();

    $fileName = 'withdrawals-' . now()->format('Ymd-His') . '.csv';
    $headers = [
      'Content-Type' => 'text/csv',
      'Content-Disposition' => "attachment; filename=\"$fileName\"",
    ];

    $callback = function () use ($rows) {
      $out = fopen('php://output', 'w');
      // BOM for Excel compatibility with UTF-8
      fprintf($out, "%s", chr(0xEF) . chr(0xBB) . chr(0xBF));

      // Header
      fputcsv($out, ['Request ID', 'Date', 'Account Holder', 'Account Number', 'Branch', 'IFSC', 'Amount', 'Status', 'Approver Status', 'Created By', 'Created At']);

      foreach ($rows as $row) {
        $trans = $row->trans_id ?? ('WD' . $row->id);
        $date = $row->created_at ? $row->created_at->toDateTimeString() : '';
        $holder = $row->account_holder_name ?? '';
        $acct = $row->account_number ?? '';
        $branch = $row->branch_name ?? '';
        $ifsc = $row->ifsc_code ?? '';
        $amount = $row->amount;
        $status = $row->status ?? '';
        $approver_status = $row->approver_status ?? '';
        $creator = '-';
        if ($row->created_by) {
          $u = User::find($row->created_by);
          $creator = $u ? ($u->name ?? trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? ''))) : $row->created_by;
        }

        fputcsv($out, [$trans, $date, $holder, $acct, $branch, $ifsc, $amount, $status, $approver_status, $creator, $date]);
      }

      fclose($out);
    };
    // dd('kkk');

    return response()->stream($callback, 200, $headers);
  }

  /**
   * Frontend payout request form (for regular users - no auth required)
   */
  public function createFrontend()
  {
    $pageConfigs = ['myLayout' => 'front'];

    return view('frontend.payout-request', ['pageConfigs' => $pageConfigs]);
  }

  /**
   * Store frontend payout request (no auth required)
   */
  public function storeFrontend(Request $request)
  {
    $validated = $request->validate([
      'account_holder_name' => 'required|string|max:255',
      'account_number' => 'required|string|max:64',
      'confirm_account_number' => 'required|string|max:64|same:account_number',
      'branch_name' => 'nullable|string|max:255',
      'ifsc_code' => 'nullable|string|max:11',
      'amount' => 'required|numeric|min:1',
      'screenshot' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
    ]);

    $transId = 'WD' . time() . rand(100, 999);

    $data = [
      'trans_id' => $transId,
      'account_holder_name' => $validated['account_holder_name'],
      'account_number' => $validated['account_number'],
      'confirm_account_number' => $validated['confirm_account_number'], // Added this field
      'branch_name' => $validated['branch_name'] ?? null,
      'ifsc_code' => $validated['ifsc_code'] ?? null,
      'amount' => $validated['amount'],
      'status' => 'active',
      'approver_status' => 'pending',
      'created_by' => Auth::check() ? Auth::id() : null, // Allow null for non-authenticated users
      'screenshot' => null, // Initialize screenshot field
    ];

    // Handle screenshot upload
    if ($request->hasFile('screenshot')) {
      try {
        $file = $request->file('screenshot');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        // Ensure storage directory exists
        if (!file_exists(storage_path('app/public/payout_screenshots'))) {
          mkdir(storage_path('app/public/payout_screenshots'), 0755, true);
        }

        $screenshotPath = $file->storeAs('payout_screenshots', $filename, 'public');
        $data['screenshot'] = $screenshotPath;
      } catch (\Exception $e) {
        \Log::error('Screenshot upload failed: ' . $e->getMessage());
        return back()->withErrors(['screenshot' => 'Failed to upload screenshot: ' . $e->getMessage()])->withInput();
      }
    }

    try {
      WithdrawalRequest::create($data);
      return redirect()->route('payout.request')->with('success', 'Payout request submitted successfully! Your request ID is: ' . $transId);
    } catch (\Exception $e) {
      \Log::error('Withdrawal request creation failed: ' . $e->getMessage());
      return back()->withErrors(['error' => 'Failed to create payout request: ' . $e->getMessage()])->withInput();
    }
  }

  /**
   * Upload screenshot for withdrawal request
   */
  public function uploadScreenshot(HttpRequest $request, $id)
  {
    $item = WithdrawalRequest::findOrFail($id);

    $validated = $request->validate([
      'screenshot' => 'required|image|mimes:jpeg,jpg,png|max:2048',
    ]);

    if ($request->hasFile('screenshot')) {
      try {
        // Delete old screenshot if exists
        if ($item->screenshot && \Storage::disk('public')->exists($item->screenshot)) {
          \Storage::disk('public')->delete($item->screenshot);
        }

        $file = $request->file('screenshot');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        // Ensure storage directory exists
        if (!file_exists(storage_path('app/public/payout_screenshots'))) {
          mkdir(storage_path('app/public/payout_screenshots'), 0755, true);
        }

        $screenshotPath = $file->storeAs('payout_screenshots', $filename, 'public');
        $item->screenshot = $screenshotPath;
        $item->updated_by = Auth::check() ? Auth::id() : null;
        $item->save();

        if ($request->ajax()) {
          return response()->json([
            'success' => true,
            'message' => 'Screenshot uploaded successfully',
            'screenshot_url' => asset('storage/' . $screenshotPath)
          ]);
        }

        return redirect()->back()->with('success', 'Screenshot uploaded successfully');
      } catch (\Exception $e) {
        \Log::error('Screenshot upload failed: ' . $e->getMessage());

        if ($request->ajax()) {
          return response()->json([
            'success' => false,
            'message' => 'Failed to upload screenshot: ' . $e->getMessage()
          ], 500);
        }

        return back()->withErrors(['screenshot' => 'Failed to upload screenshot: ' . $e->getMessage()]);
      }
    }

    if ($request->ajax()) {
      return response()->json(['success' => false, 'message' => 'No file uploaded'], 400);
    }

    return back()->withErrors(['screenshot' => 'No file uploaded']);
  }

  /**
   * Get withdrawal data for modal
   */
  public function getWithdrawal($id)
  {
    $item = WithdrawalRequest::findOrFail($id);

    return response()->json([
      'success' => true,
      'data' => [
        'id' => $item->id,
        'trans_id' => $item->trans_id,
        'account_holder_name' => $item->account_holder_name,
        'account_number' => $item->account_number,
        'confirm_account_number' => $item->confirm_account_number,
        'branch_name' => $item->branch_name,
        'ifsc_code' => $item->ifsc_code,
        'amount' => $item->amount,
        'status' => $item->status,
        'approver_status' => $item->approver_status,
        'screenshot' => $item->screenshot ? asset('storage/' . $item->screenshot) : null,
        'created_at' => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : null,
      ]
    ]);
  }

  /**
   * Change status of withdrawal request
   */
  public function changeStatus(HttpRequest $request, $id)
  {
    $item = WithdrawalRequest::findOrFail($id);

    $validated = $request->validate([
      'field' => 'required|in:status,approver_status',
      'value' => 'required|string',
    ]);

    // Validate the value based on field
    if ($validated['field'] === 'status' && !in_array($validated['value'], ['active', 'inactive'])) {
      return response()->json(['success' => false, 'message' => 'Invalid status value'], 400);
    }

    if ($validated['field'] === 'approver_status' && !in_array($validated['value'], ['approved', 'pending', 'rejected'])) {
      return response()->json(['success' => false, 'message' => 'Invalid approver status value'], 400);
    }

    try {
      $item->{$validated['field']} = $validated['value'];
      $item->updated_by = Auth::check() ? Auth::id() : null;
      $item->save();

      $fieldLabel = $validated['field'] === 'status' ? 'Status' : 'Approver Status';

      if ($request->ajax()) {
        return response()->json([
          'success' => true,
          'message' => $fieldLabel . ' updated to ' . $validated['value']
        ]);
      }

      return redirect()->back()->with('success', $fieldLabel . ' updated successfully');
    } catch (\Exception $e) {
      \Log::error('Status update failed: ' . $e->getMessage());

      if ($request->ajax()) {
        return response()->json([
          'success' => false,
          'message' => 'Failed to update status: ' . $e->getMessage()
        ], 500);
      }

      return back()->withErrors(['error' => 'Failed to update status']);
    }
  }
}

