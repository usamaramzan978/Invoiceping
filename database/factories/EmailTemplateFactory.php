<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailTemplate>
 */
final class EmailTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'subject' => fake()->sentence(),
            'template_json' => json_encode([
                'blocks' => [
                    [
                        'type' => 'text',
                        'content' => fake()->paragraph(),
                    ],
                ],
            ]),
            'template_html' => '<h1>'.fake()->sentence().'</h1><p>'.fake()->paragraph().'</p>',
            'category' => fake()->randomElement(['invoice', 'reminder', 'general', 'welcome']),
            'is_default' => false,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the template is default.
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_default' => true,
        ]);
    }

    /**
     * Indicate that the template is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    /**
     * Set a specific category for the template.
     */
    public function category(string $category): static
    {
        return $this->state(fn (array $attributes): array => [
            'category' => $category,
        ]);
    }
}
