<?php
namespace App\Http\Controllers;

use App\Models\CryptoManagement;
use Illuminate\Http\Request;

class CryptoManagementController extends Controller
{
  public function index()
  {
    $cryptos = CryptoManagement::where('created_by', auth()->user()->id)->get();
    return view('content.apps.crypto_management.list', compact('cryptos'));
  }

  public function create()
  {
    return view('content.apps.crypto_management.form');
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'wallet_address' => 'required|unique:crypto_managements,wallet_address|max:255',
      'network' => 'required|string|max:100',
      'coin' => 'required|string|max:50',
      'status' => 'required|in:active,inactive',
      'is_default' => 'boolean',
    ]);
    $validated['created_by'] = auth()->user()->id;
    $validated['updated_by'] = auth()->user()->id;

    if (!empty($validated['is_default']) && $validated['is_default']) {
      CryptoManagement::where('is_default', true)->update(['is_default' => false]);
    }

    CryptoManagement::create($validated);

    return redirect()->route('crypto-management.index')->with('success', 'Crypto wallet added successfully.');
  }

  public function edit($id)
  {
    $crypto = CryptoManagement::findOrFail($id);
    return view('content.apps.crypto_management.form', compact('crypto'));
  }

  public function update(Request $request, $id)
  {
    $crypto = CryptoManagement::findOrFail($id);

    $validated = $request->validate([
      'wallet_address' => "required|max:255|unique:crypto_managements,wallet_address,{$id}",
      'network' => 'required|string|max:100',
      'coin' => 'required|string|max:50',
      'status' => 'required|in:active,inactive',
      'is_default' => 'boolean',
    ]);

    $validated['created_by'] = auth()->user()->id;
    $validated['updated_by'] = auth()->user()->id;

    if (!empty($validated['is_default']) && $validated['is_default']) {
      CryptoManagement::where('is_default', true)->where('id', '!=', $id)->update(['is_default' => false]);
    }

    $crypto->update($validated);

    return redirect()->route('crypto-management.index')->with('success', 'Crypto wallet updated successfully.');
  }

  public function destroy($id)
  {
    $crypto = CryptoManagement::findOrFail($id);
    $crypto->delete();

    return redirect()->route('crypto-management.index')->with('success', 'Crypto wallet deleted.');
  }

  // public function setDefault($id)
  // {
  //   CryptoManagement::where('is_default', true)->update(['is_default' => false]);

  //   $crypto = CryptoManagement::findOrFail($id);
  //   $crypto->is_default = true;
  //   $crypto->save();

  //   return redirect()->back()->with('success', 'Default crypto wallet set.');
  // }
  public function setDefault($id)
  {
    $userId = auth()->user()->id;

    // Unset any currently default wallets of this user
    CryptoManagement::where('is_default', true)
      ->where('created_by', $userId)
      ->update(['is_default' => false]);

    // Find wallet by id that belongs to this user
    $crypto = CryptoManagement::where('id', $id)
      ->where('created_by', $userId)
      ->firstOrFail();

    $crypto->is_default = true;
    $crypto->save();

    return redirect()->back()->with('success', 'Default crypto wallet set.');
  }

}
