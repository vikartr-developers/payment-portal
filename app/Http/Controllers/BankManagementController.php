<?php

namespace App\Http\Controllers;

use App\Models\BankManagement;
use App\Models\Request as PaymentRequest;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class BankManagementController extends Controller
{
  public function show(Request $request)
  {
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
        ->addColumn('account_info', function ($row) {
          return $row->type == 'bank' ? $row->account_number : $row->upi_id;
        })
        ->addColumn('status', function ($row) {
          return ucfirst($row->status ?? 'inactive');
        })
        ->addColumn('code_or_number', function ($row) {
          return $row->type == 'bank' ? $row->ifsc_code : $row->upi_number;
        })
        // ->addColumn('default', function ($row) {
        //   return $row->is_default ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>';
        // })
        ->addColumn('action', function ($row) {
          $edit = route('bank-management.edit', $row->id);
          $delete = route('bank-management.destroy', $row->id);
          $statusToggle = route('bank-management.toggle-status', $row->id);
          $btn = "<a href='$edit' class='btn btn-sm btn-warning me-1'>Edit</a>";
          $btn .= "<form method='POST' action='$delete' style='display:inline-block;'>" . csrf_field() . method_field('DELETE') .
            "<button class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure?\")'>Delete</button></form>";
          // Approver can toggle status directly
          if (auth()->user() && auth()->user()->hasRole('Approver')) {
            $toggleText = $row->status === 'active' ? 'Deactivate' : 'Activate';
            $btn .= " <button class='btn btn-sm btn-secondary toggle-status' data-id='" . $row->id . "' data-url='" . $statusToggle . "'>" . $toggleText . "</button>";
          }
          return $btn;
        })
        ->rawColumns(['default', 'action'])
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
      $account_info = $row->type == 'bank' ? $row->account_number : $row->upi_id;
      $code_or_number = $row->type == 'bank' ? $row->ifsc_code : $row->upi_number;
      $edit = route('bank-management.edit', $row->id);
      $delete = route('bank-management.destroy', $row->id);
      $statusToggle = route('bank-management.toggle-status', $row->id);
      $btn = "<a href='$edit' class='btn btn-sm btn-warning me-1'>Edit</a>";
      $btn .= "<form method='POST' action='$delete' style='display:inline-block;'>" . csrf_field() . method_field('DELETE') .
        "<button class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure?\")'>Delete</button></form>";
      if (auth()->user() && auth()->user()->hasRole('Approver')) {
        $toggleText = $row->status === 'active' ? 'Deactivate' : 'Activate';
        $btn .= " <button class='btn btn-sm btn-secondary toggle-status' data-id='" . $row->id . "' data-url='" . $statusToggle . "'>" . $toggleText . "</button>";
      }
      return [
        'id' => $row->id,
        'type' => $row->type,
        'account_info' => $account_info,
        'code_or_number' => $code_or_number,
        'deposit_limit' => $row->deposit_limit,
        'status' => ucfirst($row->status ?? 'inactive'),
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
      if ($sum >= floatval($acc->deposit_limit)) {
        $acc->status = 'inactive';
        $acc->save();
      }
    }
  }

  public function create()
  {
    return view('content.apps.bank_management.form');
  }
  // public function setDefault($id)
  // {
  //   $account = BankManagement::findOrFail($id);

  //   // Ensure current user owns this account (add authorization if needed)

  //   BankManagement::where('is_default', true)->update(['is_default' => false]);
  //   $account->is_default = true;
  //   $account->save();

  //   return redirect()->back()->with('success', 'Default account updated successfully.');
  // }
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
      'bank_name' => 'nullable|string|max:255',
      'account_number' => 'nullable|required_if:type,bank|max:20',
      'ifsc_code' => 'nullable|required_if:type,bank|max:15',
      'upi_id' => 'nullable|required_if:type,upi|max:50',
      'upi_number' => 'nullable|required_if:type,upi|max:15',
      'deposit_limit' => 'required|numeric|min:0',
      'is_default' => 'boolean',
      'status' => 'required|in:active,inactive',
    ]);
    $validated['created_by'] = auth()->user()->id;
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
      'bank_name' => 'nullable|string|max:255',
      'account_number' => 'nullable|required_if:type,bank|max:20',
      'ifsc_code' => 'nullable|required_if:type,bank|max:15',
      'upi_id' => 'nullable|required_if:type,upi|max:50',
      'upi_number' => 'nullable|required_if:type,upi|max:15',
      'deposit_limit' => 'required|numeric|min:0',
      'is_default' => 'boolean',
      'status' => 'required|in:active,inactive',
    ]);
    if ($validated['is_default']) {
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
}
