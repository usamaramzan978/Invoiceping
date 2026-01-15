<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BusinessProfile;
use App\Actions\Client\CreateClientAction;
use App\Actions\Client\DeleteClientAction;
use App\Actions\Client\UpdateClientAction;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

final class ClientController extends Controller
{
    public function __construct(
        private readonly CreateClientAction $createClient,
        private readonly UpdateClientAction $updateClient,
        private readonly DeleteClientAction $deleteClient,
    ) {}

    public function index(): View
    {
        Gate::authorize('viewAny', Client::class);

        /** @var BusinessProfile|null $business */
        $business = auth()->user()->business;
        $businessId = $business?->id;

        $clients = Client::query()
            ->where('business_id', $businessId)
            ->latest()
            ->paginate(15);

        return view('dashboard.clients.index', ['clients' => $clients]);
    }

    public function create(): View|RedirectResponse
    {
        Gate::authorize('create', Client::class);

        return view('dashboard.clients.create');
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        Gate::authorize('create', Client::class);

        try {
            $data = $request->validated();
            $data['business_id'] = auth()->user()->business->id ?? null;
            $this->createClient->handle($data);

            return to_route('clients.index')->with('success', 'Client created successfully.');
        } catch (Exception $exception) {
            return back()->withInput()->with('error', 'Failed to create client: '.$exception->getMessage());
        }
    }

    public function show(Client $client): View
    {
        Gate::authorize('view', $client);

        return view('dashboard.clients.show', ['client' => $client]);
    }

    public function edit(Client $client): View
    {
        Gate::authorize('update', $client);

        return view('dashboard.clients.edit', ['client' => $client]);
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        Gate::authorize('update', $client);

        try {
            $data = $request->validated();
            $this->updateClient->handle($client, $data);

            return to_route('clients.index')->with('success', 'Client updated successfully.');
        } catch (Exception $exception) {
            return back()->withInput()->with('error', 'Failed to update client: '.$exception->getMessage());
        }
    }

    public function destroy(Request $request, Client $client): RedirectResponse|JsonResponse
    {
        Gate::authorize('delete', $client);

        $this->deleteClient->handle($client);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Client deleted successfully!',
                'redirect' => route('clients.index'),
            ]);
        }

        return to_route('clients.index')->with('success', 'Client deleted successfully.');
    }
}
