<?php

namespace Database\Factories;

use App\Models\Gender;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $items = ['client', 'courtesy'];
        $patientype = $items[array_rand($items)];
        return [
            'gender_id' => Gender::inRandomOrder()->first()->id,
            'patient_type' => $patientype
        ];
    }
}
