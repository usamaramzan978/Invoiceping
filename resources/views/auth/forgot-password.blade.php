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
                        <p class="h5 fw-semibold mb-2 text-center">Forgot password?</p>
                        <p class="mb-4 text-muted op-7 fw-normal text-center">Enter your email to receive the reset
                            link.</p>

                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.email') }}">
                            @csrf
                            <div class="row gy-3">
                                <div class="col-xl-12">
                                    <label for="reset-email" class="form-label text-default">Email</label>
                                    <input type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" id="reset-email"
                                        placeholder="Email" name="email" value="{{ old('email') }}" required
                                        autofocus>
                                    @error('email')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-xl-12 d-grid mt-2">
                                    <button type="submit" class="btn btn-lg btn-primary">Send reset link</button>
                                </div>
                            </div>
                        </form>
                        <div class="text-center mt-3">
                            <a href="{{ route('login') }}" class="text-primary">Back to login</a>
                        </div>
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
