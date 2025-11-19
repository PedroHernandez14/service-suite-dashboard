<?php

namespace Database\Seeders;

use App\Models\IdentificationType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IdentificationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Venezolano', 'acronym' => 'V'],
            ['name' => 'Extranjero', 'acronym' => 'E'],
            ['name' => 'Jurídico', 'acronym' => 'J'],
            ['name' => 'Gubernamental', 'acronym' => 'G'],
            ['name' => 'Pasaporte', 'acronym' => 'P'],
        ];

        foreach ($types as $type) {
            IdentificationType::create($type);
        }
    }
}
