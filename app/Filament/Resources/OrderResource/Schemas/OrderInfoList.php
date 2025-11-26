<?php

namespace App\Filament\Resources\OrderResource\Schemas;

use Filament\Actions\Action;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class OrderInfoList
{
        public static function configure(Schema $schema): Schema
        {
            return $schema->components([
                    Group::make()
                        ->schema([
                            Section::make('Detalles de la Solicitud')
                                ->schema([
                                    TextEntry::make('user.name')->label('Usuario / Local'),
                                    Grid::make(2)
                                        ->schema([
                                            TextEntry::make('service.description')->label('Servicio Solicitado'),
                                            TextEntry::make('order_type.name')->label('Nivel de Urgencia'),
                                        ]),
                                ]),
                            Section::make('Descripción y Archivos Adjuntos')
                                ->schema([
                                    TextEntry::make('description')
                                        ->label('Descripción del Problema')
                                        ->columnSpanFull(),

                                    // Aquí está la magia para las imágenes
                                    RepeatableEntry::make('attachments_gallery') // Nombre virtual
                                    ->label('Imagenes Adjuntas')

                                        // 1. TRANSFORMACIÓN: JSON (Array) -> Estructura Filament
                                        ->state(function ($record) {
                                            // Gracias al 'cast', esto ya es un array PHP: ['a.jpg', 'b.jpg']
                                            $files = $record->attachments;

                                            if (!is_array($files) || empty($files)) {
                                                return [];
                                            }

                                            // Transformamos a: [['path' => 'a.jpg'], ['path' => 'b.jpg']]
                                            return collect($files)
                                                ->map(fn ($file) => ['path' => $file])
                                                ->toArray();
                                        })
                                        ->grid([
                                            'default' => 2,
                                            'sm' => 3,
                                            'md' => 4,
                                            'xl' => 5,
                                        ])

                                        ->schema([
                                            // 2. VISUALIZACIÓN INDIVIDUAL
                                            ImageEntry::make('path')
                                                ->hiddenLabel()// Ocultamos la etiqueta principal para dar más espacio
                                                ->disk('public')
                                                ->columnSpanFull()
                                                ->columns(5) // Muestra hasta 5 imágenes por fila
                                                ->imageWidth(150)
                                                // 3. MODAL AL HACER CLIC
                                                ->action(
                                                    Action::make('viewImage')
                                                        ->label('Ver')
                                                        ->icon('heroicon-m-eye')
                                                        ->modalHeading('Vista Previa del Adjunto')
                                                        ->modalWidth('5xl')
                                                        ->modalSubmitAction(false)
                                                        ->modalCancelAction(false)
                                                        ->modalContent(fn ($state) => new HtmlString('
                                                            <div class="flex justify-center w-full bg-gray-100/50 p-4 rounded-lg">
                                                                <img
                                                                    src="' . Storage::url($state) . '"
                                                                    alt="Imagen completa"
                                                                    class="max-h-[85vh] w-auto object-contain rounded shadow-lg"
                                                                />
                                                            </div>
                                                        '))
                                                ),
                                        ])
                                        ->contained(false) // Fija la altura de todas las miniaturas para unificar el tamaño
                                ]),
                        ])
                        ->columnSpan(['lg' => 2]),

                    Group::make()
                        ->schema([
                            Section::make('Estado y Fecha')
                                ->schema([
                                    TextEntry::make('status.name')->label('Estado Actual')->badge(),
                                    TextEntry::make('order_date')->label('Fecha de Registro')->dateTime(),
                                ]),

                            Section::make('Evaluación del Cliente')
                                ->visible(fn ($record) => !is_null($record->rating)) // Solo visible si hay calificación
                                ->schema([
                                    TextEntry::make('rating')->label('Calificación'),
                                    TextEntry::make('feedback')->label('Comentarios'),
                                ]),
                        ])
                        ->columnSpan(['lg' => 1]),
                ])->columns(3);
        }
}
