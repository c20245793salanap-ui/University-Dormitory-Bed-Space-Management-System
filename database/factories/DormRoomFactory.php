<?php

namespace Database\Factories;

use App\Models\DormBuilding;
use App\Models\DormRoom;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DormRoom>
 */
class DormRoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dorm_building_id' => DormBuilding::factory(),
            'room_number' => fake()->unique()->numerify('###'),
        ];
    }
}
