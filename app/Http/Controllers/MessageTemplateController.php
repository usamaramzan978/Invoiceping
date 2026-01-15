<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\MessageTemplate\CreateMessageTemplateAction;
use App\Actions\MessageTemplate\DeleteMessageTemplateAction;
use App\Actions\MessageTemplate\UpdateMessageTemplateAction;
use App\Http\Requests\StoreMessageTemplateRequest;
use App\Http\Requests\UpdateMessageTemplateRequest;
use App\Models\MessageTemplates;
use App\Services\TemplateVariableService;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

final class MessageTemplateController extends Controller
{
    public function __construct(
        private readonly CreateMessageTemplateAction $createAction,
        private readonly UpdateMessageTemplateAction $updateAction,
        private readonly DeleteMessageTemplateAction $deleteAction
    ) {}

    /**
     * Display a listing of message templates.
     */
    public function index(Request $request): Factory|View
    {
        Gate::authorize('viewAny', MessageTemplates::class);

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
        Gate::authorize('create', MessageTemplates::class);

        $variableService = app(TemplateVariableService::class);
        $availableVariables = $variableService->getAvailableVariables();

        return view('dashboard.templates.create', [
            'availableVariables' => $availableVariables,
        ]);
    }

    /**
     * Store a newly created message template in storage.
     */
    public function store(StoreMessageTemplateRequest $request): RedirectResponse
    {
        Gate::authorize('create', MessageTemplates::class);

        try {
            $this->createAction->execute(auth()->id(), $request->validated());

            return to_route('templates.index')->with('success', 'Template created successfully!');
        } catch (Exception $exception) {
            return back()->withInput()->with('error', 'Failed to create template: '.$exception->getMessage());
        }
    }

    /**
     * Display the specified message template.
     */
    public function show(MessageTemplates $template): Factory|View
    {
        Gate::authorize('view', $template);

        return view('dashboard.templates.show', ['template' => $template]);
    }

    /**
     * Show the form for editing the specified message template.
     */
    public function edit(MessageTemplates $template): Factory|View
    {
        Gate::authorize('update', $template);

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
    public function update(UpdateMessageTemplateRequest $request, MessageTemplates $template): RedirectResponse
    {
        Gate::authorize('update', $template);

        try {
            $this->updateAction->execute($template, $request->validated());

            return to_route('templates.index')->with('success', 'Template updated successfully!');
        } catch (Exception $exception) {
            return back()->withInput()->with('error', 'Failed to update template: '.$exception->getMessage());
        }
    }

    /**
     * Remove the specified message template from storage.
     */
    public function destroy(Request $request, MessageTemplates $template): RedirectResponse|JsonResponse
    {
        Gate::authorize('delete', $template);

        try {
            $this->deleteAction->execute($template);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Template deleted successfully!',
                    'redirect' => route('templates.index'),
                ]);
            }

            return to_route('templates.index')->with('success', 'Template deleted successfully!');
        } catch (InvalidArgumentException $invalidArgumentException) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $invalidArgumentException->getMessage(),
                ], 422);
            }

            return to_route('templates.index')->with('error', $invalidArgumentException->getMessage());
        }
    }
}
