@extends('layouts/layoutMaster')

@section('title', isset($item) ? 'Edit Withdrawal' : 'Create Withdrawal')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST"
                action="{{ isset($item) ? route('withdrawals.update', $item->id) : route('withdrawals.store') }}">
                @csrf
                @if (isset($item))
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <label class="form-label">Account Holder Name</label>
                    <input type="text" name="account_holder_name" class="form-control" required
                        value="{{ old('account_holder_name', $item->account_holder_name ?? '') }}">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Account Number</label>
                        <input type="text" name="account_number" class="form-control" required
                            value="{{ old('account_number', $item->account_number ?? '') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Confirm Account Number</label>
                        <input type="text" name="confirm_account_number" class="form-control" required
                            value="{{ old('confirm_account_number', $item->confirm_account_number ?? '') }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Branch Name</label>
                    <input type="text" name="branch_name" class="form-control"
                        value="{{ old('branch_name', $item->branch_name ?? '') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">IFSC Code</label>
                    <input type="text" name="ifsc_code" class="form-control"
                        value="{{ old('ifsc_code', $item->ifsc_code ?? '') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Amount</label>
                    <input type="number" step="0.01" name="amount" class="form-control" required
                        value="{{ old('amount', $item->amount ?? '') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" {{ old('status', $item->status ?? 'active') == 'active' ? 'selected' : '' }}>
                            Active</option>
                        <option value="inactive" {{ old('status', $item->status ?? '') == 'inactive' ? 'selected' : '' }}>
                            Inactive</option>
                    </select>
                </div>

                @if (isset($item))
                    <div class="mb-3">
                        <label class="form-label">Approver Status</label>
                        <select name="approver_status" class="form-select">
                            <option value="pending" {{ $item->approver_status == 'pending' ? 'selected' : '' }}>Pending
                            </option>
                            <option value="approved" {{ $item->approver_status == 'approved' ? 'selected' : '' }}>Approved
                            </option>
                            <option value="rejected" {{ $item->approver_status == 'rejected' ? 'selected' : '' }}>Rejected
                            </option>
                        </select>
                    </div>
                @endif

                <button class="btn btn-primary">{{ isset($item) ? 'Update' : 'Create' }}</button>
            </form>
        </div>
    </div>
@endsection
