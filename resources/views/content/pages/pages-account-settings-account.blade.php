@extends('layouts/layoutMaster')

@section('title', 'Account settings - Account')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/umd/styles/index.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/animate-css/animate.css') }}" />
@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/umd/bundle/popular.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/umd/plugin-bootstrap5/index.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/umd/plugin-auto-focus/index.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/cleavejs/cleave.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/cleavejs/cleave-phone.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
@endsection

@section('page-script')
    <script src="{{ asset('assets/js/pages-account-settings-account.js') }}"></script>
@endsection

@section('content')
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Account Settings /</span> Account
    </h4>

    <div class="row">
        <div class="col-md-12">
            <ul class="nav nav-pills flex-column flex-md-row mb-4">
                <li class="nav-item"><a class="nav-link active" href="javascript:void(0);"><i
                            class="ti-xs ti ti-users me-1"></i> Account</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ url('pages/account-settings-security') }}"><i
                            class="ti-xs ti ti-lock me-1"></i> Security</a></li>
                {{-- <li class="nav-item"><a class="nav-link" href="{{ url('pages/account-settings-billing') }}"><i
                            class="ti-xs ti ti-file-description me-1"></i> Billing & Plans</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ url('pages/account-settings-notifications') }}"><i
                            class="ti-xs ti ti-bell me-1"></i> Notifications</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ url('pages/account-settings-connections') }}"><i
                            class="ti-xs ti ti-link me-1"></i> Connections</a></li> --}}
            </ul>
            <div class="card mb-4">
                <h5 class="card-header">Profile Details</h5>
                <!-- Account -->
                <div class="card-body">
                    <div class="d-flex align-items-start align-items-sm-center gap-4">
                        <img src="{{ asset('assets/img/avatars/14.png') }}" alt="user-avatar"
                            class="d-block w-px-100 h-px-100 rounded" id="uploadedAvatar" />
                        <div class="button-wrapper">
                            <label for="upload" class="btn btn-primary me-2 mb-3" tabindex="0">
                                <span class="d-none d-sm-block">Upload new photo</span>
                                <i class="ti ti-upload d-block d-sm-none"></i>
                                <input type="file" id="upload" class="account-file-input" hidden
                                    accept="image/png, image/jpeg" />
                            </label>
                            <button type="button" class="btn btn-label-secondary account-image-reset mb-3">
                                <i class="ti ti-refresh-dot d-block d-sm-none"></i>
                                <span class="d-none d-sm-block">Reset</span>
                            </button>

                            <div class="text-muted">Allowed JPG, GIF or PNG. Max size of 800K</div>
                            @if (auth()->user()->google2fa_enabled == 0)
                                <a href="{{ route('2fa.setup') }}" class="btn btn-warning mt-2">
                                    <i class="ti ti-shield-lock me-1"></i> Setup Two-Factor Authentication
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                <hr class="my-0">
                <div class="card-body">
                    <form id="j" method="POST" action="{{ route('update-profile', auth()->user()->id) }}">
                        @csrf
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="first_name" class="form-label">First Name</label>
                                <input class="form-control @error('first_name') is-invalid @enderror" type="text"
                                    id="first_name" name="first_name"
                                    value="{{ old('first_name', Auth()->user()->first_name) }}" autofocus />
                                @error('first_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input class="form-control @error('last_name') is-invalid @enderror" type="text"
                                    name="last_name" id="last_name"
                                    value="{{ old('last_name', Auth()->user()->last_name) }}" />
                                @error('last_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="email" class="form-label">E-mail</label>
                                <input class="form-control @error('email') is-invalid @enderror" type="email"
                                    id="email" name="email" value="{{ old('email', Auth()->user()->email) }}"
                                    placeholder="john.doe@example.com" />
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="phone_no" class="form-label">Phone Number</label>
                                <input type="text" class="form-control @error('phone_no') is-invalid @enderror"
                                    id="phone_no" name="phone_no" value="{{ old('phone_no', Auth()->user()->contact) }}"
                                    placeholder="+1 123 456 7890" />
                                @error('phone_no')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            {{-- <div class="mb-3 col-md-6">
                                <label class="form-label" for="phoneNumber">Phone Number</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text">US (+1)</span>
                                    <input type="text" id="phoneNumber" name="phoneNumber" class="form-control"
                                        placeholder="202 555 0111" />
                                </div>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="address" name="address"
                                    placeholder="Address" />
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="state" class="form-label">State</label>
                                <input class="form-control" type="text" id="state" name="state"
                                    placeholder="California" />
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="zipCode" class="form-label">Zip Code</label>
                                <input type="text" class="form-control" id="zipCode" name="zipCode"
                                    placeholder="231465" maxlength="6" />
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label" for="country">Country</label>
                                <select id="country" class="select2 form-select">
                                    <option value="">Select</option>
                                    <option value="Australia">Australia</option>
                                    <option value="Bangladesh">Bangladesh</option>
                                    <option value="Belarus">Belarus</option>
                                    <option value="Brazil">Brazil</option>
                                    <option value="Canada">Canada</option>
                                    <option value="China">China</option>
                                    <option value="France">France</option>
                                    <option value="Germany">Germany</option>
                                    <option value="India">India</option>
                                    <option value="Indonesia">Indonesia</option>
                                    <option value="Israel">Israel</option>
                                    <option value="Italy">Italy</option>
                                    <option value="Japan">Japan</option>
                                    <option value="Korea">Korea, Republic of</option>
                                    <option value="Mexico">Mexico</option>
                                    <option value="Philippines">Philippines</option>
                                    <option value="Russia">Russian Federation</option>
                                    <option value="South Africa">South Africa</option>
                                    <option value="Thailand">Thailand</option>
                                    <option value="Turkey">Turkey</option>
                                    <option value="Ukraine">Ukraine</option>
                                    <option value="United Arab Emirates">United Arab Emirates</option>
                                    <option value="United Kingdom">United Kingdom</option>
                                    <option value="United States">United States</option>
                                </select>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="language" class="form-label">Language</label>
                                <select id="language" class="select2 form-select">
                                    <option value="">Select Language</option>
                                    <option value="en">English</option>
                                    <option value="fr">French</option>
                                    <option value="de">German</option>
                                    <option value="pt">Portuguese</option>
                                </select>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="timeZones" class="form-label">Timezone</label>
                                <select id="timeZones" class="select2 form-select">
                                    <option value="">Select Timezone</option>
                                    <option value="-12">(GMT-12:00) International Date Line West</option>
                                    <option value="-11">(GMT-11:00) Midway Island, Samoa</option>
                                    <option value="-10">(GMT-10:00) Hawaii</option>
                                    <option value="-9">(GMT-09:00) Alaska</option>
                                    <option value="-8">(GMT-08:00) Pacific Time (US & Canada)</option>
                                    <option value="-8">(GMT-08:00) Tijuana, Baja California</option>
                                    <option value="-7">(GMT-07:00) Arizona</option>
                                    <option value="-7">(GMT-07:00) Chihuahua, La Paz, Mazatlan</option>
                                    <option value="-7">(GMT-07:00) Mountain Time (US & Canada)</option>
                                    <option value="-6">(GMT-06:00) Central America</option>
                                    <option value="-6">(GMT-06:00) Central Time (US & Canada)</option>
                                    <option value="-6">(GMT-06:00) Guadalajara, Mexico City, Monterrey</option>
                                    <option value="-6">(GMT-06:00) Saskatchewan</option>
                                    <option value="-5">(GMT-05:00) Bogota, Lima, Quito, Rio Branco</option>
                                    <option value="-5">(GMT-05:00) Eastern Time (US & Canada)</option>
                                    <option value="-5">(GMT-05:00) Indiana (East)</option>
                                    <option value="-4">(GMT-04:00) Atlantic Time (Canada)</option>
                                    <option value="-4">(GMT-04:00) Caracas, La Paz</option>
                                </select>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="currency" class="form-label">Currency</label>
                                <select id="currency" class="select2 form-select">
                                    <option value="">Select Currency</option>
                                    <option value="usd">USD</option>
                                    <option value="euro">Euro</option>
                                    <option value="pound">Pound</option>
                                    <option value="bitcoin">Bitcoin</option>
                                </select>
                            </div> --}}
                        </div>
                        <div class="mt-2">
                            <button type="submit" class="btn btn-primary me-2">Save changes</button>
                            <a href="{{ route('dashboard-analytics') }}" class="btn btn-label-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
                <!-- /Account -->
            </div>
            <div class="card mb-4">
                <h5 class="card-header">Bank Accounts</h5>
                <div class="card-body">
                    @if ($bankAccounts->count())
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Account Type</th>
                                    <th>Account Number / UPI ID</th>
                                    <th>IFSC / UPI Number</th>
                                    <th>Default</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bankAccounts as $account)
                                    <tr>
                                        <td>{{ ucfirst($account->type) }}</td>
                                        <td>{{ $account->type === 'bank' ? $account->account_number : $account->upi_id }}
                                        </td>
                                        <td>{{ $account->type === 'bank' ? $account->ifsc_code : $account->upi_number }}
                                        </td>
                                        <td>
                                            @if ($account->is_default)
                                                <span class="badge bg-success">Yes</span>
                                            @else
                                                <form method="POST"
                                                    action="{{ route('bank-management.set-default', $account->id) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-primary">Set as
                                                        Default</button>
                                                </form>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('bank-management.edit', $account->id) }}"
                                                class="btn btn-sm btn-warning">Edit</a>
                                            <form method="POST"
                                                action="{{ route('bank-management.destroy', $account->id) }}"
                                                style="display:inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Are you sure?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p>No bank accounts added yet.</p>
                    @endif
                    <a href="{{ route('bank-management.create') }}" class="btn btn-primary mt-3">Add New Bank Account</a>
                </div>
            </div>

            <div class="card mb-4">
                <h5 class="card-header">Crypto Wallets</h5>
                <div class="card-body">
                    @if ($cryptoAccounts->count())
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Wallet Address</th>
                                    <th>Network</th>
                                    <th>Coin</th>
                                    <th>Status</th>
                                    <th>Default</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cryptoAccounts as $crypto)
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
                                                <span class="badge bg-success">Yes</span>
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
                                            <form method="POST"
                                                action="{{ route('crypto-management.destroy', $crypto->id) }}"
                                                style="display:inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Are you sure?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p>No crypto wallets added yet.</p>
                    @endif
                    <a href="{{ route('crypto-management.create') }}" class="btn btn-primary mt-3">Add New Crypto
                        Wallet</a>
                </div>
            </div>


            @if (Session::has('success'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    {{ Session::get('success') }}
                    <button type="button" class="btn-close"Auth()->user()-bs-dismiss="alert"
                        aria-label="Close"></button>
                </div>
            @endif

            @if (Session::has('error'))
                <div class="alert alert-danger alert-dismissible" role="alert">
                    {{ Session::get('error') }}
                    <button type="button" class="btn-close"Auth()->user()-bs-dismiss="alert"
                        aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <h5 class="card-header">Delete Account</h5>
                <div class="card-body">
                    <div class="mb-3 col-12 mb-0">
                        <div class="alert alert-warning">
                            <h5 class="alert-heading mb-1">Are you sure you want to delete your account?</h5>
                            <p class="mb-0">Once you delete your account, there is no going back. Please be certain.</p>
                        </div>
                    </div>
                    <form id="formAccountDeactivation" onsubmit="return false">
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="accountActivation"
                                id="accountActivation" />
                            <label class="form-check-label" for="accountActivation">I confirm my account
                                deactivation</label>
                        </div>
                        <button type="submit" class="btn btn-danger deactivate-account">Deactivate Account</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
