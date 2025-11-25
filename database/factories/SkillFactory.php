<?php

namespace Database\Factories;

use App\Models\Skill;
use Illuminate\Database\Eloquent\Factories\Factory;

class SkillFactory extends Factory
{
    protected $model = Skill::class;

    public function definition(): array
    {
        $categories = ['Technical', 'Communication', 'Leadership', 'Design', 'Management', 'Analytics'];

        return [
            'name' => fake()->unique()->words(2, true),
            'category' => fake()->randomElement($categories),
            'proficiency_level' => fake()->numberBetween(1, 5),
            'is_active' => fake()->boolean(80),
            'description' => fake()->paragraphs(2, true),
            'attachments' => null,
            'tags' => [
                ['value' => fake()->word()],
                ['value' => fake()->word()],
            ],
            'notes' => fake()->sentence(),
            'archived_at' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'archived_at' => now(),
        ]);
    }

    public function technical(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'Technical',
        ]);
    }
}
