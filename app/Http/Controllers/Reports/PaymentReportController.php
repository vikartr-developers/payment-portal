<?php
namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Crypt;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Request as PaymentRequest;

class PaymentReportController extends Controller
{
  /**
   * Show payment reports table
   */
  public function index()
  {
    return view('content.reports.payment-reports');
  }

  /**
   * Data endpoint for DataTables (server-side)
   * Supports optional start_date and end_date query params (YYYY-MM-DD)
   */
  public function data(HttpRequest $request)
  {
    $start = $request->query('start_date');
    $end = $request->query('end_date');

    // Base query: left join users to get approver name
    $query = PaymentRequest::leftJoin('users', 'users.id', '=', 'requests.assign_to')
      ->select([
        'requests.id',
        'requests.account_upi',
        'requests.payment_amount',
        'requests.amount',
        'requests.mode',
        'requests.assign_to',
        'users.name as approver_name',
        'requests.created_at',
      ]);

    if ($start) {
      $query->whereDate('requests.created_at', '>=', $start);
    }
    if ($end) {
      $query->whereDate('requests.created_at', '<=', $end);
    }

    // Charge percent: try env/config, fallback to 4% (0.04)
    $chargePercent = config('app.charge_percent', env('CHARGE_PERCENT', 4)) / 100.0;

    return DataTables::of($query)
      ->editColumn('payment_amount', function ($row) {
        return number_format((float) $row->payment_amount, 2);
      })
      ->addColumn('charges', function ($row) use ($chargePercent) {
        // show percent as string
        return (floatval($chargePercent) > 0) ? (round($chargePercent * 100, 2) . '%') : 'N/A';
      })
      ->addColumn('total_charge', function ($row) use ($chargePercent) {
        $amt = floatval($row->payment_amount ?: $row->amount ?: 0);
        $charge = $amt * $chargePercent;
        return number_format($charge, 2);
      })
      ->addColumn('payment_date', function ($row) {
        return optional($row->created_at)->format('d/m/Y');
      })
      ->addColumn('approver', function ($row) {
        return $row->approver_name ?: '-';
      })
      ->addColumn('account', function ($row) {
        return $row->account_upi ?: '-';
      })
      ->addColumn('action', function ($row) {
        $encryptedId = Crypt::encrypt($row->id);
        return '<button class="btn btn-sm btn-primary view-details-btn" data-id="' . $encryptedId . '">View</button>';
      })
      ->rawColumns(['action'])
      ->make(true);
  }

  /**
   * Get bank account details for a payment request
   */
  public function getBankDetails($id)
  {
    try {
      $id = Crypt::decrypt($id);
      $request = PaymentRequest::leftJoin('bank_managements', function ($join) {
        $join->on('requests.account_upi', '=', DB::raw("COALESCE(bank_managements.account_number, bank_managements.upi_id)"));
      })
        ->leftJoin('users', 'users.id', '=', 'requests.assign_to')
        ->select([
          'requests.*',
          'bank_managements.name as bank_name',
          'bank_managements.bank_name as bank_full_name',
          'bank_managements.branch_name',
          'bank_managements.account_number',
          'bank_managements.account_holder_name',
          'bank_managements.ifsc_code',
          'bank_managements.upi_id',
          'bank_managements.upi_number',
          'bank_managements.type as account_type',
          'users.name as approver_name',
        ])
        ->where('requests.id', $id)
        ->first();

      if (!$request) {
        return response()->json(['error' => 'Request not found'], 404);
      }

      $chargePercent = config('app.charge_percent', env('CHARGE_PERCENT', 4)) / 100.0;
      $amount = floatval($request->payment_amount ?: $request->amount ?: 0);
      $charge = $amount * $chargePercent;

      return response()->json([
        'success' => true,
        'data' => [
          'request_id' => $request->id,
          'utr' => $request->utr,
          'amount' => number_format($amount, 2),
          'charge_percent' => round($chargePercent * 100, 2),
          'charge_amount' => number_format($charge, 2),
          'payment_date' => optional($request->created_at)->format('d/m/Y H:i'),
          'status' => $request->status,
          'account_upi' => $request->account_upi,
          'payment_from' => $request->payment_from,
          'mode' => $request->mode,
          'approver_name' => $request->approver_name ?: '-',
          // Bank details
          'bank_name' => $request->bank_name ?: '-',
          'bank_full_name' => $request->bank_full_name ?: '-',
          'branch_name' => $request->branch_name ?: '-',
          'account_number' => $request->account_number ?: '-',
          'account_holder_name' => $request->account_holder_name ?: '-',
          'ifsc_code' => $request->ifsc_code ?: '-',
          'upi_id' => $request->upi_id ?: '-',
          'upi_number' => $request->upi_number ?: '-',
          'account_type' => $request->account_type ?: '-',
        ]
      ]);
    } catch (\Exception $e) {
      return response()->json(['error' => 'Failed to fetch details: ' . $e->getMessage()], 500);
    }
  }

  /**
   * Export filtered payment reports as CSV (Excel-friendly)
   */
  public function export(HttpRequest $request)
  {
    $start = $request->query('start_date');
    $end = $request->query('end_date');

    $query = PaymentRequest::leftJoin('users', 'users.id', '=', 'requests.assign_to')
      ->select([
        'requests.id',
        'requests.account_upi',
        'requests.payment_amount',
        'requests.amount',
        'requests.mode',
        'requests.assign_to',
        'users.name as approver_name',
        'requests.created_at',
      ]);

    if ($start) {
      $query->whereDate('requests.created_at', '>=', $start);
    }
    if ($end) {
      $query->whereDate('requests.created_at', '<=', $end);
    }

    $chargePercent = config('app.charge_percent', env('CHARGE_PERCENT', 4)) / 100.0;

    $rows = $query->orderBy('requests.created_at', 'desc')->get();

    $fileName = 'payment-reports-' . now()->format('Ymd-His') . '.csv';
    $headers = [
      'Content-Type' => 'text/csv',
      'Content-Disposition' => "attachment; filename=\"$fileName\"",
    ];

    $callback = function () use ($rows, $chargePercent) {
      $out = fopen('php://output', 'w');
      // BOM for Excel
      fprintf($out, "%s", chr(0xEF) . chr(0xBB) . chr(0xBF));

      // header
      fputcsv($out, ['Account', 'Total Deposit', 'Charges (%)', 'Total Charge', 'Approver', 'Payment Date']);

      foreach ($rows as $row) {
        $account = $row->account_upi ?: '-';
        $payment = is_null($row->payment_amount) ? ($row->amount ?? 0) : $row->payment_amount;
        $chargesLabel = (floatval($chargePercent) > 0) ? (round($chargePercent * 100, 2) . '%') : 'N/A';
        $totalCharge = number_format(($payment * $chargePercent), 2, '.', '');
        $approver = $row->approver_name ?: '-';
        $paymentDate = optional($row->created_at)->format('d/m/Y');

        fputcsv($out, [$account, number_format((float) $payment, 2, '.', ''), $chargesLabel, $totalCharge, $approver, $paymentDate]);
      }

      fclose($out);
    };

    return response()->stream($callback, 200, $headers);
  }
}
