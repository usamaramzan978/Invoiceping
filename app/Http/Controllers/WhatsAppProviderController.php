<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\WhatsAppProviderType;
use App\Http\Requests\StoreWhatsAppProviderRequest;
use App\Http\Requests\UpdateWhatsAppProviderRequest;
use App\Models\WhatsAppProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class WhatsAppProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user === null) {
            abort(401);
        }

        $providers = WhatsAppProvider::query()
            ->where('user_id', $user->id)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
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
        $user = $request->user();

        if ($user === null) {
            abort(401);
        }

        try {
            DB::beginTransaction();

            $data = $request->validated();

            // If this provider is being set as default, unset other defaults
            if (isset($data['is_default']) && $data['is_default']) {
                WhatsAppProvider::query()
                    ->where('user_id', $user->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            // Create the provider
            $provider = new WhatsAppProvider();
            $provider->user_id = (string) $user->id;
            $provider->name = $data['name'];
            $provider->type = $data['type'];
            $provider->is_active = $data['is_active'] ?? true;
            $provider->is_default = $data['is_default'] ?? false;
            $provider->notes = $data['notes'] ?? null;
            
            // Set credentials based on provider type
            $providerType = WhatsAppProviderType::tryFrom($data['type']) ?? WhatsAppProviderType::WHATSAPP_CLOUD_API;
            if ($providerType === WhatsAppProviderType::WHATSAPP_CLOUD_API) {
                $provider->access_token = $data['access_token'] ?? null;
                $provider->phone_number_id = $data['phone_number_id'] ?? null;
                $provider->business_account_id = $data['business_account_id'] ?? null;
                $provider->app_id = $data['app_id'] ?? null;
                $provider->app_secret = $data['app_secret'] ?? null;
            } elseif ($providerType === WhatsAppProviderType::TWILIO) {
                $provider->account_sid = $data['account_sid'] ?? null;
                $provider->auth_token = $data['auth_token'] ?? null;
                $provider->from_phone_number = $data['from_phone_number'] ?? null;
                $provider->whatsapp_sandbox_number = $data['whatsapp_sandbox_number'] ?? null;
            } elseif ($providerType === WhatsAppProviderType::VONAGE) {
                $provider->api_key = $data['api_key'] ?? null;
                $provider->api_secret = $data['api_secret'] ?? null;
                $provider->from_number = $data['from_number'] ?? null;
                $provider->application_id = $data['application_id'] ?? null;
            }
            
            $provider->save();

            DB::commit();

            return to_route('whatsapp-providers.index')
                ->with('success', 'WhatsApp provider created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Failed to create WhatsApp provider: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(WhatsAppProvider $whatsappProvider): View
    {
        // Ensure the provider belongs to the authenticated user
        if ($whatsappProvider->user_id !== auth()->id()) {
            abort(403);
        }

        return view('dashboard.whatsapp-providers.show', [
            'provider' => $whatsappProvider,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WhatsAppProvider $whatsappProvider): View
    {
        // Ensure the provider belongs to the authenticated user
        if ($whatsappProvider->user_id !== auth()->id()) {
            abort(403);
        }

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
        $user = $request->user();

        if ($user === null) {
            abort(401);
        }

        // Ensure the provider belongs to the authenticated user
        if ($whatsappProvider->user_id !== $user->id) {
            abort(403);
        }

        try {
            DB::beginTransaction();

            $data = $request->validated();

            // If this provider is being set as default, unset other defaults
            if (isset($data['is_default']) && $data['is_default']) {
                WhatsAppProvider::query()
                    ->where('user_id', $user->id)
                    ->where('id', '!=', $whatsappProvider->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            // Update the provider
            if (isset($data['name'])) {
                $whatsappProvider->name = $data['name'];
            }
            if (isset($data['type'])) {
                $whatsappProvider->type = $data['type'];
            }
            
            // Update credentials based on provider type
            $type = $whatsappProvider->type; // Already cast to enum
            if ($type === WhatsAppProviderType::WHATSAPP_CLOUD_API) {
                if (isset($data['access_token'])) {
                    $whatsappProvider->access_token = $data['access_token'];
                }
                if (isset($data['phone_number_id'])) {
                    $whatsappProvider->phone_number_id = $data['phone_number_id'];
                }
                if (isset($data['business_account_id'])) {
                    $whatsappProvider->business_account_id = $data['business_account_id'];
                }
                if (isset($data['app_id'])) {
                    $whatsappProvider->app_id = $data['app_id'];
                }
                if (isset($data['app_secret'])) {
                    $whatsappProvider->app_secret = $data['app_secret'];
                }
            } elseif ($type === WhatsAppProviderType::TWILIO) {
                if (isset($data['account_sid'])) {
                    $whatsappProvider->account_sid = $data['account_sid'];
                }
                if (isset($data['auth_token'])) {
                    $whatsappProvider->auth_token = $data['auth_token'];
                }
                if (isset($data['from_phone_number'])) {
                    $whatsappProvider->from_phone_number = $data['from_phone_number'];
                }
                if (isset($data['whatsapp_sandbox_number'])) {
                    $whatsappProvider->whatsapp_sandbox_number = $data['whatsapp_sandbox_number'];
                }
            } elseif ($type === WhatsAppProviderType::VONAGE) {
                if (isset($data['api_key'])) {
                    $whatsappProvider->api_key = $data['api_key'];
                }
                if (isset($data['api_secret'])) {
                    $whatsappProvider->api_secret = $data['api_secret'];
                }
                if (isset($data['from_number'])) {
                    $whatsappProvider->from_number = $data['from_number'];
                }
                if (isset($data['application_id'])) {
                    $whatsappProvider->application_id = $data['application_id'];
                }
            }
            
            if (isset($data['is_active'])) {
                $whatsappProvider->is_active = $data['is_active'];
            }
            if (isset($data['is_default'])) {
                $whatsappProvider->is_default = $data['is_default'];
            }
            if (isset($data['notes'])) {
                $whatsappProvider->notes = $data['notes'];
            }

            $whatsappProvider->save();

            DB::commit();

            return to_route('whatsapp-providers.index')
                ->with('success', 'WhatsApp provider updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Failed to update WhatsApp provider: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, WhatsAppProvider $whatsappProvider): RedirectResponse|JsonResponse
    {
        // Ensure the provider belongs to the authenticated user
        if ($whatsappProvider->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $whatsappProvider->delete();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'WhatsApp provider deleted successfully!',
                    'redirect' => route('whatsapp-providers.index'),
                ]);
            }

            return to_route('whatsapp-providers.index')
                ->with('success', 'WhatsApp provider deleted successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete WhatsApp provider: ' . $e->getMessage(),
                ], 500);
            }

            return back()
                ->with('error', 'Failed to delete WhatsApp provider: ' . $e->getMessage());
        }
    }

    /**
     * Toggle the active status of a provider.
     */
    public function toggleStatus(WhatsAppProvider $whatsappProvider): RedirectResponse|JsonResponse
    {
        // Ensure the provider belongs to the authenticated user
        if ($whatsappProvider->user_id !== auth()->id()) {
            abort(403);
        }

        $whatsappProvider->is_active = ! $whatsappProvider->is_active;
        $whatsappProvider->save();

        $message = $whatsappProvider->is_active
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
    public function setDefault(WhatsAppProvider $whatsappProvider): RedirectResponse|JsonResponse
    {
        // Ensure the provider belongs to the authenticated user
        if ($whatsappProvider->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            DB::beginTransaction();

            // Unset other defaults
            WhatsAppProvider::query()
                ->where('user_id', auth()->id())
                ->where('id', '!=', $whatsappProvider->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);

            // Set this provider as default
            $whatsappProvider->is_default = true;
            $whatsappProvider->save();

            DB::commit();

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'WhatsApp provider set as default successfully.',
                ]);
            }

            return back()->with('success', 'WhatsApp provider set as default successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to set default provider: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Failed to set default provider: ' . $e->getMessage());
        }
    }
}
