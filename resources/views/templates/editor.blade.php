@extends('layouts.app')

@section('content')
    <div id="email-builder-app"></div>
@endsection


@push('scripts')
    @vite('resources/js/main.jsx')
@endpush
