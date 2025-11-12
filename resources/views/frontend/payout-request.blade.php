@extends('layouts/layoutMaster')

@section('title', 'Request Payout - Payment Portal')

@section('page-style')
    <style>
        /* :root {
                                                                        --primary-color: #000000;
                                                                        --primary-dark: #5f61e6;
                                                                        --secondary-color: #8592a3;
                                                                        --success-color: #71dd37;
                                                                        --danger-color: #ff3e1d;
                                                                        --light-bg: #f5f5f9;
                                                                    } */

        body {
            /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                                                                    min-height: 100vh;
                                                                    padding: 2rem 0; */
        }
    </style>
@endsection

@section('content')

    <style>
        .payout-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .payout-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .card-header-custom {

            background: linear-gradient(106deg, #000000 25%, #727272 69%, #000000 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .card-header-custom h1 {
            font-size: 1.75rem;
            font-weight: 600;
            margin: 0 0 0.5rem 0;
            color: white;
        }

        .card-header-custom p {
            margin: 0;
            opacity: 0.95;
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .card-body-custom {
            padding: 2.5rem;
        }

        .section-title {
            font-size: 1.15rem;
            font-weight: 600;
            color: #566a7f;
            margin: 1.5rem 0 1.5rem 0;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #f1f1f1;
            display: flex;
            align-items: center;
        }

        .section-title:first-of-type {
            margin-top: 0;
        }

        .section-title i {
            margin-right: 0.5rem;
            color: #000000;
            font-size: 1.3rem;
        }

        .form-label {
            font-weight: 600;
            color: #566a7f;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .required-mark {
            color: #ff3e1d;
        }

        .form-control {
            border-radius: 10px;
            border: 1.5px solid #d9dee3;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #000000;
            box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.15);
        }

        .info-text {
            font-size: 0.85rem;
            color: #8592a3;
            margin-top: 0.35rem;
        }

        .upload-area {
            border: 2px dashed #d9dee3;
            border-radius: 15px;
            padding: 2.5rem;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .upload-area:hover {
            border-color: #000000;
            background: #f3f4ff;
        }

        .upload-area.dragover {
            border-color: #000000;
            background: #e7e7ff;
            transform: scale(1.02);
        }

        .upload-icon {
            font-size: 3.5rem;
            color: #000000;
            margin-bottom: 1rem;
        }

        .preview-image {
            max-width: 100%;
            max-height: 350px;
            border-radius: 12px;
            margin-top: 1rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-submit {
            padding: 0.875rem 2.5rem;
            border-radius: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #000000 0%, #0000009c 100%);
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(105, 108, 255, 0.4);
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 1rem 1.25rem;
        }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .alert-danger {
            background: #ffebee;
            color: #c62828;
        }

        .footer-text {
            text-align: center;
            margin-top: 2rem;
            color: white;
            opacity: 0.9;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem 0;
            }

            .card-body-custom {
                padding: 1.5rem;
            }

            .card-header-custom {
                padding: 1.5rem;
            }

            .card-header-custom h1 {
                font-size: 1.5rem;
            }

            .btn-submit {
                width: 100%;
                margin-bottom: 0.5rem;
            }

            .section-title {
                font-size: 1rem;
            }
        }
    </style>
    <section class="section-py bg-body first-section-pt">
        <div class="container"></div>
        <div class="payout-container">
            <div class="payout-card">
                <div class="card-header-custom">
                    <h1>
                        <i class="ti ti-wallet"></i> Request Payout
                    </h1>
                    <p>Submit your withdrawal request by filling out the form below</p>
                </div>

                <div class="card-body-custom">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="ti ti-circle-check me-2"></i>
                            <strong>Success!</strong> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="ti ti-alert-triangle me-2"></i>
                            <strong>Error!</strong> Please fix the following issues:
                            <ul class="mb-0 mt-2">
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
                            <i class="ti ti-building-bank"></i> Bank Account Details
                        </div>

                        <div class="mb-3">
                            <label for="account_holder_name" class="form-label">
                                Account Holder Name <span class="required-mark">*</span>
                            </label>
                            <input type="text" class="form-control" id="account_holder_name" name="account_holder_name"
                                value="{{ old('account_holder_name') }}" required
                                placeholder="Enter account holder name as per bank records">
                            <div class="info-text">Enter the name exactly as it appears on your bank account</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="account_number" class="form-label">
                                    Account Number <span class="required-mark">*</span>
                                </label>
                                <input type="text" class="form-control" id="account_number" name="account_number"
                                    value="{{ old('account_number') }}" required placeholder="Enter account number"
                                    pattern="[0-9]+" title="Please enter numbers only">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_account_number" class="form-label">
                                    Confirm Account Number <span class="required-mark">*</span>
                                </label>
                                <input type="text" class="form-control" id="confirm_account_number"
                                    name="confirm_account_number" value="{{ old('confirm_account_number') }}" required
                                    placeholder="Re-enter account number" pattern="[0-9]+"
                                    title="Please enter numbers only">
                                <div class="info-text">Account numbers must match</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="branch_name" class="form-label">
                                    Branch Name
                                </label>
                                <input type="text" class="form-control" id="branch_name" name="branch_name"
                                    value="{{ old('branch_name') }}" placeholder="Enter branch name (optional)">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="ifsc_code" class="form-label">
                                    IFSC Code
                                </label>
                                <input type="text" class="form-control text-uppercase" id="ifsc_code" name="ifsc_code"
                                    value="{{ old('ifsc_code') }}" placeholder="e.g., SBIN0001234" maxlength="11"
                                    pattern="[A-Z]{4}0[A-Z0-9]{6}"
                                    title="IFSC code should be 11 characters (e.g., SBIN0001234)">
                                <div class="info-text">11-character code (e.g., SBIN0001234)</div>
                            </div>
                        </div>

                        <!-- Amount Section -->
                        <div class="section-title mt-4">
                            <i class="ti ti-currency-rupee"></i> Payout Amount
                        </div>

                        <div class="mb-3">
                            <label for="amount" class="form-label">
                                Amount (₹) <span class="required-mark">*</span>
                            </label>
                            <input type="number" class="form-control" id="amount" name="amount"
                                value="{{ old('amount') }}" required min="1" step="0.01"
                                placeholder="Enter payout amount">
                            <div class="info-text">Minimum amount: ₹1.00</div>
                        </div>

                        <!-- Screenshot Upload Section -->
                        <div class="section-title mt-4">
                            <i class="ti ti-photo"></i> Upload Screenshot (Optional)
                        </div>

                        <div class="mb-4">
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
                                <div class="mt-3">
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="removeImage">
                                        <i class="ti ti-x me-1"></i>Remove Image
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-submit me-2">
                                <i class="ti ti-check me-2"></i>Submit Payout Request
                            </button>
                            <button type="reset" class="btn btn-outline-secondary btn-submit" id="resetBtn">
                                <i class="ti ti-refresh me-2"></i>Reset Form
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer-text">
                <p class="mb-0">
                    <i class="ti ti-shield-check me-1"></i>
                    Your information is secure and encrypted
                </p>
            </div>
        </div>
        </div>
    </section>
@endsection

@section('page-script')
    <script>
        $(function() {
            const uploadArea = $('#uploadArea');
            const fileInput = $('#screenshot');
            const imagePreview = $('#imagePreview');
            const previewImg = $('#previewImg');
            const removeImageBtn = $('#removeImage');
            const resetBtn = $('#resetBtn');

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

            // Reset form
            resetBtn.on('click', function() {
                fileInput.val('');
                imagePreview.hide();
                uploadArea.show();
            });

            function handleFile(file) {
                if (!file) return;

                // Validate file type
                if (!file.type.match('image.*')) {
                    alert('Please upload an image file (PNG, JPG, or JPEG)');
                    fileInput.val('');
                    return;
                }

                // Validate file size (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('File size must be less than 2MB');
                    fileInput.val('');
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
                    alert('⚠️ Account numbers do not match. Please check and try again.');
                    $('#confirm_account_number').focus();
                    return false;
                }

                // Show loading state
                const submitBtn = $(this).find('button[type="submit"]');
                submitBtn.prop('disabled', true);
                submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');
            });

            // Auto-uppercase IFSC code
            $('#ifsc_code').on('input', function() {
                this.value = this.value.toUpperCase();
            });

            // Prevent paste of non-numeric values in account number fields
            $('#account_number, #confirm_account_number').on('paste', function(e) {
                const pastedData = e.originalEvent.clipboardData.getData('text');
                if (!/^\d+$/.test(pastedData)) {
                    e.preventDefault();
                    alert('Please enter numbers only in account number field');
                }
            });
        });
    </script>
@endsection
