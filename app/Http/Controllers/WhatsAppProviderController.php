<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\WhatsAppProvider\CreateWhatsAppProviderAction;
use App\Actions\WhatsAppProvider\DeleteWhatsAppProviderAction;
use App\Actions\WhatsAppProvider\SetDefaultWhatsAppProviderAction;
use App\Actions\WhatsAppProvider\ToggleStatusWhatsAppProviderAction;
use App\Actions\WhatsAppProvider\UpdateWhatsAppProviderAction;
use App\Http\Requests\StoreWhatsAppProviderRequest;
use App\Http\Requests\UpdateWhatsAppProviderRequest;
use App\Models\WhatsAppProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

final class WhatsAppProviderController extends Controller
{

    public function __construct(
        private readonly CreateWhatsAppProviderAction $createWhatsAppProviderAction,
        private readonly DeleteWhatsAppProviderAction $deleteWhatsAppProviderAction,
        private readonly SetDefaultWhatsAppProviderAction $setDefaultWhatsAppProviderAction,
        private readonly ToggleStatusWhatsAppProviderAction $toggleStatusWhatsAppProviderAction,
        private readonly UpdateWhatsAppProviderAction $updateWhatsAppProviderAction,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', WhatsAppProvider::class);

        $providers = WhatsAppProvider::query()
            ->where('user_id', $request->user()->id)
            ->orderBy('is_default', 'desc')->latest()
            ->get();

        return view('dashboard.whatsapp-providers.index', [
            'providers' => $providers,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        Gate::authorize('create', WhatsAppProvider::class);

        $typeOptions = WhatsAppProvider::getTypeOptions();

        return view('dashboard.whatsapp-providers.create', [
            'typeOptions' => $typeOptions,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWhatsAppProviderRequest $request): RedirectResponse
    {
        Gate::authorize('create', WhatsAppProvider::class);

        try {
            $this->createWhatsAppProviderAction->execute($request->user(), $request->validated());

            return to_route('whatsapp-providers.index')
                ->with('success', 'WhatsApp provider created successfully.');
        } catch (\Exception $exception) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create WhatsApp provider: ' . $exception->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(WhatsAppProvider $whatsappProvider): View
    {
        Gate::authorize('view', $whatsappProvider);

        return view('dashboard.whatsapp-providers.show', [
            'provider' => $whatsappProvider,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WhatsAppProvider $whatsappProvider): View
    {
        Gate::authorize('update', $whatsappProvider);

        $typeOptions = WhatsAppProvider::getTypeOptions();
        $requiredCredentials = WhatsAppProvider::getRequiredCredentials($whatsappProvider->type);

        return view('dashboard.whatsapp-providers.edit', [
            'provider' => $whatsappProvider,
            'typeOptions' => $typeOptions,
            'requiredCredentials' => $requiredCredentials,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWhatsAppProviderRequest $request, WhatsAppProvider $whatsappProvider): RedirectResponse
    {
        Gate::authorize('update', $whatsappProvider);

        try {
            $this->updateWhatsAppProviderAction->execute($whatsappProvider, $request->validated());

            return to_route('whatsapp-providers.index')
                ->with('success', 'WhatsApp provider updated successfully.');
        } catch (\Exception $exception) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update WhatsApp provider: ' . $exception->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, WhatsAppProvider $whatsappProvider, DeleteWhatsAppProviderAction $action): RedirectResponse|JsonResponse
    {
        Gate::authorize('delete', $whatsappProvider);

        try {
            $this->deleteWhatsAppProviderAction->execute($whatsappProvider);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'WhatsApp provider deleted successfully!',
                    'redirect' => route('whatsapp-providers.index'),
                ]);
            }

            return to_route('whatsapp-providers.index')
                ->with('success', 'WhatsApp provider deleted successfully.');
        } catch (\Exception $exception) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete WhatsApp provider: ' . $exception->getMessage(),
                ], 500);
            }

            return back()
                ->with('error', 'Failed to delete WhatsApp provider: ' . $exception->getMessage());
        }
    }

    /**
     * Toggle the active status of a provider.
     */
    public function toggleStatus(WhatsAppProvider $whatsappProvider, ToggleStatusWhatsAppProviderAction $action): RedirectResponse|JsonResponse
    {
        Gate::authorize('toggleStatus', $whatsappProvider);

        $provider = $this->toggleStatusWhatsAppProviderAction->execute($whatsappProvider);

        $message = $provider->is_active
            ? 'WhatsApp provider activated successfully.'
            : 'WhatsApp provider deactivated successfully.';

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Set a provider as default.
     */
    public function setDefault(WhatsAppProvider $whatsappProvider, SetDefaultWhatsAppProviderAction $action): RedirectResponse|JsonResponse
    {
        Gate::authorize('setDefault', $whatsappProvider);

        try {
            $this->setDefaultWhatsAppProviderAction->execute($whatsappProvider);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'WhatsApp provider set as default successfully.',
                ]);
            }

            return back()->with('success', 'WhatsApp provider set as default successfully.');
        } catch (\Exception $exception) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to set default provider: ' . $exception->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Failed to set default provider: ' . $exception->getMessage());
        }
    }
}
