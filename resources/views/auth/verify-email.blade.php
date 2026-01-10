@extends('layouts.auth')

@section('styles')
@endsection

@section('content')
@section('error-body')
    <body>
@endsection

    <div class="container">
        <div class="row justify-content-center align-items-center authentication authentication-basic h-100">
            <div class="col-xxl-4 col-xl-5 col-lg-5 col-md-6 col-sm-8 col-12">
                <div class="my-5 d-flex justify-content-center">
                    <a href="{{ route('home') }}">
                        <img src="{{ asset('build/assets/images/brand-logos/desktop-logo.png') }}" alt="logo"
                            class="desktop-logo">
                        <img src="{{ asset('build/assets/images/brand-logos/desktop-dark.png') }}" alt="logo"
                            class="desktop-dark">
                    </a>
                </div>
                <div class="card custom-card">
                    <div class="card-body p-5">
                        <p class="h5 fw-semibold mb-2 text-center">Verify your email</p>
                        <p class="mb-4 text-muted op-7 fw-normal text-center">
                            We emailed you a verification link. If you didn’t receive it, you can request another.
                        </p>

                        @if (session('status') == 'verification-link-sent')
                            <div class="alert alert-success" role="alert">
                                A new verification link has been sent to your email address.
                            </div>
                        @endif

                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf
                            <div class="d-grid">
                                <button type="submit" class="btn btn-lg btn-primary">Resend verification email</button>
                            </div>
                        </form>

                        <form method="POST" action="{{ route('logout') }}" class="mt-3 d-grid">
                            @csrf
                            <button type="submit" class="btn btn-lg btn-outline-secondary">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- SHOW PASSWORD JS -->
    <script src="{{ asset('build/assets/show-password.js') }}"></script>
@endsection

