<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Request as PaymentRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class Analytics extends Controller
{
  public function index()
  {
    return view('content.dashboard.dashboards-analytics');
  }

  /**
   * Return JSON stats for the dashboard filtered by date and scoped by role.
   * - Admin / Super Admin: all requests
   * - Approver: requests assigned to approver OR created by approver's subordinates
   * - SubApprover: only their own requests (created_by or assign_to)
   */
  public function stats(Request $request)
  {
    $user = auth()->user();

    // Check if all_time is requested
    $allTime = $request->query('all_time') === '1';

    if ($allTime) {
      // Don't apply date filters for all_time
      $start = null;
      $end = null;
    } else {
      // Get dates from request, no defaults to ensure proper filtering
      $start = $request->query('start_date');
      $end = $request->query('end_date');

      // Only set defaults if no parameters provided at all
      if (!$start && !$end && !$allTime) {
        $start = now()->startOfDay()->toDateString();
        $end = now()->endOfDay()->toDateString();
      }
    }

    $q = PaymentRequest::query();

    // Only apply date filters if not requesting all_time and we have dates
    if (!$allTime && $start && $end) {
      $q->whereDate('created_at', '>=', $start);
      $q->whereDate('created_at', '<=', $end);
    }

    if ($user->hasRole('Admin') || $user->hasRole('Super Admin')) {
      // no additional scope
    } elseif ($user->hasRole('Approver')) {
      $subordinates = User::where('created_by', $user->id)->pluck('id')->toArray();
      $allowed = array_merge([$user->id], $subordinates);
      $q->where(function ($qq) use ($allowed) {
        $qq->whereIn('assign_to', $allowed)
          ->orWhereIn('created_by', $allowed);
      });
    } else {
      // SubApprover or any normal user: only their own data
      $q->where(function ($qq) use ($user) {
        $qq->where('created_by', $user->id)
          ->orWhere('assign_to', $user->id);
      });
    }

    $total = (clone $q)->count();
    $pending = (clone $q)->where('status', 'pending')->count();
    $rejected = (clone $q)->where('status', 'rejected')->count();
    $accepted = (clone $q)->where('status', 'accepted')->count();
    $todayRevenue = (clone $q)->where('status', 'accepted')->sum('payment_amount');

    // Top approvers (by total payment_amount) within the scoped query
    $topApprovers = (clone $q)
      ->select('assign_to', DB::raw('SUM(payment_amount) as total'))
      ->whereNotNull('assign_to')
      ->groupBy('assign_to')
      ->orderByDesc('total')
      ->limit(5)
      ->get()
      ->map(function ($row) {
        $user = User::find($row->assign_to);
        return [
          'id' => $row->assign_to,
          'name' => $user ? $user->name : 'Unknown',
          'total' => (float) $row->total,
        ];
      });

    // Series: daily totals + accepted counts for chart
    $seriesData = DB::table('requests')
      ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(payment_amount) as total'), DB::raw('SUM(CASE WHEN status = "accepted" THEN 1 ELSE 0 END) as accepted_count'), DB::raw('COUNT(*) as total_count'));

    // Only apply date filters if not requesting all_time and we have dates
    if (!$allTime && $start && $end) {
      $seriesData->whereDate('created_at', '>=', $start)
        ->whereDate('created_at', '<=', $end);
    }

    // apply same scoping rules to the series query
    if (!($user->hasRole('Admin') || $user->hasRole('Super Admin'))) {
      if ($user->hasRole('Approver')) {
        $subordinates = User::where('created_by', $user->id)->pluck('id')->toArray();
        $allowed = array_merge([$user->id], $subordinates);
        $seriesData->where(function ($qq) use ($allowed) {
          $qq->whereIn('assign_to', $allowed)
            ->orWhereIn('created_by', $allowed);
        });
      } else {
        $seriesData->where(function ($qq) use ($user) {
          $qq->where('created_by', $user->id)
            ->orWhere('assign_to', $user->id);
        });
      }
    }

    $series = $seriesData->groupBy('date')->orderBy('date')->get()->map(function ($r) {
      return [
        'date' => $r->date,
        'total' => (float) $r->total,
        'accepted_count' => (int) $r->accepted_count,
        'total_count' => (int) $r->total_count,
      ];
    })->values();

    // Mode breakdown (e.g., upi, bank, crypto) for a pie chart
    $modeBreakdown = (clone $q)
      ->select('mode', DB::raw('SUM(payment_amount) as total'))
      ->groupBy('mode')
      ->get()
      ->map(function ($r) {
        return [
          'mode' => $r->mode,
          'total' => (float) $r->total,
        ];
      });

    return response()->json([
      'total' => $total,
      'pending' => $pending,
      'rejected' => $rejected,
      'accepted' => $accepted,
      'today_revenue' => (float) $todayRevenue,
      'top_approvers' => $topApprovers,
      'series' => $series,
      'mode_breakdown' => $modeBreakdown,
    ]);
  }
}
