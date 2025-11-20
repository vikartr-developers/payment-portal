<?php

namespace App\Http\Controllers;

use App\Models\BankManagement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

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
   * Approver-specific payment link with pre-selected account
   */
  public function approverPaymentLink(Request $request, $approver, $account)
  {
    $pageConfigs = ['myLayout' => 'front'];

    // Find user by slug or ID
    $user = User::where('slug', $approver)->first();
    if (!$user) {
      // Fallback to ID if slug not found (for backward compatibility)
      $user = User::find($approver);
    }

    if (!$user) {
      abort(404, 'Invalid payment link');
    }

    // Find account by short_code first, then try decryption for backward compatibility
    $bankAccount = BankManagement::where('short_code', $account)->first();

    if (!$bankAccount) {
      try {
        // Try to decrypt as fallback for old links
        $accountId = decrypt($account);
        $bankAccount = BankManagement::find($accountId);
      } catch (\Exception $e) {
        abort(404, 'Invalid account link');
      }
    }

    if (!$bankAccount) {
      abort(404, 'Account not found');
    }

    // Store approver and account in session for later use
    Session::put('payment_approver_id', $user->id);
    Session::put('payment_account_id', $bankAccount->id);

    return view('content.front-pages.payment-gateway.step1', [
      'pageConfigs' => $pageConfigs,
      'approverMode' => true
    ]);
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
    $preSelectedAccountId = Session::get('payment_account_id');
    $pageConfigs = ['myLayout' => 'front'];
    // dd($preSelectedAccountId);

    if (!$username || !$amount) {
      return redirect()->route('payment.gateway')->with('error', 'Session expired. Please start again.');
    }

    Session::put('payment_type', $request->payment_type);

    if ($request->payment_type === 'crypto') {
      return view('content.front-pages.payment-gateway.step3-crypto', compact('username', 'amount', 'pageConfigs'));
    }

    // Use pre-selected account if available, otherwise fetch all active accounts
    if ($preSelectedAccountId) {
      $selectedAccount = BankManagement::find($preSelectedAccountId);
      if (!$selectedAccount) {
        return redirect()->route('payment.gateway')->with('error', 'Invalid account. Please start again.');
      }
      return view('content.front-pages.payment-gateway.step3-regular', compact('username', 'amount', 'selectedAccount', 'pageConfigs'));
    }

    // Fetch all active accounts (for non-approver links)
    $accounts = BankManagement::where('status', 'active')
      ->whereNull('deleted_at')
      ->get();

    return view('content.front-pages.payment-gateway.step3-regular', compact('username', 'amount', 'accounts', 'pageConfigs'));
  }

  /**
   * Process final payment submission
   */
  public function processPayment(Request $request)
  {
    $request->validate([
      'payment_method' => 'required|in:upi,bank,crypto',
      'utr' => 'required|string|max:12|min:12',
      'screenshot' => 'required|image',
      'selected_account_id' => 'nullable|exists:bank_managements,id',
    ]);

    $username = Session::get('payment_username');
    $mobile = Session::get('payment_mobile');
    $amount = Session::get('payment_amount');
    $paymentType = Session::get('payment_type');

    if (!$username || !$amount) {
      return redirect()->route('payment.gateway')->with('error', 'Session expired. Please start again.');
    }

    // Check if UTR already exists in the system
    $existingRequest = \App\Models\Request::where('utr', $request->utr)->first();
    if ($existingRequest) {
      return redirect()->route('payment.gateway')->with('error', 'This UTR number has already been used. Please check your transaction or contact support.');
    }

    // Store screenshot in public/payment_screenshots
    if ($request->hasFile('screenshot')) {
      try {
        $file = $request->file('screenshot');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        // Ensure public directory exists
        if (!file_exists(public_path('payment_screenshots'))) {
          mkdir(public_path('payment_screenshots'), 0755, true);
        }

        $file->move(public_path('payment_screenshots'), $filename);
        $screenshotPath = 'payment_screenshots/' . $filename;
      } catch (\Exception $e) {
        \Log::error('Screenshot upload failed: ' . $e->getMessage());
        return redirect()->route('payment.gateway')->with('error', 'Failed to save screenshot. Please try again.');
      }
    } else {
      return redirect()->route('payment.gateway')->with('error', 'Screenshot is required.');
    }

    // Create payment request record
    // Assign to a random SubApprover (if any)
    $assignTo = null;
    try {
      $approverCount = User::role('SubApprover')->count();
      if ($approverCount > 0) {
        $offset = random_int(0, max(0, $approverCount - 1));
        $approver = User::role('SubApprover')->skip($offset)->first();
        if ($approver)
          $assignTo = $approver->id;
      }
    } catch (\Exception $e) {
      // If role query fails, silently continue without assign
      $assignTo = null;
    }

    // Get selected account details and extract account_upi based on payment method
    $accountUpi = null;
    $selectedAccountId = $request->input('selected_account_id');

    if ($selectedAccountId) {
      $selectedAccount = BankManagement::find($selectedAccountId);
      if ($selectedAccount) {
        // Use payment_method to determine which identifier to use
        $accountUpi = $request->payment_method === 'upi'
          ? $selectedAccount->upi_id
          : $selectedAccount->account_number;
      }
    }

    \App\Models\Request::create([
      'name' => $username,
      'mode' => $request->payment_method,
      'amount' => $amount,
      'payment_amount' => $amount,
      'utr' => $request->utr,
      'payment_from' => $mobile ?? $username,
      'account_upi' => $accountUpi,
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
