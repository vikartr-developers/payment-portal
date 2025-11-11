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

                    responsive: true
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
    <style>
        /* Rounded corners and card effect for the table */
        .table {
            border-radius: 16px !important;
            overflow: hidden;
            box-shadow: 0 4px 18px rgba(153, 164, 188, 0.13);
            background: #fff;
            /* Base card background */
        }

        /* Header styles */
        .table thead th {
            /* background: linear-gradient(90deg, #f3e9fa 0%, #e8f9e9 100%); */
            color: #352e5a;
            font-size: 1rem;
            font-weight: 600;
            border: none;
        }

        /* Zebra striping for rows */
        .table-striped>tbody>tr:nth-of-type(odd) {
            background-color: #f8fafc;
        }

        .table-striped>tbody>tr:nth-of-type(even) {
            background-color: #f3f4f8;
        }

        /* Hover effect on rows */
        .table tbody tr:hover {
            background: #e0f7fa !important;
            box-shadow: 0 1px 6px rgba(60, 120, 200, 0.07);
            transition: background 0.2s, box-shadow 0.2s;
        }

        /* Cell padding and font */
        .table th,
        .table td {
            padding: 0.85rem 0.75rem;
            font-size: 1rem;
            vertical-align: middle !important;
        }

        /* Bolder important cells, like status or actions */
        .table td .fw-medium,
        .table td .text-success,
        .table td .text-danger,
        .table td .text-warning {
            font-weight: 600;
            font-size: 1.06em;
        }

        .table td small {
            font-size: 0.92em;
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
            font-size: 1em;
            background: #fafaff;
            margin-right: 6px;
        }

        /* Make table horizontally scrollable on smaller viewports so icons don't get hidden */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
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
                    <a id="exportExcelBtn" class="btn btn-primary">Export Excel</a>
                </div>
            </div>

            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <select id="request_type_filter" class="form-select" style="width: auto;">
                        <option value="all">All Requests</option>
                        <option value="pending" selected>Pending Requests</option>
                        <option value="rejected">Rejected Requests</option>
                    </select>
                    <select id="mode_filter" class="form-select" style="width: auto;">
                        <option value="all">All Modes</option>
                        <option value="bank">Bank</option>
                        <option value="upi">UPI</option>
                        <option value="crypto">Crypto</option>
                    </select>
                    <select id="status_filter" class="form-select" style="width: auto;">
                        <option value="all">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="accepted">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <input type="date" id="start_date" class="form-control" style="width: auto;"
                        placeholder="Start date" />
                    <input type="date" id="end_date" class="form-control" style="width: auto;" placeholder="End date" />
                    <input type="text" id="search_term" class="form-control" style="width: 200px;"
                        placeholder="Search UTR/Trans ID" />
                    <select id="auto_reload" class="form-select" style="width: auto;">
                        <option value="0">Auto-reload: Off</option>
                        <option value="5">5s</option>
                        <option value="10">10s</option>
                        <option value="15">15s</option>
                        <option value="20">20s</option>
                        <option value="30">30s</option>
                        <option value="60">60s</option>
                    </select>
                    {{-- <a href="{{ route('requests.add') }}" class="btn btn-primary">Add Payment Request</a> --}}

                </div>
            </div>
            <div class="card-body border-bottom pt-0">
                <div class="table w-100">
                    <table class="table w-100 datatables-assigned-requests ">
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
