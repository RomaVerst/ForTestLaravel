<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CarFactory extends Factory
{
    const CARS_ELEMENTS = [
        'KIA' => [
            'Sportage', 'Ceed', 'Picanto',
            'Seltos', 'Sorento', 'Soul'
        ],
        'Ford' => [
            'Focus', 'Mondeo', 'Explorer',
            'EcoSport', 'Kuga', 'Fusion'
        ],
        'BMW' => [
            'X1', 'X2', 'X3', 'X4', 'X5',
            'X6', 'X7', 'iX', '2', '3'
        ],
        'Audi' => [
            'e-tron', 'A3', 'A4', 'A5', 'A6',
            'A7', 'A8', 'Q3', 'Q5', 'Q7', 'Q8'
        ],
    ];
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $brand = $this->faker->randomElement( array_keys(self::CARS_ELEMENTS) );
        $model = $this->faker->randomElement( self::CARS_ELEMENTS[$brand] );
        return [
            'brand' => $brand,
            'model' => $model,
            'number' => $this->faker->unique()->regexify('[A-Z]{1}[0-9]{3}[A-Z]{2}[0-9]{2,3}'),
        ];
    }
}
