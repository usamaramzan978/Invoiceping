@extends('layouts.app')

@section('content')
    <div id="email-builder-app"></div>
    
    <script>
        // Make available variables accessible to React app
        window.EMAIL_TEMPLATE_VARIABLES = @json(app(\App\Services\TemplateVariableService::class)->getAvailableVariables());
    </script>
@endsection


@push('scripts')
    @vite('resources/js/main.jsx')
@endpush
