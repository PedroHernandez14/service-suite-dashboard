<?php

namespace App\Filament\Resources\PersonResource\Pages;

use App\Filament\Resources\PersonResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePerson extends CreateRecord
{
    protected static string $resource = PersonResource::class;
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // 1. Extraemos los datos "virtuales" del array $data
        $mainEmail = $data['main_email'] ?? null;
        $mainPhone = $data['main_phone'] ?? null;
        $mainPrefix = $data['main_phone_prefix'] ?? '+58';

        // 2. Limpiamos $data para que solo queden campos reales de la tabla 'people'
        unset($data['main_email'], $data['main_phone'], $data['main_phone_prefix']);

        // 3. Creamos la Persona
        $person = static::getModel()::create($data);

        // 4. Creamos los Contactos manualmente
        if ($mainEmail) {
            $person->contacts()->create([
                'type' => 'email',
                'value' => $mainEmail,
                'label' => 'Principal',
            ]);
        }

        if ($mainPhone) {
            $person->contacts()->create([
                'type' => 'phone',
                'value' => $mainPhone,
                'prefix' => $mainPrefix,
                'label' => 'Principal',
            ]);
        }

        return $person;
    }

}
