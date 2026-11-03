<?php

namespace Database\Factories;

use App\Models\DormBuilding;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DormBuilding>
 */
class DormBuildingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'building_name' => fake()->unique()->company().' Hall',
        ];
    }
}
