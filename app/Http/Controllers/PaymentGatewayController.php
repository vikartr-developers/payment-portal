<?php

namespace App\Http\Controllers;

use App\Models\BankManagement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PaymentGatewayController extends Controller
{
  /**
   * Step 1: Show initial payment form (username + amount)
   */
  public function index()
  {
    $pageConfigs = ['myLayout' => 'front'];

    return view('content.front-pages.payment-gateway.step1', ['pageConfigs' => $pageConfigs]);
  }

  /**
   * Step 2: Select payment method (Regular or Crypto)
   */
  public function selectMethod(Request $request)
  {
    $request->validate([
      'username' => 'required|string|max:255',
      'mobile' => 'nullable|string|max:10|regex:/^[0-9]{10}$/',
      'consent' => 'accepted',
      'amount' => 'required|numeric|min:100',
    ]);

    Session::put('payment_username', $request->username);
    Session::put('payment_mobile', $request->mobile);
    Session::put('payment_consent', $request->consent);
    Session::put('payment_amount', $request->amount);
    $pageConfigs = ['myLayout' => 'front'];

    return view('content.front-pages.payment-gateway.step2', ['pageConfigs' => $pageConfigs]);
  }

  /**
   * Step 3: Show payment details based on selected method
   */
  public function showPaymentDetails(Request $request)
  {
    $request->validate([
      'payment_type' => 'required|in:regular,crypto',
    ]);

    $username = Session::get('payment_username');
    $amount = Session::get('payment_amount');
    $pageConfigs = ['myLayout' => 'front'];

    if (!$username || !$amount) {
      return redirect()->route('payment.gateway')->with('error', 'Session expired. Please start again.');
    }

    Session::put('payment_type', $request->payment_type);

    if ($request->payment_type === 'crypto') {
      return view('content.front-pages.payment-gateway.step3-crypto', compact('username', 'amount', 'pageConfigs'));
    }

    // Fetch random UPI and Bank accounts from bank_managements
    // Use count + random offset to reliably pick a random row without ORDER BY RAND()
    $upiAccount = null;
    $upiCount = BankManagement::where('type', 'upi')->whereNull('deleted_at')->count();
    if ($upiCount > 0) {
      $upiOffset = random_int(0, max(0, $upiCount - 1));
      $upiAccount = BankManagement::where('type', 'upi')->whereNull('deleted_at')->skip($upiOffset)->first();
    }

    $bankAccount = null;
    $bankCount = BankManagement::where('type', 'bank')->whereNull('deleted_at')->count();
    if ($bankCount > 0) {
      $bankOffset = random_int(0, max(0, $bankCount - 1));
      $bankAccount = BankManagement::where('type', 'bank')->whereNull('deleted_at')->skip($bankOffset)->first();
    }




    return view('content.front-pages.payment-gateway.step3-regular', compact('username', 'amount', 'upiAccount', 'bankAccount', 'pageConfigs'));
  }

  /**
   * Process final payment submission
   */
  public function processPayment(Request $request)
  {
    $request->validate([
      'payment_method' => 'required|in:upi,bank,crypto',
      'utr' => 'required|string|max:12|min:12',
      'screenshot' => 'required|image|max:2048',
    ]);

    $username = Session::get('payment_username');
    $mobile = Session::get('payment_mobile');
    $amount = Session::get('payment_amount');
    $paymentType = Session::get('payment_type');

    if (!$username || !$amount) {
      return redirect()->route('payment.gateway')->with('error', 'Session expired. Please start again.');
    }

    // Store screenshot
    $screenshotPath = $request->file('screenshot')->store('payment_screenshots', 'public');

    // Create payment request record
    // Try to assign this new request to a random approver (if any)
    $assignTo = null;
    try {
      $approverCount = User::role('Approver')->count();
      if ($approverCount > 0) {
        $offset = random_int(0, max(0, $approverCount - 1));
        $approver = User::role('Approver')->skip($offset)->first();
        if ($approver)
          $assignTo = $approver->id;
      }
    } catch (\Exception $e) {
      // If role query fails, silently continue without assign
      $assignTo = null;
    }

    \App\Models\Request::create([
      'name' => $username,
      'mode' => $request->payment_method,
      'amount' => $amount,
      'payment_amount' => $amount,
      'utr' => $request->utr,
      'payment_from' => $mobile ?? $username,
      'image' => $screenshotPath,
      'status' => 'pending',
      'created_by' => null, // Frontend submission
      'assign_to' => $assignTo,
    ]);

    // Clear session
    Session::forget(['payment_username', 'payment_mobile', 'payment_amount', 'payment_type']);

    return redirect()->route('payment.gateway')->with('success', 'Payment submitted successfully! Your UTR: ' . $request->utr);
  }
}
