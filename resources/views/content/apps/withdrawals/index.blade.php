@extends('layouts/layoutMaster')

@section('title', 'Payout')

<meta name="csrf-token" content="{{ csrf_token() }}">
@section('vendor-style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
@endsection

@section('vendor-script')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://unpkg.com/feather-icons"></script>

    <script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <!-- DataTables Buttons (client-side export) dependencies -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
@endsection

@section('page-script')
    <script src="{{ asset('assets/js/withdrawals-list.js') }}"></script>
@endsection

@section('page-style')
    <style>
        /* Ensure table rows stay in one line */
        .datatables-withdrawals th,
        .datatables-withdrawals td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Adjust action column width */
        .datatables-withdrawals td:last-child {
            min-width: 400px;
        }

        /* Screenshot column width */
        .datatables-withdrawals td:nth-child(9) {
            min-width: 200px;
        }

        /* Status buttons spacing */
        .change-status-btn {
            margin: 2px;
        }

        /* Summary card styles */
        .summary-card {
            border-radius: 25px !important;
            border: none;
            box-shadow: 0 4px 18px rgba(153, 164, 188, 0.16);
            transition: transform 0.2s, box-shadow 0.2s;
            overflow: hidden;
        }

        .summary-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(153, 164, 188, 0.24);
        }

        .summary-card .card-body {
            padding: 10px;
        }

        .summary-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-size: 1.75rem;
        }

        .summary-value {
            font-size: 12px;
            font-weight: 700;
            margin: 0.75rem 0 0.25rem;
        }

        .summary-label {
            font-size: 10px;
            font-weight: 500;
            color: #6c757d;
            margin: 0;
        }

        .bg-gradient-success {
            background: linear-gradient(135deg, #e8f9e9 0%, #b3e2b0 100%);
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, #e9f0fa 0%, #a1c6ef 100%);
        }
    </style>
@endsection

@section('content')
    <section class="app-withdrawals-list">
        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card summary-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="summary-icon bg-success text-white me-3">
                            <i class="ti ti-currency-rupee"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="summary-label">Total Payout</p>
                            <h3 class="summary-value text-success" id="total_payout">₹0.00</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card summary-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="summary-icon bg-primary text-white me-3">
                            <i class="ti ti-wallet"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="summary-label">Total Approver Earning</p>
                            <h3 class="summary-value text-primary" id="total_approver_earning">₹0.00</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="d-flex justify-content-between align-items-center ps-5 pt-5 pe-5">
                <h4 class="card-title mb-0">Payout</h4>
                <button type="button" id="exportBtn" class="btn btn-primary">
                    <i class="ti ti-download me-1"></i>Export
                </button>
            </div>
            <div class="card-header">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <select id="status_filter" class="form-select" style="width: auto;">
                        <option value="all">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <select id="approver_status_filter" class="form-select" style="width: auto;">
                        <option value="all">All Approver Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <input type="date" id="start_date" class="form-control" style="width: auto;"
                        placeholder="Start date" />
                    <input type="date" id="end_date" class="form-control" style="width: auto;" placeholder="End date" />
                    <input type="text" id="search_term" class="form-control" style="width: 200px;"
                        placeholder="Search ID/Account/IFSC" />
                    <select id="auto_reload" class="form-select" style="width: auto;">
                        <option value="0">Auto-reload: Off</option>
                        <option value="5">5s</option>
                        <option value="10">10s</option>
                        <option value="15">15s</option>
                        <option value="20">20s</option>
                        <option value="30">30s</option>
                        <option value="60">60s</option>
                    </select>
                    {{-- <select id="include_trashed" class="form-select" style="width: auto;">
                        <option value="false">Active Only</option>
                        <option value="true">Include Deleted</option>
                    </select> --}}
                    {{-- <a href="{{ route('withdrawals.create') }}" class="btn btn-primary">Create Withdrawal</a> --}}
                </div>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <div class="table-responsive">
                    <table class="table datatables-withdrawals table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Request ID</th>
                                <th>Date</th>
                                <th>Account Holder</th>
                                <th>Account Number</th>
                                <th>Branch</th>
                                <th>IFSC</th>
                                <th>Amount</th>
                                <th>Screenshot</th>
                                <th>Status</th>
                                <th>Approver Status</th>
                                <th>Created By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Screenshot Modal -->
        <div class="modal fade" id="screenshotModal" tabindex="-1" aria-labelledby="screenshotModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="screenshotModalLabel">
                            <i class="ti ti-photo me-2"></i>Payment Screenshot
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img id="screenshot_modal_img" src="" alt="Screenshot" class="img-fluid"
                            style="max-height: 50vh; border-radius: 8px;">
                    </div>
                    <div class="modal-footer">
                        <a id="screenshot_download_link" href="" download class="btn btn-primary">
                            <i class="ti ti-download me-1"></i>Download
                        </a>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Withdrawal Modal -->
        <div class="modal fade" id="editWithdrawalModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-edit me-2"></i>Edit Withdrawal Request
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit_withdrawal_id">

                        <!-- Request Info -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Transaction ID</label>
                                <input type="text" class="form-control" id="edit_trans_id" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Created Date</label>
                                <input type="text" class="form-control" id="edit_created_at" readonly>
                            </div>
                        </div>

                        <!-- Account Details -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Account Holder Name</label>
                                <input type="text" class="form-control" id="edit_account_holder_name" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Account Number</label>
                                <input type="text" class="form-control" id="edit_account_number" readonly>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Branch Name</label>
                                <input type="text" class="form-control" id="edit_branch_name" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">IFSC Code</label>
                                <input type="text" class="form-control" id="edit_ifsc_code" readonly>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Amount</label>
                                <input type="text" class="form-control" id="edit_amount" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Account Status</label>
                                <select class="form-select" id="edit_status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <!-- Approver Status -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Approver Status</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="edit_approver_status"
                                        id="status_approved" value="approved">
                                    <label class="btn btn-outline-success" for="status_approved">
                                        <i class="ti ti-check me-1"></i>Approved
                                    </label>

                                    <input type="radio" class="btn-check" name="edit_approver_status"
                                        id="status_pending" value="pending">
                                    <label class="btn btn-outline-warning" for="status_pending">
                                        <i class="ti ti-clock me-1"></i>Pending
                                    </label>

                                    <input type="radio" class="btn-check" name="edit_approver_status"
                                        id="status_rejected" value="rejected">
                                    <label class="btn btn-outline-danger" for="status_rejected">
                                        <i class="ti ti-x me-1"></i>Rejected
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Screenshot Section -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Payment Screenshot</label>
                            <div id="current_screenshot_section" style="display: none;">
                                <div class="border rounded p-3 mb-2 text-center">
                                    <img id="current_screenshot_img" src="" alt="Current Screenshot"
                                        style="max-width: 100%; max-height: 300px; border-radius: 8px;">
                                    <div class="mt-2">
                                        <a id="current_screenshot_link" href="" target="_blank"
                                            class="btn btn-sm btn-info me-2">
                                            <i class="ti ti-eye me-1"></i>View Full Size
                                        </a>
                                        <button type="button" class="btn btn-sm btn-warning" id="change_screenshot_btn">
                                            <i class="ti ti-refresh me-1"></i>Change Screenshot
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div id="upload_screenshot_section">
                                <input type="file" class="form-control" id="edit_screenshot_file" accept="image/*">
                                <small class="text-muted">Max size: 2MB (JPG, PNG, JPEG)</small>
                                <div id="edit_screenshot_preview" class="text-center mt-2" style="display: none;">
                                    <img src="" alt="Preview" id="edit_preview_img"
                                        style="max-width: 100%; max-height: 250px; border-radius: 8px;">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="saveChangesBtn">
                            <i class="ti ti-device-floppy me-1"></i>Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
