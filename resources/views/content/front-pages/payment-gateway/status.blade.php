@extends('layouts/layoutMaster')

@section('title', 'Payment Status - Transaction ' . $display_transaction_id)

@section('page-style')
    <style>
        .status-container {
            max-width: 700px;
            margin: 40px auto;
            padding: 20px;
        }

        .status-card {
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .status-header {
            background: linear-gradient(106deg, #000000 25%, #727272 69%, #000000 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .transaction-id {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .status-badge {
            display: inline-block;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: 600;
            font-size: 1rem;
            margin-top: 1rem;
        }

        .status-pending {
            background: #ffc107;
            color: #000;
        }

        .status-accepted {
            background: #71dd37;
            color: #fff;
        }

        .status-rejected {
            background: #ff3e1d;
            color: #fff;
        }

        .status-progress {
            background: #696cff;
            color: #fff;
        }

        /* Success Animation */
        .success-animation {
            display: none;
            text-align: center;
            padding: 3rem;
        }

        .success-animation.show {
            display: block;
        }

        .checkmark-circle {
            width: 150px;
            height: 150px;
            margin: 0 auto 2rem;
            border-radius: 50%;
            background: #71dd37;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: scaleIn 0.5s ease-out;
        }

        .checkmark {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: block;
            stroke-width: 5;
            stroke: #fff;
            stroke-miterlimit: 10;
            animation: fill 0.4s ease-in-out 0.4s forwards, scale 0.3s ease-in-out 0.9s both;
        }

        .checkmark-check {
            transform-origin: 50% 50%;
            stroke-dasharray: 48;
            stroke-dashoffset: 48;
            animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
        }

        @keyframes scaleIn {
            0% {
                transform: scale(0);
            }

            100% {
                transform: scale(1);
            }
        }

        @keyframes stroke {
            100% {
                stroke-dashoffset: 0;
            }
        }

        @keyframes fill {
            100% {
                box-shadow: inset 0px 0px 0px 30px #71dd37;
            }
        }

        @keyframes scale {

            0%,
            100% {
                transform: none;
            }

            50% {
                transform: scale3d(1.1, 1.1, 1);
            }
        }

        /* Upload Section */
        .upload-section {
            padding: 2rem;
        }

        .upload-area {
            border: 2px dashed #d9dee3;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .upload-area:hover {
            border-color: #000000;
            background: #f3f4ff;
        }

        .status-details {
            padding: 2rem;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            border-bottom: 1px solid #f1f1f1;
        }

        .detail-label {
            font-weight: 600;
            color: #566a7f;
        }

        .detail-value {
            color: #000;
            font-weight: 500;
        }

        /* Loading Spinner */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #000000;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 10px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .auto-refresh-badge {
            background: #e7e7ff;
            color: #696cff;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
    </style>
@endsection

@section('content')
    <section class="section-py bg-body first-section-pt">
        <div class="container">
            <div class="status-container">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="ti ti-check me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="ti ti-alert-triangle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="card status-card">
                    <!-- Header -->
                    <div class="status-header">
                        <h1 class="transaction-id">{{ $display_transaction_id }}</h1>
                        <p class="mb-0">Transaction Details</p>
                        <div id="statusBadge">
                            @php
                                $statusClass = match ($request->status) {
                                    'pending' => 'status-pending',
                                    'accepted' => 'status-accepted',
                                    'rejected' => 'status-rejected',
                                    default => 'status-progress',
                                };
                                $statusText = match ($request->status) {
                                    'pending' => 'Pending Approval',
                                    'accepted' => 'Approved',
                                    'rejected' => 'Rejected',
                                    default => 'In Progress',
                                };
                            @endphp
                            <span class="status-badge {{ $statusClass }}">
                                {{ $statusText }}
                                <span class="loading-spinner" id="loadingSpinner"></span>
                            </span>
                        </div>
                        <div class="mt-3">
                            <span class="auto-refresh-badge">
                                <i class="ti ti-refresh"></i>
                                Auto-checking status every 2 seconds
                            </span>
                        </div>
                    </div>

                    <!-- Success Animation (shown when approved) -->
                    <div class="success-animation" id="successAnimation">
                        <div class="checkmark-circle">
                            <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                                <circle class="checkmark-check" cx="26" cy="26" r="25" fill="none" />
                                <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" />
                            </svg>
                        </div>
                        <h2 class="text-success mb-3">Payment Approved!</h2>
                        <p class="text-muted">Your transaction has been successfully approved.</p>
                        <p class="fw-bold">Amount: ₹{{ number_format($request->amount, 2) }}</p>
                        @if ($request->accepted_at)
                            <small class="text-muted">Approved at:

                                {{ $request->accepted_at->format('d M Y, h:i A') }}</small>
                        @endif
                    </div>

                    <!-- Details Section -->
                    <div class="status-details" id="detailsSection">
                        <div class="detail-item">
                            <span class="detail-label">Name</span>
                            <span class="detail-value">{{ $request->name }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Amount</span>
                            <span class="detail-value">₹{{ number_format($request->amount, 2) }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Payment Method</span>
                            <span class="detail-value text-uppercase">{{ $request->mode ?: 'Not Set' }}</span>
                        </div>
                        @if ($request->utr)
                            <div class="detail-item">
                                <span class="detail-label">UTR Number</span>
                                <span class="detail-value">{{ $request->utr }}</span>
                            </div>
                        @endif
                        <div class="detail-item">
                            <span class="detail-label">Created At</span>
                            <span class="detail-value">{{ $request->created_at->format('d M Y, h:i A') }}</span>
                        </div>
                        @if ($request->accepted_at)
                            <div class="detail-item">
                                <span class="detail-label">Approved At</span>
                                <span class="detail-value text-success">
                                    {{ $request->accepted_at->format('d M Y, h:i A') }}</span>
                            </div>
                        @endif
                        @if ($request->rejected_at)
                            <div class="detail-item">
                                <span class="detail-label">Rejected At</span>
                                <span class="detail-value text-danger">
                                    {{ $request->rejected_at->format('d M Y, h:i A') }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Upload Section (if not yet uploaded) -->
                    @if (!$request->utr || !$request->image)
                        <div class="upload-section">
                            <h5 class="mb-3">Upload Payment Proof</h5>
                            <form action="{{ route('payment.upload', $transaction_id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf

                                <div class="mb-3">
                                    <label for="utr" class="form-label">UTR / Transaction ID <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="utr" name="utr"
                                        value="{{ $request->utr }}" placeholder="Enter 12-digit UTR number" maxlength="12"
                                        pattern="[0-9]{12}" required>
                                    <small class="text-muted">Enter the 12-digit UTR number from your payment</small>
                                </div>

                                <div class="mb-3">
                                    <label for="screenshot" class="form-label">Payment Screenshot <span
                                            class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="screenshot" name="screenshot"
                                        accept="image/*" required>
                                    <small class="text-muted">Upload screenshot of successful payment (Max 2MB)</small>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="ti ti-upload me-2"></i>Upload Payment Proof
                                </button>
                            </form>
                        </div>
                    @endif

                    <!-- View Screenshot -->
                    @if ($request->image)
                        <div class="upload-section">
                            <h5 class="mb-3">Payment Screenshot</h5>
                            <div class="text-center">
                                <img src="{{ asset($request->image) }}" alt="Payment Screenshot"
                                    style="max-width: 100%; max-height: 400px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                            </div>
                        </div>
                    @endif
                </div>

                <div class="text-center mt-4">
                    <a href="{{ route('payment.gateway') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left me-2"></i>Make Another Payment
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('page-script')
    <script>
        $(function() {
            let currentStatus = '{{ $request->status }}';
            let checkInterval;

            // Auto-format UTR to numbers only
            $('#utr').on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });

            // Function to check status
            function checkStatus() {
                $.ajax({
                    url: '{{ route('payment.check-status', $transaction_id) }}',
                    method: 'GET',
                    success: function(response) {
                        if (response.success && response.status !== currentStatus) {
                            currentStatus = response.status;
                            updateStatusDisplay(response);
                        }
                    },
                    error: function() {
                        console.log('Failed to check status');
                    }
                });
            }

            // Function to update status display
            function updateStatusDisplay(data) {
                const statusBadge = $('#statusBadge');
                let badgeClass = 'status-progress';
                let statusText = 'In Progress';

                if (data.status === 'accepted') {
                    badgeClass = 'status-accepted';
                    statusText = 'Approved';

                    // Show success animation
                    $('#detailsSection').fadeOut(300, function() {
                        $('#successAnimation').addClass('show').hide().fadeIn(500);
                    });

                    // Stop checking
                    clearInterval(checkInterval);
                    $('#loadingSpinner').hide();

                    // Confetti effect (optional - requires canvas-confetti library)
                    if (typeof confetti !== 'undefined') {
                        confetti({
                            particleCount: 100,
                            spread: 70,
                            origin: {
                                y: 0.6
                            }
                        });
                    }
                } else if (data.status === 'rejected') {
                    badgeClass = 'status-rejected';
                    statusText = 'Rejected';
                    clearInterval(checkInterval);
                    $('#loadingSpinner').hide();
                } else if (data.status === 'pending') {
                    badgeClass = 'status-pending';
                    statusText = 'Pending Approval';
                }

                statusBadge.html(
                    `<span class="status-badge ${badgeClass}">${statusText} <span class="loading-spinner" id="loadingSpinner"></span></span>`
                );

                // Reload page to show updated details
                if (data.status === 'accepted' || data.status === 'rejected') {
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                }
            }

            // Start auto-checking if status is pending or progress
            if (currentStatus === 'pending' || currentStatus === 'progress') {
                checkInterval = setInterval(checkStatus, 2000); // Check every 2 seconds
            } else {
                $('#loadingSpinner').hide();

                // Show success animation if already approved
                if (currentStatus === 'accepted') {
                    $('#detailsSection').hide();
                    $('#successAnimation').addClass('show');
                }
            }

            // Cleanup on page unload
            $(window).on('beforeunload', function() {
                if (checkInterval) {
                    clearInterval(checkInterval);
                }
            });
        });
    </script>
@endsection
