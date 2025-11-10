@extends('layouts/layoutMaster')

@section('title', 'Payment Reports')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" />
@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <h5 class="card-title">Payment Reports</h5>
                <p class="card-text text-muted">Export to Excel and view individual payments.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('home') }}" class="btn btn-outline-secondary">View Dashboard</a>
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
                    dom: 'Bfrtip',
                    buttons: [{
                        extend: 'excelHtml5',
                        text: 'Export Excel',
                        titleAttr: 'Export to Excel',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    }],
                    order: [
                        [5, 'desc']
                    ],
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

                $('#filterBtn').on('click', function() {
                    table.ajax.reload();
                });
                $('#clearBtn').on('click', function() {
                    setDefaults();
                    table.ajax.reload();
                });
            });
        })();
    </script>
@endsection
