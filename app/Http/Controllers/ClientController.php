<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Client\CreateClientAction;
use App\Actions\Client\DeleteClientAction;
use App\Actions\Client\UpdateClientAction;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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
        $clients = Client::query()->latest()->paginate(15);

        return view('dashboard.clients.index', ['clients' => $clients]);
    }

    public function create(): View|RedirectResponse
    {
        return view('dashboard.clients.create');
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['business_id'] = auth()->user()->business->id ?? null;
        $this->createClient->handle($data);

        return to_route('clients.index')->with('success', 'Client created successfully.');
    }

    public function show(Client $client): View
    {
        return view('dashboard.clients.show', ['client' => $client]);
    }

    public function edit(Client $client): View
    {
        return view('dashboard.clients.edit', ['client' => $client]);
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $data = $request->validated();
        $this->updateClient->handle($client, $data);

        return to_route('clients.index')->with('success', 'Client updated successfully.');
    }

    public function destroy(Request $request, Client $client): RedirectResponse|JsonResponse
    {
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
