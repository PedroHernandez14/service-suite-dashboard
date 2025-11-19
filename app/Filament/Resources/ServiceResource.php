<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Resources\ServiceResource\RelationManagers;
use App\Models\Service;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $modelLabel = 'Servicio';
    protected static ?string $pluralModelLabel = 'Catálogo de Servicios';
    protected static ?string $slug = 'servicios';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Grid::make(1) // Una columna para que se vea limpio en modales o full page
                        ->schema([
                            // 1. Selector de TIPO DE SERVICIO
                            Forms\Components\Select::make('service_type_id')
                                ->relationship('serviceType', 'name') // Relación definida en el modelo
                                ->label('Tipo / Categoría')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->native(false)

                                // TRUCO PRO: Permitir crear un tipo nuevo desde aquí mismo
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('name')
                                        ->required()
                                        ->maxLength(100),
                                    Forms\Components\Select::make('status_id') // Asumiendo que Status existe
                                    ->relationship('status', 'name')
                                        ->default(1)
                                        ->required(),
                                ]),

                            // 2. DESCRIPCIÓN (Nombre del servicio)
                            Forms\Components\Textarea::make('description')
                                ->label('Nombre / Descripción del Servicio')
                                ->required()
                                ->maxLength(255)
                                ->rows(3)
                                ->placeholder('Ej: Mantenimiento Preventivo de Aire Acondicionado Split')
                                ->columnSpanFull(),
                        ]),
                    ])->maxWidth('2xl'), // Limitamos el ancho para que no se estire demasiado
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Agrupamos visualmente por Tipo
                Tables\Columns\TextColumn::make('serviceType.name')
                    ->label('Categoría')
                    ->sortable()
                    ->searchable()
                    ->badge() // Se ve genial como etiqueta
                    ->color('gray'), // O dinámico si ServiceType tuviera color

                Tables\Columns\TextColumn::make('description')
                    ->label('Servicio')
                    ->searchable()
                    ->wrap() // Si el texto es largo, que baje de línea
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filtro muy útil para ver solo servicios de "Limpieza" o "Soporte"
                Tables\Filters\SelectFilter::make('service_type')
                    ->relationship('serviceType', 'name')
                    ->label('Filtrar por Categoría')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                // --- ACCIÓN DE DESCARGA PDF ---
                Tables\Actions\Action::make('pdf')
                    ->label('Descargar PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('warning') // Un color distintivo (Naranja/Amarillo)
                    ->action(function ($record) {
                        // 1. Cargamos la vista y le pasamos el registro ($record)
                        $pdf = Pdf::loadView('pdf.services.service-details', ['record' => $record]);

                        // 2. Opcional: Configurar tamaño de papel
                        $pdf->setPaper('a4', 'portrait');

                        // 3. Descargamos el archivo (streamDownload es lo mejor para Filament)
                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'servicio-' . $record->id .'-'. Carbon::now() . '.pdf');
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageServices::route('/'),
        ];
    }
}
