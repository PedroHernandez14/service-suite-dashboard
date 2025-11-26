<?php

namespace App\Filament\Resources\OrderResource\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make('Detalles de la Solicitud')
                            ->description('Información base de la orden')
                            ->schema([
                                // Selección de Usuario con búsqueda optimizada
                                Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->label('Usuario / Local')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpanFull(), // Ocupa todo el ancho de la sección

                                // Servicio y Urgencia en una misma fila
                                Grid::make(2)
                                    ->schema([
                                        Select::make('service_id')
                                            ->relationship('service', 'description')
                                            ->label('Servicio Solicitado')
                                            ->searchable()
                                            ->preload()
                                            ->required(),

                                        Select::make('order_type_id')
                                            ->relationship('order_type', 'name')
                                            ->label('Nivel de Urgencia')
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                    ]),
                            ]),

                        Section::make('Descripción Técnica')
                            ->schema([
                                Textarea::make('description')
                                    ->label('Descripción del Problema')
                                    ->rows(5)
                                    ->required()
                                    ->columnSpanFull(),

                                // KeyValue para los "adjustments" JSON
//                                Forms\Components\KeyValue::make('adjustments')
//                                    ->label('Ajustes y Materiales Adicionales')
//                                    ->keyLabel('Concepto')
//                                    ->valueLabel('Detalle/Cantidad')
//                                    ->reorderable(),

                                // Campo para subir hasta 5 imágenes
                                FileUpload::make('attachments')
                                    ->label('Imágenes Adjuntas (Opcional)')
                                    ->multiple()
                                    ->reorderable()
                                    ->maxFiles(5)
                                    ->image() // Valida que sean imágenes y muestra previsualización
                                    ->disk('public') // Asegúrate de tener configurado el disco 'public'
                                    ->directory('order-attachments') // Guarda los archivos en 'storage/app/public/order-attachments'
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]), // Ocupa 2/3 del espacio en pantallas grandes

                // --- COLUMNA LATERAL (DERECHA) ---
                Group::make()
                    ->schema([
                        Section::make('Estado y Fecha')
                            // Esta sección ahora está vacía en el formulario,
                            // los valores se asignan automáticamente al crear.
                            ->hidden(),

                        // Sección colapsable para el feedback final
                        Section::make('Evaluación del Cliente')
                            ->description('A completar al cerrar la orden')
                            // Solo será visible en la página de edición, no en la de creación.
                            ->visibleOn('edit')
                            ->schema([
                                Select::make('rating')
                                    ->label('Calificación')
                                    ->options([
                                        1 => '1 - Muy Malo',
                                        2 => '2 - Malo',
                                        3 => '3 - Regular',
                                        4 => '4 - Bueno',
                                        5 => '5 - Excelente',
                                    ])
                                    ->native(false),

                                Textarea::make('feedback')
                                    ->label('Comentarios')
                                    ->rows(3),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]), // Ocupa 1/3 del espacio
            ])
            ->columns(3);
    }
}
