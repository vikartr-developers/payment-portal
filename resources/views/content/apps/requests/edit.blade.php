@extends('layouts/layoutMaster')

@section('title', 'Edit Payment Request')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
@endsection

@section('page-script')
    <script>
        $(document).ready(function() {
            $('#mode, #status').select2();
        });
    </script>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Edit Payment Request</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('requests.update', $requestModel->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" id="name" name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $requestModel->name) }}" required />
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3 col-md-6">
                                <label for="mode" class="form-label">Mode <span class="text-danger">*</span></label>
                                <select id="mode" name="mode"
                                    class="form-select @error('mode') is-invalid @enderror" required>
                                    <option value="">Select Mode</option>
                                    <option value="bank"
                                        {{ old('mode', $requestModel->mode) === 'bank' ? 'selected' : '' }}>Bank</option>
                                    <option value="upi"
                                        {{ old('mode', $requestModel->mode) === 'upi' ? 'selected' : '' }}>UPI</option>
                                    <option value="crypto"
                                        {{ old('mode', $requestModel->mode) === 'crypto' ? 'selected' : '' }}>Crypto
                                    </option>
                                </select>
                                @error('mode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" id="amount" name="amount"
                                    class="form-control @error('amount') is-invalid @enderror"
                                    value="{{ old('amount', $requestModel->amount) }}" required />
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3 col-md-6">
                                <label for="payment_amount" class="form-label">Payment Amount</label>
                                <input type="number" step="0.01" id="payment_amount" name="payment_amount"
                                    class="form-control @error('payment_amount') is-invalid @enderror"
                                    value="{{ old('payment_amount', $requestModel->payment_amount) }}" />
                                @error('payment_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="utr" class="form-label">UTR</label>
                                <input type="text" id="utr" name="utr"
                                    class="form-control @error('utr') is-invalid @enderror"
                                    value="{{ old('utr', $requestModel->utr) }}" />
                                @error('utr')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3 col-md-6">
                                <label for="payment_from" class="form-label">Payment From</label>
                                <input type="text" id="payment_from" name="payment_from"
                                    class="form-control @error('payment_from') is-invalid @enderror"
                                    value="{{ old('payment_from', $requestModel->payment_from) }}" />
                                @error('payment_from')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="account_upi" class="form-label">Account/UPI</label>
                            <input type="text" id="account_upi" name="account_upi"
                                class="form-control @error('account_upi') is-invalid @enderror"
                                value="{{ old('account_upi', $requestModel->account_upi) }}" />
                            @error('account_upi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Image</label>
                            @if ($requestModel->image)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $requestModel->image) }}" width="120"
                                        alt="Payment Image">
                                </div>
                            @endif
                            <input type="file" id="image" name="image"
                                class="form-control @error('image') is-invalid @enderror" />
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select id="status" name="status"
                                class="form-select @error('status') is-invalid @enderror" required>
                                <option value="pending"
                                    {{ old('status', $requestModel->status) === 'pending' ? 'selected' : '' }}>Pending
                                </option>
                                <option value="accepted"
                                    {{ old('status', $requestModel->status) === 'accepted' ? 'selected' : '' }}>Accepted
                                </option>
                                <option value="rejected"
                                    {{ old('status', $requestModel->status) === 'rejected' ? 'selected' : '' }}>Rejected
                                </option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Update Request</button>
                        <a href="{{ route('requests.list') }}" class="btn btn-outline-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
