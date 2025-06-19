<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(['open', 'in_progress', 'closed', 'reopened']),
            'priority' => $this->faker->randomElement(['low', 'medium', 'high', 'urgent']),
            'created_by_user_id' => $this->faker->randomElement([2]),
            'assigned_to_user_id' => $this->faker->randomElement([1]),
        ];
    }
}
