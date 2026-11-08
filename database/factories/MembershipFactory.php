<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Membership\Models\Member;
use Modules\Membership\Models\Membership;

class MembershipFactory extends Factory
{
    protected $model = Membership::class;

    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'type' => fake()->randomElement(['basic', 'premium', 'student']),
            'start_date' => now()->subMonths(fake()->numberBetween(1, 12)),
            'end_date' => now()->addYear(),
            'fee' => fake()->randomElement([0, 50000, 100000]),
            'status' => 'active',
        ];
    }
}
