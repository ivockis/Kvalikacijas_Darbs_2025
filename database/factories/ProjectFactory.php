<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Project::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'materials' => $this->faker->text(200),
            'creation_time' => $this->faker->words(2, true), // e.g., "3 hours"
            'is_public' => $this->faker->boolean(80), // 80% public
            'is_blocked' => false,
            'user_id' => \App\Models\User::factory(), // Automatically create a user if not provided
        ];
    }

    /**
     * Indicate that the project is private.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }
}