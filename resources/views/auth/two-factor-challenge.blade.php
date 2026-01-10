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
                        <p class="h5 fw-semibold mb-2 text-center">Two-factor challenge</p>
                        <p class="mb-4 text-muted op-7 fw-normal text-center">
                            Enter the code from your authenticator app or a recovery code to continue.
                        </p>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('two-factor.login') }}">
                            @csrf
                            <div class="row gy-3">
                                <div class="col-xl-12">
                                    <label for="code" class="form-label text-default">Authentication code</label>
                                    <input type="text" class="form-control form-control-lg" id="code" name="code"
                                        inputmode="numeric" autofocus>
                                </div>
                                <div class="col-xl-12">
                                    <label for="recovery_code" class="form-label text-default">Recovery code</label>
                                    <input type="text" class="form-control form-control-lg" id="recovery_code"
                                        name="recovery_code">
                                </div>
                                <div class="col-xl-12 d-grid mt-2">
                                    <button type="submit" class="btn btn-lg btn-primary">Authenticate</button>
                                </div>
                            </div>
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

