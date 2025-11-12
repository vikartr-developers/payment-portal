<?php

namespace App\Http\Controllers;

use App\Models\BankManagement;
use App\Models\Request as PaymentRequest;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class BankManagementController extends Controller
{
  public function show($id)
  {
    $record = BankManagement::with('subApprovers')->findOrFail($id);
    return view('content.apps.bank_management.show', compact('record'));
  }

  public function index(Request $request)
  {
    if ($request->ajax()) {
      // If current user is an Approver, show all accounts; otherwise only user's accounts
      if (auth()->user() && auth()->user()->hasRole('Approver') || auth()->user()->hasRole('Admin') || auth()->user()->hasRole('SubApprover')) {
        $data = BankManagement::query();
        // Enforce daily deposit limits: deactivate accounts which exceeded today's approved total
        $this->enforceDailyLimits();
      } else {
        $data = BankManagement::where('created_by', auth()->user()->id);
      }
      return DataTables::of($data)
        ->addColumn('name', function ($row) {
          return $row->name ?? '-';
        })
        ->addColumn('daily_max_amount', function ($row) {
          return $row->daily_max_amount ? number_format($row->daily_max_amount, 2) : '-';
        })
        ->addColumn('max_transaction_amount', function ($row) {
          return $row->max_transaction_amount ? number_format($row->max_transaction_amount, 2) : '-';
        })
        ->addColumn('daily_max_transaction_count', function ($row) {
          return $row->daily_max_transaction_count ?? '-';
        })
        ->addColumn('upi', function ($row) {
          if ($row->type == 'upi') {
            $badge = $row->is_merchant_upi ? '<span class="badge bg-success">Merchant</span>' : '<span class="badge bg-info">Normal</span>';
            return $row->upi_id . ' ' . $badge;
          }
          return '-';
        })
        ->addColumn('status', function ($row) {
          return ucfirst($row->status ?? 'inactive');
        })
        ->addColumn('assign_sub_approver', function ($row) {
          $count = $row->subApprovers()->count();
          return "<button class='btn btn-sm btn-info assign-btn' data-id='{$row->id}' data-name='{$row->name}'>
                    <i class='ti ti-users me-1'></i>Assign ({$count})
                  </button>";
        })
        ->addColumn('action', function ($row) {
          $edit = route('bank-management.edit', $row->id);
          $view = route('bank-management.show', $row->id);
          $delete = route('bank-management.destroy', $row->id);
          $statusToggle = route('bank-management.toggle-status', $row->id);

          $btn = "<a href='$edit' class='btn btn-sm btn-primary me-1' title='Edit'><i class='ti ti-edit'></i></a>";
          $btn .= "<a href='$view' class='btn btn-sm btn-info me-1' title='View'><i class='ti ti-eye'></i></a>";

          // Enable/Disable button
          if (auth()->user() && (auth()->user()->hasRole('Approver') || auth()->user()->hasRole('Admin'))) {
            $toggleText = $row->status === 'active' ? 'Disable' : 'Enable';
            $toggleIcon = $row->status === 'active' ? 'ban' : 'check';
            $btn .= " <button class='btn btn-sm btn-secondary toggle-status me-1' data-id='" . $row->id . "' data-url='" . $statusToggle . "' title='" . $toggleText . "'><i class='ti ti-" . $toggleIcon . "'></i></button>";
          }

          $btn .= "<form method='POST' action='$delete' style='display:inline-block;'>" . csrf_field() . method_field('DELETE') .
            "<button class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure?\")' title='Delete'><i class='ti ti-trash'></i></button></form>";

          return $btn;
        })
        ->rawColumns(['upi', 'assign_sub_approver', 'action'])
        ->make(true);
    }
    return view('content.apps.bank_management.list');
  }

  /**
   * Return all bank accounts as JSON for client-side DataTables (used by advanced client filters/exports).
   */
  public function all(Request $request)
  {
    $accounts = BankManagement::query()->get();

    $rows = $accounts->map(function ($row) {
      $edit = route('bank-management.edit', $row->id);
      $view = route('bank-management.show', $row->id);
      $delete = route('bank-management.destroy', $row->id);
      $statusToggle = route('bank-management.toggle-status', $row->id);

      $btn = "<a href='$edit' class='btn btn-sm btn-primary me-1' title='Edit'><i class='ti ti-edit'></i></a>";
      $btn .= "<a href='$view' class='btn btn-sm btn-info me-1' title='View'><i class='ti ti-eye'></i></a>";

      if (auth()->user() && (auth()->user()->hasRole('Approver') || auth()->user()->hasRole('Admin'))) {
        $toggleText = $row->status === 'active' ? 'Disable' : 'Enable';
        $toggleIcon = $row->status === 'active' ? 'ban' : 'check';
        $btn .= " <button class='btn btn-sm btn-secondary toggle-status me-1' data-id='" . $row->id . "' data-url='" . $statusToggle . "' title='" . $toggleText . "'><i class='ti ti-" . $toggleIcon . "'></i></button>";
      }

      $btn .= "<form method='POST' action='$delete' style='display:inline-block;'>" . csrf_field() . method_field('DELETE') .
        "<button class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure?\")' title='Delete'><i class='ti ti-trash'></i></button></form>";

      $count = $row->subApprovers()->count();
      $assignBtn = "<button class='btn btn-sm btn-info assign-btn' data-id='{$row->id}' data-name='{$row->name}'>
                      <i class='ti ti-users me-1'></i>Assign ({$count})
                    </button>";

      return [
        'id' => $row->id,
        'name' => $row->name ?? '-',
        'daily_max_amount' => $row->daily_max_amount ? number_format($row->daily_max_amount, 2) : '-',
        'max_transaction_amount' => $row->max_transaction_amount ? number_format($row->max_transaction_amount, 2) : '-',
        'daily_max_transaction_count' => $row->daily_max_transaction_count ?? '-',
        'upi' => $row->type == 'upi' ? ($row->upi_id . ($row->is_merchant_upi ? ' (Merchant)' : ' (Normal)')) : '-',
        'status' => ucfirst($row->status ?? 'inactive'),
        'assign_sub_approver' => $assignBtn,
        'action' => $btn
      ];
    });

    return response()->json(['data' => $rows]);
  }

  /**
   * Toggle status (active/inactive) for a bank account (Approver only)
   */
  public function toggleStatus(Request $request, $id)
  {
    // dd($id);
    if (!auth()->user() || !auth()->user()->hasRole('Approver')) {
      return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }
    $record = BankManagement::findOrFail($id);
    $record->status = $record->status === 'active' ? 'inactive' : 'active';
    $record->save();

    return response()->json(['success' => true, 'status' => $record->status]);
  }

  /**
   * Enforce daily limits: if total approved (accepted) transactions for an account exceed its deposit_limit, deactivate it.
   */
  protected function enforceDailyLimits()
  {
    // today's date range
    $today = now()->toDateString();
    $accounts = BankManagement::where('status', 'active')->get();
    foreach ($accounts as $acc) {
      $identifier = $acc->type === 'bank' ? $acc->account_number : $acc->upi_id;
      if (empty($identifier))
        continue;
      // sum accepted requests for today matching this account identifier
      $sum = PaymentRequest::where('status', 'accepted')
        ->whereDate('accepted_at', $today)
        ->where(function ($q) use ($identifier) {
          $q->where('account_upi', $identifier)
            ->orWhere('payment_from', $identifier);
        })
        ->sum('payment_amount');
      if ($sum >= floatval($acc->max_transaction_amount)) {
        $acc->status = 'inactive';
        $acc->save();
      }
    }
  }

  public function create()
  {
    return view('content.apps.bank_management.form');
  }

  public function setDefault($id)
  {
    $userId = auth()->user()->id;

    // Ensure this account belongs to the user
    $account = BankManagement::where('id', $id)
      ->where('created_by', $userId)
      ->firstOrFail();

    // Unset any existing default accounts of this user
    BankManagement::where('is_default', true)
      ->where('created_by', $userId)
      ->update(['is_default' => false]);

    $account->is_default = true;
    $account->save();

    return redirect()->back()->with('success', 'Default account updated successfully.');
  }


  public function store(Request $request)
  {
    // dd($request->all());

    $validated = $request->validate([
      'type' => 'required|in:bank,upi',
      'name' => 'required|string|max:255',
      'bank_name' => 'nullable|string|max:255',
      'account_holder_name' => 'nullable|string|max:255',
      'account_number' => 'nullable|required_if:type,bank|max:20',
      'ifsc_code' => 'nullable|required_if:type,bank|max:15',
      'upi_id' => 'nullable|required_if:type,upi|max:50',
      'upi_number' => 'nullable|required_if:type,upi|max:15',
      'is_merchant_upi' => 'boolean',
      'daily_max_amount' => 'required|numeric|min:0',
      'daily_max_transaction_count' => 'required|integer|min:0',
      'max_transaction_amount' => 'required|numeric|min:0',
      'deposit_limit' => 'nullable|numeric|min:0',
      'is_default' => 'boolean',
      'status' => 'required|in:active,inactive',
    ]);
    $validated['created_by'] = auth()->user()->id;
    $validated['is_merchant_upi'] = $request->has('is_merchant_upi') ? 1 : 0;
    // if ($validated['is_default']) {
    //   BankManagement::where('is_default', true)->update(['is_default' => false]);
    // }
    BankManagement::create($validated);
    return redirect()->route('bank-management.index')->with('success', 'Record saved.');
  }

  public function edit($id)
  {
    $record = BankManagement::findOrFail($id);
    return view('content.apps.bank_management.form', compact('record'));
  }

  public function update(Request $request, $id)
  {
    $record = BankManagement::findOrFail($id);
    $validated = $request->validate([
      'type' => 'required|in:bank,upi',
      'name' => 'required|string|max:255',
      'bank_name' => 'nullable|string|max:255',
      'account_holder_name' => 'nullable|string|max:255',
      'account_number' => 'nullable|required_if:type,bank|max:20',
      'ifsc_code' => 'nullable|required_if:type,bank|max:15',
      'upi_id' => 'nullable|required_if:type,upi|max:50',
      'upi_number' => 'nullable|required_if:type,upi|max:15',
      'is_merchant_upi' => 'boolean',
      'daily_max_amount' => 'required|numeric|min:0',
      'daily_max_transaction_count' => 'required|integer|min:0',
      'max_transaction_amount' => 'required|numeric|min:0',
      'deposit_limit' => 'nullable|numeric|min:0',
      'is_default' => 'boolean',
      'status' => 'required|in:active,inactive',
    ]);
    $validated['is_merchant_upi'] = $request->has('is_merchant_upi') ? 1 : 0;
    if ($validated['is_default'] ?? false) {
      BankManagement::where('is_default', true)->where('id', '!=', $id)->update(['is_default' => false]);
    }
    $record->update($validated);
    return redirect()->route('bank-management.index')->with('success', 'Record updated.');
  }

  public function destroy($id)
  {
    $record = BankManagement::findOrFail($id);
    $record->delete();
    return redirect()->route('bank-management.index')->with('success', 'Record deleted.');
  }

  /**
   * Assign sub approvers to a bank account
   */
  public function assignSubApprovers(Request $request, $id)
  {
    $account = BankManagement::findOrFail($id);

    $validated = $request->validate([
      'sub_approvers' => 'nullable|array',
      'sub_approvers.*' => 'exists:users,id'
    ]);

    // Sync sub approvers
    $account->subApprovers()->sync($validated['sub_approvers'] ?? []);

    return response()->json([
      'success' => true,
      'message' => 'Sub approvers assigned successfully'
    ]);
  }

  /**
   * Get sub approvers for a bank account
   */
  public function getSubApprovers($id)
  {
    $account = BankManagement::findOrFail($id);
    $subApprovers = $account->subApprovers()->pluck('users.id')->toArray();

    return response()->json([
      'success' => true,
      'sub_approvers' => $subApprovers
    ]);
  }
}
