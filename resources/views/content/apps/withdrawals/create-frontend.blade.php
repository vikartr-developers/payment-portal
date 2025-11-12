@extends('layouts/layoutMaster')

@section('title', 'Request Payout')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
@endsection

@section('page-style')
    <style>
        .payout-card {
            border-radius: 18px;
            box-shadow: 0 4px 18px rgba(153, 164, 188, 0.16);
            border: none;
        }

        .form-label {
            font-weight: 600;
            color: #566a7f;
            margin-bottom: 0.5rem;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            border: 1px solid #d9dee3;
            padding: 0.625rem 0.875rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #696cff;
            box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.15);
        }

        .upload-area {
            border: 2px dashed #d9dee3;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .upload-area:hover {
            border-color: #696cff;
            background: #f3f4ff;
        }

        .upload-area.dragover {
            border-color: #696cff;
            background: #e7e7ff;
        }

        .upload-icon {
            font-size: 3rem;
            color: #696cff;
            margin-bottom: 1rem;
        }

        .preview-image {
            max-width: 100%;
            max-height: 300px;
            border-radius: 8px;
            margin-top: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-submit {
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .required-mark {
            color: #ff4c51;
        }

        .info-text {
            font-size: 0.875rem;
            color: #697a8d;
            margin-top: 0.25rem;
        }

        .section-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #566a7f;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #f1f1f1;
        }
    </style>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card payout-card">
                <div class="card-header bg-primary text-white">
                    <h4 class="card-title text-white mb-0">
                        <i class="ti ti-wallet me-2"></i>Request Payout
                    </h4>
                    <p class="card-text text-white-50 mb-0 mt-2">Fill in the details below to submit your payout request
                    </p>
                </div>

                <div class="card-body p-4">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="ti ti-check me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="ti ti-alert-triangle me-2"></i>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('payout.request.store') }}" method="POST" enctype="multipart/form-data"
                        id="payoutForm">
                        @csrf

                        <!-- Bank Details Section -->
                        <div class="section-title">
                            <i class="ti ti-building-bank me-2"></i>Bank Account Details
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="account_holder_name" class="form-label">
                                    Account Holder Name <span class="required-mark">*</span>
                                </label>
                                <input type="text" class="form-control" id="account_holder_name"
                                    name="account_holder_name" value="{{ old('account_holder_name') }}" required
                                    placeholder="Enter account holder name as per bank records">
                                <div class="info-text">Enter the name exactly as it appears on your bank account</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="account_number" class="form-label">
                                    Account Number <span class="required-mark">*</span>
                                </label>
                                <input type="text" class="form-control" id="account_number" name="account_number"
                                    value="{{ old('account_number') }}" required placeholder="Enter account number">
                            </div>
                            <div class="col-md-6">
                                <label for="confirm_account_number" class="form-label">
                                    Confirm Account Number <span class="required-mark">*</span>
                                </label>
                                <input type="text" class="form-control" id="confirm_account_number"
                                    name="confirm_account_number" value="{{ old('confirm_account_number') }}" required
                                    placeholder="Re-enter account number">
                                <div class="info-text">Account numbers must match</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="branch_name" class="form-label">
                                    Branch Name
                                </label>
                                <input type="text" class="form-control" id="branch_name" name="branch_name"
                                    value="{{ old('branch_name') }}" placeholder="Enter branch name (optional)">
                            </div>
                            <div class="col-md-6">
                                <label for="ifsc_code" class="form-label">
                                    IFSC Code
                                </label>
                                <input type="text" class="form-control text-uppercase" id="ifsc_code" name="ifsc_code"
                                    value="{{ old('ifsc_code') }}" placeholder="e.g., SBIN0001234" maxlength="11">
                                <div class="info-text">11-character code (e.g., SBIN0001234)</div>
                            </div>
                        </div>

                        <!-- Amount Section -->
                        <div class="section-title mt-4">
                            <i class="ti ti-currency-rupee me-2"></i>Payout Amount
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="amount" class="form-label">
                                    Amount (₹) <span class="required-mark">*</span>
                                </label>
                                <input type="number" class="form-control" id="amount" name="amount"
                                    value="{{ old('amount') }}" required min="1" step="0.01"
                                    placeholder="Enter payout amount">
                                <div class="info-text">Minimum amount: ₹1.00</div>
                            </div>
                        </div>

                        <!-- Screenshot Upload Section -->
                        <div class="section-title mt-4">
                            <i class="ti ti-photo me-2"></i>Upload Screenshot (Optional)
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label for="screenshot" class="form-label">
                                    Payment Screenshot
                                </label>
                                <div class="upload-area" id="uploadArea">
                                    <input type="file" class="d-none" id="screenshot" name="screenshot"
                                        accept="image/*">
                                    <i class="ti ti-cloud-upload upload-icon"></i>
                                    <h5 class="mb-2">Click to upload or drag and drop</h5>
                                    <p class="text-muted mb-0">PNG, JPG or JPEG (Max 2MB)</p>
                                </div>
                                <div id="imagePreview" class="text-center" style="display: none;">
                                    <img src="" alt="Preview" class="preview-image" id="previewImg">
                                    <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="removeImage">
                                        <i class="ti ti-x me-1"></i>Remove
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary btn-submit me-2">
                                    <i class="ti ti-check me-2"></i>Submit Payout Request
                                </button>
                                <a href="{{ route('withdrawals.index') }}" class="btn btn-outline-secondary btn-submit">
                                    <i class="ti ti-x me-2"></i>Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script>
        $(function() {
            const uploadArea = $('#uploadArea');
            const fileInput = $('#screenshot');
            const imagePreview = $('#imagePreview');
            const previewImg = $('#previewImg');
            const removeImageBtn = $('#removeImage');

            // Click to upload
            uploadArea.on('click', function() {
                fileInput.click();
            });

            // File input change
            fileInput.on('change', function(e) {
                handleFile(e.target.files[0]);
            });

            // Drag and drop
            uploadArea.on('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('dragover');
            });

            uploadArea.on('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('dragover');
            });

            uploadArea.on('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('dragover');

                const files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    fileInput[0].files = files;
                    handleFile(files[0]);
                }
            });

            // Remove image
            removeImageBtn.on('click', function() {
                fileInput.val('');
                imagePreview.hide();
                uploadArea.show();
            });

            function handleFile(file) {
                if (!file) return;

                // Validate file type
                if (!file.type.match('image.*')) {
                    alert('Please upload an image file (PNG, JPG, or JPEG)');
                    return;
                }

                // Validate file size (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('File size must be less than 2MB');
                    return;
                }

                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.attr('src', e.target.result);
                    uploadArea.hide();
                    imagePreview.show();
                };
                reader.readAsDataURL(file);
            }

            // Form validation
            $('#payoutForm').on('submit', function(e) {
                const accountNumber = $('#account_number').val();
                const confirmAccountNumber = $('#confirm_account_number').val();

                if (accountNumber !== confirmAccountNumber) {
                    e.preventDefault();
                    alert('Account numbers do not match. Please check and try again.');
                    $('#confirm_account_number').focus();
                    return false;
                }
            });

            // Auto-uppercase IFSC code
            $('#ifsc_code').on('input', function() {
                this.value = this.value.toUpperCase();
            });
        });
    </script>
@endsection
