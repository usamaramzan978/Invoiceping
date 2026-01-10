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
                        <p class="h5 fw-semibold mb-2 text-center">Reset password</p>
                        <p class="mb-4 text-muted op-7 fw-normal text-center">Choose a new password to access your account.</p>

                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $request->route('token') }}">

                            <div class="row gy-3">
                                <div class="col-xl-12">
                                    <label for="reset-email" class="form-label text-default">Email</label>
                                    <input type="email" class="form-control form-control-lg" id="reset-email"
                                        name="email" value="{{ old('email', $request->email) }}" required autofocus>
                                    @error('email')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-xl-12">
                                    <label for="new-password" class="form-label text-default">New password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control form-control-lg" id="new-password"
                                            name="password" placeholder="Password" required>
                                        <button class="btn btn-light" type="button"
                                            onclick="createpassword('new-password',this)"><i
                                                class="ri-eye-off-line align-middle"></i></button>
                                    </div>
                                    @error('password')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-xl-12">
                                    <label for="confirm-password" class="form-label text-default">Confirm password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control form-control-lg" id="confirm-password"
                                            name="password_confirmation" placeholder="Confirm password" required>
                                        <button class="btn btn-light" type="button"
                                            onclick="createpassword('confirm-password',this)"><i
                                                class="ri-eye-off-line align-middle"></i></button>
                                    </div>
                                </div>
                                <div class="col-xl-12 d-grid mt-2">
                                    <button type="submit" class="btn btn-lg btn-primary">Update password</button>
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

