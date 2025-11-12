@extends('layouts/layoutMaster')

@section('title', 'View Account Details')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Account Details</h4>
            <div>
                <a href="{{ route('bank-management.edit', $record->id) }}" class="btn btn-primary">
                    <i class="ti ti-edit me-1"></i>Edit
                </a>
                <a href="{{ route('bank-management.index') }}" class="btn btn-secondary">
                    <i class="ti ti-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-3">General Information</h5>
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Name:</th>
                            <td>{{ $record->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Type:</th>
                            <td>
                                <span class="badge bg-{{ $record->type == 'bank' ? 'primary' : 'info' }}">
                                    {{ strtoupper($record->type) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="badge bg-{{ $record->status == 'active' ? 'success' : 'danger' }}">
                                    {{ ucfirst($record->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Daily Max Amount:</th>
                            <td>{{ $record->daily_max_amount ? number_format($record->daily_max_amount, 2) : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Max Transaction Amount:</th>
                            <td>{{ $record->max_transaction_amount ? number_format($record->max_transaction_amount, 2) : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Daily Max Transaction Count:</th>
                            <td>{{ $record->daily_max_transaction_count ?? '-' }}</td>
                        </tr>
                        {{-- <tr>
                            <th>Deposit Limit:</th>
                            <td>{{ $record->deposit_limit ? number_format($record->deposit_limit, 2) : '-' }}</td>
                        </tr> --}}
                    </table>
                </div>

                <div class="col-md-6">
                    @if ($record->type == 'bank')
                        <h5 class="mb-3">Bank Account Details</h5>
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Account Holder Name:</th>
                                <td>{{ $record->account_holder_name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Bank Name:</th>
                                <td>{{ $record->bank_name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Account Number:</th>
                                <td>{{ $record->account_number ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>IFSC Code:</th>
                                <td>{{ $record->ifsc_code ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Branch Name:</th>
                                <td>{{ $record->branch_name ?? '-' }}</td>
                            </tr>
                        </table>
                    @else
                        <h5 class="mb-3">UPI Details</h5>
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">UPI ID:</th>
                                <td>{{ $record->upi_id ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>UPI Number:</th>
                                <td>{{ $record->upi_number ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Merchant UPI:</th>
                                <td>
                                    @if ($record->is_merchant_upi)
                                        <span class="badge bg-success">
                                            <i class="ti ti-check me-1"></i>Yes
                                        </span>
                                        <small class="text-muted d-block mt-1">Works faster with admin approval</small>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    @endif
                </div>
            </div>

            <hr class="my-4">

            <div class="row">
                <div class="col-12">
                    <h5 class="mb-3">Assigned Sub Approvers</h5>
                    @if ($record->subApprovers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($record->subApprovers as $index => $approver)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $approver->name }}</td>
                                            <td>{{ $approver->email }}</td>
                                            <td>
                                                <span class="badge bg-success">Active</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-2"></i>No sub approvers assigned to this account yet.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
