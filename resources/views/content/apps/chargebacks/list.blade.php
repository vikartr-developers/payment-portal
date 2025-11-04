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
    @if (session('success'))
        <h6 class="alert alert-success">{{ session('success') }}</h6>
    @endif

    <section class="app-chargebacks-list">
        <div class="card">
            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
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
                    <table class="table datatables-chargebacks table-striped">
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
