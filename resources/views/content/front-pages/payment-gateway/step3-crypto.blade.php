@extends('layouts/layoutMaster')


@section('title', 'Payment Details - Crypto Payment')

@section('page-style')
    <style>
        .payment-card {
            max-width: 700px;
            margin: 40px auto;
        }

        .crypto-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
        }

        .wallet-address {
            background: white;
            padding: 15px;
            border-radius: 8px;
            word-break: break-all;
            font-family: monospace;
            font-size: 0.9rem;
            margin: 15px 0;
        }

        .qr-placeholder {
            width: 250px;
            height: 250px;
            margin: 20px auto;
            background: white;
            border: 2px dashed #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
        }
    </style>
@endsection

@section('content')
    <section class="section-py bg-body first-section-pt">
        <div class="container">
            <div class="card payment-card">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h3 class="mb-2">Crypto Payment</h3>
                        <div class="alert alert-info">
                            <strong>Amount to Pay: ₹{{ number_format($amount, 2) }}</strong>
                        </div>
                    </div>

                    <div class="crypto-details">
                        <h5 class="mb-3">Send Payment To</h5>
                        <div class="qr-placeholder">
                            <div>
                                <i class="ti ti-qrcode" style="font-size: 4rem; color: #ccc;"></i>
                                <p class="text-muted mt-2">Wallet QR Code</p>
                            </div>
                        </div>

                        <div class="wallet-address">
                            <small class="text-muted d-block mb-2">Wallet Address (USDT TRC20)</small>
                            <strong>TXyz123abc456def789ghi012jkl345mno678</strong>
                        </div>

                        <div class="alert alert-warning">
                            <i class="ti ti-alert-triangle me-2"></i>
                            <small>Only send USDT via TRC20 network. Sending other coins or networks will result in loss of
                                funds.</small>
                        </div>
                    </div>

                    <form action="{{ route('payment.process') }}" method="POST" enctype="multipart/form-data"
                        class="mt-4">
                        @csrf
                        <input type="hidden" name="payment_method" value="crypto">

                        <h5 class="mb-3">Upload Payment Proof</h5>

                        <div class="mb-3">
                            <label for="screenshot" class="form-label">Upload Transaction Screenshot <span
                                    class="text-danger">*</span></label>
                            <input type="file" id="screenshot" name="screenshot"
                                class="form-control @error('screenshot') is-invalid @enderror" accept="image/*" required>
                            <small class="text-muted">Upload screenshot of successful transaction</small>
                            @error('screenshot')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="utr" class="form-label">Transaction Hash / ID <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="utr" name="utr"
                                class="form-control @error('utr') is-invalid @enderror" placeholder="Enter transaction hash"
                                maxlength="66" required>
                            <small class="text-muted">Enter the transaction hash from your crypto wallet</small>
                            @error('utr')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-3 mt-4">
                            <a href="{{ route('payment.gateway') }}" class="btn btn-outline-secondary btn-lg flex-fill">
                                <i class="ti ti-arrow-left me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg flex-fill">
                                Submit Payment<i class="ti ti-check ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
