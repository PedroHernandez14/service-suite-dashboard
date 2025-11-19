<?php
namespace App\Filament\Resources\PersonResource\Pages;

use App\Filament\Resources\PersonResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Model; // Importante
use App\Models\Contact; // Importa el modelo Contact

class ManageServices extends ManageRecords
{
    protected static string $resource = PersonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    // --- 1. MODIFICACIÓN: AISLAR LOS CONTACTOS (PRE-GUARDADO) ---
                    // Filament intentará guardar los datos que quedan después de este método.

                    // Guardamos los datos de contactos 'virtuales' que no deben ir a la tabla 'people'
                    $this->mainEmail = $data['main_mail'] ?? null;
                    $this->mainPhone = $data['main_phone'] ?? null;
                    $this->mainPrefix = $data['main_phone_prefix'] ?? '+58';

                    // Eliminamos los campos 'virtuales' del array de datos para evitar el error SQL
                    unset($data['main_email'], $data['main_phone'], $data['main_phone_prefix']);

                    // El Repeater ('contacts') se maneja solo porque tiene ->relationship()

                    return $data; // Solo quedan los campos de la tabla 'people'
                })
                ->after(function (Model $record, array $data) {
                    // --- 2. MODIFICACIÓN: GUARDAR CONTACTOS (POST-GUARDADO) ---
                    // El $record ahora es la nueva Persona recién creada.
                    // Usamos las variables guardadas en $this (la instancia de la página).

                    // Guardamos Email Principal
                    if (!empty($this->mainEmail)) {
                        $record->contacts()->create([
                            'type' => 'email',
                            'value' => $this->mainEmail,
                            'label' => 'Principal',
                        ]);
                    }

                    // Guardamos Teléfono Principal
                    if (!empty($this->mainPhone)) {
                        $record->contacts()->create([
                            'type' => 'phone',
                            'value' => $this->mainPhone,
                            'prefix' => $this->mainPrefix,
                            'label' => 'Principal',
                        ]);
                    }

                    // IMPORTANTE: El Repeater de contactos adicionales (si lo tienes)
                    // ya se guardó automáticamente por Filament.

                }),
        ];
    }

    // --- IMPORTANTE: Añadir las propiedades para guardar los datos temporalmente ---
    public $mainEmail = null;
    public $mainPhone = null;
    public $mainPrefix = null;

    // ... (El resto del código de la clase ManagePeople) ...
}
