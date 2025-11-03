<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class SecurityController extends Controller
{
  /**
   * Show the account security settings page.
   */
  public function showSecurityPage()
  {
    return view('content.pages.pages-account-settings-security');

    // return view('pages.account-settings-security');
  }

  /**
   * Handle the change password request.
   */
  public function changePassword(Request $request)
  {
    $request->validate([
      'currentPassword' => 'required',
      'newPassword' => 'required|string|min:8|confirmed',
    ]);

    $user = Auth::user();

    if (!Hash::check($request->currentPassword, $user->password)) {
      return response()->json(['error' => 'Current password is incorrect.'], 422);
    }

    $user->password = Hash::make($request->newPassword);
    $user->save();

    return response()->json(['success' => 'Password changed successfully.']);
  }
}
