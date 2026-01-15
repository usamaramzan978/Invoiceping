<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light"
    data-menu-styles="dark" data-toggled="close">

<head>

    {{-- META DATA --}}
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">

    {{-- TITLE --}}
    <title>
        @hasSection('title')
            @yield('title')
        @else
            {{ config('app.name') }} - One Ping Away From Payment
        @endif
    </title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="title" content="InvoicePing - Bill Smarter, Get Paid Faster">
    <meta name="description"
        content="InvoicePing helps businesses create invoices, send smart payment reminders via WhatsApp and email, and get paid faster. One ping away from payment.">
    <meta name="keywords"
        content="InvoicePing, invoicing software, invoice management, online invoicing, WhatsApp invoice reminders, billing software, SaaS invoicing, payment reminders, business invoicing">
    <meta name="author" content="InvoicePing">

    {{-- OPEN GRAPH / FACEBOOK --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://invoiceping.com">
    <meta property="og:title" content="InvoicePing - One Ping Away From Payment">
    <meta property="og:description"
        content="Create invoices, send WhatsApp & email reminders, and get paid faster with InvoicePing.">
    <meta property="og:image" content="https://invoiceping.com/assets/og-image.png">
    <meta property="og:site_name" content="InvoicePing">

    {{-- TWITTER / X --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="InvoicePing - Bill Smarter, Get Paid Faster">
    <meta name="twitter:description" content="Smart invoicing with WhatsApp & email reminders. Stop chasing payments.">
    <meta name="twitter:image" content="https://invoiceping.com/assets/og-image.png">

    {{-- APP / PWA (OPTIONAL BUT RECOMMENDED) --}}
    <meta name="theme-color" content="#4f46e5">
    <meta name="apple-mobile-web-app-title" content="InvoicePing">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">

    {{-- SECURITY --}}
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">

    {{-- PERFORMANCE --}}
    <meta http-equiv="Cache-Control" content="public, max-age=31536000">

    {{-- FAVICON --}}
    <link rel="icon" type="image/png" href="{{ asset('favicon/favicon-96x96.png') }}" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon/favicon.svg') }}" />
    <link rel="shortcut icon" href="{{ asset('favicon/favicon.ico') }}" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon/apple-touch-icon.png') }}" />
    <meta name="apple-mobile-web-app-title" content="MyWebSite" />
    <link rel="manifest" href="{{ asset('favicon/site.webmanifest') }}" />


    <script src="https://code.jquery.com/jquery-3.7.1.slim.min.js"
        integrity="sha256-kmHvs0B+OpCW5GVHUNjv9rOmY0IvSIRcf7zGUDTDQM8=" crossorigin="anonymous"></script>
    <!-- BOOTSTRAP CSS -->
    <link id="style" href="{{ asset('build/assets/libs/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- ICONS CSS -->
    <link href="{{ asset('build/assets/icon-fonts/icons.css') }}" rel="stylesheet">

    <!-- APP SCSS -->
    @vite(['resources/sass/app.scss'])

    @include('layouts.components.styles')

    <!-- MAIN JS -->
    <script src="{{ asset('build/assets/main.js') }}"></script>

    @yield('styles')
    @viteReactRefresh
    @vite('resources/js/main.jsx')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

</head>

<body data-success="{{ session('success') }}" data-error="{{ session('error') }}">

    <!-- LOADER -->
    <div id="loader">
        <img src="{{ asset('build/assets/images/media/loader.svg') }}" alt="">
    </div>
    <!-- END LOADER -->

    <!-- PAGE -->
    <div class="page">

        <!-- HEADER -->

        @include('layouts.components.header')

        <!-- END HEADER -->

        <!-- SIDEBAR -->

        @include('layouts.components.sidebar')

        <!-- END SIDEBAR -->

        <!-- MAIN-CONTENT -->

        <div class="main-content app-content">

            @yield('content')

        </div>
        <!-- END MAIN-CONTENT -->

        <!-- SEARCH-MODAL -->

        @include('layouts.components.search-modal')

        <!-- END SEARCH-MODAL -->

        <!-- FOOTER -->

        @include('layouts.components.footer')

        <!-- END FOOTER -->

        <!-- GLOBAL DELETE MODAL -->
        @include('components.delete-modal')

    </div>
    <!-- END PAGE-->

    <!-- SCRIPTS -->

    @include('layouts.components.scripts')



    <!-- STICKY JS -->
    <script src="{{ asset('build/assets/sticky.js') }}"></script>

    <!-- APP JS -->
    @vite('resources/js/app.js')
    @yield('scripts')

    <!-- Initialize Bootstrap Tooltips -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize all tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>

    <!-- CUSTOM-SWITCHER JS -->
    @vite('resources/assets/js/custom-switcher.js')
    <!-- SELECT2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @vite('resources/assets/js/select2.js')

    <!-- END SCRIPTS -->

</body>

</html>
