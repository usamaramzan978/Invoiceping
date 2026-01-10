@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center my-4">
            <div>
                <h5 class="fw-semibold mb-1">Clients</h5>
                <p class="text-muted mb-0">Manage clients.</p>
            </div>
            <a href="{{ route('clients.create') }}" class="btn btn-primary">
                <i class="ri-add-line me-1"></i>Add Client
            </a>
        </div>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">Clients</div>
            </div>
            <div class="card-body">
                <form method="GET" class="mb-3">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="ri-search-line"></i>
                                </span>
                                <input type="text" name="search" class="form-control" placeholder="Search clients..."
                                    value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="hstack gap-2">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="ri-search-line me-1"></i>Search
                                </button>
                                <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">
                                    <i class="ri-refresh-line me-1"></i>Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-hover text-nowrap align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>WhatsApp Number</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($clients as $client)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="avatar avatar-sm avatar-rounded bg-primary-transparent me-2">
                                                <i class="ri-user-line"></i>
                                            </span>
                                            <span class="fw-semibold">{{ $client->name }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $client->whatsapp_number }}</td>
                                    <td>
                                        @if ($client->email)
                                            <div class="d-flex align-items-center">
                                                <i class="ri-mail-line text-muted me-1"></i>
                                                <span>{{ $client->email }}</span>
                                            </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($client->status)
                                            <span class="badge bg-{{ $client->status == 'active' ? 'success' : 'secondary' }}-transparent">
                                                {{ ucfirst($client->status) }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="hstack gap-2 justify-content-end">
                                            {{-- View --}}
                                            <a href="{{ route('clients.show', $client) }}"
                                                class="btn btn-icon btn-sm btn-info-light" data-bs-toggle="tooltip"
                                                data-bs-placement="top" title="View">
                                                <i class="ri-eye-line"></i>
                                            </a>

                                            {{-- Edit --}}
                                            <a href="{{ route('clients.edit', $client) }}"
                                                class="btn btn-icon waves-effect waves-light btn-sm btn-primary-light"
                                                data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                <i class="ri-edit-line"></i>
                                            </a>

                                            {{-- Delete --}}
                                            <form action="{{ route('clients.destroy', $client) }}" method="POST"
                                                onsubmit="return confirm('Delete this client?');" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-icon btn-sm btn-danger-transparent"
                                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="ri-inbox-line fs-24 d-block mb-2"></i>
                                        No clients found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $clients->links() }}</div>
            </div>
        </div>
    </div>
@endsection

