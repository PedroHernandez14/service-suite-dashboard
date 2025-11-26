<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonResource\Pages;
use App\Filament\Resources\PersonResource\RelationManagers\ContactsRelationManager;
use App\Models\Person;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PersonResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Person::class;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-user-group';

    protected static string|null|\UnitEnum $navigationGroup = 'Configuración';
    protected static ?string $modelLabel = 'Persona';
    protected static ?string $pluralModelLabel = 'Personas';
    protected static ?string $slug = 'personas';

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // --- SECCIÓN 1: DATOS PERSONALES ---
                Section::make('Datos Personales')
                    ->columnSpanFull()
                    ->components([
                        Grid::make(2)
                            ->components([
                                Forms\Components\TextInput::make('first_name')
                                    ->label('Nombres')
                                    ->required()
                                    ->maxLength(100),

                                Forms\Components\TextInput::make('last_name')
                                    ->label('Apellidos')
                                    ->required()
                                    ->maxLength(100),
                            ]),

                        Grid::make(2)
                            ->components([
                                Grid::make(4)
                                    ->components([
                                        Forms\Components\Select::make('identification_type_id')
                                            ->label('Tipo')
                                            ->relationship('identificationType', 'acronym') // Muestra V, E, J...
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->default(1) // Por defecto selecciona el ID 1 (V)
                                            ->selectablePlaceholder(false)
                                            ->columnSpan(1),

                                        Forms\Components\TextInput::make('dni')
                                            ->label('Doc. de Identidad')
                                            ->required()
                                            ->maxLength(20)
                                            ->unique(ignoreRecord: true)
                                            ->columnSpan(3), // Ocupa 3/4 del espacio
                                    ])
                                    ->columnSpan(1),

                                Forms\Components\DatePicker::make('birth_date')
                                    ->label('Fecha de Nacimiento')
                                    ->required()
                                    ->native(false) // Usa el calendario JS de Filament
                                    ->displayFormat('d/m/Y') // Formato visual amigable
                                    ->maxDate(now())
                                    ->prefixIcon('heroicon-m-calendar'), // No pueden nacer en el futuro
                            ]),
                    ]),
            ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            // Importante: Cargar relaciones para evitar el problema N+1 (lentitud)
            ->modifyQueryUsing(fn ($query) => $query->with(['identificationType', 'contacts']))
            ->columns([
                // 1. Nombre Completo (Combinado)
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nombre Completo')
                    ->getStateUsing(fn (Person $record) => "{$record->first_name} {$record->last_name}")
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(query: function ($query, string $direction) {
                        return $query->orderBy('last_name', $direction);
                    })
                    ->weight('bold'),

                // 2. Documento (Acrónimo + Número)
                Tables\Columns\TextColumn::make('dni')
                    ->label('Documento')
                    ->getStateUsing(fn (Person $record) =>
                        // "V-12345678"
                    $record->identificationType
                        ? "{$record->identificationType->acronym}-{$record->dni}"
                        : $record->dni
                    )
                    ->searchable(['dni', 'identificationType.acronym'])
                    ->copyable(),

                // 3. Email (Buscamos el primero en la relación 'contacts')
                Tables\Columns\TextColumn::make('email_contact')
                    ->label('Email Principal')
                    ->icon('heroicon-m-envelope')
                    ->getStateUsing(function (Person $record) {
                        // Filtramos en memoria los contactos cargados
                        $email = $record->contacts->firstWhere('type', 'email');
                        return $email ? $email->value : '-';
                    })
                    ->copyable(),

                // 4. Teléfono (Buscamos el primero y formateamos)
                Tables\Columns\TextColumn::make('phone_contact')
                    ->label('Teléfono Principal')
                    ->icon('heroicon-m-phone')
                    ->getStateUsing(function (Person $record) {
                        $phone = $record->contacts->firstWhere('type', 'phone');
                        // "(+58) 412 123 4567"
                        return $phone
                            ? "({$phone->prefix}) {$phone->value}"
                            : '-';
                    })
                    ->copyable(),

                // Fecha de Registro
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filtro por Tipo de Documento (Útil)
                Tables\Filters\SelectFilter::make('identification_type')
                    ->relationship('identificationType', 'name')
                    ->label('Tipo de Documento'),
            ])
            ->actions([
                EditAction::make()
                    ->modalWidth('2xl'), // Ajuste para que el Repeater se vea bien
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
            ContactsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageServices::route('/'),
            'create' => Pages\CreatePerson::route('/create'),
            'edit' => Pages\EditPerson::route('/{record}/edit'),
        ];
    }
}
