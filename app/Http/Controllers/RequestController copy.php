<?php
namespace App\Http\Controllers;

use App\Models\Request;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RequestController extends Controller
{
  public function index()
  {
    $requests = Request::latest()->paginate(20);
    return view('content.apps.requests.list', compact('requests'));
  }

  public function create()
  {
    return view('content.apps.requests.add');
  }

  public function store(HttpRequest $request)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string|max:255',
      'mode' => 'required|string|max:100',
      'amount' => 'required|numeric|min:0',
      'payment_amount' => 'nullable|numeric|min:0',
      'utr' => 'nullable|string|max:100',
      'payment_from' => 'nullable|string|max:255',
      'account_upi' => 'nullable|string|max:255',
      'image' => 'nullable|image|max:2048',
      'status' => 'nullable|in:pending,accepted,rejected'
    ]);

    // If UTR exists in any request, auto-reject this request
    if ($request->utr && Request::where('utr', $request->utr)->exists()) {
      return back()->withErrors(['utr' => 'This UTR already exists. Payment request auto-rejected.'])->withInput();
    }

    $data = $validator->validated();
    $data['created_by'] = Auth::id();

    // Handle image upload
    if ($request->hasFile('image')) {
      $data['image'] = $request->file('image')->store('payment_images', 'public');
    }

    $requestModel = Request::create($data);
    return redirect()->route('requests.list')->with('success', 'Payment request created!');
  }

  public function edit($id)
  {
    $requestModel = Request::findOrFail($id);
    return view('content.apps.requests.edit', compact('requestModel'));
  }

  public function update(HttpRequest $request, $id)
  {
    $requestModel = Request::findOrFail($id);

    $validator = Validator::make($request->all(), [
      'name' => 'required|string|max:255',
      'mode' => 'required|string|max:100',
      'amount' => 'required|numeric|min:0',
      'payment_amount' => 'nullable|numeric|min:0',
      'utr' => 'nullable|string|max:100',
      'payment_from' => 'nullable|string|max:255',
      'account_upi' => 'nullable|string|max:255',
      'image' => 'nullable|image|max:2048',
      'status' => 'nullable|in:pending,accepted,rejected'
    ]);

    // If UTR exists in another request, auto-reject
    if ($request->utr && Request::where('utr', $request->utr)->where('id', '!=', $id)->exists()) {
      return back()->withErrors(['utr' => 'This UTR already exists. Payment request auto-rejected.'])->withInput();
    }

    $data = $validator->validated();
    $data['updated_by'] = Auth::id();

    // Handle image upload
    if ($request->hasFile('image')) {
      $data['image'] = $request->file('image')->store('payment_images', 'public');
    }

    $requestModel->update($data);
    return redirect()->route('requests.list')->with('success', 'Payment request updated!');
  }
  public function dataTable()
  {
    $requests = Request::where('created_by', auth()->user()->id)->select(['id', 'name', 'mode', 'amount', 'utr', 'status', 'created_at']);

    return datatables()->of($requests)
      ->addColumn('action', function ($request) {
        return '<a href="' . route('requests.edit', $request->id) . '" class="btn btn-sm btn-warning">Edit</a>';
      })
      ->rawColumns(['action'])
      ->make(true);
  }
}
