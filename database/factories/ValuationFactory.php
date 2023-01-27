<?php

namespace Database\Factories;

use App\Models\TypeTreatment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ValuationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->sentence('2');
        return [
            'name' => $this->faker->sentence('2'),
            'slug' => Str::slug(  $name. '-' . Str::random(8), '-'),
            'doctor_id' => 1,
            'type_treatment_id' => TypeTreatment::inRandomOrder()->first()->id,
            'objectives' => $this->faker->sentence($nbWords = 6, $variableNbWords = true)
        ];
    }
}
