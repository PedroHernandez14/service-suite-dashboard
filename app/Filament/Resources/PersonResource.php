<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonResource\Pages;
use App\Models\Person;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PersonResource extends Resource
{
    protected static ?string $model = Person::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Configuración';
    protected static ?string $modelLabel = 'Persona';
    protected static ?string $pluralModelLabel = 'Personas';
    protected static ?string $slug = 'personas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // --- SECCIÓN 1: DATOS PERSONALES ---
                Section::make('Datos Personales')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('first_name')
                                    ->label('Nombres')->required()->maxLength(100),
                                Forms\Components\TextInput::make('last_name')
                                    ->label('Apellidos')->required()->maxLength(100),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Grid::make(4)
                                    ->schema([
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

                // --- SECCIÓN 2: CONTACTO PRINCIPAL (Obligatorio) ---
                Section::make('Datos de Contacto')
                    ->description('Medios de comunicación primarios (Guardados en ficha)')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Grid::make(9)
                                    ->schema([
                                        Forms\Components\Select::make('main_phone_prefix')
                                            ->label('País')
                                            ->options([
                                                '+58' => '🇻🇪 +58',
                                                '+1'  => '🇺🇸 +1',
                                                '+34' => '🇪🇸 +34',
                                                // ... más países
                                            ])
                                            ->default('+58')
                                            ->selectablePlaceholder(false)
                                            ->formatStateUsing(fn ($record) => $record?->contacts->where('type', 'phone')->first()?->prefix ?? '+58')
                                            ->dehydrated(false)
                                            ->columnSpan(3),

                                        Forms\Components\TextInput::make('main_phone')
                                            ->label('Teléfono Principal')
                                            ->tel()
                                            ->mask('999 999 9999')
                                            ->required()
                                            ->length(12)
                                            ->formatStateUsing(fn ($record) => $record?->contacts->where('type', 'phone')->first()?->value)
                                            ->dehydrated(false)
                                            ->columnSpan(6),
                                    ])
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('main_email')
                                    ->label('Correo Principal')
                                    ->email()
                                    ->required()
                                    ->prefixIcon('heroicon-m-envelope')
                                    ->formatStateUsing(fn ($record) => $record?->contacts->where('type', 'email')->first()?->value)
                                    ->dehydrated(false)

                                // Grid para Prefijo + Teléfono Principal
                            ]),
                    ]),

                Section::make('Datos Adicionales')
                    ->description('Correos o teléfonos secundarios (Opcional)')
                    ->collapsed()
                    ->schema([
                        Repeater::make('contacts') // Nombre de la relación
                        ->relationship()
                            ->hiddenLabel() // Oculta la etiqueta "Contacts" para que se vea limpio
                            ->addActionLabel('Agregar nuevo contacto')
                            ->defaultItems(1) // Muestra 1 vacío al empezar
                            ->grid(2) // Muestra los contactos en 2 columnas (opcional, se ve bien)
                            ->schema([
                                // 1. Tipo
                                Forms\Components\Select::make('type')
                                    ->label('Tipo')
                                    ->options([
                                        'email' => '✉️ Correo',
                                        'phone' => '📱 Teléfono',
                                        'whatsapp' => '💬 WhatsApp',
                                    ])
                                    ->live()
                                    ->required(false)// Reactivo
                                    ->afterStateUpdated(fn (Forms\Set $set) => $set('prefix', '+58')) // Default al cambiar
                                    ->columnSpanFull(), // Ocupa todo el ancho de la tarjetita

                                // 2. Prefijo (Solo teléfonos)
                                Forms\Components\Select::make('prefix')
                                    ->label('Prefijo')
                                    ->options(['+58' => '🇻🇪 +58', '+1' => '🇺🇸 +1', '+34' => '🇪🇸 +34'])
                                    ->default('+58')
                                    ->visible(fn (Get $get) => in_array($get('type'), ['phone', 'whatsapp']))
                                    ->columnSpan(1),

                                // 3. Valor
                                Forms\Components\TextInput::make('value')
                                    ->label('Dato')
                                    ->email(fn (Get $get) => $get('type') === 'email')
                                    ->mask(fn (Get $get) => in_array($get('type'), ['phone', 'whatsapp']) ? '999 999 9999' : null)
                                    // Si es email ocupa todo, si es teléfono comparte espacio con prefijo
                                    ->columnSpan(fn (Get $get) => in_array($get('type'), ['phone', 'whatsapp']) ? 2 : 3),

                                // 4. Etiqueta
                                Forms\Components\TextInput::make('label')
                                    ->label('Etiqueta')
                                    ->placeholder('Ej: Principal, Trabajo...')
                                    ->columnSpanFull(),
                            ])
                            ->columns(3), // Grid interno del item del repeater
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
                Tables\Actions\EditAction::make()
                    ->modalWidth('2xl'), // Ajuste para que el Repeater se vea bien
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
            'index' => Pages\ListPeople::route('/'),
            'create' => Pages\CreatePerson::route('/create'),
            'edit' => Pages\EditPerson::route('/{record}/edit'),
        ];
    }
}
