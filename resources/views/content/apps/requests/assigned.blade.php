@extends('layouts/layoutMaster')

@section('title', 'Assigned Deposit Requests')

<meta name="csrf-token" content="{{ csrf_token() }}">

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    <style>
        .cache-status-indicator {
            display: inline-flex;
            align-items: center;
            font-size: 0.75rem;
        }

        .cache-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 4px;
        }

        .cache-success {
            background-color: #28a745;
        }

        .cache-warning {
            background-color: #ffc107;
        }

        .cache-pending {
            background-color: #6c757d;
        }

        .cache-loading {
            background-color: #007bff;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }

            100% {
                opacity: 1;
            }
        }
    </style>
@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
@endsection

@section('page-script')
    <script>
        $(function() {
            var dt_assigned_requests_table = $('.datatables-assigned-requests');
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            var dt_assigned_requests;
            var autoReloadTimer = null;

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
                        },
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        dataSrc: function(json) {
                            updateCacheStatus(json.cache_status, json.load_time);
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
                            data: 'name',
                            name: 'name',
                            render: function(data, type, full) {
                                return '<div class="fw-medium">' + (data || '-') + '</div>';
                            }
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
                            data: null,
                            name: 'amount',
                            render: function(data, type, full) {
                                var amount = full.amount != null ? parseFloat(full.amount) : null;
                                var pAmount = full.payment_amount != null ? parseFloat(full
                                    .payment_amount) : null;
                                var amountHtml = amount != null ? amount.toLocaleString(undefined, {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                }) : '-';
                                if (pAmount != null) {
                                    amountHtml += ' <small class="text-muted">(' + pAmount
                                        .toLocaleString(undefined, {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2
                                        }) + ')</small>';
                                }
                                return amountHtml;
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
                    dom: '<"row"' + '<"col-md-6"l><"col-md-6"fB>>' + 'rt' + '<"row"' +
                        '<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                    buttons: [{
                        extend: 'collection',
                        text: 'Export',
                        className: 'btn btn-outline-secondary',
                        buttons: ['copy', 'excel', 'pdf', 'print']
                    }],
                    responsive: true
                });
            }

            function triggerReload() {
                updateCacheStatus('PENDING');
                if (dt_assigned_requests) dt_assigned_requests.ajax.reload(null, false);
            }

            // Filter controls triggering DataTable reload
            $('#mode_filter, #status_filter').on('change', triggerReload);
            $('#start_date, #end_date').on('change', triggerReload);
            $('#search_term').on('keyup', function(e) {
                if (e.key === 'Enter') triggerReload();
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
        });
    </script>
@endsection

@section('content')
    <section class="app-assigned-requests-list">
        <div class="card">
            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                <h4 class="card-title">Assigned Deposit Requests
                    <span class="cache-status-indicator ms-2" title="Cache status">
                        <span class="cache-dot cache-pending"></span>
                        <small class="cache-text">LOADING</small>
                        <small class="load-time ms-1"></small>
                    </span>
                </h4>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <select id="mode_filter" class="form-select" style="width: auto;">
                        <option value="all">All Modes</option>
                        <option value="bank">Bank</option>
                        <option value="upi">UPI</option>
                        <option value="crypto">Crypto</option>
                    </select>
                    <select id="status_filter" class="form-select" style="width: auto;">
                        <option value="all">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="accepted">Accepted</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <input type="date" id="start_date" class="form-control" style="width: auto;"
                        placeholder="Start date" />
                    <input type="date" id="end_date" class="form-control" style="width: auto;" placeholder="End date" />
                    <input type="text" id="search_term" class="form-control" style="width: 200px;"
                        placeholder="Search UTR/Trans ID" />
                    <select id="auto_reload" class="form-select" style="width: auto;">
                        <option value="0">Auto-reload: Off</option>
                        <option value="15">15s</option>
                        <option value="30">30s</option>
                        <option value="60">60s</option>
                    </select>
                </div>
            </div>
            <div class="card-body border-bottom pt-0">
                <div class="table-responsive">
                    <table class="table datatables-assigned-requests table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Trans ID</th>
                                <th>Name</th>
                                <th>Approver</th>
                                <th>Mode</th>
                                <th>Amount</th>
                                <th>UTR</th>
                                <th>Payment From</th>
                                <th>Account / UPI</th>
                                <th>Image</th>
                                <th>Status</th>
                                <th>Date/Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection
