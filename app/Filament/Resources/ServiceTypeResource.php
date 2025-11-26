<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceTypeResource\Pages;
use App\Filament\Resources\ServiceTypeResource\RelationManagers;
use App\Models\ServiceType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\ColorPicker;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceTypeResource extends Resource
{
    protected static ?string $model = ServiceType::class;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|null|\UnitEnum $navigationGroup = 'Configuración';
    protected static ?string $modelLabel = 'Tipo de Servicio';
    protected static ?string $pluralModelLabel = 'Tipos de Servicio';
    protected static ?string $slug = 'tipos-servicio';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalles del Tipo')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true)
                            ->placeholder('Ej: Mantenimiento Preventivo')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('status_id')
                            ->relationship('status', 'name')
                            ->label('Estado')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)

                            // TRUCO PRO: Permitir crear un tipo nuevo desde aquí mismo
                            ->createOptionForm([
                                Section::make('Nuevo Estatus')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nombre del Estado')
                                            ->required()
                                            ->maxLength(100)
                                            ->unique('statuses', 'name'), // Validación única para que no repitan
                                        ColorPicker::make('color')
                                            ->label('Color del Tag')
                                            ->default('#808080') // Un gris por defecto (Hex)
                                            ->format('hex')
                                            ->required(),
                                        Forms\Components\Textarea::make('description')
                                            ->label('Descripción')
                                            ->maxLength(255)
                                            ->rows(3),
                                    ])
                                    ->columns(1),
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->maxLength(255),
                    ])
                    ->columns(1)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                // Mostramos el estado con un badge de color
                Tables\Columns\TextColumn::make('status.name')
                    ->label('Estado')
                    ->badge()
                    ->color(fn ($record) => Color::hex($record->status->color ?? '#808080')), // Se ve mejor como "badge"

            Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(50)
                    ->toggleable(), // El usuario puede ocultar esta columna si quiere

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filtro por Estado (Muy útil)
                Tables\Filters\SelectFilter::make('status')
                    ->relationship('status', 'name')
                    ->label('Estado'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
