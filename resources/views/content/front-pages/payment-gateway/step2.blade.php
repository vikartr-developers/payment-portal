@extends('layouts/layoutMaster')

@section('title', 'Select Payment Method')


@section('page-style')
    <style>
        .payment-card {
            max-width: 700px;
            margin: 40px auto;
        }

        .payment-option {
            border: 2px solid #e7e7e7;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 20px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .payment-option:hover {
            border-color: #000000;
            box-shadow: 0 4px 15px rgba(115, 103, 240, 0.2);
        }

        .payment-option input[type="radio"] {
            display: none;
        }

        .payment-option.selected {
            border-color: #000000;
            background-color: #f8f7ff;
        }

        .payment-icon {
            font-size: 3rem;
            color: #000000;
            margin-bottom: 15px;
        }
    </style>
@endsection

@section('content')
    <section class="section-py bg-body first-section-pt">
        <div class="container">
            <div class="card payment-card">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h3 class="mb-2">Select Payment Method</h3>
                        <p class="text-muted">Amount: <strong>₹{{ number_format(session('payment_amount'), 2) }}</strong></p>
                        <p class="text-muted">User: <strong>{{ session('payment_username') }}</strong></p>
                    </div>

                    <form action="{{ route('payment.show-details') }}" method="POST" id="paymentMethodForm">
                        @csrf

                        <label class="payment-option" for="regular">
                            <input type="radio" name="payment_type" id="regular" value="regular" required>
                            <div class="text-center">
                                <div class="payment-icon">
                                    <i class="ti ti-building-bank"></i>
                                </div>
                                <h4 class="mb-2">Regular Payment</h4>
                                <p class="text-muted mb-0">Pay with UPI or bank transfer</p>
                            </div>
                        </label>

                        <label class="payment-option" for="crypto">
                            <input type="radio" name="payment_type" id="crypto" value="crypto" required>
                            <div class="text-center">
                                <div class="payment-icon">
                                    <i class="ti ti-currency-bitcoin"></i>
                                </div>
                                <h4 class="mb-2">Crypto Payment</h4>
                                <p class="text-muted mb-0">Pay with cryptocurrency</p>
                            </div>
                        </label>

                        <div class="d-flex gap-3 mt-4">
                            <a href="{{ route('payment.gateway') }}" class="btn btn-outline-secondary btn-lg flex-fill">
                                <i class="ti ti-arrow-left me-2"></i>Back
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg flex-fill">
                                Continue<i class="ti ti-arrow-right ms-2"></i>
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
            // Handle payment option selection
            $('.payment-option').on('click', function() {
                $('.payment-option').removeClass('selected');
                $(this).addClass('selected');
                $(this).find('input[type="radio"]').prop('checked', true);
            });
        });
    </script>
@endsection
