@extends('layouts/layoutMaster')

@section('title', 'Bank & UPI Management')

@section('vendor-style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />

    {{-- <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs4/datatables.bootstrap4.css') }}"> --}}
@endsection

{{-- @section('vendor-script') --}}
{{-- <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>

    <script src="{{ asset('assets/vendor/libs/datatables/datatables.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/datatables-bs4/datatables.bootstrap4.js') }}"></script> --}}
{{-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />

@endsection --}}



@section('vendor-script')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    {{-- <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script> --}}
    <script src="https://unpkg.com/feather-icons"></script>
    @yield('links')
@endsection


@section('page-script')
    {{-- <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script> --}}

    <script>
        $(function() {
            $('#bankManagementTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('bank-management.index') }}',
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
                        data: 'default',
                        name: 'default',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
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
        <div class="card-body">
            <table id="bankManagementTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Account / UPI ID</th>
                        <th>IFSC / Number</th>
                        <th>Deposit Limit</th>
                        <th>Default</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection
