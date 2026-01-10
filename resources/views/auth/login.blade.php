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
                        <x-logo />
                    </a>
                </div>
                <div class="card custom-card">
                    <div class="card-body p-5">
                        <p class="h5 fw-semibold mb-2 text-center">LOGIN</p>
                        <form action="{{ route('login') }}" method="POST">
                            @csrf
                            <div class="row gy-3">
                                <div class="col-xl-12">
                                    <label for="login-email" class="form-label text-default">Email</label>
                                    <input type="text"
                                        class="form-control form-control-lg @error('email') is-invalid @enderror"
                                        id="login-email" placeholder="Email" name="email">
                                    @error('email')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-xl-12 mb-2">
                                    <label for="signin-password" class="form-label text-default d-block">Password
                                        <a href="{{ route('password.request') }}" class="float-end text-danger">Forget
                                            password ?</a>
                                    </label>
                                    <div class="input-group">
                                        <input type="password"
                                            class="form-control form-control-lg @error('password') is-invalid @enderror"
                                            id="signin-password" placeholder="*****" name="password">
                                        <button class="btn btn-light" type="button"
                                            onclick="createpassword('signin-password',this)" id="button-addon2"><i
                                                class="ri-eye-off-line align-middle"></i></button>
                                    </div>
                                    @error('password')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                    <div class="mt-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="1"
                                                id="defaultCheck1" name="remember">
                                            <label class="form-check-label text-muted fw-normal" for="defaultCheck1">
                                                Remember password ?
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-12 d-grid mt-2">
                                    <button type="submit" class="btn btn-lg btn-primary">Login</button>
                                </div>
                        </form>
                    </div>
                    <div class="text-center">
                        <p class="fs-12 text-muted mt-3">Dont have an account?
                            <a href="{{ url('register') }}" class="text-primary">Register</a>
                        </p>
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
