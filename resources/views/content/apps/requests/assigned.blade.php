@extends('layouts/layoutMaster')

@section('title', 'Assigned Payment Requests')

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
    <script>
        $(function() {
            var dt_assigned_requests_table = $('.datatables-assigned-requests');
            var csrfToken = $('meta[name="csrf-token"]').attr('content');

            if (dt_assigned_requests_table.length) {
                var dt_assigned_requests = dt_assigned_requests_table.DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route('requests.assigned.data') }}',
                        type: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        dataSrc: function(json) {
                            return json.data;
                        }
                    },
                    columns: [{
                            data: 'id',
                            name: 'id',
                            visible: false
                        },
                        {
                            data: 'name',
                            name: 'name'
                        },
                        {
                            data: 'mode',
                            name: 'mode'
                        },
                        {
                            data: 'amount',
                            name: 'amount'
                        },
                        {
                            data: 'utr',
                            name: 'utr'
                        },
                        {
                            data: 'status',
                            name: 'status'
                        },
                        {
                            data: 'created_at',
                            name: 'created_at'
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        }
                    ],
                    order: [
                        [6, 'desc']
                    ],
                    responsive: true
                });
            }

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
                            toastr.success(response.message || 'Payment request accepted');
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
                            toastr.success(response.message || 'Payment request rejected');
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
                <h4 class="card-title">Assigned Payment Requests</h4>
            </div>
            <div class="card-body border-bottom pt-0">
                <div class="table-responsive">
                    <table class="table datatables-assigned-requests table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Mode</th>
                                <th>Amount</th>
                                <th>UTR</th>
                                <th>Status</th>
                                <th>Created At</th>
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
