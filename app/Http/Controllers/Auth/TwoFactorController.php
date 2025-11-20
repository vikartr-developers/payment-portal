<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
// use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
  /**
   * Show 2FA setup page where a secret is generated for the authenticated user.
   */
  public function showSetup(Request $request)
  {
    $user = Auth::user();
    // dd($user);
    if (!$user) {
      return redirect()->route('login');
    }
    $google2fa = new \PragmaRX\Google2FA\Google2FA();

    // Check if user already has a temp secret in session or database
    $secret = $request->session()->get('2fasetupsecret');

    // If no secret in session, check if user has a temp_google2fa_secret stored
    if (!$secret && !empty($user->temp_google2fa_secret)) {
      $secret = $user->temp_google2fa_secret;
    }

    // If still no secret, generate a new one
    if (!$secret) {
      $secret = $google2fa->generateSecretKey();
    }

    // Store in session and user's temp field
    $request->session()->put('2fasetupsecret', $secret);
    $user->temp_google2fa_secret = $secret;
    $user->save();

    try {
      $qrImage = (new \PragmaRX\Google2FAQRCode\Google2FA())->getQRCodeInline(config('app.name'), $user->email, $secret);
    } catch (\Exception $e) {
      $qrImage = null;
    }
    return view('auth.2fa_setup', compact('secret', 'qrImage', 'user'));
  }

  /**
   * Enable 2FA for authenticated user after verifying the provided TOTP code.
   */
  public function enable(Request $request)
  {
    $user = Auth::user();
    if (!$user)
      return redirect()->route('login');
    $secret = $request->session()->get('2fasetupsecret');
    if (empty($secret))
      $secret = $request->input('secret');
    if (empty($secret)) {
      return redirect()->back()->with('error', 'Missing 2FA secret. Please refresh the page.');
    }
    $request->validate(['code' => 'required|string']);
    $code = preg_replace('/\D+/', '', trim($request->input('code')));
    $google2fa = new \PragmaRX\Google2FA\Google2FA();

    if ($google2fa->verifyKey($secret, $code)) {
      $user->google2fa_secret = $secret;
      $user->google2fa_enabled = true;
      $user->temp_google2fa_secret = null; // Clear temp secret
      $user->save();
      $request->session()->forget('2fasetupsecret');
      return redirect()->route('home')->with('success', 'Two-factor authentication enabled successfully!');
    }
    return redirect()->back()->with('error', 'The provided 2FA code is invalid. Please try again.');
  }

  /**
   * Show the 2FA verification form (after password login when 2FA is enabled).
   */
  public function showVerify(Request $request)
  {
    $userId = $request->session()->get('2fa:user:id');
    if (empty($userId)) {
      return redirect()->route('login');
    }

    return view('auth.2fa_verify');
  }

  /**
   * Verify the provided TOTP and log the user in if correct.
   */
  public function verify(Request $request)
  {
    $userId = $request->session()->get('2fa:user:id');
    if (empty($userId)) {
      return redirect()->route('login');
    }
    $user = User::find($userId);
    if (!$user || empty($user->google2fa_secret)) {
      $request->session()->forget('2fa:user:id');
      return redirect()->route('login')->withErrors(['2fa' => 'Invalid 2FA session.']);
    }
    $code = preg_replace('/\D+/', '', trim($request->input('code')));
    $google2fa = new \PragmaRX\Google2FA\Google2FA();
    // dd($google2fa->verifyKey($user->google2fa_secret, $code));
    if ($google2fa->verifyKey($user->google2fa_secret, $code)) {
      Auth::loginUsingId($user->id);
      $request->session()->forget('2fa:user:id');
      return redirect()->intended('app/home');
    }
    return redirect()->back()->withErrors(['code' => 'The provided 2FA code is invalid.']);
  }
}
