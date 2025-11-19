<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';
    protected static ?string $slug = 'ordenes';
    protected static ?string $navigationLabel = 'Ordenes de Servicios';

    protected static ?string $modelLabel = 'Orden';

    protected static ?string $pluralModelLabel = 'Ordenes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Section::make('Detalles de la Solicitud')
                            ->description('Información base de la orden')
                            ->schema([
                                // Selección de Usuario con búsqueda optimizada
                                Forms\Components\Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->label('Usuario / Local')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpanFull(), // Ocupa todo el ancho de la sección

                                // Servicio y Urgencia en una misma fila
                                Grid::make(2)
                                    ->schema([
                                        Forms\Components\Select::make('service_id')
                                            ->relationship('service', 'description')
                                            ->label('Servicio Solicitado')
                                            ->searchable()
                                            ->preload()
                                            ->required(),

                                        Forms\Components\Select::make('urgency')
                                            ->label('Nivel de Urgencia')
                                            ->options([
                                                'baja' => 'Baja',
                                                'media' => 'Media',
                                                'alta' => 'Alta',
                                                'critica' => 'Crítica',
                                            ])
                                            ->native(false) // Usa el estilo UI de Filament
                                            ->required(),
                                    ]),
                            ]),

                        Section::make('Descripción Técnica')
                            ->schema([
                                Forms\Components\Textarea::make('description')
                                    ->label('Descripción del Problema')
                                    ->rows(5)
                                    ->required()
                                    ->columnSpanFull(),

                                // KeyValue para los "adjustments" JSON
                                Forms\Components\KeyValue::make('adjustments')
                                    ->label('Ajustes y Materiales Adicionales')
                                    ->keyLabel('Concepto')
                                    ->valueLabel('Detalle/Cantidad')
                                    ->reorderable(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]), // Ocupa 2/3 del espacio en pantallas grandes

                // --- COLUMNA LATERAL (DERECHA) ---
                Forms\Components\Group::make()
                    ->schema([
                        Section::make('Estado y Fecha')
                            ->schema([
                                Forms\Components\Select::make('status_id')
                                    ->relationship('status', 'name')
                                    ->label('Estado Actual')
                                    ->default(1)
                                    ->required()
                                    ->selectablePlaceholder(false),

                                Forms\Components\DateTimePicker::make('order_date')
                                    ->label('Fecha de Registro')
                                    ->default(now())
                                    ->required(),
                            ]),

                        // Sección colapsable para el feedback final
                        Section::make('Evaluación del Cliente')
                            ->description('A completar al cerrar la orden')
                            ->collapsible()
                            ->collapsed() // Por defecto cerrado para no estorbar
                            ->schema([
                                Forms\Components\Select::make('rating')
                                    ->label('Calificación')
                                    ->options([
                                        1 => '1 - Muy Malo',
                                        2 => '2 - Malo',
                                        3 => '3 - Regular',
                                        4 => '4 - Bueno',
                                        5 => '5 - Excelente',
                                    ])
                                    ->native(false),

                                Forms\Components\Textarea::make('feedback')
                                    ->label('Comentarios')
                                    ->rows(3),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]), // Ocupa 1/3 del espacio
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true), // Oculto por defecto en v4

                Tables\Columns\TextColumn::make('user.username')
                    ->label('Local')
                    ->searchable(),

                Tables\Columns\TextColumn::make('service.description')
                    ->label('Servicio')
                    ->limit(30), // Corta el texto si es muy largo

                // BadgeColumn para el estado (Visualmente atractivo)
                Tables\Columns\TextColumn::make('status.name')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pendiente' => 'gray',
                        'En Proceso' => 'info',
                        'Completado' => 'success',
                        'Cancelado' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('urgency')
                    ->label('Urgencia')
                    ->badge()
                    ->color(fn ($record) => Color::hex($record->status->color ?? '#808080')), // Se ve mejor como "badge"


                Tables\Columns\TextColumn::make('order_date')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                // Filtros útiles para Filament 4
                Tables\Filters\SelectFilter::make('status')
                    ->relationship('status', 'name')
                    ->label('Filtrar por Estado'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
