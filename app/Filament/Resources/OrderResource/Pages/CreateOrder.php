<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status_id'] = 1; // Asigna 'Pendiente de Aprobación' (o el ID que corresponda)
        $data['order_date'] = now(); // Asigna la fecha y hora actual
        return $data;
    }
}