@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <h1 class="page-title fw-semibold fs-18 mb-0">Message Templates</h1>
            <div class="ms-md-1 ms-0">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Message Templates</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- Page Header Close -->

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <span class="avatar avatar-md avatar-rounded bg-primary-transparent">
                                    <i class="ri-file-list-line fs-18"></i>
                                </span>
                            </div>
                            <div>
                                <p class="fw-semibold mb-0 text-muted fs-12">Total Templates</p>
                                <h5 class="fw-semibold mb-0">{{ $templates->count() }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <span class="avatar avatar-md avatar-rounded bg-success-transparent">
                                    <i class="ri-whatsapp-line fs-18"></i>
                                </span>
                            </div>
                            <div>
                                <p class="fw-semibold mb-0 text-muted fs-12">WhatsApp Templates</p>
                                <h5 class="fw-semibold mb-0">{{ $templates->where('channel', 'whatsapp')->count() }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <span class="avatar avatar-md avatar-rounded bg-info-transparent">
                                    <i class="ri-message-2-line fs-18"></i>
                                </span>
                            </div>
                            <div>
                                <p class="fw-semibold mb-0 text-muted fs-12">SMS Templates</p>
                                <h5 class="fw-semibold mb-0">{{ $templates->where('channel', 'sms')->count() }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <span class="avatar avatar-md avatar-rounded bg-warning-transparent">
                                    <i class="ri-star-line fs-18"></i>
                                </span>
                            </div>
                            <div>
                                <p class="fw-semibold mb-0 text-muted fs-12">Default Templates</p>
                                <h5 class="fw-semibold mb-0">{{ $templates->where('is_default', true)->count() }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Templates Table -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header justify-content-between">
                        <div class="card-title">
                            All Message Templates
                        </div>
                        <div class="d-flex gap-2">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-primary active"
                                    data-filter="all">All</button>
                                <button type="button" class="btn btn-sm btn-outline-success"
                                    data-filter="whatsapp">WhatsApp</button>
                                <button type="button" class="btn btn-sm btn-outline-info" data-filter="sms">SMS</button>
                            </div>
                            <a href="{{ route('templates.create') }}" class="btn btn-sm btn-primary btn-wave">
                                <i class="ri-add-line fw-semibold align-middle me-1"></i> New Template
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if ($templates->isEmpty())
                            <div class="text-center py-5">
                                <img src="{{ asset('build/assets/images/svgs/empty-box.svg') }}" alt="No templates"
                                    class="mb-3" style="max-width: 200px;">
                                <h5 class="fw-semibold mb-2">No Templates Yet</h5>
                                <p class="text-muted mb-3">Create your first WhatsApp or SMS template to get started</p>
                                <a href="{{ route('templates.create') }}" class="btn btn-primary">
                                    <i class="ri-add-line me-1"></i> Create Template
                                </a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table text-nowrap table-hover" id="templates-table">
                                    <thead>
                                        <tr>
                                            <th scope="col" width="35%">Template Name</th>
                                            <th scope="col" width="15%">Channel</th>
                                            <th scope="col" width="30%">Content Preview</th>
                                            <th scope="col" width="10%" class="text-center">Status</th>
                                            <th scope="col" width="10%" class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($templates as $template)
                                            <tr data-channel="{{ $template->channel }}">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div>
                                                            <p class="mb-0 fw-semibold">{{ $template->name }}</p>
                                                            @if ($template->is_default)
                                                                <span class="badge bg-primary-transparent fs-10 mt-1">
                                                                    <i class="ri-star-fill me-1"></i>Default
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @switch($template->channel)
                                                        @case('whatsapp')
                                                            <span class="badge bg-success-transparent">
                                                                <i class="ri-whatsapp-line me-1"></i>WhatsApp
                                                            </span>
                                                        @break

                                                        @case('sms')
                                                            <span class="badge bg-info-transparent">
                                                                <i class="ri-message-2-line me-1"></i>SMS
                                                            </span>
                                                        @break

                                                        @default
                                                            <span class="badge bg-secondary-transparent">Other</span>
                                                    @endswitch
                                                </td>
                                                <td>
                                                    <div class="text-muted"
                                                        style="font-size: 13px; max-height: 40px; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                                        {{ Str::limit($template->content, 100) }}
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    @if ($template->is_active)
                                                        <span class="badge bg-success">Active</span>
                                                    @else
                                                        <span class="badge bg-secondary">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-list text-center">
                                                        <a href="{{ route('templates.edit', $template) }}"
                                                            class="btn btn-sm btn-icon btn-success-light"
                                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Template">
                                                            <i class="ri-pencil-line"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-icon btn-info-light"
                                                            data-bs-toggle="modal" data-bs-target="#previewModal"
                                                            onclick="showPreview('{{ $template->channel }}', '{{ $template->name }}', `{{ addslashes($template->content) }}`)"
                                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Preview Template">
                                                            <i class="ri-eye-line"></i>
                                                        </button>
                                                        <button type="button"
                                                            class="btn btn-sm btn-icon btn-danger-light"
                                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Delete Template" data-delete-modal
                                                            data-title="Delete Template"
                                                            data-message="Are you sure you want to delete '{{ $template->name }}'? This action cannot be undone."
                                                            data-form-id="{{ route('templates.destroy', $template->id) }}"
                                                            data-record-name="template">
                                                            <i class="ri-delete-bin-line"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="previewModalLabel">Template Preview</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="preview-channel-badge" class="mb-3"></div>
                    <h6 id="preview-template-name" class="fw-semibold mb-2"></h6>
                    <div id="preview-content" class="p-3 bg-light rounded"
                        style="white-space: pre-wrap; word-wrap: break-word;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        // Filter functionality
        document.querySelectorAll('[data-filter]').forEach(button => {
            button.addEventListener('click', function() {
                const filter = this.dataset.filter;

                // Update active button
                document.querySelectorAll('[data-filter]').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                // Filter rows
                document.querySelectorAll('#templates-table tbody tr').forEach(row => {
                    if (filter === 'all' || row.dataset.channel === filter) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });

        // Preview modal
        function showPreview(channel, name, content) {
            let channelBadge = '';
            switch (channel) {
                case 'whatsapp':
                    channelBadge =
                        '<span class="badge bg-success-transparent"><i class="ri-whatsapp-line me-1"></i>WhatsApp</span>';
                    break;
                case 'sms':
                    channelBadge =
                        '<span class="badge bg-info-transparent"><i class="ri-message-2-line me-1"></i>SMS</span>';
                    break;
            }

            document.getElementById('preview-channel-badge').innerHTML = channelBadge;
            document.getElementById('preview-template-name').textContent = name;
            document.getElementById('preview-content').textContent = content;
        }
    </script>
@endsection
