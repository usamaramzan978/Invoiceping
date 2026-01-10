@extends('layouts.app')

@section('styles')
@endsection

@section('content')
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <h1 class="page-title fw-semibold fs-18 mb-0">Template List</h1>
            <div class="ms-md-1 ms-0">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Template</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Template List</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- Page Header Close -->

        <!-- Start::row-1 -->
        <div class="row">
            <div class="col-xl-12 col-lg-12 col-md-12 col-12">
                <div class="card custom-card">
                    <div class="card-header justify-content-between">
                        <div class="card-title">
                            Manage Templates
                        </div>
                        <div class="d-flex">
                            <a href="{{ route('templates.create') }}" class="btn btn-sm btn-primary btn-wave waves-light">
                                <i class="ri-add-line fw-semibold align-middle me-1"></i>
                                Create Template</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-nowrap table-bordered">
                                <thead>
                                    <tr>
                                        <th scope="col">Name</th>
                                        <th scope="col">Channel</th>
                                        <th scope="col">Content</th>
                                        <th scope="col">Default</th>
                                        <th scope="col">Active</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($templates as $template)
                                        <tr>
                                            <td>
                                                <p class="mb-0 fw-semibold">{{ $template->name }}</p>
                                            </td>
                                            <td>
                                                @switch($template->channel)
                                                    @case('email')
                                                        <span class="badge bg-primary">Email</span>
                                                    @break

                                                    @case('whatsapp')
                                                        <span class="badge bg-success">whatsapp</span>
                                                    @break

                                                    @default
                                                        <span class="badge bg-secondary">Other</span>
                                                @endswitch
                                            </td>
                                            <td style="white-space: pre-wrap; word-break: break-word;">
                                                {{ $template->content }}
                                            </td>

                                            <td>
                                                @if ($template->is_default)
                                                    <span class="badge bg-primary">Default</span>
                                                @else
                                                    <span class="badge bg-secondary">No</span>
                                                @endif
                                            </td>
                                            <td>
                                                {!! $template->is_active
                                                    ? '<span class="badge bg-success-transparent">Yes</span>'
                                                    : '<span class="badge bg-danger-transparent">No</span>' !!}
                                            </td>
                                            <td>
                                                <a href="{{ route('templates.show', $template) }}"
                                                    class="btn btn-info-light btn-icon btn-sm ms-1 template-btn">
                                                    <i class="ri-eye-line"></i>
                                                </a>
                                                <a href="{{ route('templates.edit', $template) }}"
                                                    class="btn btn-success-light btn-icon btn-sm ms-1 template-btn">
                                                    <i class="ri-pencil-line"></i>
                                                </a>
                                                <form action="{{ route('templates.destroy', $template->id) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="btn btn-danger-light btn-icon btn-sm ms-1 template-btn">
                                                        <i class="ri-delete-bin-5-line"></i>
                                                    </button>
                                                </form>

                                            </td>
                                        </tr>
                                        @empty
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <nav aria-label="Page navigation">
                                <ul class="pagination mb-0 float-end">
                                    <li class="page-item disabled">
                                        <a class="page-link">Previous</a>
                                    </li>
                                    <li class="page-item"><a class="page-link" href="javascript:void(0);">1</a></li>
                                    <li class="page-item active" aria-current="page">
                                        <a class="page-link" href="javascript:void(0);">2</a>
                                    </li>
                                    <li class="page-item"><a class="page-link" href="javascript:void(0);">3</a></li>
                                    <li class="page-item">
                                        <a class="page-link" href="javascript:void(0);">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection
