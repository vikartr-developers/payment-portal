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

@section('content')
    <section class="app-withdrawals-list">
        <div class="card">
            <h4 class="card-title ps-5 pt-5">Payout</h4>
            <div class="card-header d-flex justify-content-between align-items-center">
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
                    <a href="{{ route('withdrawals.create') }}" class="btn btn-primary">Create Withdrawal</a>
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
    </section>
@endsection
