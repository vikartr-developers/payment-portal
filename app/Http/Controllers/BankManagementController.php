<?php

namespace App\Http\Controllers;

use App\Models\BankManagement;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class BankManagementController extends Controller
{
  public function index(Request $request)
  {
    if ($request->ajax()) {
      $data = BankManagement::where('created_by', auth()->user()->id);
      return DataTables::of($data)
        ->addColumn('account_info', function ($row) {
          return $row->type == 'bank' ? $row->account_number : $row->upi_id;
        })
        ->addColumn('code_or_number', function ($row) {
          return $row->type == 'bank' ? $row->ifsc_code : $row->upi_number;
        })
        ->addColumn('default', function ($row) {
          return $row->is_default ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>';
        })
        ->addColumn('action', function ($row) {
          $edit = route('bank-management.edit', $row->id);
          $delete = route('bank-management.destroy', $row->id);
          return "<a href='$edit' class='btn btn-sm btn-warning'>Edit</a>
                    <form method='POST' action='$delete' style='display:inline-block;'>
                        " . csrf_field() . method_field('DELETE') .
            "<button class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure?\")'>Delete</button>
                    </form>";
        })
        ->rawColumns(['default', 'action'])
        ->make(true);
    }
    return view('content.apps.bank_management.list');
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
      'account_number' => 'nullable|required_if:type,bank|max:20',
      'ifsc_code' => 'nullable|required_if:type,bank|max:15',
      'upi_id' => 'nullable|required_if:type,upi|max:50',
      'upi_number' => 'nullable|required_if:type,upi|max:15',
      'deposit_limit' => 'required|numeric|min:0',
      'is_default' => 'boolean',
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
      'account_number' => 'nullable|required_if:type,bank|max:20',
      'ifsc_code' => 'nullable|required_if:type,bank|max:15',
      'upi_id' => 'nullable|required_if:type,upi|max:50',
      'upi_number' => 'nullable|required_if:type,upi|max:15',
      'deposit_limit' => 'required|numeric|min:0',
      'is_default' => 'boolean',
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
