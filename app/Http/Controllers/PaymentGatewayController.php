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

    // Store approver and account in session for later use
    Session::put('payment_approver_id', $approver);
    Session::put('payment_account_id', $account);

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
    $pageConfigs = ['myLayout' => 'front'];

    if (!$username || !$amount) {
      return redirect()->route('payment.gateway')->with('error', 'Session expired. Please start again.');
    }

    Session::put('payment_type', $request->payment_type);

    if ($request->payment_type === 'crypto') {
      return view('content.front-pages.payment-gateway.step3-crypto', compact('username', 'amount', 'pageConfigs'));
    }

    // Check if this is an approver-specific link
    $approverId = Session::get('payment_approver_id');
    $accountId = Session::get('payment_account_id');

    $upiAccount = null;
    $bankAccount = null;

    if ($approverId && $accountId) {
      // Use the specific account provided by approver
      $account = BankManagement::find($accountId);
      if ($account) {
        if ($account->type === 'upi') {
          $upiAccount = $account;
        } else {
          $bankAccount = $account;
        }
      }
    } else {
      // Fetch random UPI and Bank accounts from bank_managements
      // Use count + random offset to reliably pick a random row without ORDER BY RAND()
      $upiCount = BankManagement::where('type', 'upi')->where('status', 'active')->whereNull('deleted_at')->count();
      if ($upiCount > 0) {
        $upiOffset = random_int(0, max(0, $upiCount - 1));
        $upiAccount = BankManagement::where('type', 'upi')->where('status', 'active')->whereNull('deleted_at')->skip($upiOffset)->first();
      }

      $bankCount = BankManagement::where('type', 'bank')->where('status', 'active')->whereNull('deleted_at')->count();
      if ($bankCount > 0) {
        $bankOffset = random_int(0, max(0, $bankCount - 1));
        $bankAccount = BankManagement::where('type', 'bank')->where('status', 'active')->whereNull('deleted_at')->skip($bankOffset)->first();
      }
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
      'screenshot' => 'required|image',
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

    // Store screenshot with a stable, unique filename on the public disk
    try {
      $file = $request->file('screenshot');
      $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
      $screenshotPath = $file->storeAs('payment_screenshots', $filename, 'public'); // returns path relative to disk
    } catch (\Exception $e) {
      return redirect()->route('payment.gateway')->with('error', 'Failed to save screenshot. Please try again.');
    }

    // Create payment request record
    // Check if this is from an approver-specific link
    $approverId = Session::get('payment_approver_id');
    $accountId = Session::get('payment_account_id');

    $assignTo = null;

    if ($approverId && $accountId) {
      // If payment came through approver link, assign to that approver or their subapprovers
      $account = BankManagement::find($accountId);
      if ($account) {
        // Check if account has assigned sub approvers
        $subApprovers = $account->subApprovers()->pluck('users.id')->toArray();
        if (!empty($subApprovers)) {
          // Randomly assign to one of the sub approvers
          $assignTo = $subApprovers[array_rand($subApprovers)];
        } else {
          // Assign to the approver who created the account
          $assignTo = $account->created_by;
        }
      }
    } else {
      // Try to assign this new request to a random SubApprover (if any)
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

    // Clear session including approver data
    Session::forget(['payment_username', 'payment_mobile', 'payment_amount', 'payment_type', 'payment_approver_id', 'payment_account_id']);

    return redirect()->route('payment.gateway')->with('success', 'Payment submitted successfully! Your UTR: ' . $request->utr);
  }
}
