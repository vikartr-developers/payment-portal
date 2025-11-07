@extends('layouts/layoutMaster')

@section('title', 'Payment Details - Regular Payment')

@section('vendor-style')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.css">
@endsection

@section('page-style')
    <style>
        .payment-card {
            max-width: 700px;
            margin: 40px auto;
        }

        .payment-method-option {
            border: 2px solid #e7e7e7;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .payment-method-option:hover {
            border-color: #7367f0;
        }

        .payment-method-option.selected {
            border-color: #7367f0;
            background-color: #f8f7ff;
        }

        .payment-method-option input[type="radio"] {
            display: none;
        }

        .qr-code-container {
            text-align: center;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 10px;
            margin: 20px 0;
        }

        #qrcode {
            display: inline-block;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .upi-details {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }

        #upiPaymentSection,
        #bankPaymentSection {
            display: none;
        }

        .upi-app-logos {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-top: 12px;
            align-items: center;
        }

        .upi-app-logos img {
            width: 48px;
            height: auto;
            cursor: pointer;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            transition: transform .12s ease, box-shadow .12s ease;
        }

        .upi-app-logos img:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.12);
        }
    </style>
@endsection

@section('content')
    <section class="section-py bg-body first-section-pt">
        <div class="container">
            <div class="card payment-card">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h3 class="mb-2">Complete Payment</h3>
                        <div class="alert alert-info">
                            <strong>Amount to Pay: ₹{{ number_format($amount, 2) }}</strong>
                        </div>
                    </div>

                    <form action="{{ route('payment.process') }}" method="POST" enctype="multipart/form-data"
                        id="paymentForm">
                        @csrf

                        <h5 class="mb-3">Select Payment Method</h5>

                        <label class="payment-method-option" for="upi">
                            <input type="radio" name="payment_method" id="upi" value="upi" required>
                            <div class="d-flex align-items-center">
                                <i class="ti ti-qrcode me-3" style="font-size: 2rem; color: #7367f0;"></i>
                                <div>
                                    <h5 class="mb-1">UPI Payment</h5>
                                    <p class="text-muted mb-0">Pay using any UPI app</p>
                                </div>
                            </div>
                        </label>

                        <label class="payment-method-option" for="bank">
                            <input type="radio" name="payment_method" id="bank" value="bank" required>
                            <div class="d-flex align-items-center">
                                <i class="ti ti-building-bank me-3" style="font-size: 2rem; color: #7367f0;"></i>
                                <div>
                                    <h5 class="mb-1">Bank Transfer</h5>
                                    <p class="text-muted mb-0">IMPS and RTGS only</p>
                                </div>
                            </div>
                        </label>

                        <!-- UPI Payment Section -->
                        <div id="upiPaymentSection">
                            @if ($upiAccount)
                                <div class="qr-code-container">
                                    <h5 class="mb-3">Scan QR Code to Pay</h5>
                                    @php
                                        $upiId = $upiAccount->upi_id ?? '';
                                        $upiNumber = $upiAccount->upi_number ?? '';
                                        $merchantName = 'Merchant';
                                        // Create UPI payment URL
                                        $upiPaymentUrl = "upi://pay?pa={$upiId}&pn={$merchantName}&am={$amount}&cu=INR&tn=Payment";
                                    @endphp
                                    <div id="qrcode" class="mb-3"></div>
                                    <!-- UPI app logos (click to copy UPI ID) -->
                                    <div class="upi-app-logos" aria-hidden="false">
                                        <img class="upi-app-logo"
                                            src="https://img.icons8.com/?size=100&id=am4ltuIYDpQ5&format=png&color=000000"
                                            alt="Google Pay" title="Copy UPI ID to clipboard"
                                            data-upi="{{ $upiId }}">
                                        <img class="upi-app-logo"
                                            src="https://img.icons8.com/?size=100&id=OYtBxIlJwMGA&format=png&color=000000"
                                            alt="PhonePe" title="Copy UPI ID to clipboard" data-upi="{{ $upiId }}">
                                        <img class="upi-app-logo"
                                            src="https://img.icons8.com/?size=100&id=68067&format=png&color=000000"
                                            alt="Paytm" title="Copy UPI ID to clipboard" data-upi="{{ $upiId }}">
                                        <img class="upi-app-logo"
                                            src="https://img.icons8.com/?size=100&id=5RcHTSNy4fbL&format=png&color=000000"
                                            alt="BHIM" title="Copy UPI ID to clipboard" data-upi="{{ $upiId }}">
                                        {{-- <img class="upi-app-logo"
                                            src="https://img.icons8.com/?size=100&id=5RcHTSNy4fbL&format=png&color=000000"
                                            alt="BHIM" title="Copy UPI ID to clipboard" data-upi="{{ $upiId }}"> --}}
                                    </div>
                                    <div class="upi-details mt-3">
                                        @if ($upiAccount->upi_id)
                                            <p class="mb-1"><strong>UPI ID:</strong> {{ $upiAccount->upi_id }}</p>
                                        @endif
                                        @if ($upiAccount->upi_number)
                                            <p class="mb-1"><strong>UPI Number:</strong> {{ $upiAccount->upi_number }}</p>
                                        @endif
                                        <p class="mb-1"><strong>Amount:</strong> ₹{{ number_format($amount, 2) }}</p>
                                        @if ($upiAccount->deposit_limit)
                                            <p class="mb-0 text-muted"><small>Deposit Limit:
                                                    ₹{{ number_format($upiAccount->deposit_limit, 2) }}</small></p>
                                        @endif
                                    </div>
                                    <!-- Hidden data for QR generation -->
                                    <input type="hidden" id="upiPaymentUrl" value="{{ $upiPaymentUrl }}">
                                </div>
                            @else
                                <div class="alert alert-danger">
                                    <i class="ti ti-alert-circle me-2"></i>No UPI accounts available at the moment. Please
                                    try bank transfer or contact support.
                                </div>
                            @endif
                        </div>

                        <!-- Bank Transfer Section -->
                        <div id="bankPaymentSection">
                            @if ($bankAccount)
                                <div class="alert alert-warning">
                                    <h6 class="alert-heading">Bank Transfer Details</h6>
                                    <p class="mb-1">
                                        <strong>Account Number:</strong>
                                        <span id="accountNumber">{{ $bankAccount->account_number }}</span>
                                        <button type="button" class="btn btn-sm btn-outline-secondary ms-2 copy-btn"
                                            data-copy="{{ $bankAccount->account_number }}" title="Copy account number"
                                            aria-label="Copy account number">
                                            <i class="ti ti-files"></i>
                                        </button>
                                    </p>
                                    <p class="mb-1">
                                        <strong>IFSC Code:</strong>
                                        <span id="ifscCode">{{ $bankAccount->ifsc_code }}</span>
                                        <button type="button" class="btn btn-sm btn-outline-secondary ms-2 copy-btn"
                                            data-copy="{{ $bankAccount->ifsc_code }}" title="Copy IFSC code"
                                            aria-label="Copy IFSC code">
                                            <i class="ti ti-files"></i>
                                        </button>
                                    </p>
                                    <p class="mb-1"><strong>Amount:</strong> ₹{{ number_format($amount, 2) }}</p>
                                    @if ($bankAccount->deposit_limit)
                                        <p class="mb-0 text-muted"><small>Deposit Limit:
                                                ₹{{ number_format($bankAccount->deposit_limit, 2) }}</small></p>
                                    @endif
                                </div>
                            @else
                                <div class="alert alert-danger">
                                    <i class="ti ti-alert-circle me-2"></i>No bank accounts available at the moment. Please
                                    try UPI payment or contact support.
                                </div>
                            @endif
                        </div>

                        <!-- Upload Screenshot Section -->
                        <div id="uploadSection" style="display: none;">
                            <hr class="my-4">
                            <h5 class="mb-3">Upload Payment Proof</h5>

                            <div class="mb-3">
                                <label for="screenshot" class="form-label">Upload Screenshot <span
                                        class="text-danger">*</span></label>
                                <input type="file" id="screenshot" name="screenshot"
                                    class="form-control @error('screenshot') is-invalid @enderror" accept="image/*"
                                    required>
                                <small class="text-muted">Upload screenshot of successful payment (auto-detect UTR)</small>
                                @error('screenshot')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="utr" class="form-label">UTR / Transaction ID <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="utr" name="utr"
                                    class="form-control @error('utr') is-invalid @enderror"
                                    placeholder="Enter 12-digit UTR number" maxlength="12" pattern="[0-9]{12}" required>
                                <small class="text-muted">Enter the 12-digit UTR number from your payment</small>
                                @error('utr')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex gap-3 mt-4">
                            <a href="{{ route('payment.gateway') }}" class="btn btn-outline-secondary btn-lg flex-fill">
                                <i class="ti ti-arrow-left me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg flex-fill" id="submitBtn" disabled>
                                Submit Payment<i class="ti ti-check ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('vendor-script')
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
@endsection

@section('page-script')
    <script>
        $(function() {
            // Generate QR Code for UPI payment
            function generateQRCode() {
                var upiUrl = $('#upiPaymentUrl').val();
                if (upiUrl) {
                    var qr = qrcode(0, 'M');
                    qr.addData(upiUrl);
                    qr.make();

                    // Create image
                    var qrImage = qr.createImgTag(6, 8); // size 6, margin 8
                    $('#qrcode').html(qrImage);
                }
            }

            // Click on UPI app logo to copy UPI ID to clipboard
            $(document).on('click', '.upi-app-logo', function() {
                var upi = $(this).data('upi') || $('#upiPaymentUrl').val();
                if (!upi) {
                    alert('No UPI ID available to copy.');
                    return;
                }

                // If the data-upi contains full UPI ID or URL, normalize to UPI ID
                // If it's a URL like upi://pay?pa=xxx..., try to extract pa=
                var upiId = upi;
                var match = String(upi).match(/pa=([^&]+)/);
                if (match) {
                    upiId = decodeURIComponent(match[1]);
                }

                // Try clipboard API, fallback to prompt
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(upiId).then(function() {
                        // show small feedback
                        var originalTitle = document.title;
                        // simple feedback via alert for now
                        alert('UPI ID copied: ' + upiId);
                    }).catch(function() {
                        prompt('Copy UPI ID', upiId);
                    });
                } else {
                    prompt('Copy UPI ID', upiId);
                }
            });

            // Click on copy buttons to copy text to clipboard (account number / IFSC)
            $(document).on('click', '.copy-btn', function(e) {
                e.preventDefault();
                var text = $(this).data('copy');
                if (!text) {
                    alert('Nothing to copy.');
                    return;
                }

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(function() {
                        alert('Copied: ' + text);
                    }).catch(function() {
                        prompt('Copy to clipboard', text);
                    });
                } else {
                    prompt('Copy to clipboard', text);
                }
            });

            // Handle payment method selection
            $('.payment-method-option').on('click', function() {
                $('.payment-method-option').removeClass('selected');
                $(this).addClass('selected');
                $(this).find('input[type="radio"]').prop('checked', true);

                var selectedMethod = $(this).find('input[type="radio"]').val();

                // Hide all sections
                $('#upiPaymentSection, #bankPaymentSection').hide();

                // Show relevant section
                if (selectedMethod === 'upi') {
                    $('#upiPaymentSection').slideDown(function() {
                        // Generate QR code after section is visible
                        generateQRCode();
                    });
                } else if (selectedMethod === 'bank') {
                    $('#bankPaymentSection').slideDown();
                }

                // Show upload section
                $('#uploadSection').slideDown();
                $('#submitBtn').prop('disabled', false);
            });

            // Auto-format UTR to numbers only
            $('#utr').on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });

            // Optional: Auto-detect UTR from screenshot (placeholder for OCR)
            $('#screenshot').on('change', function() {
                // Here you could integrate OCR to auto-detect UTR from screenshot
                // For now, just show a message
                if (this.files && this.files[0]) {
                    console.log('Screenshot uploaded. OCR detection can be implemented here.');
                }
            });
        });
    </script>
@endsection
