@extends('layouts/layoutMaster')

@section('title', isset($crypto) ? 'Edit Crypto Wallet' : 'Add Crypto Wallet')

@section('content')
    <div class="card">
        <h5 class="card-header">{{ isset($crypto) ? 'Edit Crypto Wallet' : 'Add New Crypto Wallet' }}</h5>
        <div class="card-body">

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST"
                action="{{ isset($crypto) ? route('crypto-management.update', $crypto->id) : route('crypto-management.store') }}">
                @csrf
                @if (isset($crypto))
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <label for="wallet_address" class="form-label">Wallet Address</label>
                    <input type="text" class="form-control" id="wallet_address" name="wallet_address"
                        value="{{ old('wallet_address', $crypto->wallet_address ?? '') }}" required maxlength="255" />
                </div>

                <div class="mb-3">
                    <label for="network" class="form-label">Network</label>
                    <input type="text" class="form-control" id="network" name="network"
                        value="{{ old('network', $crypto->network ?? '') }}" required maxlength="100" />
                </div>

                <div class="mb-3">
                    <label for="coin" class="form-label">Coin</label>
                    <input type="text" class="form-control" id="coin" name="coin"
                        value="{{ old('coin', $crypto->coin ?? '') }}" required maxlength="50" />
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select" required>
                        <option value="active" {{ old('status', $crypto->status ?? '') === 'active' ? 'selected' : '' }}>
                            Active</option>
                        <option value="inactive"
                            {{ old('status', $crypto->status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="form-check mb-3">
                    <input type="hidden" name="is_default" value="0" />
                    <input type="checkbox" class="form-check-input" id="is_default" name="is_default" value="1"
                        {{ old('is_default', $crypto->is_default ?? false) ? 'checked' : '' }} />
                    <label class="form-check-label" for="is_default">Set as Default Wallet</label>
                </div>

                <button type="submit" class="btn btn-primary">{{ isset($crypto) ? 'Update' : 'Add' }} Wallet</button>
                <a href="{{ route('crypto-management.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection
