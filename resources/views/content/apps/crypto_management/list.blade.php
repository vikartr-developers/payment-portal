@extends('layouts/layoutMaster')

@section('title', 'Crypto Management - List')

@section('content')
    <div class="card">
        <h5 class="card-header">Crypto Wallets</h5>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <a href="{{ route('crypto-management.create') }}" class="btn btn-primary mb-3">Add New Crypto Wallet</a>

            @if ($cryptos->count())
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Wallet Address</th>
                            <th>Network</th>
                            <th>Coin</th>
                            <th>Status</th>
                            <th>Default</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cryptos as $crypto)
                            <tr>
                                <td>{{ $crypto->wallet_address }}</td>
                                <td>{{ $crypto->network }}</td>
                                <td>{{ $crypto->coin }}</td>
                                <td>
                                    @if ($crypto->status === 'active')
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($crypto->is_default)
                                        <span class="badge bg-primary">Yes</span>
                                    @else
                                        <form method="POST"
                                            action="{{ route('crypto-management.set-default', $crypto->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Set as
                                                Default</button>
                                        </form>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('crypto-management.edit', $crypto->id) }}"
                                        class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('crypto-management.destroy', $crypto->id) }}" method="POST"
                                        style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure to delete this wallet?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>No crypto wallets found. Add some.</p>
            @endif
        </div>
    </div>
@endsection
