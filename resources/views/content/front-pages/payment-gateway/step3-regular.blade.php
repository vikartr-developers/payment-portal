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
            border-color: #000000;
        }

        .payment-method-option.selected {
            border-color: #000000;
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

                        @if (isset($selectedAccount))
                            {{-- Pre-selected account mode: Show both UPI and Bank options for this account --}}
                            <input type="hidden" name="selected_account_id" value="{{ $selectedAccount->id }}">

                            @if ($selectedAccount->upi_id)
                                <label class="payment-method-option" for="upi">
                                    <input type="radio" name="payment_method" id="upi" value="upi" required>
                                    <div class="d-flex align-items-center">
                                        <i class="ti ti-qrcode me-3" style="font-size: 2rem; color: #000000;"></i>
                                        <div>
                                            <h5 class="mb-1">UPI Payment</h5>
                                            <p class="text-muted mb-0">Pay using any UPI app</p>
                                        </div>
                                    </div>
                                </label>
                            @endif

                            @if ($selectedAccount->account_number)
                                <label class="payment-method-option" for="bank">
                                    <input type="radio" name="payment_method" id="bank" value="bank" required>
                                    <div class="d-flex align-items-center">
                                        <i class="ti ti-building-bank me-3" style="font-size: 2rem; color: #000000;"></i>
                                        <div>
                                            <h5 class="mb-1">Bank Transfer</h5>
                                            <p class="text-muted mb-0">IMPS and RTGS only</p>
                                        </div>
                                    </div>
                                </label>
                            @endif
                        @else
                            {{-- Normal mode: Show payment method selection --}}
                            <label class="payment-method-option" for="upi">
                                <input type="radio" name="payment_method" id="upi" value="upi" required>
                                <div class="d-flex align-items-center">
                                    <i class="ti ti-qrcode me-3" style="font-size: 2rem; color: #000000;"></i>
                                    <div>
                                        <h5 class="mb-1">UPI Payment</h5>
                                        <p class="text-muted mb-0">Pay using any UPI app</p>
                                    </div>
                                </div>
                            </label>

                            <label class="payment-method-option" for="bank">
                                <input type="radio" name="payment_method" id="bank" value="bank" required>
                                <div class="d-flex align-items-center">
                                    <i class="ti ti-building-bank me-3" style="font-size: 2rem; color: #000000;"></i>
                                    <div>
                                        <h5 class="mb-1">Bank Transfer</h5>
                                        <p class="text-muted mb-0">IMPS and RTGS only</p>
                                    </div>
                                </div>
                            </label>
                        @endif

                        <!-- UPI Payment Section -->
                        <div id="upiPaymentSection">
                            @if (isset($selectedAccount) && $selectedAccount->upi_id)
                                {{-- Pre-selected account: Show UPI details --}}
                                <div class="qr-code-container mb-4">
                                    <h5 class="mb-3">{{ $selectedAccount->name ?? 'UPI Payment' }}</h5>
                                    @php
                                        $upiId = $selectedAccount->upi_id ?? '';
                                        $merchantName = $selectedAccount->name ?? 'Merchant';
                                        $upiPaymentUrl = "upi://pay?pa={$upiId}&pn={$merchantName}&am={$amount}&cu=INR&tn=Payment";
                                    @endphp
                                    <div id="qrcode-selected" class="mb-3"></div>
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
                                    </div>
                                    <div class="upi-details mt-3">
                                        @if ($selectedAccount->name)
                                            <p class="mb-1"><strong>Account Name:</strong> {{ $selectedAccount->name }}
                                            </p>
                                        @endif
                                        @if ($selectedAccount->upi_id)
                                            <p class="mb-1"><strong>UPI ID:</strong> {{ $selectedAccount->upi_id }}</p>
                                        @endif
                                        @if ($selectedAccount->upi_number)
                                            <p class="mb-1"><strong>UPI Number:</strong>
                                                {{ $selectedAccount->upi_number }}</p>
                                        @endif
                                        <p class="mb-1"><strong>Amount:</strong> ₹{{ number_format($amount, 2) }}</p>
                                    </div>
                                    <input type="hidden" id="selectedUpiPaymentUrl" value="{{ $upiPaymentUrl }}">
                                </div>
                            @elseif(isset($accounts))
                                {{-- Normal mode: Show account selection --}}
                                @php
                                    $upiAccounts = $accounts->filter(function ($acc) {
                                        return !empty($acc->upi_id);
                                    });
                                @endphp
                                @if ($upiAccounts->count() > 0)
                                    <h5 class="mb-3">Select UPI Account</h5>
                                    <div class="mb-4">
                                        @foreach ($upiAccounts as $index => $upiAccount)
                                            <label class="payment-method-option" for="upi_account_{{ $upiAccount->id }}">
                                                <input type="radio" name="selected_account_id"
                                                    id="upi_account_{{ $upiAccount->id }}" value="{{ $upiAccount->id }}"
                                                    data-type="upi" data-index="{{ $index }}" required>
                                                <div class="d-flex align-items-center">
                                                    <i class="ti ti-qrcode me-3"
                                                        style="font-size: 1.5rem; color: #000000;"></i>
                                                    <div>
                                                        <h6 class="mb-1">
                                                            {{ $upiAccount->name ?? 'UPI Account ' . ($index + 1) }}</h6>
                                                        <p class="text-muted mb-0 small">{{ $upiAccount->upi_id }}</p>
                                                    </div>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>

                                    @foreach ($upiAccounts as $index => $upiAccount)
                                        <div class="qr-code-container mb-4 upi-account-details"
                                            id="upi_details_{{ $upiAccount->id }}" style="display: none;">
                                            <h5 class="mb-3">{{ $upiAccount->name ?? 'UPI Account' }}</h5>
                                            @php
                                                $upiId = $upiAccount->upi_id ?? '';
                                                $upiNumber = $upiAccount->upi_number ?? '';
                                                $merchantName = $upiAccount->name ?? 'Merchant';
                                                // Create UPI payment URL
                                                $upiPaymentUrl = "upi://pay?pa={$upiId}&pn={$merchantName}&am={$amount}&cu=INR&tn=Payment";
                                            @endphp
                                            <div id="qrcode-{{ $index }}" class="mb-3"></div>
                                            <!-- UPI app logos (click to copy UPI ID) -->
                                            <div class="upi-app-logos" aria-hidden="false">
                                                <img class="upi-app-logo"
                                                    src="https://img.icons8.com/?size=100&id=am4ltuIYDpQ5&format=png&color=000000"
                                                    alt="Google Pay" title="Copy UPI ID to clipboard"
                                                    data-upi="{{ $upiId }}">
                                                <img class="upi-app-logo"
                                                    src="https://img.icons8.com/?size=100&id=OYtBxIlJwMGA&format=png&color=000000"
                                                    alt="PhonePe" title="Copy UPI ID to clipboard"
                                                    data-upi="{{ $upiId }}">
                                                <img class="upi-app-logo"
                                                    src="https://img.icons8.com/?size=100&id=68067&format=png&color=000000"
                                                    alt="Paytm" title="Copy UPI ID to clipboard"
                                                    data-upi="{{ $upiId }}">
                                                <img class="upi-app-logo"
                                                    src="https://img.icons8.com/?size=100&id=5RcHTSNy4fbL&format=png&color=000000"
                                                    alt="BHIM" title="Copy UPI ID to clipboard"
                                                    data-upi="{{ $upiId }}">
                                            </div>
                                            <div class="upi-details mt-3">
                                                @if ($upiAccount->name)
                                                    <p class="mb-1"><strong>Account Name:</strong>
                                                        {{ $upiAccount->name }}
                                                    </p>
                                                @endif
                                                @if ($upiAccount->upi_id)
                                                    <p class="mb-1"><strong>UPI ID:</strong> {{ $upiAccount->upi_id }}
                                                    </p>
                                                @endif
                                                @if ($upiAccount->upi_number)
                                                    <p class="mb-1"><strong>UPI Number:</strong>
                                                        {{ $upiAccount->upi_number }}</p>
                                                @endif
                                                <p class="mb-1"><strong>Amount:</strong>
                                                    ₹{{ number_format($amount, 2) }}
                                                </p>
                                            </div>
                                            <!-- Hidden data for QR generation -->
                                            <input type="hidden" class="upiPaymentUrl" value="{{ $upiPaymentUrl }}"
                                                data-qr-id="qrcode-{{ $index }}">
                                        </div>
                                    @endforeach
                                @else
                                    <div class="alert alert-danger">
                                        <i class="ti ti-alert-circle me-2"></i>No UPI accounts available at the moment.
                                        Please
                                        try bank transfer or contact support.
                                    </div>
                                @endif
                            @else
                                <div class="alert alert-danger">
                                    <i class="ti ti-alert-circle me-2"></i>No UPI details available for this account.
                                </div>
                            @endif
                        </div>

                        <!-- Bank Transfer Section -->
                        <div id="bankPaymentSection">
                            @if (isset($selectedAccount) && $selectedAccount->account_number)
                                {{-- Pre-selected account: Show bank details --}}
                                <div class="alert alert-warning mb-4">
                                    <h6 class="alert-heading">{{ $selectedAccount->name ?? 'Bank Account' }}</h6>
                                    @if ($selectedAccount->name)
                                        <p class="mb-1"><strong>Account Name:</strong> {{ $selectedAccount->name }}</p>
                                    @endif
                                    @if ($selectedAccount->bank_name)
                                        <p class="mb-1"><strong>Bank Name:</strong> {{ $selectedAccount->bank_name }}
                                        </p>
                                    @endif
                                    @if ($selectedAccount->account_holder_name)
                                        <p class="mb-1"><strong>Account Holder:</strong>
                                            {{ $selectedAccount->account_holder_name }}</p>
                                    @endif
                                    <p class="mb-1">
                                        <strong>Account Number:</strong>
                                        <span>{{ $selectedAccount->account_number }}</span>
                                        <button type="button" class="btn btn-sm btn-outline-secondary ms-2 copy-btn"
                                            data-copy="{{ $selectedAccount->account_number }}"
                                            title="Copy account number">
                                            <i class="ti ti-files"></i>
                                        </button>
                                    </p>
                                    @if ($selectedAccount->ifsc_code)
                                        <p class="mb-1">
                                            <strong>IFSC Code:</strong>
                                            <span>{{ $selectedAccount->ifsc_code }}</span>
                                            <button type="button" class="btn btn-sm btn-outline-secondary ms-2 copy-btn"
                                                data-copy="{{ $selectedAccount->ifsc_code }}" title="Copy IFSC code">
                                                <i class="ti ti-files"></i>
                                            </button>
                                        </p>
                                    @endif
                                    @if ($selectedAccount->branch_name)
                                        <p class="mb-1"><strong>Branch:</strong> {{ $selectedAccount->branch_name }}</p>
                                    @endif
                                    <p class="mb-1"><strong>Amount:</strong> ₹{{ number_format($amount, 2) }}</p>
                                </div>
                            @elseif(isset($accounts))
                                {{-- Normal mode: Show account selection --}}
                                @php
                                    $bankAccounts = $accounts->filter(function ($acc) {
                                        return !empty($acc->account_number);
                                    });
                                @endphp
                                @if ($bankAccounts->count() > 0)
                                    <h5 class="mb-3">Select Bank Account</h5>
                                    <div class="mb-4">
                                        @foreach ($bankAccounts as $index => $bankAccount)
                                            <label class="payment-method-option"
                                                for="bank_account_{{ $bankAccount->id }}">
                                                <input type="radio" name="selected_account_id"
                                                    id="bank_account_{{ $bankAccount->id }}"
                                                    value="{{ $bankAccount->id }}" data-type="bank"
                                                    data-index="{{ $index }}" required>
                                                <div class="d-flex align-items-center">
                                                    <i class="ti ti-building-bank me-3"
                                                        style="font-size: 1.5rem; color: #000000;"></i>
                                                    <div>
                                                        <h6 class="mb-1">
                                                            {{ $bankAccount->name ?? 'Bank Account ' . ($index + 1) }}</h6>
                                                        <p class="text-muted mb-0 small">{{ $bankAccount->bank_name }} -
                                                            {{ $bankAccount->account_number }}</p>
                                                    </div>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>

                                    @foreach ($bankAccounts as $index => $bankAccount)
                                        <div class="alert alert-warning mb-4 bank-account-details"
                                            id="bank_details_{{ $bankAccount->id }}" style="display: none;">
                                            <h6 class="alert-heading">{{ $bankAccount->name ?? 'Bank Account' }}</h6>
                                            {{-- @if ($bankAccount->name)
                                                <p class="mb-1"><strong>Account Name:</strong> {{ $bankAccount->name }}
                                                </p>
                                            @endif
                                            @if ($bankAccount->bank_name)
                                                <p class="mb-1"><strong>Bank Name:</strong>
                                                    {{ $bankAccount->bank_name }}
                                                </p>
                                            @endif
                                            @if ($bankAccount->account_holder_name)
                                                <p class="mb-1"><strong>Account Holder:</strong>
                                                    {{ $bankAccount->account_holder_name }}</p>
                                            @endif --}}
                                            <p class="mb-1">
                                                <strong>Account Number:</strong>
                                                <span
                                                    id="accountNumber-{{ $index }}">{{ $bankAccount->account_number }}</span>
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-secondary ms-2 copy-btn"
                                                    data-copy="{{ $bankAccount->account_number }}"
                                                    title="Copy account number" aria-label="Copy account number">
                                                    <i class="ti ti-files"></i>
                                                </button>
                                            </p>
                                            <p class="mb-1">
                                                <strong>IFSC Code:</strong>
                                                <span
                                                    id="ifscCode-{{ $index }}">{{ $bankAccount->ifsc_code }}</span>
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-secondary ms-2 copy-btn"
                                                    data-copy="{{ $bankAccount->ifsc_code }}" title="Copy IFSC code"
                                                    aria-label="Copy IFSC code">
                                                    <i class="ti ti-files"></i>
                                                </button>
                                            </p>
                                            @if ($bankAccount->branch_name)
                                                <p class="mb-1"><strong>Branch:</strong> {{ $bankAccount->branch_name }}
                                                </p>
                                            @endif
                                            <p class="mb-1"><strong>Amount:</strong> ₹{{ number_format($amount, 2) }}
                                            </p>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="alert alert-danger">
                                        <i class="ti ti-alert-circle me-2"></i>No bank accounts available at the moment.
                                        Please
                                        try UPI payment or contact support.
                                    </div>
                                @endif
                            @else
                                <div class="alert alert-danger">
                                    <i class="ti ti-alert-circle me-2"></i>No bank account details available for this
                                    account.
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
                                <div id="screenshotFeedback" class="form-text text-muted mt-1"></div>
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
    <!-- Tesseract.js for client-side OCR to auto-detect UTR from screenshots -->
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@2.1.5/dist/tesseract.min.js"></script>
@endsection

@section('page-script')
    <script>
        $(function() {
            // Check if we have a pre-selected account
            var isPreSelectedAccount = $('input[name="selected_account_id"][type="hidden"]').length > 0;

            // Generate QR Code for pre-selected account if UPI is selected
            function generateSelectedAccountQR() {
                var upiUrl = $('#selectedUpiPaymentUrl').val();
                if (upiUrl) {
                    var qr = qrcode(0, 'M');
                    qr.addData(upiUrl);
                    qr.make();
                    var qrImage = qr.createImgTag(6, 8);
                    $('#qrcode-selected').html(qrImage);
                }
            }

            // Generate QR Code for all UPI payments (normal mode)
            function generateQRCodes() {
                $('.upiPaymentUrl').each(function() {
                    var upiUrl = $(this).val();
                    var qrId = $(this).data('qr-id');
                    if (upiUrl && qrId) {
                        var qr = qrcode(0, 'M');
                        qr.addData(upiUrl);
                        qr.make();

                        // Create image
                        var qrImage = qr.createImgTag(6, 8); // size 6, margin 8
                        $('#' + qrId).html(qrImage);
                    }
                });
            }

            // Click on UPI app logo to copy UPI ID to clipboard
            $(document).on('click', '.upi-app-logo', function() {
                var upi = $(this).data('upi');
                if (!upi) {
                    alert('No UPI ID available to copy.');
                    return;
                }

                // Try clipboard API, fallback to prompt
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(upi).then(function() {
                        alert('UPI ID copied: ' + upi);
                    }).catch(function() {
                        prompt('Copy UPI ID', upi);
                    });
                } else {
                    prompt('Copy UPI ID', upi);
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

            // Handle payment method selection (UPI or Bank)
            $('input[name="payment_method"]').on('change', function() {
                var selectedMethod = $(this).val();

                // Hide all sections
                $('#upiPaymentSection, #bankPaymentSection').hide();
                $('.upi-account-details, .bank-account-details').hide();

                // Show relevant section
                if (selectedMethod === 'upi') {
                    $('#upiPaymentSection').slideDown(function() {
                        // If pre-selected account, generate QR code immediately
                        if (isPreSelectedAccount) {
                            generateSelectedAccountQR();
                            $('#uploadSection').slideDown();
                            $('#submitBtn').prop('disabled', false);
                        }
                    });
                } else if (selectedMethod === 'bank') {
                    $('#bankPaymentSection').slideDown(function() {
                        // If pre-selected account, show upload section immediately
                        if (isPreSelectedAccount) {
                            $('#uploadSection').slideDown();
                            $('#submitBtn').prop('disabled', false);
                        }
                    });
                }

                // For normal mode, hide upload section until account is selected
                if (!isPreSelectedAccount) {
                    $('#uploadSection').hide();
                    $('#submitBtn').prop('disabled', true);
                }
            });

            // Handle account selection
            $('input[name="selected_account_id"]').on('change', function() {
                var accountId = $(this).val();
                var accountType = $(this).data('type');

                // Hide all account details
                $('.upi-account-details, .bank-account-details').hide();

                // Show selected account details
                if (accountType === 'upi') {
                    $('#upi_details_' + accountId).slideDown(function() {
                        // Generate QR code for selected account only
                        var qrUrl = $('#upi_details_' + accountId).find('.upiPaymentUrl').val();
                        var qrId = $('#upi_details_' + accountId).find('.upiPaymentUrl').data(
                            'qr-id');
                        if (qrUrl && qrId) {
                            var qr = qrcode(0, 'M');
                            qr.addData(qrUrl);
                            qr.make();
                            var qrImage = qr.createImgTag(6, 8);
                            $('#' + qrId).html(qrImage);
                        }
                    });
                } else if (accountType === 'bank') {
                    $('#bank_details_' + accountId).slideDown();
                }

                // Show upload section
                $('#uploadSection').slideDown();
                $('#submitBtn').prop('disabled', false);
            });

            // Auto-format UTR to numbers only
            $('#utr').on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });

            // Helper: compute SHA-256 hex of a File (returns Promise<string>)
            async function fileSha256Hex(file) {
                const arrayBuffer = await file.arrayBuffer();
                const hashBuffer = await crypto.subtle.digest('SHA-256', arrayBuffer);
                const hashArray = Array.from(new Uint8Array(hashBuffer));
                return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
            }

            // Manage recent uploaded hashes in sessionStorage to detect immediate duplicates
            function getRecentScreenshotHashes() {
                try {
                    const raw = sessionStorage.getItem('uploaded_screenshot_hashes') || '[]';
                    return JSON.parse(raw);
                } catch (e) {
                    return [];
                }
            }

            function pushScreenshotHash(hash) {
                const arr = getRecentScreenshotHashes();
                arr.push({
                    h: hash,
                    t: Date.now()
                });
                // Keep recent 50 entries max
                while (arr.length > 50) arr.shift();
                sessionStorage.setItem('uploaded_screenshot_hashes', JSON.stringify(arr));
            }

            // Clean old entries (older than 24 hours)
            function cleanupScreenshotHashes() {
                const DAY = 24 * 60 * 60 * 1000;
                const arr = getRecentScreenshotHashes().filter(x => (Date.now() - x.t) < DAY);
                sessionStorage.setItem('uploaded_screenshot_hashes', JSON.stringify(arr));
            }

            // Try to OCR the image and return extracted text (or empty)
            async function ocrImage(file) {
                try {
                    const {
                        data: {
                            text
                        }
                    } = await Tesseract.recognize(file, 'eng', {
                        logger: m => console.log('Tesseract', m)
                    });
                    return text || '';
                } catch (e) {
                    console.warn('OCR failed', e);
                    return '';
                }
            }

            // When screenshot changes: detect duplicate quickly and run OCR to extract 12-digit UTR
            $('#screenshot').on('change', async function() {
                const file = this.files && this.files[0];
                const feedback = $('#screenshotFeedback');
                $('#utr').val('');
                feedback.removeClass('text-danger text-success').addClass('text-muted').text('');
                $('#submitBtn').prop('disabled', true);
                if (!file) return;

                cleanupScreenshotHashes();

                // Compute hash
                let hash = null;
                try {
                    feedback.text('Processing image...');
                    hash = await fileSha256Hex(file);
                } catch (e) {
                    console.error('Hash failed', e);
                }

                if (hash) {
                    const recent = getRecentScreenshotHashes();
                    // If same hash exists within last 60s, block as duplicate
                    const DUP_WINDOW = 60 * 1000; // 60 seconds
                    const found = recent.find(x => x.h === hash && (Date.now() - x.t) < DUP_WINDOW);
                    if (found) {
                        feedback.addClass('text-danger').text(
                            'This screenshot was just uploaded — duplicates are not allowed right now.'
                        );
                        // clear input
                        $(this).val('');
                        return;
                    }
                    // remember hash
                    pushScreenshotHash(hash);
                }

                // Run OCR to extract UTR (12-digit number)
                feedback.text('Running OCR to detect UTR — this may take a few seconds...');
                const text = await ocrImage(file);
                // Look for 12-digit UTR pattern
                const match = String(text).match(/\b(\d{12})\b/);
                if (match) {
                    const utr = match[1];
                    $('#utr').val(utr);
                    feedback.removeClass('text-muted').addClass('text-success').text(
                        'UTR detected and filled automatically. Please verify and submit.');
                    $('#submitBtn').prop('disabled', false);
                } else {
                    feedback.removeClass('text-muted').addClass('text-danger').text(
                        'No UTR found automatically — please enter it manually in the UTR field.');
                    // Allow manual entry: enable submit only once UTR input has 12 digits
                    $('#utr').focus();
                    // Enable submit only when UTR is valid
                    $('#utr').off('input.utrCheck').on('input.utrCheck', function() {
                        const val = $(this).val().replace(/[^0-9]/g, '');
                        $(this).val(val);
                        if (/^\d{12}$/.test(val)) {
                            $('#submitBtn').prop('disabled', false);
                            feedback.removeClass('text-danger').addClass('text-success').text(
                                'UTR looks valid. You may submit.');
                        } else {
                            $('#submitBtn').prop('disabled', true);
                            feedback.removeClass('text-success').addClass('text-danger').text(
                                'Please enter a 12-digit UTR to enable submission.');
                        }
                    });
                }
            });
        });
    </script>
@endsection
