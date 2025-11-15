@extends('layouts/layoutMaster')

@section('title', 'View Deposit Request')
@php
    use Illuminate\Support\Facades\Storage;
@endphp
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Deposit Request Details</h4>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">Name</dt>
                        <dd class="col-sm-9">{{ $requestModel->name }}</dd>

                        <dt class="col-sm-3">Mode</dt>
                        <dd class="col-sm-9">{{ ucfirst($requestModel->mode) }}</dd>

                        <dt class="col-sm-3">Amount</dt>
                        <dd class="col-sm-9">{{ $requestModel->amount }}</dd>

                        <dt class="col-sm-3">Payment Amount</dt>
                        <dd class="col-sm-9">{{ $requestModel->payment_amount }}</dd>

                        <dt class="col-sm-3">UTR</dt>
                        <dd class="col-sm-9">{{ $requestModel->utr }}</dd>

                        <dt class="col-sm-3">Payment From</dt>
                        <dd class="col-sm-9">{{ $requestModel->payment_from }}</dd>

                        <dt class="col-sm-3">Account/UPI</dt>
                        <dd class="col-sm-9">{{ $requestModel->account_upi }}</dd>

                        <dt class="col-sm-3">Status</dt>
                        <dd class="col-sm-9">
                            @if ($requestModel->status === 'pending')
                                <span class="badge bg-label-warning">Pending</span>
                            @elseif($requestModel->status === 'accepted')
                                <span class="badge bg-label-success">Accepted</span>
                            @elseif($requestModel->status === 'rejected')
                                <span class="badge bg-label-danger">Rejected</span>
                            @else
                                {{ $requestModel->status }}
                            @endif
                        </dd>

                        <dt class="col-sm-3">Created At</dt>
                        <dd class="col-sm-9">{{ $requestModel->created_at }}</dd>

                        <dt class="col-sm-3">Updated At</dt>
                        <dd class="col-sm-9">{{ $requestModel->updated_at }}</dd>
                        @if ($requestModel->image)
                            @php
                                $imgPath = $requestModel->image;
                                $imgExists = Storage::disk('public')->exists($imgPath);
                                // Use Laravel's asset() helper with proper storage path
$imgUrl = $imgExists ? asset('storage/' . $imgPath) : null;
                            @endphp
                            <dt class="col-sm-3">Image</dt>
                            <dd class="col-sm-9">
                                @if ($imgExists)
                                    <a href="{{ $imgUrl }}" target="_blank" rel="noopener">
                                        <img src="{{ $imgUrl }}" width="200" alt="Payment Image">
                                    </a>
                                @else
                                    <div class="text-muted">Image not found on disk: <code>{{ $imgPath }}</code></div>
                                @endif
                            </dd>
                        @endif


                        {{--
                        @if ($requestModel->image)
                            @php
                                $imgPath = $requestModel->image;
                                $disk = \Illuminate\Support\Facades\Storage::disk('public');
                                // Prefer the exact storage path if the file exists there (as requested)
                                $storageFile = storage_path('app/public/' . $imgPath);
                                if (file_exists($storageFile)) {
                                    // User requested path: /storage/app/public/payment_screenshots/...
                                    $imgUrl = asset('storage/app/public/' . $imgPath);
                                    $imgExists = true;
                                } elseif ($disk->exists($imgPath)) {
                                    // Default public storage link path: /storage/payment_screenshots/...
                                    $imgUrl = asset('storage/' . $imgPath);
                                    $imgExists = true;
                                } else {
                                    $imgUrl = asset('storage/' . $imgPath);
                                    $imgExists = false;
                                }
                            @endphp
                            <dt class="col-sm-3">Image</dt>
                            <dd class="col-sm-9">
                                @if ($imgExists)
                                    <a href="{{ $imgUrl }}" target="_blank" rel="noopener">
                                        <img src="{{ $imgUrl }}" width="200" alt="Payment Image">
                                    </a>
                                @else
                                    <div class="text-muted">Image not found on disk: <code>{{ $imgPath }}</code></div>
                                @endif
                            </dd>
                        @endif --}}
                    </dl>
                    <a href="{{ route('requests.list') }}" class="btn btn-secondary">Back to List</a>
                </div>
            </div>
        </div>
    </div>
@endsection
