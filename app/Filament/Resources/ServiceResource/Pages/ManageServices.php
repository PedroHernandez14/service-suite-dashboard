<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\ServiceResource;
use App\Models\Service;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Resources\Pages\ManageRecords;
use Filament\Actions;

class ManageServices extends ManageRecords
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // 1. Tu botón de crear existente
            Actions\CreateAction::make()
                ->modalWidth('lg')
                ->slideOver(),

            // 2. NUEVO BOTÓN: Exportar PDF
            Actions\Action::make('exportPdf')
                ->label('Reporte General')
                ->icon('heroicon-o-printer')
                ->color('info') // Azulito
                ->action(function () {
                    // A. Obtenemos todos los datos necesarios (Eager Loading para rapidez)
                    $records = Service::with(['serviceType.status'])
                        ->orderBy('service_type_id') // Ordenamos por tipo para que se vea bonito
                        ->get();

                    // B. Generamos el PDF
                    $pdf = Pdf::loadView('pdf.services.services-list', ['records' => $records]);

                    // C. Importante: Ponerlo en Horizontal (Landscape) porque es una tabla
                    $pdf->setPaper('a4', 'landscape');

                    // D. Descargar
                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->output();
                    }, 'catalogo-servicios-' . now()->format('YmdHis') . '.pdf');
                }),
        ];
    }
}
