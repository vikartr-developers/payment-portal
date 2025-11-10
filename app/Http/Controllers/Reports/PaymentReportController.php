<?php
namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
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
                $viewUrl = route('requests.view', $row->id);
                return '<a href="' . $viewUrl . '" class="btn btn-sm btn-primary">View</a>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}
