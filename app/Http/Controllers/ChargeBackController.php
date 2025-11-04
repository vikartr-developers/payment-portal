<?php

namespace App\Http\Controllers;

use App\Models\ChargeBack;
use App\Models\Request as PaymentRequest;
use App\Models\User;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\DataTables;

class ChargeBackController extends Controller
{
  public function __construct()
  {
    // Create permissions if not exist
    $permissions = ['chargeback-list', 'chargeback-create', 'chargeback-edit', 'chargeback-delete'];
    foreach ($permissions as $permission) {
      Permission::firstOrCreate(['name' => $permission]);
    }

    // Approver-only for add/store
    // $this->middleware(['role:Approver'])->only(['create', 'store']);

    // Optional: general permissions for listing in future
    // $this->middleware('permission:chargeback-list', ['only' => ['index']]);
  }

  // Minimal list (optional)
  public function index()
  {
    return view('content.apps.chargebacks.list');
  }

  public function create()
  {
    // Show payment orders (payment requests) for dropdown; limit for performance
    $requests = PaymentRequest::orderBy('id', 'desc')->limit(500)->get();

    // dd($requests);
    return view('content.apps.chargebacks.add', compact('requests'));
  }

  public function store(HttpRequest $request)
  {
    // Only Approver can add
    if (!Auth::user() || !Auth::user()->hasRole('Approver')) {
      abort(403);
    }

    $validator = Validator::make($request->all(), [
      'slip' => 'required|file|max:4096',
      'request_id' => 'required|integer|exists:requests,id',
      'reason' => 'required|string|max:2000',
    ]);

    if ($validator->fails()) {
      return back()->withErrors($validator)->withInput();
    }

    $validated = $validator->validated();

    // Fetch payment request to derive amount and user
    $paymentRequest = PaymentRequest::findOrFail($validated['request_id']);

    // Upload slip first
    $slipPath = null;
    if ($request->hasFile('slip')) {
      $slipPath = $request->file('slip')->store('chargebacks/slips', 'public');
    }

    $chargeback = ChargeBack::create([
      'merchant_name' => $paymentRequest->payment_from ?? null, // best-effort source
      'user_id' => $paymentRequest->created_by ?? null,
      'request_id' => $paymentRequest->id,
      'amount' => $paymentRequest->amount ?? 0,
      'reason' => $validated['reason'],
      'slip_path' => $slipPath,
      'status' => 'pending',
      'date' => now(),
      'created_by' => Auth::id(),
    ]);

    return redirect()->route('chargebacks.list')->with('success', 'Charge back created successfully.');
  }

  /**
   * Server-side DataTable for charge backs
   */
  public function dataTable(HttpRequest $request)
  {
    $query = ChargeBack::query()->latest();

    if ($request->filled('status') && $request->status !== 'all') {
      $query->where('status', $request->status);
    }
    if ($request->filled('start_date')) {
      $query->whereDate('created_at', '>=', $request->start_date);
    }
    if ($request->filled('end_date')) {
      $query->whereDate('created_at', '<=', $request->end_date);
    }
    if ($request->filled('search_term')) {
      $term = $request->search_term;
      $query->where(function ($q) use ($term) {
        $q->where('reason', 'like', "%$term%")
          ->orWhere('merchant_name', 'like', "%$term%")
          ->orWhere('request_id', 'like', "%$term%")
          ->orWhere('id', 'like', "%$term%");
      });
    }

    return DataTables::of($query)
      ->addColumn('transaction', function ($cb) {
        return 'TXN-' . str_pad((string) $cb->request_id, 6, '0', STR_PAD_LEFT);
      })
      ->addColumn('user_name', function ($cb) {
        if (!$cb->user_id)
          return '-';
        $user = User::find($cb->user_id);
        if (!$user)
          return '-';
        if (!empty($user->name))
          return $user->name;
        $first = $user->first_name ?? '';
        $last = $user->last_name ?? '';
        return trim($first . ' ' . $last) ?: '-';
      })
      ->addColumn('slip', function ($cb) {
        if (!$cb->slip_path)
          return '<span class="text-muted">-</span>';
        $src = asset('storage/' . $cb->slip_path);
        return '<a href="' . e($src) . '" target="_blank" class="btn btn-sm btn-outline-primary">View</a>';
      })
      ->editColumn('status', function ($cb) {
        return match ($cb->status) {
          'pending' => '<span class="badge bg-label-warning">Pending</span>',
          'accepted' => '<span class="badge bg-label-success">Accepted</span>',
          'rejected' => '<span class="badge bg-label-danger">Rejected</span>',
          default => e($cb->status ?? '-')
        };
      })
      ->rawColumns(['status', 'slip'])
      ->make(true);
  }
}
