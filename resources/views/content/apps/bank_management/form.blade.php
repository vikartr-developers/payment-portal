@extends('layouts/layoutMaster')

@section('title', isset($record) ? 'Edit Record' : 'Add New Record')

@section('content')
    <div class="card">
        <div class="card-header">
            <h4>{{ isset($record) ? 'Edit Account' : 'Add New Account' }}</h4>
        </div>
        <div class="card-body">
            <form method="POST"
                action="{{ isset($record) ? route('bank-management.update', $record->id) : route('bank-management.store') }}">
                @csrf
                @if (isset($record))
                    @method('PUT')
                @endif

                <!-- Common Fields -->
                <div class="row mb-3">
                    <div class="col-12 col-md-6">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" id="name" required
                            value="{{ old('name', $record->name ?? '') }}" placeholder="Enter account name">
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="daily_max_amount" class="form-label">Daily Maximum Amount <span
                                class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="daily_max_amount" class="form-control"
                            id="daily_max_amount" required
                            value="{{ old('daily_max_amount', $record->daily_max_amount ?? '') }}" placeholder="0.00">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12 col-md-6">
                        <label for="daily_max_transaction_count" class="form-label">Daily Maximum Transaction Count <span
                                class="text-danger">*</span></label>
                        <input type="number" name="daily_max_transaction_count" class="form-control"
                            id="daily_max_transaction_count" required
                            value="{{ old('daily_max_transaction_count', $record->daily_max_transaction_count ?? '') }}"
                            placeholder="0">
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="max_transaction_amount" class="form-label">Maximum Total Transaction Amount <span
                                class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="max_transaction_amount" class="form-control"
                            id="max_transaction_amount" required
                            value="{{ old('max_transaction_amount', $record->max_transaction_amount ?? '') }}"
                            placeholder="0.00">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12 col-md-6">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="active"
                                {{ old('status', $record->status ?? 'active') == 'active' ? 'selected' : '' }}>
                                Active</option>
                            <option value="inactive"
                                {{ old('status', $record->status ?? '') == 'inactive' ? 'selected' : '' }}>
                                Inactive</option>
                        </select>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Account Type Tabs -->
                <ul class="nav nav-tabs" id="tab-menu" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {{ old('type', $record->type ?? '') == 'bank' || !old('type') ? 'active' : '' }}"
                            id="bank-tab" data-bs-toggle="tab" href="#bank" role="tab" aria-controls="bank"
                            aria-selected="{{ old('type', $record->type ?? '') == 'bank' || !old('type') ? 'true' : 'false' }}">
                            <i class="ti ti-building-bank me-1"></i>Bank Account
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ old('type', $record->type ?? '') == 'upi' ? 'active' : '' }}" id="upi-tab"
                            data-bs-toggle="tab" href="#upi" role="tab" aria-controls="upi"
                            aria-selected="{{ old('type', $record->type ?? '') == 'upi' ? 'true' : 'false' }}">
                            <i class="ti ti-qrcode me-1"></i>UPI
                        </a>
                    </li>
                </ul>

                <div class="tab-content mt-3" id="tab-content">
                    <input type="hidden" name="type" id="type" value="{{ old('type', $record->type ?? 'bank') }}">

                    <!-- Bank Account Tab -->
                    <div class="tab-pane fade {{ old('type', $record->type ?? '') == 'bank' || !old('type') ? 'show active' : '' }}"
                        id="bank" role="tabpanel" aria-labelledby="bank-tab">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label for="account_holder_name" class="form-label">Account Holder Name</label>
                                    <input type="text" name="account_holder_name" class="form-control"
                                        id="account_holder_name"
                                        value="{{ old('account_holder_name', $record->account_holder_name ?? '') }}"
                                        placeholder="Enter account holder name">
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label for="bank_name" class="form-label">Bank Name</label>
                                    <input type="text" name="bank_name" class="form-control" id="bank_name"
                                        value="{{ old('bank_name', $record->bank_name ?? '') }}"
                                        placeholder="Enter bank name">
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label for="ifsc_code" class="form-label">IFSC Code</label>
                                    <input type="text" name="ifsc_code" class="form-control" id="ifsc_code"
                                        value="{{ old('ifsc_code', $record->ifsc_code ?? '') }}"
                                        placeholder="Enter IFSC code">
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label for="account_number" class="form-label">Account Number</label>
                                    <input type="text" name="account_number" class="form-control" id="account_number"
                                        value="{{ old('account_number', $record->account_number ?? '') }}"
                                        placeholder="Enter account number">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- UPI Tab -->
                    <div class="tab-pane fade {{ old('type', $record->type ?? '') == 'upi' ? 'show active' : '' }}"
                        id="upi" role="tabpanel" aria-labelledby="upi-tab">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label for="upi_id" class="form-label">UPI ID</label>
                                    <input type="text" name="upi_id" class="form-control" id="upi_id"
                                        value="{{ old('upi_id', $record->upi_id ?? '') }}" placeholder="example@upi">
                                </div>
                            </div>

                            {{-- <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label for="upi_number" class="form-label">UPI Number</label>
                                    <input type="text" name="upi_number" class="form-control" id="upi_number"
                                        value="{{ old('upi_number', $record->upi_number ?? '') }}"
                                        placeholder="Enter UPI number">
                                </div>
                            </div> --}}

                            <div class="col-12">
                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" name="is_merchant_upi"
                                        id="is_merchant_upi" value="1"
                                        {{ old('is_merchant_upi', $record->is_merchant_upi ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_merchant_upi">
                                        Merchant UPI
                                        <small class="text-muted">(Select for admin approval - works faster than normal
                                            UPI)</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i>{{ isset($record) ? 'Update' : 'Save' }}
                    </button>
                    <a href="{{ route('bank-management.index') }}" class="btn btn-secondary">
                        <i class="ti ti-x me-1"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('page-script')
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
