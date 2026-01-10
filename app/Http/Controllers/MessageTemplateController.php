<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MessageTemplates;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class MessageTemplateController extends Controller
{
    public function index(Request $request): Factory|View
    {
        $business = Auth::user()->business;
        $templates = MessageTemplates::query()->where('business_id', $business->id)->get();

        return view('dashboard.templates.index', ['templates' => $templates]);
    }

    public function create(): Factory|View
    {
        return view('dashboard.templates.create');
    }

    public function store(Request $request)
    {
        $business = Auth::user()->business;
        $validated = $request->validate([
            'channel' => ['required', 'in:email,whatsapp'],
            'subject' => ['nullable', 'string', 'max:150'],
            'content' => ['required', 'string'],
            'is_default' => ['required', 'in:0,1'],
            'is_active' => ['required', 'in:0,1'],
        ]);
        MessageTemplates::query()->create([
            'business_id' => $business->id,
            'name' => $validated['subject'] ?? ($validated['channel'] === 'email' ? 'Email Template' : 'WhatsApp Template'),
            'channel' => $validated['channel'],
            'content' => $validated['content'],
            'is_default' => $validated['is_default'],
            'is_active' => $validated['is_active'],
            // Optionally store attach_invoice if you add the column
        ]);

        return to_route('templates.index')->with('success', 'Template created successfully!');
    }

    public function edit($id): Factory|View
    {
        $template = MessageTemplates::query()->findOrFail($id);

        return view('dashboard.templates.edit', ['template' => $template]);
    }

    public function update(Request $request, $id)
    {
        $template = MessageTemplates::query()->findOrFail($id);
        $validated = $request->validate([
            'channel' => ['required', 'in:email,whatsapp'],
            'subject' => ['nullable', 'string', 'max:150'],
            'content' => ['required', 'string'],
            'attach_invoice' => ['required', 'in:0,1'],
            'is_default' => ['required', 'in:0,1'],
            'is_active' => ['required', 'in:0,1'],
        ]);
        $template->update([
            'channel' => $validated['channel'],
            'subject' => $validated['subject'],
            'content' => $validated['content'],
            'is_default' => $validated['is_default'],
            'is_active' => $validated['is_active'],
        ]);

        return to_route('templates.index')->with('success', 'Template updated successfully!');
    }
}
