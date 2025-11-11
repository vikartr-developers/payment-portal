@extends('layouts/layoutMaster')

@section('title', isset($record) ? 'Edit Record' : 'Add New Record')

@section('content')
    <form method="POST"
        action="{{ isset($record) ? route('bank-management.update', $record->id) : route('bank-management.store') }}">
        @csrf
        @if (isset($record))
            @method('PUT')
        @endif

        <ul class="nav nav-tabs" id="tab-menu" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ old('type', $record->type ?? '') == 'bank' || !old('type') ? 'active' : '' }}"
                    id="bank-tab" data-bs-toggle="tab" href="#bank" role="tab" aria-controls="bank"
                    aria-selected="{{ old('type', $record->type ?? '') == 'bank' || !old('type') ? 'true' : 'false' }}">Bank
                    Account</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ old('type', $record->type ?? '') == 'upi' ? 'active' : '' }}" id="upi-tab"
                    data-bs-toggle="tab" href="#upi" role="tab" aria-controls="upi"
                    aria-selected="{{ old('type', $record->type ?? '') == 'upi' ? 'true' : 'false' }}">UPI</a>
            </li>
        </ul>

        <div class="tab-content" id="tab-content">
            <input type="hidden" name="type" id="type" value="{{ old('type', $record->type ?? 'bank') }}">
            <div class="tab-pane fade {{ old('type', $record->type ?? '') == 'bank' || !old('type') ? 'show active' : '' }}"
                id="bank" role="tabpanel" aria-labelledby="bank-tab">
                <div class="mb-3">
                    <label for="account_number" class="form-label">Account Number</label>
                    <input type="text" name="account_number" class="form-control" id="account_number"
                        value="{{ old('account_number', $record->account_number ?? '') }}">
                </div>
                <div class="mb-3">
                    <label for="ifsc_code" class="form-label">IFSC Code</label>
                    <input type="text" name="ifsc_code" class="form-control" id="ifsc_code"
                        value="{{ old('ifsc_code', $record->ifsc_code ?? '') }}">
                </div>
            </div>

            <div class="tab-pane fade {{ old('type', $record->type ?? '') == 'upi' ? 'show active' : '' }}" id="upi"
                role="tabpanel" aria-labelledby="upi-tab">
                <div class="mb-3">
                    <label for="upi_id" class="form-label">UPI ID</label>
                    <input type="text" name="upi_id" class="form-control" id="upi_id"
                        value="{{ old('upi_id', $record->upi_id ?? '') }}">
                </div>
                <div class="mb-3">
                    <label for="upi_number" class="form-label">Number</label>
                    <input type="text" name="upi_number" class="form-control" id="upi_number"
                        value="{{ old('upi_number', $record->upi_number ?? '') }}">
                </div>
            </div>
        </div>

        <div class="row">
            {{-- <div class="col-12 col-md-6">
                <div class="mb-3">
                    <label for="bank_name" class="form-label">Bank Name</label>
                    <input type="text" name="bank_name" class="form-control" id="bank_name"
                        value="{{ old('bank_name', $record->bank_name ?? '') }}">
                </div>
            </div> --}}
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <label for="branch_name" class="form-label">Branch Name</label>
                    <input type="text" name="branch_name" class="form-control" id="branch_name"
                        value="{{ old('branch_name', $record->branch_name ?? '') }}">
                </div>
            </div>
            {{-- </div> --}}

            <div class="col-12 col-md-6">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="active" {{ old('status', $record->status ?? 'active') == 'active' ? 'selected' : '' }}>
                        Active</option>
                    <option value="inactive" {{ old('status', $record->status ?? '') == 'inactive' ? 'selected' : '' }}>
                        Inactive</option>
                </select>
            </div>

            <div class="col-12 col-md-6">
                <label for="deposit_limit" class="form-label">Per Day Deposit Limit</label>
                <input type="number" step="0.01" name="deposit_limit" class="form-control" id="deposit_limit"
                    value="{{ old('deposit_limit', $record->deposit_limit ?? '') }}">
            </div>

            <div class="col-12 col-md-6">
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" name="is_default" id="is_default"
                        {{ old('is_default', $record->is_default ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_default">Set as Default</label>
                </div>
            </div>

        </div>

        <button type="submit" class="btn btn-primary mt-2">{{ isset($record) ? 'Update' : 'Save' }}</button>
    </form>
@endsection

@section('vendor-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tabMenu = document.getElementById('tab-menu');
            var typeInput = document.getElementById('type');

            tabMenu.querySelectorAll('a[data-bs-toggle="tab"]').forEach(function(tabLink) {
                tabLink.addEventListener('shown.bs.tab', function(e) {
                    typeInput.value = e.target.getAttribute('aria-controls'); // 'bank' or 'upi'
                });
            });
        });
    </script>

    <!-- Make sure Bootstrap 5 JS and its dependencies are loaded -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/bootstrap/bootstrap.bundle.js') }}"></script>
@endsection
