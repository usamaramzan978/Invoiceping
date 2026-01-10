<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateEmailTemplateRequest;
use App\Http\Requests\DeleteEmailTemplateRequest;
use App\Http\Requests\UpdateEmailTemplateRequest;
use App\Models\EmailTemplate;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class EmailTemplateController extends Controller
{
    /**
     * Display a listing of templates for the current user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $query = EmailTemplate::query()
            ->where('user_id', $user->id)
            ->orderBy('is_default', 'desc')->latest();

        // Filter by active status if requested
        if ($request->has('active_only') && $request->boolean('active_only')) {
            $query->where('is_active', true);
        }

        // Filter by category if provided
        if ($request->filled('category')) {
            $query->where('category', $request->string('category')->toString());
        }

        $templates = $query->get();

        return response()->json([
            'success' => true,
            'data' => $templates,
            'meta' => [
                'total' => $templates->count(),
                'default_template' => $templates->firstWhere('is_default', true)?->id,
            ],
        ]);
    }

    /**
     * Store a newly created template.
     */
    public function store(CreateEmailTemplateRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        try {
            DB::beginTransaction();

            /** @var array{name: string, subject?: string|null, template_json: string, template_html?: string|null, category?: string|null, is_default?: bool, is_active?: bool} $data */
            $data = $request->validated();

            // If this template is being set as default, unset other defaults
            if (isset($data['is_default']) && $data['is_default']) {
                EmailTemplate::query()->where('user_id', $user->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            // Create the template
            $template = new EmailTemplate();
            $template->user_id = (string) $user->id;
            $template->name = $data['name'];
            $template->subject = $data['subject'] ?? null;
            /** @var array<string, mixed> */
            $templateJson = json_decode($data['template_json'], true) ?? [];
            $template->template_json = $templateJson;
            $template->template_html = $data['template_html'] ?? null;
            $template->category = $data['category'] ?? null;
            $template->is_default = $data['is_default'] ?? false;
            $template->is_active = $data['is_active'] ?? true;
            $template->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Template created successfully.',
                'data' => $template->fresh(),
            ], 201);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('Failed to create email template', [
                'error' => $exception->getMessage(),
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create template.',
                'error' => $exception->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified template.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        try {
            $template = EmailTemplate::query()->where('user_id', $user->id)
                ->where('id', $id)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $template,
            ]);
        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Template not found.',
            ], 404);
        }
    }

    /**
     * Update the specified template.
     */
    public function update(UpdateEmailTemplateRequest $request, string $id): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        try {
            DB::beginTransaction();

            $template = EmailTemplate::query()->where('user_id', $user->id)
                ->where('id', $id)
                ->firstOrFail();

            /** @var array{name?: string, subject?: string|null, template_json?: string, template_html?: string|null, category?: string|null, is_default?: bool, is_active?: bool} $data */
            $data = $request->validated();

            // If this template is being set as default, unset other defaults
            if (isset($data['is_default']) && $data['is_default']) {
                EmailTemplate::query()->where('user_id', $user->id)
                    ->where('id', '!=', $id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            // Update only provided fields
            if (isset($data['name'])) {
                $template->name = $data['name'];
            }

            if (isset($data['subject'])) {
                $template->subject = $data['subject'];
            }

            if (isset($data['template_json'])) {
                /** @var array<string, mixed> */
                $templateJson = json_decode($data['template_json'], true) ?? [];
                $template->template_json = $templateJson;
            }

            if (isset($data['template_html'])) {
                $template->template_html = $data['template_html'];
            }

            if (isset($data['category'])) {
                $template->category = $data['category'];
            }

            if (isset($data['is_default'])) {
                $template->is_default = $data['is_default'];
            }

            if (isset($data['is_active'])) {
                $template->is_active = $data['is_active'];
            }

            $template->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Template updated successfully.',
                'data' => $template->fresh(),
            ]);
        } catch (ModelNotFoundException) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Template not found.',
            ], 404);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update email template', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'template_id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update template.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified template (soft delete).
     */
    public function destroy(DeleteEmailTemplateRequest $request, string $id): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        try {
            $template = EmailTemplate::query()->where('user_id', $user->id)
                ->where('id', $id)
                ->firstOrFail();

            // Prevent deletion of default template
            if ($template->is_default) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete the default template. Please set another template as default first.',
                ], 422);
            }

            $template->delete();

            return response()->json([
                'success' => true,
                'message' => 'Template deleted successfully.',
            ]);
        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Template not found.',
            ], 404);
        } catch (Exception $e) {
            Log::error('Failed to delete email template', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'template_id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete template.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Set a template as the default for the user.
     */
    public function setDefault(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        try {
            DB::beginTransaction();

            $template = EmailTemplate::query()->where('user_id', $user->id)
                ->where('id', $id)
                ->firstOrFail();

            // Unset all other defaults for this user
            EmailTemplate::query()->where('user_id', $user->id)
                ->where('id', '!=', $id)
                ->update(['is_default' => false]);

            // Set this template as default
            $template->is_default = true;
            $template->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Template set as default successfully.',
                'data' => $template->fresh(),
            ]);
        } catch (ModelNotFoundException) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Template not found.',
            ], 404);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to set default email template', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'template_id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to set template as default.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle the active status of a template.
     */
    public function toggleActive(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        try {
            $template = EmailTemplate::query()->where('user_id', $user->id)
                ->where('id', $id)
                ->firstOrFail();

            $template->is_active = ! $template->is_active;
            $template->save();

            return response()->json([
                'success' => true,
                'message' => 'Template status updated successfully.',
                'data' => $template->fresh(),
            ]);
        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Template not found.',
            ], 404);
        } catch (Exception $e) {
            Log::error('Failed to toggle email template status', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'template_id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update template status.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the template editor view.
     */
    public function create(): Factory|View
    {
        return view('dashboard.templates.editor');
    }

    /**
     * Show the template management index view.
     */
    public function indexView(): Factory|View
    {
        return view('dashboard.email-templates.index');
    }
}
