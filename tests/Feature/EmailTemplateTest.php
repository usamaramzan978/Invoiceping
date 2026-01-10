<?php

declare(strict_types=1);

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

describe('Email Template Index', function (): void {
    it('returns all templates for authenticated user', function (): void {
        EmailTemplate::factory()->count(3)->create(['user_id' => $this->user->id]);
        EmailTemplate::factory()->count(2)->create(); // Other user's templates

        $response = $this->actingAs($this->user)->getJson('/api/email-templates');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'name',
                        'subject',
                        'template_json',
                        'template_html',
                        'category',
                        'is_default',
                        'is_active',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'meta' => ['total', 'default_template'],
            ])
            ->assertJsonCount(3, 'data');
    });

    it('filters by active status when requested', function (): void {
        EmailTemplate::factory()->create(['user_id' => $this->user->id, 'is_active' => true]);
        EmailTemplate::factory()->create(['user_id' => $this->user->id, 'is_active' => false]);

        $response = $this->actingAs($this->user)->getJson('/api/email-templates?active_only=1');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    });

    it('filters by category when provided', function (): void {
        EmailTemplate::factory()->create(['user_id' => $this->user->id, 'category' => 'invoice']);
        EmailTemplate::factory()->create(['user_id' => $this->user->id, 'category' => 'reminder']);

        $response = $this->actingAs($this->user)->getJson('/api/email-templates?category=invoice');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    });

    it('requires authentication', function (): void {
        $response = $this->getJson('/api/email-templates');

        $response->assertUnauthorized();
    });
});

describe('Email Template Store', function (): void {
    it('creates a new template with valid data', function (): void {
        $data = [
            'name' => 'Welcome Email',
            'subject' => 'Welcome to our platform',
            'template_json' => json_encode(['blocks' => []]),
            'template_html' => '<h1>Welcome</h1>',
            'category' => 'general',
            'is_default' => false,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/email-templates', $data);

        $response->assertCreated()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'user_id', 'name'],
            ]);

        $this->assertDatabaseHas('email_templates', [
            'user_id' => $this->user->id,
            'name' => 'Welcome Email',
            'subject' => 'Welcome to our platform',
        ]);
    });

    it('sets template as default and unsets other defaults', function (): void {
        EmailTemplate::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Old Default',
            'is_default' => true,
        ]);

        $data = [
            'name' => 'New Default',
            'subject' => 'Test',
            'template_json' => json_encode(['blocks' => []]),
            'is_default' => true,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/email-templates', $data);

        $response->assertCreated();

        $this->assertDatabaseHas('email_templates', [
            'name' => 'Old Default',
            'is_default' => false,
        ]);

        $this->assertDatabaseHas('email_templates', [
            'name' => 'New Default',
            'is_default' => true,
        ]);
    });

    it('validates required fields', function (): void {
        $response = $this->actingAs($this->user)->postJson('/api/email-templates', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'template_json']);
    });

    it('validates unique template name per user', function (): void {
        EmailTemplate::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Duplicate Name',
        ]);

        $data = [
            'name' => 'Duplicate Name',
            'template_json' => json_encode(['blocks' => []]),
        ];

        $response = $this->actingAs($this->user)->postJson('/api/email-templates', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    });

    it('allows duplicate names across different users', function (): void {
        $otherUser = User::factory()->create();

        EmailTemplate::factory()->create([
            'user_id' => $otherUser->id,
            'name' => 'Common Name',
        ]);

        $data = [
            'name' => 'Common Name',
            'template_json' => json_encode(['blocks' => []]),
        ];

        $response = $this->actingAs($this->user)->postJson('/api/email-templates', $data);

        $response->assertCreated();
    });

    it('requires authentication', function (): void {
        $data = [
            'name' => 'Test Template',
            'template_json' => json_encode(['blocks' => []]),
        ];

        $response = $this->postJson('/api/email-templates', $data);

        $response->assertUnauthorized();
    });
});

describe('Email Template Show', function (): void {
    it('returns a specific template for authenticated user', function (): void {
        $template = EmailTemplate::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->getJson("/api/email-templates/{$template->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => ['id', 'name', 'user_id'],
            ])
            ->assertJson([
                'data' => ['id' => $template->id],
            ]);
    });

    it('returns 404 for non-existent template', function (): void {
        $response = $this->actingAs($this->user)->getJson('/api/email-templates/99999');

        $response->assertNotFound();
    });

    it('prevents accessing other user templates', function (): void {
        $otherUser = User::factory()->create();
        $template = EmailTemplate::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->getJson("/api/email-templates/{$template->id}");

        $response->assertNotFound();
    });

    it('requires authentication', function (): void {
        $template = EmailTemplate::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/api/email-templates/{$template->id}");

        $response->assertUnauthorized();
    });
});

describe('Email Template Update', function (): void {
    it('updates template with valid data', function (): void {
        $template = EmailTemplate::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Original Name',
        ]);

        $data = [
            'name' => 'Updated Name',
            'subject' => 'Updated Subject',
        ];

        $response = $this->actingAs($this->user)->patchJson("/api/email-templates/{$template->id}", $data);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Template updated successfully.',
            ]);

        $this->assertDatabaseHas('email_templates', [
            'id' => $template->id,
            'name' => 'Updated Name',
            'subject' => 'Updated Subject',
        ]);
    });

    it('updates only provided fields', function (): void {
        $template = EmailTemplate::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Original',
            'subject' => 'Original Subject',
        ]);

        $response = $this->actingAs($this->user)->patchJson("/api/email-templates/{$template->id}", [
            'name' => 'Updated',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('email_templates', [
            'id' => $template->id,
            'name' => 'Updated',
            'subject' => 'Original Subject',
        ]);
    });

    it('validates unique name when updating', function (): void {
        EmailTemplate::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Existing Name',
        ]);

        $template = EmailTemplate::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Template To Update',
        ]);

        $response = $this->actingAs($this->user)->patchJson("/api/email-templates/{$template->id}", [
            'name' => 'Existing Name',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    });

    it('unsets other defaults when setting as default', function (): void {
        $defaultTemplate = EmailTemplate::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => true,
        ]);

        $template = EmailTemplate::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => false,
        ]);

        $response = $this->actingAs($this->user)->patchJson("/api/email-templates/{$template->id}", [
            'is_default' => true,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('email_templates', [
            'id' => $defaultTemplate->id,
            'is_default' => false,
        ]);

        $this->assertDatabaseHas('email_templates', [
            'id' => $template->id,
            'is_default' => true,
        ]);
    });

    it('prevents updating other user templates', function (): void {
        $otherUser = User::factory()->create();
        $template = EmailTemplate::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->patchJson("/api/email-templates/{$template->id}", [
            'name' => 'Hacked',
        ]);

        $response->assertNotFound();
    });

    it('requires authentication', function (): void {
        $template = EmailTemplate::factory()->create(['user_id' => $this->user->id]);

        $response = $this->patchJson("/api/email-templates/{$template->id}", [
            'name' => 'Updated',
        ]);

        $response->assertUnauthorized();
    });
});

describe('Email Template Delete', function (): void {
    it('soft deletes a template', function (): void {
        $template = EmailTemplate::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => false,
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/api/email-templates/{$template->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Template deleted successfully.',
            ]);

        $this->assertSoftDeleted('email_templates', [
            'id' => $template->id,
        ]);
    });

    it('prevents deleting default template', function (): void {
        $template = EmailTemplate::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => true,
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/api/email-templates/{$template->id}");

        $response->assertUnprocessable()
            ->assertJson([
                'success' => false,
                'message' => 'Cannot delete the default template. Please set another template as default first.',
            ]);

        $this->assertDatabaseHas('email_templates', [
            'id' => $template->id,
        ]);
    });

    it('prevents deleting other user templates', function (): void {
        $otherUser = User::factory()->create();
        $template = EmailTemplate::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->deleteJson("/api/email-templates/{$template->id}");

        $response->assertNotFound();
    });

    it('requires authentication', function (): void {
        $template = EmailTemplate::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/email-templates/{$template->id}");

        $response->assertUnauthorized();
    });
});

describe('Email Template Set Default', function (): void {
    it('sets a template as default', function (): void {
        $template = EmailTemplate::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => false,
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/email-templates/{$template->id}/set-default");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Template set as default successfully.',
            ]);

        $this->assertDatabaseHas('email_templates', [
            'id' => $template->id,
            'is_default' => true,
        ]);
    });

    it('unsets previous default template', function (): void {
        $oldDefault = EmailTemplate::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => true,
        ]);

        $newDefault = EmailTemplate::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => false,
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/email-templates/{$newDefault->id}/set-default");

        $response->assertOk();

        $this->assertDatabaseHas('email_templates', [
            'id' => $oldDefault->id,
            'is_default' => false,
        ]);

        $this->assertDatabaseHas('email_templates', [
            'id' => $newDefault->id,
            'is_default' => true,
        ]);
    });

    it('only allows one default per user', function (): void {
        $template1 = EmailTemplate::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => true,
        ]);
        $template2 = EmailTemplate::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => false,
        ]);
        $template3 = EmailTemplate::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => false,
        ]);

        $this->actingAs($this->user)->postJson("/api/email-templates/{$template2->id}/set-default");

        $defaultCount = EmailTemplate::where('user_id', $this->user->id)
            ->where('is_default', true)
            ->count();

        expect($defaultCount)->toBe(1);
    });

    it('requires authentication', function (): void {
        $template = EmailTemplate::factory()->create(['user_id' => $this->user->id]);

        $response = $this->postJson("/api/email-templates/{$template->id}/set-default");

        $response->assertUnauthorized();
    });
});

describe('Email Template Toggle Active', function (): void {
    it('toggles template active status from true to false', function (): void {
        $template = EmailTemplate::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/email-templates/{$template->id}/toggle-active");

        $response->assertOk();

        $this->assertDatabaseHas('email_templates', [
            'id' => $template->id,
            'is_active' => false,
        ]);
    });

    it('toggles template active status from false to true', function (): void {
        $template = EmailTemplate::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/email-templates/{$template->id}/toggle-active");

        $response->assertOk();

        $this->assertDatabaseHas('email_templates', [
            'id' => $template->id,
            'is_active' => true,
        ]);
    });

    it('requires authentication', function (): void {
        $template = EmailTemplate::factory()->create(['user_id' => $this->user->id]);

        $response = $this->postJson("/api/email-templates/{$template->id}/toggle-active");

        $response->assertUnauthorized();
    });
});

