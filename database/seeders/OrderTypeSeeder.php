<?php

namespace Database\Seeders;

use App\Models\OrderType;
use Illuminate\Database\Seeder;

class OrderTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Bajo', 'color' => 'V'],
            ['name' => 'Medio', 'color' => 'E'],
            ['name' => 'Alto', 'color' => 'J'],
            ['name' => 'Urgente', 'color' => 'G'],
        ];

        foreach ($types as $type) {
            OrderType::create($type);
        }
    }
}
