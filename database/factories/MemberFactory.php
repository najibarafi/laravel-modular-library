<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Membership\Models\Member;

class MemberFactory extends Factory
{
    protected $model = Member::class;

    public function definition(): array
    {
        return [
            'membership_number' => 'MBR-' . fake()->unique()->numerify('######'),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'status' => Member::STATUS_ACTIVE,
            'max_loans' => 3,
            'active_loans' => 0,
            'joined_at' => fake()->dateTimeBetween('-2 years', 'now'),
        ];
    }
}
