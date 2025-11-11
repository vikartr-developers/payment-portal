@extends('layouts/layoutMaster')

@section('title', 'Payment Reports')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" />
    <style>
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
            padding: 1.75rem;
        }

        .summary-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            font-size: 1.75rem;
        }

        .summary-value {
            font-size: 1.85rem;
            font-weight: 700;
            margin: 0.75rem 0 0.25rem;
        }

        .summary-label {
            font-size: 0.95rem;
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

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
@endsection

@section('content')
    <!-- Summary Cards Row -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card summary-card ">
                <div class="card-body d-flex align-items-center">
                    <div class="summary-icon bg-success text-white me-3">
                        <i class="ti ti-currency-dollar"></i>
                    </div>
                    <div class="flex-grow-1">
                        <p class="summary-label">Total Deposits</p>
                        <h3 class="summary-value text-success" id="totalDeposits">₹0.00</h3>
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
                        <p class="summary-label">Total Approver Earnings</p>
                        <h3 class="summary-value text-primary" id="totalEarnings">₹0.00</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <h5 class="card-title">Payment Reports</h5>
            </div>
            <div class="d-flex gap-2">
                <a id="exportExcelBtn" class="btn btn-primary">Export Excel</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Start date</label>
                    <input type="date" id="start_date" class="form-control" />
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">End date</label>
                    <input type="date" id="end_date" class="form-control" />
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button id="filterBtn" class="btn btn-primary">Filter</button>
                    <button id="clearBtn" class="btn btn-outline-secondary ms-2">Clear</button>
                </div>
            </div>

            <div class="table-responsive">
                <table id="paymentsTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Account Number / UPI</th>
                            <th>Total Deposit</th>
                            <th>Charges</th>
                            <th>Total Charge</th>
                            <th>Approver</th>
                            <th>Payment Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <script>
        (function() {
            let table;

            // Format currency as Indian Rupee
            function formatCurrency(amount) {
                if (amount === null || amount === undefined) return '₹0.00';
                return new Intl.NumberFormat('en-IN', {
                    style: 'currency',
                    currency: 'INR',
                    maximumFractionDigits: 2
                }).format(amount);
            }

            // Update summary cards
            function updateSummaryCards() {
                const start = $('#start_date').val();
                const end = $('#end_date').val();
                const url =
                    `{{ route('reports.payments.data') }}?start_date=${encodeURIComponent(start)}&end_date=${encodeURIComponent(end)}&summary=1`;

                $.ajax({
                    url: url,
                    method: 'GET',
                    success: function(response) {
                        // Calculate totals from the data
                        let totalDeposits = 0;
                        let totalCharges = 0;

                        if (response.data && Array.isArray(response.data)) {
                            response.data.forEach(function(row) {
                                const amount = parseFloat(row.payment_amount) || 0;
                                totalDeposits += amount;

                                // Calculate charge (using the charge percent from the row or default 4%)
                                const chargePercent = 0.04; // 4% default
                                totalCharges += (amount * chargePercent);
                            });
                        }

                        $('#totalDeposits').text(formatCurrency(totalDeposits));
                        $('#totalEarnings').text(formatCurrency(totalCharges));
                    },
                    error: function() {
                        console.error('Failed to load summary data');
                    }
                });
            }

            function buildTable(start, end) {
                if (table) {
                    table.ajax.url(`{{ route('reports.payments.data') }}?start_date=${start}&end_date=${end}`).load();
                    return;
                }

                table = $('#paymentsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: `{{ route('reports.payments.data') }}`,
                        data: function(d) {
                            d.start_date = $('#start_date').val();
                            d.end_date = $('#end_date').val();
                        }
                    },
                    columns: [{
                            data: 'account',
                            name: 'account_upi'
                        },
                        {
                            data: 'payment_amount',
                            name: 'payment_amount'
                        },
                        {
                            data: 'charges',
                            name: 'charges',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'total_charge',
                            name: 'total_charge',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'approver',
                            name: 'approver_name'
                        },
                        {
                            data: 'payment_date',
                            name: 'created_at'
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        }
                    ],
                    // dom: 'Bfrtip',

                    responsive: true
                });
            }

            function setDefaults() {
                const end = new Date();
                const start = new Date();
                start.setDate(end.getDate() - 6);
                $('#start_date').val(start.toISOString().slice(0, 10));
                $('#end_date').val(end.toISOString().slice(0, 10));
            }

            $(document).ready(function() {
                setDefaults();
                buildTable($('#start_date').val(), $('#end_date').val());
                updateSummaryCards();

                $('#filterBtn').on('click', function() {
                    table.ajax.reload();
                    updateSummaryCards();
                });
                $('#clearBtn').on('click', function() {
                    setDefaults();
                    table.ajax.reload();
                    updateSummaryCards();
                });
                // Export full dataset (server-side) using current filters
                $('#exportExcelBtn').on('click', function(e) {
                    e.preventDefault();
                    var start = $('#start_date').val();
                    var end = $('#end_date').val();
                    var url = '{{ route('reports.payments.export') }}';
                    var params = [];
                    if (start) params.push('start_date=' + encodeURIComponent(start));
                    if (end) params.push('end_date=' + encodeURIComponent(end));
                    if (params.length) url += '?' + params.join('&');
                    // trigger download
                    window.location = url;
                });
            });
        })();
    </script>
@endsection
