@extends('layouts/layoutMaster')

@section('title', 'Add Charge Back')

{{-- <meta name="csrf-token" content="{{ csrf_token() }}"> --}}

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
@endsection

@section('page-script')
    <script>
        $(function() {
            $('#request_id').select2();
        })
    </script>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Create Charge Back</h4>
                    <a href="{{ route('chargebacks.list') }}" class="btn btn-outline-secondary">Back to List</a>
                </div>
                <div class="card-body">
                    <form action="{{ route('chargebacks.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="slip" class="form-label">Upload Slip <span class="text-danger">*</span></label>
                            <input type="file" id="slip" name="slip"
                                class="form-control @error('slip') is-invalid @enderror" required>
                            @error('slip')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="request_id" class="form-label">Payment Order (payment request with amount) <span
                                    class="text-danger">*</span></label>
                            <select id="request_id" name="request_id"
                                class="form-select @error('request_id') is-invalid @enderror" required>
                                <option value="">Select a payment request</option>
                                @foreach ($requests as $req)
                                    @php($tx = 'TXN-' . str_pad((string) $req->id, 6, '0', STR_PAD_LEFT))
                                    @php($amt = $req->amount ?? 0)
                                    <option value="{{ $req->id }}"
                                        {{ old('request_id') == $req->id ? 'selected' : '' }}>
                                        {{ $tx }} — Amount: {{ number_format((float) $amt, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('request_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason <span class="text-danger">*</span></label>
                            <textarea id="reason" name="reason" rows="4" class="form-control @error('reason') is-invalid @enderror"
                                placeholder="Enter reason" required>{{ old('reason') }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Create Charge Back</button>
                        <a href="{{ route('chargebacks.list') }}" class="btn btn-outline-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
