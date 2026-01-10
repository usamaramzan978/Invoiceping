@extends('layouts.auth')

@section('styles')
@endsection

@section('content')
@section('error-body')

    <body>
    @endsection

    <div class="container-lg">
        <div class="row justify-content-center align-items-center authentication authentication-basic h-100">
            <div class="col-xxl-4 col-xl-5 col-lg-5 col-md-6 col-sm-8 col-12">
                <div class="my-5 d-flex justify-content-center">
                    <a href="{{ route('home') }}">
                        <x-logo />
                    </a>
                </div>
                <div class="card custom-card">
                    <div class="card-body p-5">
                        <p class="h5 fw-semibold mb-2 text-center">Register</p>
                        <p class="mb-4 text-muted op-7 fw-normal text-center">Welcome & Join us by creating a free
                            account !</p>
                        <form action="{{ route('register') }}" method="POST">
                            @csrf
                            <div class="row gy-3">
                                <div class="col-xl-12">
                                    <label for="signup-name" class="form-label text-default">Name</label>
                                    <input type="text"
                                        class="form-control form-control-lg @error('name') is-invalid @enderror"
                                        id="signup-name" placeholder="name" name="name">
                                    @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-xl-12">
                                    <label for="signup-email" class="form-label text-default">Email</label>
                                    <input type="email"
                                        class="form-control form-control-lg @error('email') is-invalid @enderror"
                                        id="signup-email" placeholder="email" name="email">
                                    @error('email')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-xl-12">
                                    <label for="signup-password" class="form-label text-default">Password</label>
                                    <div class="input-group">
                                        <input type="password"
                                            class="form-control form-control-lg @error('password') is-invalid @enderror"
                                            id="signup-password" placeholder="password" name="password">
                                        <button class="btn btn-light" onclick="createpassword('signup-password',this)"
                                            type="button" id="button-addon2"><i
                                                class="ri-eye-off-line align-middle"></i></button>
                                    </div>
                                    @error('password')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-xl-12 mb-2">
                                    <label for="signup-confirmpassword" class="form-label text-default">Confirm
                                        Password</label>
                                    <div class="input-group">
                                        <input type="password"
                                            class="form-control form-control-lg @error('password_confirmation') is-invalid @enderror"
                                            id="signup-confirmpassword" placeholder="confirm password"
                                            name="password_confirmation">
                                        <button class="btn btn-light"
                                            onclick="createpassword('signup-confirmpassword',this)" type="button"
                                            id="button-addon21"><i class="ri-eye-off-line align-middle"></i></button>
                                    </div>
                                    @error('password_confirmation')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-xl-12">
                                    <div class="form-check mt-3">
                                        <input class="form-check-input" type="checkbox" value=""
                                            id="defaultCheck1">
                                        <label class="form-check-label text-muted fw-normal" for="defaultCheck1">
                                            By creating a account you agree to our <a
                                                href="{{ url('terms-conditions') }}" class="text-success"><u>Terms &
                                                    Conditions</u></a> and <a class="text-success"><u>Privacy
                                                    Policy</u></a>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-xl-12 d-grid mt-2">
                                    <button type="submit" class="btn btn-lg btn-primary">Create Account</button>
                                </div>
                            </div>
                        </form>
                        <div class="text-center">
                            <p class="fs-12 text-muted mt-3">Already have an account? <a href="{{ route('login') }}"
                                    class="text-primary">Sign In</a></p>
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
