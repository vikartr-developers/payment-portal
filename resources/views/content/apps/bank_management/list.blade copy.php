@extends('layouts/layoutMaster')

@section('title', 'Bank & UPI Management')


@section('vendor-style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />
@endsection

@section('vendor-script')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
@endsection


@section('page-script')
    {{-- <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script> --}}

    <script>
        $(function() {
            $('#bankManagementTable').DataTable({
                processing: true,
                serverSide: false, // client-side advanced features (export/date filters)
                ajax: {
                    url: '{{ route('bank-management.index') }}',
                    dataSrc: 'data'
                },
                columns: [{
                        data: 'type',
                        name: 'type'
                    },
                    {
                        data: 'account_info',
                        name: 'account_info',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'code_or_number',
                        name: 'code_or_number',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'deposit_limit',
                        name: 'deposit_limit'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    // {
                    //     data: 'default',
                    //     name: 'default',
                    //     orderable: false,
                    //     searchable: false
                    // },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                drawCallback: function(settings) {
                    // wire toggle-status button click handler
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
                                    $('#bankManagementTable').DataTable().ajax.reload(null,
                                        false);
                                } else {
                                    if (window.toastr) toastr.error(resp.message ||
                                        'Unable to update status');
                                }
                            }).fail(function() {
                                if (window.toastr) toastr.error('Request failed');
                            });
                        });
                }
            });
        });
    </script>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3>Bank & UPI Accounts</h3>
            <a href="{{ route('bank-management.create') }}" class="btn btn-primary float-end">Add New</a>
        </div>
        <div class="card-datatable table-responsive">
            <table id="bankManagementTable" class="dt-responsive table">
                {{-- <div class="card-body">
                <table id="bankManagementTable" class="table table-bordered table-striped"> --}}
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Account / UPI ID</th>
                        <th>IFSC / Number</th>
                        <th>Deposit Limit</th>
                        <th>Status</th>
                        {{-- <th>Default</th> --}}
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection
