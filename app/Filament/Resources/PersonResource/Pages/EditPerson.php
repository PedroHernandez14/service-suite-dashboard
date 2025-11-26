<?php

namespace App\Filament\Resources\PersonResource\Pages;

use App\Filament\Resources\PersonResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPerson extends EditRecord
{
    protected static string $resource = PersonResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);

        if (request()->query('open_contact_modal') === 'true') {
            // Ensure the action is only mounted once
            if (!isset($this->mountedActions['create'])) {
                 $this->mountAction('create');
            }
        }
    }
}
