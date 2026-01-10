@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <h1 class="page-title fw-semibold fs-18 mb-0">Email Templates</h1>
            <div class="ms-md-1 ms-0">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Email Templates</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- Page Header Close -->

        <!-- Alert Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Templates Grid -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header justify-content-between">
                        <div class="card-title">
                            Manage Email Templates
                        </div>
                        <div class="d-flex">
                            <a href="{{ route('email.templates.create') }}"
                                class="btn btn-sm btn-primary btn-wave waves-light">
                                <i class="ri-add-line fw-semibold align-middle me-1"></i>
                                Create Template
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row" id="templates-container">
                            <!-- Templates will be loaded here via JavaScript -->
                            <div class="col-12 text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteTemplateModal" tabindex="-1" aria-labelledby="deleteTemplateModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteTemplateModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this template? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let deleteTemplateId = null;
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteTemplateModal'));

        // Load templates on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadTemplates();
        });

        function loadTemplates() {
            const container = document.getElementById('templates-container');

            fetch('/api/email-templates', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderTemplates(data.data);
                    } else {
                        showError('Failed to load templates');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('An error occurred while loading templates');
                });
        }

        function renderTemplates(templates) {
            const container = document.getElementById('templates-container');

            if (templates.length === 0) {
                container.innerHTML = `
                    <div class="col-12 text-center py-5">
                        <i class="ri-mail-line fs-1 text-muted"></i>
                        <h5 class="mt-3">No Templates Found</h5>
                        <p class="text-muted">Create your first email template to get started.</p>
                        <a href="{{ route('email.templates.create') }}" class="btn btn-primary">
                            <i class="ri-add-line me-1"></i> Create Template
                        </a>
                    </div>
                `;
                return;
            }

            container.innerHTML = templates.map(template => `
                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <div>
                                    <h5 class="fw-semibold mb-1">${escapeHtml(template.name)}</h5>
                                    ${template.category ? `<span class="badge bg-primary-transparent">${escapeHtml(template.category)}</span>` : ''}
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-icon btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                        <i class="ti ti-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <button class="dropdown-item" onclick="toggleActive('${template.id}')">
                                                <i class="ri-toggle-line me-2"></i>
                                                ${template.is_active ? 'Deactivate' : 'Activate'}
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item" onclick="setAsDefault('${template.id}')">
                                                <i class="ri-star-line me-2"></i>
                                                Set as Default
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <button class="dropdown-item text-danger" onclick="confirmDelete('${template.id}')">
                                                <i class="ri-delete-bin-line me-2"></i>
                                                Delete
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            ${template.subject ? `
                                    <p class="text-muted mb-2">
                                        <strong>Subject:</strong> ${escapeHtml(template.subject)}
                                    </p>
                                ` : ''}

                            <div class="d-flex align-items-center justify-content-between mt-3">
                                <div>
                                    ${template.is_default ? '<span class="badge bg-success"><i class="ri-star-fill me-1"></i>Default</span>' : ''}
                                    ${template.is_active ?
                                        '<span class="badge bg-success-transparent">Active</span>' :
                                        '<span class="badge bg-secondary">Inactive</span>'}
                                </div>
                                <div class="text-muted fs-11">
                                    ${new Date(template.created_at).toLocaleDateString()}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function toggleActive(templateId) {
            fetch(`/api/email-templates/${templateId}/toggle-active`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccess(data.message);
                        loadTemplates();
                    } else {
                        showError(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('An error occurred');
                });
        }

        function setAsDefault(templateId) {
            fetch(`/api/email-templates/${templateId}/set-default`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccess(data.message);
                        loadTemplates();
                    } else {
                        showError(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('An error occurred');
                });
        }

        function confirmDelete(templateId) {
            deleteTemplateId = templateId;
            deleteModal.show();
        }

        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            if (!deleteTemplateId) return;

            fetch(`/api/email-templates/${deleteTemplateId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    deleteModal.hide();
                    if (data.success) {
                        showSuccess(data.message);
                        loadTemplates();
                    } else {
                        showError(data.message);
                    }
                })
                .catch(error => {
                    deleteModal.hide();
                    console.error('Error:', error);
                    showError('An error occurred');
                });
        });

        function showSuccess(message) {
            // You can use your toast notification system here
            alert(message);
        }

        function showError(message) {
            // You can use your toast notification system here
            alert(message);
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }
    </script>
@endsection
