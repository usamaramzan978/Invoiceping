@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <h1 class="page-title fw-semibold fs-18 mb-0">Reminder Rules</h1>
            <div class="ms-md-1 ms-0">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Automation</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Reminder Rules</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- Page Header Close -->

        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header justify-content-between">
                        <div class="card-title">
                            Manage Reminder Rules
                        </div>
                        <div class="d-flex">
                            <a href="{{ route('reminder-rules.create') }}" class="btn btn-sm btn-primary btn-wave waves-light">
                                <i class="ri-add-line fw-semibold align-middle me-1"></i>
                                Create Rule
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-nowrap table-bordered">
                                <thead>
                                    <tr>
                                        <th scope="col">Rule Name</th>
                                        <th scope="col">Steps</th>
                                        <th scope="col">Default</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($rules as $rule)
                                        <tr>
                                            <td>
                                                <p class="mb-0 fw-semibold">{{ $rule->name }}</p>
                                                <small class="text-muted">Created {{ $rule->created_at->format('d M, Y') }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info-transparent">
                                                    {{ $rule->steps->count() }} Steps
                                                </span>
                                            </td>
                                            <td>
                                                @if ($rule->is_default)
                                                    <span class="badge bg-success">Default</span>
                                                @else
                                                    <span class="badge bg-light text-muted">No</span>
                                                @endif
                                            </td>
                                            <td>
                                                <form action="{{ route('reminder-rules.toggle-status', $rule) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-{{ $rule->is_active ? 'success' : 'danger' }}-transparent">
                                                        {{ $rule->is_active ? 'Active' : 'Inactive' }}
                                                    </button>
                                                </form>
                                            </td>
                                            <td>
                                                <div class="btn-list">
                                                    <a href="{{ route('reminder-rules.edit', $rule) }}"
                                                        class="btn btn-success-light btn-icon btn-sm">
                                                        <i class="ri-pencil-line"></i>
                                                    </a>
                                                    <form action="{{ route('reminder-rules.destroy', $rule) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger-light btn-icon btn-sm" onclick="return confirm('Are you sure?')">
                                                            <i class="ri-delete-bin-5-line"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No reminder rules found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
