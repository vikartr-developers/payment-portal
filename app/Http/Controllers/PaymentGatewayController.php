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

    // Check if there's an existing pending request in session
    $sessionId = Session::getId();
    $existingRequest = \App\Models\Request::where('session_id', $sessionId)
      ->where('process_status', '!=', 'completed')
      ->where('expires_at', '>', now())
      ->first();

    if (!$existingRequest) {
      // Create new request record with 7-minute timer
      $startedAt = now();
      $expiresAt = now()->addMinutes(7);

      $requestRecord = \App\Models\Request::create([
        'name' => $request->username,
        'payment_from' => $request->mobile ?? $request->username,
        'amount' => $request->amount,
        'payment_amount' => $request->amount,
        'status' => 'progress',
        'mode' => '',
        'process_status' => 'step1',
        'started_at' => $startedAt,
        'expires_at' => $expiresAt,
        'session_id' => $sessionId,
      ]);

      Session::put('payment_request_id', $requestRecord->id);
    } else {
      $requestRecord = $existingRequest;
      Session::put('payment_request_id', $existingRequest->id);
    }

    // Store approver info in session if exists
    $approverId = Session::get('payment_approver_id');
    $accountId = Session::get('payment_account_id');

    if ($approverId) {
      Session::put('payment_approver_id', $approverId);
    }
    if ($accountId) {
      Session::put('payment_account_id', $accountId);
    }

    // Generate transaction ID and redirect to step 2 with encrypted transaction ID
    $encryptedId = encrypt($requestRecord->id);
    $transactionId = 'TXN-' . str_pad((string) $requestRecord->id, 6, '0', STR_PAD_LEFT);

    return redirect()->route('payment.select-method-view', ['transaction_id' => $encryptedId]);
  }

  /**
   * Show step 2 page with transaction ID
   */
  public function showSelectMethod($transaction_id)
  {
    // Decrypt the transaction ID
    try {
      $id = decrypt($transaction_id);
    } catch (\Exception $e) {
      return redirect()->route('payment.gateway')->with('error', 'Invalid transaction link. Please start again.');
    }

    $requestRecord = \App\Models\Request::find($id);

    if (!$requestRecord) {
      return redirect()->route('payment.gateway')->with('error', 'Invalid transaction. Please start again.');
    }

    // Check if request has expired
    if ($requestRecord->expires_at && now()->gt($requestRecord->expires_at)) {
      $requestRecord->update(['process_status' => 'expired']);
      return redirect()->route('payment.gateway')->with('error', 'Your session has expired (7 minutes). Please start again.');
    }

    // Check if already completed
    if ($requestRecord->process_status === 'completed') {
      return redirect()->route('payment.status', ['transaction_id' => $transaction_id]);
    }

    $pageConfigs = ['myLayout' => 'front'];
    $displayTransactionId = 'TXN-' . str_pad((string) $requestRecord->id, 6, '0', STR_PAD_LEFT);

    return view('content.front-pages.payment-gateway.step2', [
      'pageConfigs' => $pageConfigs,
      'transaction_id' => $transaction_id,
      'display_transaction_id' => $displayTransactionId,
      'requestRecord' => $requestRecord
    ]);
  }

  /**
   * Handle payment type selection and redirect to step 3
   */
  public function selectPaymentType(Request $request)
  {
    $request->validate([
      'payment_type' => 'required|in:regular,crypto',
      'transaction_id' => 'required|string',
    ]);

    // Decrypt to validate transaction exists
    try {
      $id = decrypt($request->transaction_id);
      $requestRecord = \App\Models\Request::find($id);

      if (!$requestRecord) {
        return redirect()->route('payment.gateway')->with('error', 'Invalid transaction. Please start again.');
      }
    } catch (\Exception $e) {
      return redirect()->route('payment.gateway')->with('error', 'Invalid transaction link. Please start again.');
    }

    // Redirect to step 3 with transaction ID and payment type in URL
    return redirect()->route('payment.show-details', [
      'transaction_id' => $request->transaction_id,
      'payment_type' => $request->payment_type
    ]);
  }

  /**
   * Step 3: Show payment details based on selected method
   */
  public function showPaymentDetails($transaction_id, $payment_type)
  {
    // Validate payment type
    if (!in_array($payment_type, ['regular', 'crypto'])) {
      return redirect()->route('payment.gateway')->with('error', 'Invalid payment type.');
    }

    // Decrypt the transaction ID
    try {
      $id = decrypt($transaction_id);
    } catch (\Exception $e) {
      return redirect()->route('payment.gateway')->with('error', 'Invalid transaction link. Please start again.');
    }

    $requestRecord = \App\Models\Request::find($id);

    if (!$requestRecord) {
      return redirect()->route('payment.gateway')->with('error', 'Invalid transaction. Please start again.');
    }

    // Check if request has expired
    if ($requestRecord->expires_at && now()->gt($requestRecord->expires_at)) {
      $requestRecord->update(['process_status' => 'expired']);
      return redirect()->route('payment.gateway')->with('error', 'Your session has expired (7 minutes). Please start again.');
    }

    // Check if already completed
    if ($requestRecord->process_status === 'completed') {
      return redirect()->route('payment.status', ['transaction_id' => $transaction_id]);
    }

    $username = $requestRecord->name;
    $amount = $requestRecord->amount;
    $preSelectedAccountId = Session::get('payment_account_id');
    $pageConfigs = ['myLayout' => 'front'];

    // Update process status to step2 and store payment type
    if ($requestRecord->process_status === 'step1') {
      $requestRecord->update(['process_status' => 'step2']);
    }

    $displayTransactionId = 'TXN-' . str_pad((string) $requestRecord->id, 6, '0', STR_PAD_LEFT);

    if ($payment_type === 'crypto') {
      return view('content.front-pages.payment-gateway.step3-crypto', [
        'username' => $username,
        'amount' => $amount,
        'pageConfigs' => $pageConfigs,
        'transaction_id' => $transaction_id,
        'display_transaction_id' => $displayTransactionId,
        'requestRecord' => $requestRecord
      ]);
    }

    // Use pre-selected account if available, otherwise fetch all active accounts
    if ($preSelectedAccountId) {
      $selectedAccount = BankManagement::find($preSelectedAccountId);
      if (!$selectedAccount) {
        return redirect()->route('payment.gateway')->with('error', 'Invalid account. Please start again.');
      }
      return view('content.front-pages.payment-gateway.step3-regular', [
        'username' => $username,
        'amount' => $amount,
        'selectedAccount' => $selectedAccount,
        'pageConfigs' => $pageConfigs,
        'transaction_id' => $transaction_id,
        'display_transaction_id' => $displayTransactionId,
        'requestRecord' => $requestRecord
      ]);
    }

    // Fetch all active accounts (for non-approver links)
    $accounts = BankManagement::where('status', 'active')
      ->whereNull('deleted_at')
      ->get();

    return view('content.front-pages.payment-gateway.step3-regular', [
      'username' => $username,
      'amount' => $amount,
      'accounts' => $accounts,
      'pageConfigs' => $pageConfigs,
      'transaction_id' => $transaction_id,
      'display_transaction_id' => $displayTransactionId,
      'requestRecord' => $requestRecord
    ]);
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
      'transaction_id' => 'required|string',
    ]);

    // Decrypt the transaction ID
    try {
      $id = decrypt($request->transaction_id);
    } catch (\Exception $e) {
      return redirect()->route('payment.gateway')->with('error', 'Invalid transaction link. Please start again.');
    }

    $requestRecord = \App\Models\Request::find($id);

    if (!$requestRecord) {
      return redirect()->route('payment.gateway')->with('error', 'Invalid transaction. Please start again.');
    }

    // Check if request has expired
    if ($requestRecord->expires_at && now()->gt($requestRecord->expires_at)) {
      $requestRecord->update(['process_status' => 'expired']);
      return redirect()->route('payment.gateway')->with('error', 'Your session has expired (7 minutes). Please start again.');
    }

    $username = $requestRecord->name;
    $mobile = $requestRecord->payment_from;
    $amount = $requestRecord->amount;
    $paymentType = $request->payment_method;

    // Check if UTR already exists in completed requests
    $existingRequest = \App\Models\Request::where('utr', $request->utr)
      ->where('process_status', 'completed')
      ->first();
    if ($existingRequest) {
      return redirect()->route('payment.gateway')->with('error', 'This UTR number has already been used. Please check your transaction or contact support.');
    }

    // requestRecord is already loaded above

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

    // Get selected account details and extract account_upi based on payment method
    $accountUpi = null;
    $assignTo = null;
    $selectedAccountId = $request->input('selected_account_id');

    if ($selectedAccountId) {
      $selectedAccount = BankManagement::find($selectedAccountId);
      if ($selectedAccount) {
        // Use payment_method to determine which identifier to use
        $accountUpi = $request->payment_method === 'upi'
          ? $selectedAccount->upi_id
          : $selectedAccount->account_number;

        // Assign to the user who created this bank account
        $assignTo = $selectedAccount->created_by;
      }
    }

    // If no account selected or no owner found, assign to a random SubApprover (if any)
    if (!$assignTo) {
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

    // Update existing request
    $requestRecord->update([
      'mode' => $request->payment_method,
      'utr' => $request->utr,
      'account_upi' => $accountUpi,
      'image' => $screenshotPath,
      'status' => 'pending',
      'process_status' => 'completed',
      'assign_to' => $assignTo,
    ]);

    // Clear session data
    Session::forget(['payment_username', 'payment_mobile', 'payment_amount', 'payment_type', 'payment_request_id', 'payment_approver_id', 'payment_account_id']);

    // Redirect to transaction status page with encrypted ID
    $encryptedId = encrypt($requestRecord->id);
    return redirect()->route('payment.status', ['transaction_id' => $encryptedId])
      ->with('success', 'Payment submitted successfully!');
  }

  /**
   * Show transaction status page
   */
  public function checkStatus($transaction_id)
  {
    // Decrypt the transaction ID
    try {
      $id = decrypt($transaction_id);
    } catch (\Exception $e) {
      abort(404, 'Invalid transaction link');
    }

    $request = \App\Models\Request::find($id);

    if (!$request) {
      abort(404, 'Transaction not found');
    }

    $pageConfigs = ['myLayout' => 'front'];
    $displayTransactionId = 'TXN-' . str_pad((string) $request->id, 6, '0', STR_PAD_LEFT);

    return view('content.front-pages.payment-gateway.status', [
      'pageConfigs' => $pageConfigs,
      'request' => $request,
      'transaction_id' => $transaction_id,
      'display_transaction_id' => $displayTransactionId
    ]);
  }

  /**
   * Upload proof for existing transaction
   */
  public function uploadProof(Request $httpRequest, $transaction_id)
  {
    $httpRequest->validate([
      'utr' => 'required|string|max:12|min:12',
      'screenshot' => 'required|image|max:2048',
    ]);

    // Decrypt the transaction ID
    try {
      $id = decrypt($transaction_id);
    } catch (\Exception $e) {
      return back()->with('error', 'Invalid transaction link');
    }

    $request = \App\Models\Request::find($id);

    if (!$request) {
      return back()->with('error', 'Transaction not found');
    }

    // Check if UTR already exists in other requests
    $existingRequest = \App\Models\Request::where('utr', $httpRequest->utr)
      ->where('id', '!=', $id)
      ->first();

    if ($existingRequest) {
      return back()->with('error', 'This UTR number has already been used in another transaction.');
    }

    // Upload screenshot
    if ($httpRequest->hasFile('screenshot')) {
      try {
        $file = $httpRequest->file('screenshot');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        if (!file_exists(public_path('payment_screenshots'))) {
          mkdir(public_path('payment_screenshots'), 0755, true);
        }

        $file->move(public_path('payment_screenshots'), $filename);
        $screenshotPath = 'payment_screenshots/' . $filename;

        // Update request
        $request->update([
          'utr' => $httpRequest->utr,
          'image' => $screenshotPath,
          'status' => 'pending',
        ]);

        return back()->with('success', 'Payment proof uploaded successfully! Waiting for approval.');
      } catch (\Exception $e) {
        \Log::error('Screenshot upload failed: ' . $e->getMessage());
        return back()->with('error', 'Failed to upload screenshot. Please try again.');
      }
    }

    return back()->with('error', 'Screenshot is required.');
  }

  /**
   * Get transaction status (AJAX)
   */
  public function getStatus($transaction_id)
  {
    // Decrypt the transaction ID
    try {
      $id = decrypt($transaction_id);
    } catch (\Exception $e) {
      return response()->json(['success' => false, 'message' => 'Invalid transaction link'], 404);
    }

    $request = \App\Models\Request::find($id);

    if (!$request) {
      return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
    }

    return response()->json([
      'success' => true,
      'status' => $request->status,
      'has_utr' => !empty($request->utr),
      'has_image' => !empty($request->image),
      'amount' => $request->amount,
      'created_at' => $request->created_at ? $request->created_at->format('Y-m-d H:i:s') : null,
      'accepted_at' => $request->accepted_at ? $request->accepted_at->format('Y-m-d H:i:s') : null,
      'rejected_at' => $request->rejected_at ? $request->rejected_at->format('Y-m-d H:i:s') : null,
    ]);
  }
}
