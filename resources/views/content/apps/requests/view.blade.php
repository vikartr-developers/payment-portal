@extends('layouts/layoutMaster')

@section('title', 'View Deposit Request')

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
                            <dt class="col-sm-3">Image</dt>
                            <dd class="col-sm-9">
                                <img src="{{ asset('storage/' . $requestModel->image) }}" width="200"
                                    alt="Payment Image">
                            </dd>
                        @endif
                    </dl>
                    <a href="{{ route('requests.list') }}" class="btn btn-secondary">Back to List</a>
                </div>
            </div>
        </div>
    </div>
@endsection
