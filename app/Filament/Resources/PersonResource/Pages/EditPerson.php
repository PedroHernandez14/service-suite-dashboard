<?php

namespace App\Filament\Resources\PersonResource\Pages;

use App\Filament\Resources\PersonResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditPerson extends EditRecord
{
    protected static string $resource = PersonResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $contacts = $this->record->contacts;

        // Buscamos email y teléfono principal
        $email = $contacts->where('type', 'email')->first();
        $phone = $contacts->where('type', 'phone')->first();

        // Los inyectamos en la data del formulario
        $data['main_email'] = $email?->value;
        $data['main_phone'] = $phone?->value;
        $data['main_phone_prefix'] = $phone?->prefix ?? '+58';

        return $data;
    }

    // 2. GUARDAR LOS CAMBIOS
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $formData = $this->data; // Datos completos del formulario

        // Extracción de datos virtuales
        $mainEmail = $formData['main_email'] ?? null;
        $mainPhone = $formData['phone_number_principal'] ?? null;
        $mainPrefix = $formData['main_phone_prefix'] ?? '+58';

        // Limpiar data para el modelo Person (los campos que SÍ van a people)
        unset($data['main_email'], $data['phone_number_principal'], $data['main_phone_prefix']);

        // Actualizar Persona
        $record->update($data);

        // Actualizar/Crear Contactos
        if ($mainEmail) {
            $record->contacts()->updateOrCreate(
                ['type' => 'email', 'label' => 'Principal'],
                ['value' => $mainEmail, 'prefix' => null]
            );
        }

        if ($mainPhone) {
            $record->contacts()->updateOrCreate(
                ['type' => 'phone', 'label' => 'Principal'],
                ['value' => $mainPhone, 'prefix' => $mainPrefix]
            );
        }

        return $record;
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
