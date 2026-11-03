<?php

namespace Database\Factories;

use App\Models\BedSlot;
use App\Models\DormRoom;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BedSlot>
 */
class BedSlotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dorm_room_id' => DormRoom::factory(),
            'bed_slot' => fake()->unique()->randomElement(['A', 'B', 'C', 'D']),
            'status' => 'Vacant',
        ];
    }
}
