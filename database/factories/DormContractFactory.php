<?php

namespace Database\Factories;

use App\Models\BedSlot;
use App\Models\DormContract;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DormContract>
 */
class DormContractFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'bed_slot_id' => BedSlot::factory(),
            'monthly_rate' => fake()->randomFloat(2, 1500, 4000),
            'contract_start' => fake()->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
            'contract_end' => fake()->dateTimeBetween('+2 months', '+1 year')->format('Y-m-d'),
            'security_deposit' => fake()->randomFloat(2, 1500, 4000),
            'total_paid' => 0,
            'lease_status' => 'Pending',
        ];
    }
}
