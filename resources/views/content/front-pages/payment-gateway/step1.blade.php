@extends('layouts/layoutMaster')

@section('title', 'Payment Gateway')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/animate-css/animate.css') }}" />
@endsection

@section('page-style')
    <style>
        .amount-btn {
            font-size: 1.2rem;
            padding: 15px 30px;
            min-width: 120px;
            margin: 8px;
        }

        .amount-btn.active {
            background-color: #7367f0;
            color: white;
            border-color: #7367f0;
        }

        .payment-card {
            max-width: 600px;
            margin: 40px auto;
        }
    </style>
@endsection

@section('content')
    <section class="section-py bg-body first-section-pt">
        <div class="container">
            <div class="card payment-card">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h3 class="mb-2">Payment Gateway</h3>
                        <p class="text-muted">Enter your details to proceed with payment</p>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('payment.select-method') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" id="username" name="username"
                                class="form-control form-control-lg @error('username') is-invalid @enderror"
                                placeholder="Enter your username" value="{{ old('username') }}" required>
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="mobile" class="form-label">Mobile Number</label>
                            <input type="tel" id="mobile" name="mobile"
                                class="form-control form-control-lg @error('mobile') is-invalid @enderror"
                                placeholder="Enter mobile number (optional)" value="{{ old('mobile') }}" maxlength="10"
                                pattern="[0-9]{10}">
                            @error('mobile')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                            <input type="number" id="amount" name="amount"
                                class="form-control form-control-lg @error('amount') is-invalid @enderror"
                                placeholder="Enter amount" value="{{ old('amount') }}" min="100" step="0.01"
                                required>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="text-center mb-4">
                            <p class="text-muted mb-2">Quick Select Amount</p>
                            <button type="button" class="btn btn-outline-primary amount-btn"
                                data-amount="500">₹500</button>
                            <button type="button" class="btn btn-outline-primary amount-btn"
                                data-amount="1000">₹1000</button>
                            <button type="button" class="btn btn-outline-primary amount-btn"
                                data-amount="1500">₹1500</button>
                            <button type="button" class="btn btn-outline-primary amount-btn"
                                data-amount="2000">₹2000</button>
                            <button type="button" class="btn btn-outline-primary amount-btn"
                                data-amount="3000">₹3000</button>
                        </div>

                        <div class="mb-3 form-check">
                            <input class="form-check-input @error('consent') is-invalid @enderror" type="checkbox"
                                id="consent" name="consent" value="1" {{ old('consent') ? 'checked' : '' }}
                                required>
                            <label class="form-check-label" for="consent">I give full consent for this payment</label>
                            @error('consent')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="ti ti-arrow-right me-2"></i>Proceed to Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('page-script')
    <script>
        $(function() {
            // Amount quick select buttons
            $('.amount-btn').on('click', function() {
                $('.amount-btn').removeClass('active');
                $(this).addClass('active');
                $('#amount').val($(this).data('amount'));
            });

            // Auto-format mobile number to numbers only
            $('#mobile').on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        });
    </script>
@endsection
