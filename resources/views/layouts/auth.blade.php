<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light"
    data-menu-styles="light" data-toggled="close">

<head>

    <!-- META DATA eta Data -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=no'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- TITLE -->
    <title> {{ config('app.name') }} </title>

    <!-- FAVICON -->
    <link rel="icon" href="{{ asset('build/assets/images/brand-logos/favicon.ico') }}" type="image/x-icon">

    <!-- BOOTSTRAP CSS -->
    <link id="style" href="{{ asset('build/assets/libs/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- ICONS CSS -->
    <link href="{{ asset('build/assets/icon-fonts/icons.css') }}" rel="stylesheet">

    <!-- APP SCSS -->
    @vite(['resources/sass/app.scss'])


    <!-- MAIN JS -->
    <script src="{{ asset('build/assets/authentication-main.js') }}"></script>

    @yield('styles')

</head>

@yield('error-body')

@yield('content')


<!-- SCRIPTS -->

<!-- BOOTSTRAP JS -->
<script src="{{ asset('build/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

@yield('scripts')


<!-- END SCRIPTS -->

</body>

</html>
