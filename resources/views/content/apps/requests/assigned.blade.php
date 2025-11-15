@extends('layouts/layoutMaster')

@section('title', 'Assigned Deposit Requests')

<meta name="csrf-token" content="{{ csrf_token() }}">


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
    <script>
        $(function() {
            var dt_assigned_requests_table = $('.datatables-assigned-requests');
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            var dt_assigned_requests;
            var autoReloadTimer = null;
            var assignedLastMaxId = 0;

            function playNotificationSound() {
                try {
                    const audioEl = document.getElementById('notif-sound');
                    if (audioEl) {
                        audioEl.currentTime = 0;
                        audioEl.volume = 0.25;
                        const p = audioEl.play();
                        if (p && p.catch) {
                            p.catch(function() {
                                try {
                                    const AudioContext = window.AudioContext || window.webkitAudioContext;
                                    const ctx = new AudioContext();
                                    const o = ctx.createOscillator();
                                    const g = ctx.createGain();
                                    o.type = 'sine';
                                    o.frequency.value = 880;
                                    g.gain.value = 0.05;
                                    o.connect(g);
                                    g.connect(ctx.destination);
                                    o.start(0);
                                    setTimeout(function() {
                                        o.stop();
                                        ctx.close();
                                    }, 180);
                                } catch (e) {
                                    console.warn('Audio notification unavailable', e);
                                }
                            });
                        }
                        return;
                    }

                    const AudioContext = window.AudioContext || window.webkitAudioContext;
                    const ctx = new AudioContext();
                    const o = ctx.createOscillator();
                    const g = ctx.createGain();
                    o.type = 'sine';
                    o.frequency.value = 880;
                    g.gain.value = 0.05;
                    o.connect(g);
                    g.connect(ctx.destination);
                    o.start(0);
                    setTimeout(function() {
                        o.stop();
                        ctx.close();
                    }, 180);
                } catch (e) {
                    console.warn('Audio notification unavailable', e);
                }
            }

            // Cache status indicator update
            function updateCacheStatus(status, loadTime) {
                const indicator = $('.cache-status-indicator');
                const statusText = $('.cache-text', indicator);
                const statusDot = $('.cache-dot', indicator);

                if (indicator.length) {
                    statusDot.removeClass('cache-success cache-warning cache-pending cache-loading');
                    if (status === 'CACHE') {
                        statusDot.addClass('cache-success');
                        statusText.text('CACHE');
                        indicator.attr('title', 'Data loaded from cache (fast)');
                    } else if (status === 'DATABASE') {
                        statusDot.addClass('cache-warning');
                        statusText.text('DATABASE');
                        indicator.attr('title', 'Data loaded from database (slower)');
                    } else {
                        statusDot.addClass('cache-pending');
                        statusText.text('PENDING');
                        indicator.attr('title', 'Loading data...');
                    }
                }

                if ($('.load-time').length && loadTime) {
                    $('.load-time').text(loadTime + ' ms');
                }
            }

            if (dt_assigned_requests_table.length) {
                dt_assigned_requests = dt_assigned_requests_table.DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route('requests.assigned.data') }}',
                        type: 'GET',
                        data: function(d) {
                            d.mode = $('#mode_filter').val() || 'all';
                            d.status = $('#status_filter').val() || 'all';
                            d.start_date = $('#start_date').val() || '';
                            d.end_date = $('#end_date').val() || '';
                            d.search_term = $('#search_term').val() || '';

                            // Handle request type filter
                            var requestType = $('#request_type_filter').val();
                            if (requestType === 'pending') {
                                d.status = 'pending'; // Override status for pending requests
                            } else if (requestType === 'rejected') {
                                d.status = 'rejected'; // Override status for rejected requests
                            } else if (requestType === 'progress') {
                                d.status = 'progress'; // Override status for rejected requests
                            }
                        },
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        dataSrc: function(json) {
                            updateCacheStatus(json.cache_status, json.load_time);
                            try {
                                const arr = Array.isArray(json.data) ? json.data : [];
                                if (arr.length) {
                                    const maxId = Math.max.apply(null, arr.map(function(r) {
                                        return r.id || 0;
                                    }));
                                    if (assignedLastMaxId > 0 && maxId > assignedLastMaxId) {
                                        playNotificationSound();
                                    }
                                    assignedLastMaxId = Math.max(assignedLastMaxId, maxId);
                                }
                            } catch (e) {
                                console.warn('Error checking new assigned requests', e);
                            }
                            return json.data;
                        }
                    },
                    columns: [{
                            data: 'id',
                            name: 'id',
                            visible: false
                        },
                        {
                            data: 'trans_id',
                            name: 'trans_id'
                        },
                        {
                            data: 'approver_name',
                            name: 'approver_name',
                            defaultContent: '-'
                        },
                        {
                            data: 'mode',
                            name: 'mode',
                            render: function(data) {
                                var icon = '';
                                switch ((data || '').toLowerCase()) {
                                    case 'bank':
                                        icon = '<i class="ti ti-building-bank me-1"></i>';
                                        break;
                                    case 'upi':
                                        icon = '<i class="ti ti-qrcode me-1"></i>';
                                        break;
                                    case 'crypto':
                                        icon = '<i class="ti ti-currency-bitcoin me-1"></i>';
                                        break;
                                    default:
                                        icon = '<i class="ti ti-cash me-1"></i>';
                                }
                                return icon + (data || '-');
                            }
                        },
                        {
                            data: 'amount',
                            name: 'amount',
                            render: function(data, type, full) {
                                var amount = data != null ? parseFloat(data) : null;
                                if (amount != null) {
                                    return amount.toLocaleString(undefined, {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                }
                                return '-';
                            }
                        },
                        {
                            data: 'payment_amount',
                            name: 'payment_amount',
                            render: function(data, type, full) {
                                var pAmount = data != null ? parseFloat(data) : null;
                                if (pAmount != null) {
                                    return pAmount.toLocaleString(undefined, {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                }
                                return '-';
                            }
                        },
                        {
                            data: 'utr',
                            name: 'utr',
                            defaultContent: '-'
                        },
                        {
                            data: 'payment_from',
                            name: 'payment_from',
                            defaultContent: '-'
                        },
                        {
                            data: 'account_upi',
                            name: 'account_upi',
                            defaultContent: '-'
                        },
                        {
                            data: 'image',
                            name: 'image',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, full) {
                                return data || '<span class="text-muted">-</span>';
                            }
                        },
                        {
                            data: 'status',
                            name: 'status'
                        },
                        {
                            data: 'created_at',
                            name: 'created_at',
                            render: function(data) {
                                if (!data) return '-';
                                var date = new Date(data);
                                return (
                                    date.toLocaleDateString() +
                                    ' <small class="text-muted">' + date.toLocaleTimeString(
                                        [], {
                                            hour: '2-digit',
                                            minute: '2-digit',
                                            second: '2-digit'
                                        }) + '</small>'
                                );
                            }
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, full) {
                                return data || '<span class="text-muted">No Actions</span>';
                            }
                        }
                    ],
                    order: [
                        [11, 'desc']
                    ],
                    scrollX: true,
                    responsive: false,
                    autoWidth: false
                });
            }

            function triggerReload() {
                updateCacheStatus('PENDING');
                if (dt_assigned_requests) dt_assigned_requests.ajax.reload(null, false);
            }

            // Filter controls triggering DataTable reload
            $('#request_type_filter, #mode_filter, #status_filter').on('change', triggerReload);
            $('#start_date, #end_date').on('change', triggerReload);
            $('#search_term').on('keyup', function(e) {
                if (e.key === 'Enter') triggerReload();
            });

            // Top button handlers
            $('#pendingRequestsBtn').on('click', function() {
                $('#request_type_filter').val('pending').trigger('change');
            });

            $('#rejectedRequestsBtn').on('click', function() {
                $('#request_type_filter').val('rejected').trigger('change');
            });

            // Export full dataset (server-side) using current filters (top button)
            $('#exportExcelBtn').on('click', function(e) {
                e.preventDefault();
                var params = {};
                var mode = $('#mode_filter').val();
                var status = $('#status_filter').val();
                var start_date = $('#start_date').val();
                var end_date = $('#end_date').val();
                var search_term = $('#search_term').val();

                // Handle request type filter for export
                var requestType = $('#request_type_filter').val();
                if (requestType === 'pending') {
                    params.status = 'pending';
                } else if (requestType === 'rejected') {
                    params.status = 'rejected';
                } else if (status && status !== 'all') {
                    params.status = status;
                }

                if (mode && mode !== 'all') params.mode = mode;
                if (start_date) params.start_date = start_date;
                if (end_date) params.end_date = end_date;
                if (search_term) params.search_term = search_term;

                var query = Object.keys(params).map(function(k) {
                    return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]);
                }).join('&');

                var url = '{{ route('requests.assigned.export') }}' + (query ? ('?' + query) : '');
                window.location = url;
            });

            // Auto-reload handler
            $('#auto_reload').on('change', function() {
                var val = parseInt($(this).val(), 10) || 0;
                if (autoReloadTimer) {
                    clearInterval(autoReloadTimer);
                    autoReloadTimer = null;
                }
                if (val > 0) {
                    autoReloadTimer = setInterval(function() {
                        if ($('.datatables-assigned-requests').is(':visible')) {
                            triggerReload();
                        }
                    }, val * 1000);
                }
            });

            // DataTable search handler
            $('#dt_search').on('keyup', function() {
                if (dt_assigned_requests) {
                    dt_assigned_requests.search(this.value).draw();
                }
            });

            // Accept assigned request
            $(document).on('click', '.assigned-accept-request', function() {
                const requestId = $(this).data('id');
                if (confirm('Are you sure you want to accept this payment request?')) {
                    $.ajax({
                        url: '/app/payment/requests/accept-payment/' + requestId,
                        type: 'POST',
                        data: {
                            _token: csrfToken
                        },
                        success: function(response) {
                            dt_assigned_requests.ajax.reload();
                            toastr.success(response.message || 'Deposit request accepted');
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON?.message ||
                                'Error accepting payment request');
                        }
                    });
                }
            });

            // Reject assigned request
            $(document).on('click', '.assigned-reject-request', function() {
                const requestId = $(this).data('id');
                if (confirm('Are you sure you want to reject this payment request?')) {
                    $.ajax({
                        url: '/app/payment/requests/reject/' + requestId,
                        type: 'POST',
                        data: {
                            _token: csrfToken
                        },
                        success: function(response) {
                            dt_assigned_requests.ajax.reload();
                            toastr.success(response.message || 'Deposit request rejected');
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON?.message ||
                                'Error rejecting payment request');
                        }
                    });
                }
            });

            // Edit/Update Transaction - Open Modal
            $(document).on('click', '.assigned-edit-request', function() {
                const requestId = $(this).data('id');

                // Fetch request data
                $.ajax({
                    url: '/app/payment/requests/' + requestId + '/get',
                    type: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function(response) {
                        const data = response.data;

                        // Populate modal fields
                        $('#update_request_id').val(data.id);
                        $('#update_trans_id').val(data.trans_id || '-');
                        $('#update_mode').val((data.mode || '-').toUpperCase());

                        // Format amount with 2 decimals
                        const amount = parseFloat(data.amount || 0).toFixed(2);
                        $('#update_amount').val('₹ ' + amount);

                        $('#update_payment_from').val(data.payment_from || '-');

                        // Format status with proper capitalization
                        const status = (data.status || 'pending').charAt(0).toUpperCase() + (
                            data.status || 'pending').slice(1);
                        $('#update_current_status').val(status);

                        // Populate editable fields
                        $('#update_utr').val(data.utr || '');

                        // Set payment amount - if exists use it, otherwise use the requested amount as default
                        const paymentAmount = data.payment_amount || data.amount || '';
                        $('#update_payment_amount').val(paymentAmount);

                        // Show modal
                        $('#updateTransactionModal').modal('show');
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Error loading request data');
                    }
                });
            });

            // Approve Transaction
            $('#approveTransactionBtn').on('click', function() {
                const requestId = $('#update_request_id').val();
                const utr = $('#update_utr').val().trim();
                const paymentAmount = $('#update_payment_amount').val();

                if (!utr) {
                    toastr.error('Please enter UTR number');
                    return;
                }

                if (!paymentAmount || parseFloat(paymentAmount) <= 0) {
                    toastr.error('Please enter valid payment amount');
                    return;
                }

                if (confirm('Are you sure you want to approve this transaction?')) {
                    const btn = $(this);
                    btn.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-1"></span>Approving...');

                    $.ajax({
                        url: '/app/payment/requests/' + requestId + '/update-and-approve',
                        type: 'POST',
                        data: {
                            _token: csrfToken,
                            utr: utr,
                            payment_amount: paymentAmount
                        },
                        success: function(response) {
                            $('#updateTransactionModal').modal('hide');
                            dt_assigned_requests.ajax.reload();
                            toastr.success(response.message ||
                                'Transaction approved successfully');
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON?.message ||
                                'Error approving transaction');
                        },
                        complete: function() {
                            btn.prop('disabled', false).html(
                                '<i class="ti ti-check me-1"></i>Approve');
                        }
                    });
                }
            });

            // Cancel Transaction
            $('#cancelTransactionBtn').on('click', function() {
                const requestId = $('#update_request_id').val();

                if (confirm('Are you sure you want to cancel this transaction?')) {
                    const btn = $(this);
                    btn.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-1"></span>Cancelling...');

                    $.ajax({
                        url: '/app/payment/requests/reject/' + requestId,
                        type: 'POST',
                        data: {
                            _token: csrfToken
                        },
                        success: function(response) {
                            $('#updateTransactionModal').modal('hide');
                            dt_assigned_requests.ajax.reload();
                            toastr.success(response.message ||
                                'Transaction cancelled successfully');
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON?.message ||
                                'Error cancelling transaction');
                        },
                        complete: function() {
                            btn.prop('disabled', false).html(
                                '<i class="ti ti-x me-1"></i>Cancel Transaction');
                        }
                    });
                }
            });

            // Image Preview Modal - Show full size image when clicked
            $(document).on('click', '.payment-screenshot-img', function() {
                const imageSrc = $(this).data('image');
                if (imageSrc) {
                    $('#previewImage').attr('src', imageSrc);
                    $('#downloadImageBtn').attr('href', imageSrc);
                    $('#imagePreviewModal').modal('show');
                }
            });
        });
    </script>
@endsection

@section('content')
    <style>
        #DataTables_Table_0_filter,
        #DataTables_Table_0_length {
            display: none;
        }

        /* Rounded corners and card effect for the table */
        .table {
            border-radius: 16px !important;
            overflow: hidden;
            box-shadow: 0 4px 18px rgba(153, 164, 188, 0.13);
            background: #fff;
            /* Base card background */
        }

        /* Header styles */
        .table th {
            /* background: linear-gradient(90deg, #f3e9fa 0%, #e8f9e9 100%); */
            color: #352e5a;
            font-size: 12px;
            font-weight: 600;
            border: none;
        }

        /* Zebra striping for rows */
        /* .table-striped>tbody>tr:nth-of-type(odd) {
                                                                                                        background-color: #f8fafc;
                                                                                                    }

                                                                                                    .table-striped>tbody>tr:nth-of-type(even) {
                                                                                                        background-color: #f3f4f8;
                                                                                                    } */

        /* Hover effect on rows */
        .table tbody tr:hover {
            background: #e0f7fa !important;
            box-shadow: 0 1px 6px rgba(60, 120, 200, 0.07);
            transition: background 0.2s, box-shadow 0.2s;
        }

        /* Cell padding and font */
        .table th,
        .table td {
            font-size: 12px;
            padding: 0.5rem 0.5rem;
            /* font-size: 0.875rem; */
            vertical-align: middle !important;
            white-space: nowrap;
        }

        /* Bolder important cells, like status or actions */
        .table td .fw-medium,
        .table td .text-success,
        .table td .text-danger,
        .table td .text-warning {
            font-weight: 600;
            /* font-size: 1.06em; */
        }

        .table td small {
            /* font-size: 0.92em; */
            color: #8678c5;
        }

        /* Rounded pagination for DataTables */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 8px !important;
            margin: 0 3px;
            background: #f3e9fa !important;
            color: #352e5a !important;
            border: none !important;
            transition: background 0.18s;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #b3e2b0 !important;
            color: #283593 !important;
        }

        /* Search and filter boxes styling */
        .dataTables_filter input,
        .dataTables_length select {
            border-radius: 8px;
            border: 1px solid #d1c4e9;
            padding: 0.4em 1em;
            /* font-size: 1em; */
            background: #fafaff;
            margin-right: 6px;
        }

        /* Make table horizontally scrollable on smaller viewports so icons don't get hidden */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* Table responsive wrapper */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .datatables-assigned-requests {
            width: 100% !important;
        }

        @media (max-width: 992px) {}
    </style>
    <section class="app-assigned-requests-list">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="card-title"> Deposit Requests</h5>
                    <span class="cache-status-indicator ms-2" title="Cache status">
                        <span class="cache-dot cache-pending"></span>
                        <small class="cache-text">LOADING</small>
                        <small class="load-time ms-1"></small>
                    </span>
                </div>
                <div class="d-flex gap-2">
                    <button id="pendingRequestsBtn" class="btn btn-success">Pending Requests</button>
                    <button id="rejectedRequestsBtn" class="btn btn-danger">Rejected Requests</button>
                    <a id="exportExcelBtn" style="color:#fff" class="btn btn-primary">Export Excel</a>
                </div>
            </div>

            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <select id="auto_reload" class="form-select form-select-sm" style="width: auto;">
                        <option value="0">Auto-reload: Off</option>
                        <option value="5">5s</option>
                        <option value="10">10s</option>
                        <option value="15">15s</option>
                        <option value="20">20s</option>
                        <option value="30">30s</option>
                        <option value="60">60s</option>
                    </select>
                    <select id="mode_filter" class="form-select form-select-sm" style="width: auto;">
                        <option value="all">All</option>
                        <option value="bank">Bank</option>
                        <option value="upi">UPI</option>
                        <option value="crypto">Crypto</option>
                    </select>

                    <select id="request_type_filter" class="form-select form-select-sm" style="width: auto;">
                        <option value="all">All </option>
                        <option value="pending" selected>Pending </option>
                        <option value="accepted">Approved</option>
                        <option value="progress">Progress</option>
                        <option value="rejected">Rejected </option>
                    </select>
                    <label for="" class="mb-0 small">from:</label>
                    <input type="date" id="start_date" class="form-control form-control-sm" style="width: auto;"
                        placeholder="Start date" />
                    <label for="" class="mb-0 small">to:</label>

                    <input type="date" id="end_date" class="form-control form-control-sm" style="width: auto;"
                        placeholder="End date" />
                    {{-- <input type="text" id="search_term" class="form-control" style="width: 200px;"
                        placeholder="Search UTR/Trans ID" /> --}}

                    <input type="search" id="dt_search" class="form-control form-control-sm" style="width: 180px;"
                        placeholder="Search UTR/Trans ID" />
                    {{-- <a href="{{ route('requests.add') }}" class="btn btn-primary">Add Payment Request</a> --}}

                </div>
            </div>
            <div class="card-body border-bottom pt-0">
                <div class="table">
                    <table class="table datatables-assigned-requests">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Trans. ID</th>
                                <th>Approver Name</th>
                                <th>Mode</th>
                                <th>Amount</th>
                                <th>Payment Amount</th>
                                <th>UTR</th>
                                <th>Payment From</th>
                                <th>Account/UPI</th>
                                <th>Image</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Image Preview Modal -->
        <div class="modal fade" id="imagePreviewModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-photo me-2"></i>Payment Screenshot
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center p-0">
                        <img id="previewImage" src="" alt="Payment Screenshot" class="img-fluid"
                            style="max-height: 80vh; width: auto;">
                    </div>
                    <div class="modal-footer">
                        <a id="downloadImageBtn" href="" download class="btn btn-primary">
                            <i class="ti ti-download me-1"></i>Download
                        </a>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Update Transaction Modal -->
        <div class="modal fade" id="updateTransactionModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-edit me-2"></i>Update Transaction
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="update_request_id">

                        <!-- Transaction Info (Read-only) -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Transaction ID</label>
                                <input type="text" class="form-control-plaintext" id="update_trans_id" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Mode</label>
                                <input type="text" class="form-control-plaintext" id="update_mode" readonly>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Requested Amount</label>
                                <input type="text" class="form-control-plaintext" id="update_amount" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Payment From</label>
                                <input type="text" class="form-control-plaintext" id="update_payment_from" readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Current Status</label>
                            <input type="text" class="form-control-plaintext" id="update_current_status" readonly>
                        </div>

                        <hr class="my-4">

                        <!-- Editable Fields -->
                        <h6 class="mb-3 text-primary">Update Payment Details</h6>

                        <div class="mb-3">
                            <label for="update_utr" class="form-label fw-bold">UTR Number <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="update_utr" placeholder="Enter UTR number"
                                required>
                            <small class="form-text text-muted">Enter the unique transaction reference number</small>
                        </div>

                        <div class="mb-3">
                            <label for="update_payment_amount" class="form-label fw-bold">Payment Amount <span
                                    class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="update_payment_amount"
                                placeholder="Enter actual payment amount" required>
                            <small class="form-text text-muted">Enter the actual amount received</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="ti ti-x me-1"></i>Close
                        </button>
                        <button type="button" class="btn btn-danger" id="cancelTransactionBtn">
                            <i class="ti ti-ban me-1"></i>Reject Transaction
                        </button>
                        <button type="button" class="btn btn-success" id="approveTransactionBtn">
                            <i class="ti ti-check me-1"></i>Approve & Update
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
