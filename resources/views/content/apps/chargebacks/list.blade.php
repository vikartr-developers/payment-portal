@extends('layouts/layoutMaster')

@section('title', 'Charge Backs')

<meta name="csrf-token" content="{{ csrf_token() }}">

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
@endsection

@section('page-script')
    <script src="{{ asset('assets/js/chargebacks-list.js') }}"></script>
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
            padding: 0.5rem 0.5rem;
            font-size: 0.875rem;
            vertical-align: middle !important;
            white-space: nowrap;
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

        /* Force horizontal scroll for table */
        .card-body {
            overflow-x: auto;
        }

        .datatables-assigned-requests {
            width: max-content !important;
            min-width: 100%;
        }

        @media (max-width: 992px) {}
    </style>
    @if (session('success'))
        <h6 class="alert alert-success">{{ session('success') }}</h6>
    @endif

    <section class="app-chargebacks-list">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Charge Backs</h4>
                @php($current = Auth::user())
                @if ($current && $current->hasRole('Approver'))
                    <a href="{{ route('chargebacks.create') }}" class="btn btn-primary">New Charge Back</a>
                @endif
            </div>
            <div class="card-body border-bottom pt-0">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <select id="status_filter" class="form-select" style="width:auto;">
                        <option value="all">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="accepted">Accepted</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <input type="date" id="start_date" class="form-control" style="width:auto;" />
                    <input type="date" id="end_date" class="form-control" style="width:auto;" />
                    <input type="text" id="search_term" class="form-control" style="width:200px;"
                        placeholder="Search reason/transaction" />
                </div>
                <div class="table-responsive">
                    <table class="table datatables-chargebacks">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Transaction</th>
                                <th>Merchant</th>
                                <th>User</th>
                                <th>Amount</th>
                                <th>Reason</th>
                                <th>Slip</th>
                                <th>Status</th>
                                <th>Date/Time</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection
