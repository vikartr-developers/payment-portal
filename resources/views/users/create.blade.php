@extends('layouts/layoutMaster')

@section('title', 'Create User')

@section('content')
    <h4 class="py-3 mb-4"><span class="text-muted fw-light">User Management /</span> Create User</h4>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('users.store') }}">
        @csrf

        <div class="mb-3">
            <label for="role" class="form-label">Role</label>
            <select id="role" name="role" class="form-select" onchange="toggleOptionalFields()">
                <option value="customer">Customer</option>
                {{-- <option value="admin">Admin</option> --}}
            </select>
        </div>

        <div class="mb-3">
            <label for="first_name" class="form-label">First Name</label>
            <input type="text" class="form-control" id="first_name" name="first_name" required />
        </div>

        <div class="mb-3">
            <label for="last_name" class="form-label">Last Name</label>
            <input type="text" class="form-control" id="last_name" name="last_name" required />
        </div>

        {{-- <div id="optionalFields"> --}}
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" />
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" class="form-control" id="phone" name="phone" />
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" />
        </div>
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" />
        </div>
        {{-- </div> --}}

        <button type="submit" class="btn btn-primary">Create User</button>
    </form>

    <script>
        function toggleOptionalFields() {
            const role = document.getElementById('role').value;
            const optionalFields = document.getElementById('optionalFields');
            if (role === 'customer') {
                optionalFields.style.display = 'none';
            } else {
                optionalFields.style.display = 'block';
            }
        }
        // Initialize display on page load
        toggleOptionalFields();
    </script>
@endsection
