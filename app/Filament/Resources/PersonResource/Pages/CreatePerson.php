<?php

namespace App\Filament\Resources\PersonResource\Pages;

use App\Filament\Resources\PersonResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePerson extends CreateRecord
{
    protected static string $resource = PersonResource::class;

    protected function getRedirectUrl(): string
    {
        // Redirect to the edit page of the newly created record
        $record = $this->getRecord();

        // Add a query parameter to trigger the modal opening on the edit page
        return $this->getResource()::getUrl('edit', ['record' => $record]) . '?open_contact_modal=true';
    }
}
