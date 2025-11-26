<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-user';

    protected static string|null|\UnitEnum $navigationGroup = 'Configuración';
    protected static ?string $modelLabel = 'Usuario';
    protected static ?string $pluralModelLabel = 'Usuarios';
    protected static ?string $slug = 'usuarios';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos Generales del Usuario')
                    ->columnSpanFull()
                    ->components([
                        Grid::make(2)
                            ->components([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre de Usuario')
                                    ->required()
                                    ->prefixIcon('heroicon-m-user'),

                                Forms\Components\TextInput::make('email')
                                    ->label('Correo Electronico')
                                    ->email()
                                    ->required()
                                    ->prefixIcon('heroicon-m-envelope')

                            ]),
                        Grid::make(2)
                            ->components([
                                Forms\Components\TextInput::make('password')
                                    ->label('Contraseña')
                                    ->password()
                                    ->revealable()
                                    ->required(fn (string $context): bool => $context === 'create') // Requerido solo al crear
                                    ->dehydrated(fn ($state) => filled($state)) // Solo se guarda si se llena
                                    ->dehydrateStateUsing(fn ($state) => Hash::make($state)) // Hashea la contraseña con bcrypt
                                    ->rule(Password::min(8)->max(16)->mixedCase()->numbers()->symbols()) // Reglas de validación complejas
                                    ->prefixIcon('heroicon-m-key'),

                                Forms\Components\TextInput::make('password_confirmation')
                                    ->label('Confirmar Contraseña')
                                    ->password()
                                    ->revealable()
                                    ->required(fn (string $context): bool => $context === 'create')
                                    ->same('password') // Debe coincidir con el campo de contraseña
                                    ->dehydrated(false) // No guardar este campo en la BD
                                    ->prefixIcon('heroicon-m-key'),
                            ]),
                        Grid::make(2)
                            ->components([
                                Forms\Components\Select::make('person_id')
                                    ->relationship(name: 'person', titleAttribute: 'dni') // Usamos un atributo real como base
                                    ->label('Persona')
                                    ->getOptionLabelFromRecordUsing(fn($record) => $record->dni_and_full_name)
                                    ->required()
                                    // Buscamos en las columnas reales de la BD
                                    ->searchable(['dni', 'first_name', 'last_name'])
                                    ->preload()
                                    ->required()
                                    ->native(false)

                                    // TRUCO PRO: Permitir crear un tipo nuevo desde aquí mismo
                                    ->createOptionForm([
                                        Section::make('Nueva Persona')
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
                                    ]),
                                Forms\Components\Select::make('company_id')
                                    ->relationship(name: 'company', titleAttribute: 'identification_number') // Usamos un atributo real como base
                                    ->label('Empresa')
                                    ->getOptionLabelFromRecordUsing(fn($record) => $record->rif_and_name)
                                    ->required()
                                    // Buscamos en las columnas reales de la BD
                                    ->searchable(['identification_number', 'name'])
                                    ->preload()
                                    ->required()
                                    ->native(false)

                                    // TRUCO PRO: Permitir crear un tipo nuevo desde aquí mismo
                                    ->createOptionForm([
                                        Section::make('Nueva Empresa')
                                            ->columnSpanFull()
                                            ->components([
                                                Grid::make(12)
                                                    ->components([
                                                        Forms\Components\TextInput::make('name')
                                                            ->label('Nombre de la Empresa')
                                                            ->required()
                                                            ->maxLength(255)
                                                            ->columnSpan(6),

                                                        Forms\Components\Select::make('identification_type_id')
                                                            ->label('Tipo')
                                                            ->relationship('identificationType', 'acronym') // Muestra V, E, J...
                                                            ->searchable()
                                                            ->preload()
                                                            ->required()
                                                            ->default(3) // Por defecto selecciona el ID 1 (V)
                                                            ->selectablePlaceholder(false)
                                                            ->columnSpan(1),

                                                        Forms\Components\TextInput::make('identification_number')
                                                            ->label('R.I.F.')
                                                            ->required()
                                                            ->maxLength(20)
                                                            ->unique(ignoreRecord: true)
                                                            ->columnSpan(5), // Ocupa 3/4 del espacio
                                                    ]),
                                                Grid::make(2)
                                                    ->components([
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
                                                        Textarea::make('address')
                                                            ->label('Dirección')
                                                            ->required()
                                                            ->rows(2)
                                                            ->maxLength(255),
                                                    ])
                                            ]),
                                    ]),
                            ])
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre de Usuario')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Correo Electrónico')
                    ->searchable(),
                Tables\Columns\TextColumn::make('company.name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('person.dni')
                    ->label('DNI')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make()
                    // Oculta el botón de editar si el usuario es el ID 1
                    ->visible(fn (User $record): bool => $record->id !== 1),
                DeleteAction::make()
                    // Oculta el botón de eliminar si el usuario es el ID 1
                    ->visible(fn (User $record): bool => $record->id !== 1),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        // Deshabilita la casilla de selección para el usuario con ID 1
                        ->deselectRecordsAfterCompletion()
                        ->action(fn (\Illuminate\Database\Eloquent\Collection $records) => $records->filter(fn ($record) => $record->id !== 1)->each->delete()),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
