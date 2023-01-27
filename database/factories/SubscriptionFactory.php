<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'plan_id' => Plan::inRandomOrder()->first()->id,
            'expiration_date' => $this->faker->dateTimeBetween('-1 week', '+1 week')
        ];
    }
}
