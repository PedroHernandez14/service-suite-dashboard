<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StatusResource\Pages;
use App\Filament\Resources\StatusResource\RelationManagers;
use App\Models\Status;
use Filament\Forms;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StatusResource extends Resource
{
    protected static ?string $model = Status::class;

    // Icono de "Etiqueta" (Ideal para estados)
    protected static ?string $navigationIcon = 'heroicon-o-tag';

    // Agrupamos este recurso para que no desordene el menú principal
    protected static ?string $navigationGroup = 'Configuración';

    protected static ?string $modelLabel = 'Estado';
    protected static ?string $pluralModelLabel = 'Estados';
    protected static ?string $slug = 'estados';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del Estado')
                    ->description('Define los estados posibles para las órdenes y servicios')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(100)
                            // Validación única: Evita duplicados, pero ignora el registro actual al editar
                            ->unique(ignoreRecord: true)
                            ->placeholder('Ej: En Proceso, Pendiente, Cancelado')
                            ->columnSpanFull(),
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
                    // Hacemos que la sección sea estrecha para que se vea bien en pantallas grandes
                    // ya que tiene pocos campos.
                    ->columns(1)
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

                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold') // Negrita para destacar el nombre
                    ->badge()
                    ->color(fn ($record) => Color::hex($record->color ?? '#808080')), // Se ve mejor como "badge"

                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // No hacen falta filtros complejos aquí por ahora
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
