<?php

namespace Database\Factories;

use App\Models\City;
use Illuminate\Support\Str;
use App\Models\IdentificationType;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->name();
        $lastName = $this->faker->lastName;
        return [
            'name' => $name,
            'last_name' => $lastName,
            'email' => $this->faker->unique()->safeEmail(),
            'document' => $this->faker->creditCardNumber,
            'identification_type_id' => IdentificationType::inRandomOrder()->first()->id,
            'birthday' => $this->faker->date($format = 'Y-m-d', $max = 'now'),
            'address' => $this->faker->address,
            'phone' => $this->faker->phoneNumber,
            'email_verified_at' => now(),
            'picture' => '/assets/images/user-profile.png',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'slug' => Str::slug( $name.'-'.$lastName. '-' . Str::random(8), '-'),
            'city_id' => City::inRandomOrder()->first()->id,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
