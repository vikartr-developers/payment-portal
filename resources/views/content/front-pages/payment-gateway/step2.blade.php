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
                        <p class="text-muted">Amount: <strong>₹{{ number_format($requestRecord->amount, 2) }}</strong></p>
                        <p class="text-muted">User: <strong>{{ $requestRecord->name }}</strong></p>
                        <p class="text-muted small">Transaction ID: <strong>{{ $display_transaction_id }}</strong></p>

                        <div class="alert alert-info mt-3">
                            <i class="ti ti-link me-2"></i>
                            <small>You can share this page link to continue payment from another device</small>
                            <button type="button" class="btn btn-sm btn-outline-primary ms-2" id="copyLinkBtn">
                                <i class="ti ti-copy me-1"></i>Copy Link
                            </button>
                        </div>
                    </div>

                    <form action="{{ route('payment.select-payment-type') }}" method="POST" id="paymentMethodForm">
                        @csrf
                        <input type="hidden" name="transaction_id" value="{{ $transaction_id }}">

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
                            <button type="button" onclick="history.back()"
                                class="btn btn-outline-secondary btn-lg flex-fill">
                                <i class="ti ti-arrow-left me-2"></i>Back
                            </button>
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

            // Copy link functionality
            $('#copyLinkBtn').on('click', function() {
                const currentUrl = window.location.href;
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(currentUrl).then(function() {
                        // Show success message
                        const btn = $('#copyLinkBtn');
                        const originalHtml = btn.html();
                        btn.html('<i class="ti ti-check me-1"></i>Copied!');
                        btn.removeClass('btn-outline-primary').addClass('btn-success');

                        setTimeout(function() {
                            btn.html(originalHtml);
                            btn.removeClass('btn-success').addClass('btn-outline-primary');
                        }, 2000);
                    }).catch(function() {
                        prompt('Copy this link:', currentUrl);
                    });
                } else {
                    prompt('Copy this link:', currentUrl);
                }
            });
        });
    </script>
@endsection
