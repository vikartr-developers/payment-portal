@extends('layouts/layoutMaster')

@section('title', $page_data['page_title'])

@section('vendor-style')
    {{-- Page Css files --}}

@endsection

@section('page-style')
    {{-- Page Css files --}}
@endsection

@section('content')

    @if ($page_data['form_title'] == 'Add New User')
        <form action="{{ route('app-users-store') }}" method="POST" enctype="multipart/form-data">
            @csrf
        @else
            <form action="{{ route('app-users-update', encrypt($user->id)) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
    @endif

    <section id="multiple-column-form">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>{{ $page_data['form_title'] }}</h4>
                        <a href="{{ route('app-users-list') }}" class="col-md-2 btn btn-primary float-end">User
                            List</a>

                        {{-- <h4 class="card-title">{{$page_data['form_title']}}</h4> --}}
                    </div>
                    <div class="card-body">
                        <div class="row">
                            {{-- <div class="col-md-6 col-sm-12 mb-1">
                                <label class="form-label" for="username">
                                    Username</label>
                                <input type="text" id="username" class="form-control" placeholder="Username"
                                    name="username" value="{{ old('username') ?? ($user != '' ? $user->username : '') }}">
                                <span class="text-danger">
                                    @error('username')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div> --}}
                            <div class="col-md-6 col-sm-12 mb-1">
                                <label class="form-label" for="first_name">
                                    First Name</label>
                                <input type="text" id="first_name" class="form-control" placeholder="First Name"
                                    name="first_name"
                                    value="{{ old('first_name') ?? ($user != '' ? $user->first_name : '') }}">
                                <span class="text-danger">
                                    @error('first_name')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div>
                            <div class="col-md-6 col-sm-12 mb-1">
                                <label class="form-label" for="last_name">
                                    Last Name</label>
                                <input type="text" id="last_name" class="form-control" placeholder="Last Name"
                                    name="last_name"
                                    value="{{ old('last_name') ?? ($user != '' ? $user->last_name : '') }}">
                                <span class="text-danger">
                                    @error('last_name')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div>
                            <div class="col-md-6 col-sm-12 mb-1">
                                <label class="form-label" for="phone_no">
                                    Phone No</label>
                                <input type="text" id="phone_no" class="form-control" placeholder="Phone No"
                                    name="phone_no" value="{{ old('phone_no') ?? ($user != '' ? $user->contact : '') }}">
                                <span class="text-danger">
                                    @error('phone_no')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div>
                            <div class="col-md-6 col-sm-12 mb-1">
                                <label class="form-label" for="email">
                                    Email</label>
                                <input type="email" id="email" class="form-control" placeholder="Email" name="email"
                                    value="{{ old('email') ?? ($user != '' ? $user->email : '') }}">
                                <span class="text-danger">
                                    @error('email')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div>



                            {{-- <div class="col-md-6 col-sm-12 mb-1">
                                <label class="form-label" for="dob">
                                    Date of Birth</label>
                                <input type="date" name="dob" id="dob" class="form-control"
                                    value="{{ old('dob') ?? ($user != '' ? $user->dob : '') }}">
                                <span class="text-danger">
                                    @error('dob')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div> --}}
                            {{-- <div class="col-md-6 col-sm-12 mb-1">
                                                <label class="form-label" for="branch">
                                                    Branch</label>
                                                <select class="select2 form-select" name="branch" id="branch">
                                                    <option value="" >Select Branch</option>
                                                    @foreach ($data['branches'] as $branch)
                                                        <option value="{{ $branch->id }}" {{ old('branch') ? (old('branch') == $branch->id ? 'selected' : '') : ($user ? ($user->branch == $branch->id ? 'selected' : '') : '') }}>{{ $branch->name }}</option>
                                                    @endforeach
                                                </select>
                                                <span class="text-danger">
                                                    @error('branch')
                                                    {{ $message }}
                                                    @enderror
                                                </span>
                                            </div> --}}
                            {{-- <div class="col-md-6 col-sm-12 mb-1">
                                <label class="form-label" for="form_group">
                                    Form Group</label>
                                <select class="select2 form-select" id="form_group" name="form_group">
                                    <option value="">Select Form Group</option>
                                    @foreach ($data['form_groups'] as $form_group)
                                        <option value="{{ $form_group->id }}"
                                            {{ old('form_group') ? (old('form_group') == $form_group->id ? 'selected' : '') : ($user ? ($user->form_group == $form_group->id ? 'selected' : '') : '') }}>
                                            {{ $form_group->name }}</option>
                                    @endforeach
                                </select>
                                <span class="text-danger">
                                    @error('form_group')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div> --}}
                            {{-- <div class="col-md-6 col-sm-12 mb-1">
                                <label class="form-label" for="report_to">
                                    Report To</label>
                                <select class="select2 form-select" name="report_to">
                                    <option value="" hidden>Select Report To</option>
                                    @foreach ($data['reports_to'] as $report_to)
                                        <option value="{{ $report_to->id }}"
                                            {{ old('report_to') ? (old('report_to') == $report_to->id ? 'selected' : '') : ($user ? ($user->report_to == $report_to->id ? 'selected' : '') : '') }}>
                                            {{ $report_to->first_name . ' ' . $report_to->last_name }}</option>
                                    @endforeach
                                </select>
                                <span class="text-danger">
                                    @error('report_to')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div> --}}
                            <div class="col-md-6 col-sm-12 mb-1">
                                <label class="form-label" for="address_line_1">
                                    Address Line 1</label>
                                <textarea class="form-control" id="exampleFormControlTextarea1" rows="3" name="address_line_1"
                                    placeholder="Address Line 1">{{ old('address_line_1') ?? ($user != '' ? $user->address_line_1 : '') }}</textarea>
                                <span class="text-danger">
                                    @error('address_line_1')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div>
                            <div class="col-md-6 col-sm-12 mb-1">
                                <label class="form-label" for="address_line_2">
                                    Address Line 2</label>
                                <textarea class="form-control" id="exampleFormControlTextarea1" rows="3" name="address_line_2"
                                    placeholder="address_line_2">{{ old('address_line_2') ?? ($user != '' ? $user->address_line_2 : '') }}</textarea>
                                <span class="text-danger">
                                    @error('address_line_2')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div>
                            <div class="col-md-6 col-sm-12 mb-1">
                                <label class="form-label" for="state_name">
                                    State Name</label>
                                <input type="text" id="state_name" class="form-control" placeholder="State Name"
                                    name="state_name"
                                    value="{{ old('state_name') ?? ($user != '' ? $user->state_name : '') }}">
                                <span class="text-danger">
                                    @error('state_name')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div>
                            <div class="col-md-6 col-sm-12 mb-1">
                                <label class="form-label" for="zip_code">
                                    Zip Code</label>
                                <input type="text" id="zip_code" class="form-control" placeholder="Zip Code"
                                    name="zip_code" value="{{ old('zip_code') ?? ($user != '' ? $user->zip_code : '') }}">
                                <span class="text-danger">
                                    @error('zip_code')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div>
                            <div class="col-md-6 col-sm-12 mb-1">
                                <label class="form-label" for="role">
                                    Select Role</label>
                                <select class="select2 form-select" name="role" id="role">
                                    <option value="" selected disabled>Select Role</option>
                                    @forelse($roles as $role)
                                        <option value="{{ $role->id }}"
                                            {{ $user != '' ? ($role->display_name == $user->role ? 'selected' : '') : '' }}>
                                            {{ $role->display_name }}</option>
                                    @empty
                                        <option value="" selected disabled>No Roles Found</option>
                                    @endforelse
                                </select>
                                <span class="text-danger">
                                    @error('role')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <div class="row align-items-md-end">
                                    <div class="col-md-9 col-sm-12">
                                        <label class="form-label" for="password">
                                            Password</label>
                                        {{-- <div class="input-group input-group-merge form-password-toggle">
                                                            <input
                                                                type="password"
                                                                class="form-control form-control-merge"
                                                                id="password"
                                                                name="password"
                                                                value=""
                                                            />
                                                            <span class="input-group-text cursor-pointer"><i data-feather="eye"></i></span>
                                                        </div> --}}
                                        <input type="text" id="password" class="form-control"
                                            placeholder="{{ $user ? ($user->password ? 'Enter or Click Generate to Change Password' : 'Password') : '' }}"
                                            name="password" value="{{ old('password') ?? old('password') }}">
                                        <span class="text-danger">
                                            @error('password')
                                                {{ $message }}
                                            @enderror
                                        </span>
                                    </div>
                                    <div class="col-md-3 col-sm-12">
                                        <button type="button" class="btn btn-outline-primary"
                                            id="generatePassword">Generate Password
                                        </button>
                                    </div>
                                </div>
                            </div>
                            {{-- <div class="col-md-6 col-sm-12 mb-4">
                                <div class="row">
                                    <div class="col-md-4 col-sm-4 mt-2">
                                        <label class="form-label" for="can_export_excel">
                                            Can Export Excel</label>
                                        <div class="form-check form-check-success form-switch">
                                            <input type="checkbox" name="can_export_excel"
                                                {{ $user != '' && $user->can_export_excel == true ? 'checked' : '' }}
                                                class="form-check-input" id="customSwitch4" />
                                        </div>
                                        <span class="text-danger">
                                            @error('can_export_excel')
                                                {{ $message }}
                                            @enderror
                                        </span>
                                    </div>
                                    <div class="col-md-4 col-sm-4 mt-2">
                                        <label class="form-label" for="can_print_report">
                                            Can Print Report </label>
                                        <div class="form-check form-check-success form-switch">
                                            <input type="checkbox" name="can_print_reports"
                                                {{ $user != '' && $user->can_print_reports == true ? 'checked' : '' }}
                                                class="form-check-input" id="can_print_report" />
                                        </div>
                                        <span class="text-danger">
                                            @error('can_print_report')
                                                {{ $message }}
                                            @enderror
                                        </span>
                                    </div>
                                    <div class="col-md-4 col-sm-4 mt-2">
                                        <label class="form-label" for="can_remove_tax">
                                            Can Remove TAX</label>
                                        <div class="form-check form-check-success form-switch">
                                            <input type="checkbox" name="can_remove_tax"
                                                {{ $user != '' && $user->can_remove_tax == true ? 'checked' : '' }}
                                                class="form-check-input" id="can_remove_tax" />
                                        </div>
                                        <span class="text-danger">
                                            @error('can_remove_tax')
                                                {{ $message }}
                                            @enderror
                                        </span>
                                    </div>
                                    <div class="col-md-4 col-sm-4 mt-2">
                                        <label class="form-label" for="can_delete_package">
                                            Can Delete Package</label>
                                        <div class="form-check form-check-success form-switch">
                                            <input type="checkbox" name="can_delete_package"
                                                {{ $user != '' && $user->can_delete_package == true ? 'checked' : '' }}
                                                class="form-check-input" id="can_delete_package" />
                                        </div>
                                        <span class="text-danger">
                                            @error('can_delete_package')
                                                {{ $message }}
                                            @enderror
                                        </span>
                                    </div>
                                    <div class="col-md-4 col-sm-4 mt-2">
                                        <label class="form-label" for="active">
                                            Status</label>
                                        <div class="form-check form-check-success form-switch">
                                            <input type="checkbox" name="status"
                                                {{ $user != '' && $user->status == true ? 'checked' : '' }}
                                                class="form-check-input" id="active" />
                                        </div>
                                        <span class="text-danger">
                                            @error('active')
                                                {{ $message }}
                                            @enderror
                                        </span>
                                    </div>
                                </div>
                            </div> --}}
                        </div>

                        <div class="col-12">
                            <button type="submit" name="submit" value="submit" class="btn btn-primary me-1">Submit
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">Reset</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    </form>
@endsection

@section('vendor-script')
    {{-- Vendor js files --}}
@endsection
@section('page-script')
    <!-- Page js files -->
    <script>
        // Function to generate a random password
        function generateRandomPassword(length) {
            const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            let password = "";
            for (let i = 0; i < length; i++) {
                const randomIndex = Math.floor(Math.random() * charset.length);
                password += charset.charAt(randomIndex);
            }
            return password;
        }

        $(document).ready(function() {
            // Generate password when the button is clicked
            $("#generatePassword").click(function() {
                const generatedPassword = generateRandomPassword(
                    10); // You can adjust the length of the password here
                $("#password").val(generatedPassword);
                $("#password").select();
                document.execCommand("copy");
                alert("Password copied to clipboard!");
            });
        });
    </script>
@endsection
