<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MessageTemplates;
use App\Services\TemplateVariableService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

final class MessageTemplateController extends Controller
{
    /**
     * Display a listing of message templates.
     */
    public function index(Request $request): Factory|View
    {
        Auth::user()->business;
        $templates = MessageTemplates::query()
            ->where('user_id', auth()->user()->id)->latest()
            ->get();

        return view('dashboard.templates.index', ['templates' => $templates]);
    }

    /**
     * Show the form for creating a new message template.
     */
    public function create(): Factory|View
    {
        $variableService = app(TemplateVariableService::class);
        $availableVariables = $variableService->getAvailableVariables();

        return view('dashboard.templates.create', [
            'availableVariables' => $availableVariables,
        ]);
    }

    /**
     * Store a newly created message template in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $business = Auth::user()->business;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'channel' => ['required', 'string', Rule::in(['whatsapp', 'sms'])],
            'content' => ['required', 'string', 'max:5000'],
            'is_default' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ]);

        // If setting as default, unset other defaults for this channel
        if ($validated['is_default']) {
            MessageTemplates::query()
                ->where('business_id', $business->id)
                ->where('channel', $validated['channel'])
                ->update(['is_default' => false]);
        }

        MessageTemplates::query()->create([
            'business_id' => $business->id,
            'name' => $validated['name'],
            'channel' => $validated['channel'],
            'content' => $validated['content'],
            'is_default' => $validated['is_default'],
            'is_active' => $validated['is_active'],
        ]);

        return to_route('templates.index')->with('success', 'Template created successfully!');
    }

    /**
     * Display the specified message template.
     */
    public function show(string $id): Factory|View
    {
        $template = MessageTemplates::query()->findOrFail($id);

        return view('dashboard.templates.show', ['template' => $template]);
    }

    /**
     * Show the form for editing the specified message template.
     */
    public function edit(string $id): Factory|View
    {
        $template = MessageTemplates::query()->findOrFail($id);
        $variableService = app(TemplateVariableService::class);
        $availableVariables = $variableService->getAvailableVariables();

        return view('dashboard.templates.edit', [
            'template' => $template,
            'availableVariables' => $availableVariables,
        ]);
    }

    /**
     * Update the specified message template in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $template = MessageTemplates::query()->findOrFail($id);
        $business = Auth::user()->business;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'channel' => ['required', 'string', Rule::in(['whatsapp', 'sms'])],
            'content' => ['required', 'string', 'max:5000'],
            'is_default' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ]);

        // If setting as default, unset other defaults for this channel
        if ($validated['is_default']) {
            MessageTemplates::query()
                ->where('business_id', $business->id)
                ->where('channel', $validated['channel'])
                ->where('id', '!=', $id)
                ->update(['is_default' => false]);
        }

        $template->update([
            'name' => $validated['name'],
            'channel' => $validated['channel'],
            'content' => $validated['content'],
            'is_default' => $validated['is_default'],
            'is_active' => $validated['is_active'],
        ]);

        return to_route('templates.index')->with('success', 'Template updated successfully!');
    }

    /**
     * Remove the specified message template from storage.
     */
    public function destroy(Request $request, string $id): RedirectResponse|JsonResponse
    {
        $template = MessageTemplates::query()->findOrFail($id);

        // Prevent deletion of default templates
        if ($template->is_default) {
            $message = 'Cannot delete a default template. Set another template as default first.';

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return to_route('templates.index')->with('error', $message);
        }

        $template->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Template deleted successfully!',
                'redirect' => route('templates.index'),
            ]);
        }

        return to_route('templates.index')->with('success', 'Template deleted successfully!');
    }
}
