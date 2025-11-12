@extends('layouts/layoutMaster')

@section('title', 'Sub Approvers List')

@section('vendor-style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" />
    <style>
        #users-table {
            font-size: 13px;
        }

        #users-table thead th {
            background: #f8f9fb;
            color: #333;
            border-bottom: 2px solid #e9ecef;
            font-weight: 600;
            white-space: nowrap;
        }

        #users-table tbody tr td {
            vertical-align: middle;
            white-space: nowrap;
            padding: 8px 12px;
        }

        #users-table .btn {
            padding: 4px 8px;
            font-size: 11px;
            margin-right: 2px;
        }

        #users-table form {
            display: inline-block;
            margin: 0;
        }

        .badge {
            font-size: 11px;
            padding: 4px 8px;
        }
    </style>
@endsection

@section('vendor-script')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    @yield('links')
@endsection

@section('content')
    <!-- users list start -->
    @if (session('status'))
        <h6 class="alert alert-warning">{{ session('status') }}</h6>
    @endif
    <section class="app-user-list">
        <div class="card mt-5">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Sub Approvers List</h4>
                <a href="{{ route('app-users-add') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>Add Sub Approver
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table" id="users-table">
                        <thead>
                            <tr>
                                <th>Approver Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Updated At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </section>
    <!-- users list ends -->
@endsection

@section('page-script')
    <script>
        $(document).ready(function() {
            var table = $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                "lengthMenu": [10, 25, 50, 100, 200],
                ajax: "{{ route('app-site-users-get-all') }}",
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'updated_at',
                        name: 'updated_at'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [3, 'desc']
                ], // Sort by updated_at descending
                dom: '<"d-flex justify-content-between align-items-center mb-3"<"dt-buttons btn-group"><"ms-auto"f>>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="ti ti-file-spreadsheet me-1"></i>Excel',
                        className: 'btn btn-outline-primary',
                        exportOptions: {
                            columns: [0, 1, 2, 3]
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        text: '<i class="ti ti-file-text me-1"></i>CSV',
                        className: 'btn btn-outline-secondary',
                        exportOptions: {
                            columns: [0, 1, 2, 3]
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="ti ti-printer me-1"></i>Print',
                        className: 'btn btn-outline-info',
                        exportOptions: {
                            columns: [0, 1, 2, 3]
                        }
                    }
                ],
                drawCallback: function() {
                    feather.replace();
                    $('[data-bs-toggle="tooltip"]').tooltip();

                    // Handle disable/enable toggle
                    $('#users-table').off('click', '.toggle-status').on('click', '.toggle-status',
                        function(e) {
                            e.preventDefault();
                            var url = $(this).data('url');
                            var userId = $(this).data('id');
                            if (!url) return;
                            if (!confirm('Are you sure you want to change this user status?'))
                                return;
                            var token = $('meta[name="csrf-token"]').attr('content');
                            $.post(url, {
                                _token: token
                            }, function(resp) {
                                if (resp && resp.success) {
                                    if (window.toastr) toastr.success(resp.message ||
                                        'Status updated');
                                    table.ajax.reload(null, false);
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

            table.buttons().container().appendTo($('.dt-buttons'));
        });
    </script>
@endsection
