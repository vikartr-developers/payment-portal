@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Bank Management</h2>
        <a href="{{ route('bank-management.create') }}" class="btn btn-primary mb-3">Add New Account</a>

        <table class="table table-bordered" id="bankManagementTable">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Account Number / UPI ID</th>
                    <th>IFSC Code / Number</th>
                    <th>Deposit Limit</th>
                    <th>Default</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>

    <!-- Include jQuery and DataTables JS/CSS -->
    <link rel="stylesheet" href="//cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <script src="//code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="//cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

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
