@extends('layouts/layoutMaster')

@section('title', 'Bank & UPI Management')

@section('vendor-style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
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
    </style>
@endsection

@section('vendor-script')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    @yield('links')
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
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
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
        <div class="card-body">
            <table id="bankManagementTable" class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Daily Max. Amount</th>
                        <th>Max. TRAN Amount</th>
                        <th>Max. TRAN Count</th>
                        <th>UPI</th>
                        <th>Status</th>
                        <th>Assign Sub Approver</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <!-- Assign Sub Approvers Modal -->
    <div class="modal fade" id="assignModal" tabindex="-1" aria-labelledby="assignModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignModalLabel">Manage Sub Approvers</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="assignForm">
                    @csrf
                    <input type="hidden" id="account_id" name="account_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="sub_approvers" class="form-label">Select Sub Approvers (Add/Remove)</label>
                            <select name="sub_approvers[]" id="sub_approvers" class="form-select" multiple>
                                @foreach (\App\Models\User::whereHas('roles', function ($q) {
            $q->where('name', 'SubApprover');
        })->get() as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
