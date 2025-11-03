@php
    $customizerHidden = 'customizer-hide';
    $configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')
@section('content')
    <div class="container">
        <h2>Two-Factor Authentication Setup</h2>

        <p>Scan the following secret into your Google Authenticator (or similar) app, or enter it manually.</p>

        <div class="card">
            <div class="card-body">


                @if (!empty($qrImage))
                    <p><strong>Scan this QR code with your authenticator app:</strong></p>
                    <div class="mb-3">
                        @php
                            // The QR generator may return either a data URI (PNG) or raw SVG markup.
                            $isDataUri = \Illuminate\Support\Str::startsWith($qrImage, 'data:');
                            $isSvg = \Illuminate\Support\Str::startsWith(trim($qrImage), '<svg');
                        @endphp

                        @if ($isDataUri) <img src="{{ $qrImage }}" alt="2FA QR Code" />
                        @elseif($isSvg)
                            {{-- render raw SVG markup inline --}}
                            {!! $qrImage !!}
                        @else
                            {{-- Fallback: output as image src raw --}}
                            {!! $qrImage !!}

                            {{-- <img src={!! $qrImage !!} alt="2FA QR Code" /> --}} @endif
                    </div>
                    <p>If your app can't scan the QR, use the following secret:</p>
                    <p><strong>Secret:</strong> <code>{{ $secret }}</code></p>
                @else
                    <p><strong>Secret:</strong> <code>{{ $secret }}</code></p>
                    <p>If your authenticator app supports scanning QR codes, you can also use the secret above to create the
                        account.</p>
                @endif

                <form method="POST" action="{{ route('2fa.enable') }}">
                    @csrf
                    {{-- include secret as a hidden fallback in case session is lost between requests --}}
                    <input type="hidden" name="secret" value="{{ $secret }}">
                    <div class="form-group">
                        <label for="code">Enter the 6-digit code from your app to confirm setup</label>
                        <input id="code" name="code" class="form-control" required>
                    </div>
                    <button class="btn btn-primary mt-2">Enable Two-Factor Authentication</button>
                </form>
            </div>
        </div>
    </div>
@endsection
