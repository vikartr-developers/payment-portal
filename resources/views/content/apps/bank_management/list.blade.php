@extends('layouts/layoutMaster')

@section('title', 'Bank & UPI Management')

@section('vendor-style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <style>
        /* Bank table visual tweaks */
        #bankManagementTable {
            font-size: 12px;
        }

        #bankManagementTable thead th {
            background: #f8f9fb;
            color: #333;
            border-bottom: 2px solid #e9ecef;
            font-weight: 600;
            white-space: nowrap;
        }

        #bankManagementTable tbody tr td {
            vertical-align: middle;
            white-space: nowrap;
            padding: 8px 12px;
        }

        .table thead th,
        .table tbody td {
            /* background: linear-gradient(90deg, #f3e9fa 0%, #e8f9e9 100%); */
            color: #000 !important;
            font-size: 12px !important;
            font-weight: 600;
            letter-spacing: 0px;
            border: none;
        }

        .dt-buttons .btn {
            margin-right: .35rem;
        }

        /* Make action buttons inline and compact */
        #bankManagementTable .btn {
            padding: 4px 8px;
            font-size: 11px;
            margin-right: 2px;
        }

        #bankManagementTable form {
            display: inline-block;
            margin: 0;
        }

        /* Badge styling */
        #bankManagementTable .badge {
            font-size: 10px;
            padding: 3px 6px;
        }

        /* Modal enhancements */


        .modal-content {
            border-radius: 12px;
            overflow: hidden;
        }

        .modal-header {
            padding: 1.5rem;
        }

        /* .select2-container--bootstrap-5 .select2-selection {
                                    border-radius: 8px;
                                    border: 1px solid #d9dee3;
                                    min-height: 45px;
                                } */

        /* .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
                                    background-color: #667eea;
                                    border: none;
                                    color: white;
                                    padding: 5px 10px;
                                    border-radius: 6px;
                                    margin: 3px;
                                } */

        /* .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove {
                                    color: white;
                                    margin-right: 5px;
                                } */

        /* .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove:hover {
                                    color: #ffebee;
                                } */

        .alert-info.bg-label-info {
            background-color: #e7f3ff !important;
            color: #0c5eb5;
        }
    </style>
@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
    <script>
        $(function() {
            var currentFilter = 'active'; // Default filter

            var table = $('#bankManagementTable').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: '{{ route('bank-management.index') }}',
                    data: function(d) {
                        d.status_filter = currentFilter;
                    },
                    dataSrc: function(json) {
                        // Filter data based on status
                        if (currentFilter === 'active') {
                            json.data = json.data.filter(function(item) {
                                return item.status.toLowerCase() === 'active';
                            });
                        } else if (currentFilter === 'inactive') {
                            json.data = json.data.filter(function(item) {
                                return item.status.toLowerCase() === 'inactive';
                            });
                        }
                        return json.data;
                    }
                },
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'daily_max_amount',
                        name: 'daily_max_amount'
                    },
                    {
                        data: 'max_transaction_amount',
                        name: 'max_transaction_amount'
                    },
                    {
                        data: 'daily_max_transaction_count',
                        name: 'daily_max_transaction_count'
                    },
                    {
                        data: 'upi',
                        name: 'upi'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'assign_sub_approver',
                        name: 'assign_sub_approver',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'payment_link',
                        name: 'payment_link',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                dom: '<"d-flex justify-content-between align-items-center mb-3"<"dt-buttons btn-group"><"ms-auto"f>>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="ti ti-file-spreadsheet me-1"></i>Excel',
                        className: 'btn btn-outline-primary',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5]
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        text: '<i class="ti ti-file-text me-1"></i>CSV',
                        className: 'btn btn-outline-secondary',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5]
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="ti ti-printer me-1"></i>Print',
                        className: 'btn btn-outline-info',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5]
                        }
                    }
                ],
                createdRow: function(row, data) {
                    $('td', row).last().html(data.action);
                },
                drawCallback: function() {
                    // Copy payment link handler
                    $('#bankManagementTable').off('click', '.copy-payment-link').on('click',
                        '.copy-payment-link',
                        function(e) {
                            e.preventDefault();
                            var link = $(this).data('link');
                            if (!link) return;

                            // Create temporary textarea to copy text
                            var $temp = $('<textarea>');
                            $('body').append($temp);
                            $temp.val(link).select();
                            document.execCommand('copy');
                            $temp.remove();

                            if (window.toastr) toastr.success('Payment link copied to clipboard!');
                        });

                    // Toggle status handler
                    $('#bankManagementTable').off('click', '.toggle-status').on('click',
                        '.toggle-status',
                        function(e) {
                            e.preventDefault();
                            var url = $(this).data('url');
                            if (!url) return;
                            if (!confirm('Are you sure you want to toggle this account status?'))
                                return;
                            var token = $('meta[name="csrf-token"]').attr('content');
                            $.post(url, {
                                _token: token
                            }, function(resp) {
                                if (resp && resp.success) {
                                    if (window.toastr) toastr.success(resp.message ||
                                        'Status updated');
                                    table.ajax.reload(null, false);
                                } else {
                                    if (window.toastr) toastr.error(resp.message ||
                                        'Unable to update status');
                                }
                            }).fail(function() {
                                if (window.toastr) toastr.error('Request failed');
                            });
                        });

                    // Assign sub approver handler
                    $('#bankManagementTable').off('click', '.assign-btn').on('click', '.assign-btn',
                        function(e) {
                            e.preventDefault();
                            var accountId = $(this).data('id');
                            var accountName = $(this).data('name');

                            $('#assignModal').modal('show');
                            $('#assignModalLabel').text('Manage Sub Approvers - ' + accountName);
                            $('#account_id').val(accountId);

                            // Load current sub approvers for this account
                            loadSubApprovers(accountId);
                        });

                    // View sub approvers handler
                    $('#bankManagementTable').off('click', '.assign-view').on('click', '.assign-view',
                        function(e) {
                            e.preventDefault();
                            var accountId = $(this).data('id');
                            var accountName = $(this).data('name');

                            $('#viewModal').modal('show');
                            $('#viewModalLabel').text('Assigned Sub Approvers - ' + accountName);

                            // Load and display sub approvers
                            loadAndDisplaySubApprovers(accountId);
                        });
                }
            });

            table.buttons().container().appendTo($('.dt-buttons'));

            // Initialize Select2 for sub approvers
            $('#sub_approvers').select2({
                theme: 'bootstrap-5',
                placeholder: 'Select sub approvers',
                allowClear: true,
                dropdownParent: $('#assignModal')
            });

            // Handle assign form submission
            $('#assignForm').on('submit', function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                var accountId = $('#account_id').val();

                $.ajax({
                    url: '{{ route('bank-management.assign-sub-approvers', ':id') }}'.replace(':id',
                        accountId),
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            if (window.toastr) toastr.success(response.message ||
                                'Sub approvers assigned successfully');
                            $('#assignModal').modal('hide');
                            table.ajax.reload(null, false);
                        } else {
                            if (window.toastr) toastr.error(response.message ||
                                'Failed to assign sub approvers');
                        }
                    },
                    error: function() {
                        if (window.toastr) toastr.error('Request failed');
                    }
                });
            });

            function loadSubApprovers(accountId) {
                $.ajax({
                    url: '{{ route('bank-management.get-sub-approvers', ':id') }}'.replace(':id',
                        accountId),
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            $('#sub_approvers').val(response.sub_approvers).trigger('change');
                        }
                    }
                });
            }

            function loadAndDisplaySubApprovers(accountId) {
                var container = $('#subApproversContainer');
                container.html(
                    '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>'
                    );

                $.ajax({
                    url: '{{ route('bank-management.get-sub-approvers-details', ':id') }}'.replace(':id',
                        accountId),
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            if (response.sub_approvers.length > 0) {
                                var html = '<div class="row g-3">';
                                response.sub_approvers.forEach(function(user) {
                                    html += `
                                        <div class="col-md-6">
                                            <div class="card border shadow-sm h-100">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-lg bg-label-primary rounded me-3">
                                                            <span class="avatar-initial">${user.name.charAt(0).toUpperCase()}</span>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-1">${user.name}</h6>
                                                            <small class="text-muted">
                                                                <i class="ti ti-mail me-1"></i>${user.email}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                });
                                html += '</div>';
                                container.html(html);
                            } else {
                                container.html(`
                                    <div class="alert alert-warning border-0 text-center">
                                        <i class="ti ti-alert-triangle fs-3 mb-2"></i>
                                        <p class="mb-0">No sub approvers assigned to this account yet.</p>
                                    </div>
                                `);
                            }
                        } else {
                            container.html(
                                '<div class="alert alert-danger">Failed to load sub approvers</div>'
                                );
                        }
                    },
                    error: function() {
                        container.html(
                            '<div class="alert alert-danger">Error loading sub approvers</div>');
                    }
                });
            }

            // Archive/Active filter toggle
            $('#archiveBtn').on('click', function() {
                var btn = $(this);
                if (currentFilter === 'active') {
                    currentFilter = 'inactive';
                    btn.html('<i class="ti ti-list me-1"></i>Active Account List');
                    btn.removeClass('btn-warning').addClass('btn-success');
                } else {
                    currentFilter = 'active';
                    btn.html('<i class="ti ti-archive me-1"></i>Archive Account List');
                    btn.removeClass('btn-success').addClass('btn-warning');
                }
                table.ajax.reload(null, false);
            });
        });
    </script>
@endsection

@section('content')

    <section class="app-assigned-requests-list">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">

                <h3>Bank & UPI Accounts</h3>
                <div>
                    <button type="button" id="archiveBtn" class="btn btn-warning me-2">
                        <i class="ti ti-archive me-1"></i>Inactive Account List
                    </button>
                    <a href="{{ route('bank-management.create') }}" class="btn btn-primary">
                        <i class="ti ti-plus me-1"></i>Add New
                    </a>
                </div>
            </div>

            <div class="card-body border-bottom pt-0">
                <div class="table">
                    <table id="bankManagementTable"class="table datatables-assigned-requests">

                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Daily Max. Amount</th>
                                <th>Max. TRAN Amount</th>
                                <th>Max. TRAN Count</th>
                                <th>UPI</th>
                                <th>Status</th>
                                <th>Assign Sub Approver</th>
                                <th>Payment Link</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Assign Sub Approvers Modal -->
        <div class="modal fade" id="assignModal" tabindex="-1" aria-labelledby="assignModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-gradient-primary text-white border-0">
                        <div>
                            <h5 class="modal-title mb-1" id="assignModalLabel">
                                <i class="ti ti-users-group me-2"></i>Manage Sub Approvers
                            </h5>
                            <small class="opacity-75">Assign team members to this bank account</small>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <form id="assignForm">
                        @csrf
                        <input type="hidden" id="account_id" name="account_id">
                        <div class="modal-body p-4">
                            <div class="alert alert-info border-0 bg-label-info mb-4">
                                <div class="d-flex align-items-center">
                                    <i class="ti ti-info-circle fs-4 me-2"></i>
                                    <div>
                                        <strong>Multi-Selection Enabled</strong>
                                        <p class="mb-0 small">Select multiple sub approvers to assign them to this account.
                                            You can add or remove assignees anytime.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="sub_approvers" class="form-label fw-semibold">
                                    <i class="ti ti-user-check me-1"></i>Select Sub Approvers
                                </label>
                                <select name="sub_approvers[]" id="sub_approvers" class="form-select" multiple
                                    style="width: 100%;">
                                    @foreach (\App\Models\User::whereHas('roles', function ($q) {
            $q->where('name', 'SubApprover');
        })->where('created_by', auth()->id())->get() as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->email }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text mt-2">
                                    <i class="ti ti-info-circle me-1"></i>
                                    Only sub approvers created by you are shown
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-0">
                            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                                <i class="ti ti-x me-1"></i>Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- View Sub Approvers Modal -->
        <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-gradient-info text-white border-0">
                        <div>
                            <h5 class="modal-title mb-1" id="viewModalLabel">
                                <i class="ti ti-users me-2"></i>Assigned Sub Approvers
                            </h5>
                            <small class="opacity-75">View team members assigned to this account</small>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div id="subApproversContainer">
                            <!-- Sub approvers will be loaded here -->
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                            <i class="ti ti-x me-1"></i>Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
