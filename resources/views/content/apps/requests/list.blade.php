{{-- resources/views/requests/list.blade.php --}}
@extends('layouts/layoutMaster')

@section('title', 'Dosit Requests')

<meta name="csrf-token" content="{{ csrf_token() }}">

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    <style>
        .cache-status-indicator {
            display: inline-flex;
            align-items: center;
            font-size: 0.75rem;
        }

        .cache-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 4px;
        }

        .cache-success {
            background-color: #28a745;
        }

        .cache-warning {
            background-color: #ffc107;
        }

        .cache-pending {
            background-color: #6c757d;
        }

        .cache-loading {
            background-color: #007bff;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }

            100% {
                opacity: 1;
            }
        }
    </style>
@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
@endsection

@section('page-script')
    <script src="{{ asset('assets/js/requests-list.js') }}"></script>
@endsection

@section('content')
    @if (session('status'))
        <h6 class="alert alert-warning">{{ session('status') }}</h6>
    @endif

    @if (session('success'))
        <h6 class="alert alert-success">{{ session('success') }}</h6>
    @endif

    <section class="app-requests-list">
        <div class="card">
            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                <h4 class="card-title">Deposit Requests
                    <span class="cache-status-indicator ms-2" title="Cache status">
                        <span class="cache-dot cache-pending"></span>
                        <small class="cache-text">LOADING</small>
                        <small class="load-time ms-1"></small>
                    </span>
                </h4>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <select id="mode_filter" class="form-select" style="width: auto;">
                        <option value="all">All Modes</option>
                        <option value="bank">Bank</option>
                        <option value="upi">UPI</option>
                        <option value="crypto">Crypto</option>
                    </select>
                    <select id="status_filter" class="form-select" style="width: auto;">
                        <option value="all">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="accepted">Accepted</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <input type="date" id="start_date" class="form-control" style="width: auto;"
                        placeholder="Start date" />
                    <input type="date" id="end_date" class="form-control" style="width: auto;" placeholder="End date" />
                    <input type="text" id="search_term" class="form-control" style="width: 200px;"
                        placeholder="Search UTR/Trans ID" />
                    <select id="auto_reload" class="form-select" style="width: auto;">
                        <option value="0">Auto-reload: Off</option>
                        <option value="15">15s</option>
                        <option value="30">30s</option>
                        <option value="60">60s</option>
                    </select>
                    <select id="include_trashed" class="form-select" style="width: auto;">
                        <option value="false">Active Requests Only</option>
                        <option value="true">Include Deleted</option>
                    </select>
                    <select id="only_trashed" class="form-select" style="width: auto;">
                        <option value="false">Show All</option>
                        <option value="true">Deleted Only</option>
                    </select>
                    <a href="{{ route('requests.add') }}" class="btn btn-primary">Add Payment Request</a>
                </div>
            </div>
            <div class="card-body border-bottom pt-0">
                <div class="table-responsive">
                    <table class="table datatables-requests table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Trans ID</th>
                                <th>Name</th>
                                <th>Approver</th>
                                <th>Mode</th>
                                <th>Amount</th>
                                <th>UTR</th>
                                <th>Payment From</th>
                                <th>Account / UPI</th>
                                <th>Image</th>
                                <th>Status</th>
                                <th>Date/Time</th>
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
