<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Filament\Resources\CompanyResource\RelationManagers;
use App\Filament\Resources\PersonResource\RelationManagers\ContactsRelationManager;
use App\Models\Company;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $modelLabel = 'Empresa';
    protected static ?string $pluralModelLabel = 'Empresas';
    protected static ?string $slug = 'empresas';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // --- SECCIÓN 1: DATOS PERSONALES ---
                Section::make('Datos Empresariales')
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
                                    ->label('Tipo Doc.')
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['identificationType', 'contacts']))
            ->columns([
                // 1. Nombre Completo (Combinado)
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre Completo')
                    ->getStateUsing(fn (Company $record) => "{$record->name}")
                    ->searchable(['name'])
                    ->sortable(query: function ($query, string $direction) {
                        return $query->orderBy('name', $direction);
                    })
                    ->weight('bold'),

                // 2. Documento (Acrónimo + Número)
                Tables\Columns\TextColumn::make('identification_number')
                    ->label('R.I.F.')
                    ->getStateUsing(fn (Company $record) =>
                        // "V-12345678"
                    $record->identificationType
                        ? "{$record->identificationType->acronym}-{$record->identification_number}"
                        : $record->identification_number
                    )
                    ->searchable(['identification_number', 'identificationType.acronym'])
                    ->copyable(),

                // 3. Email (Buscamos el primero en la relación 'contacts')
                Tables\Columns\TextColumn::make('email_contact')
                    ->label('Email Principal')
                    ->icon('heroicon-m-envelope')
                    ->getStateUsing(function (Company $record) {
                        // Filtramos en memoria los contactos cargados
                        $email = $record->contacts->firstWhere('type', 'email');
                        return $email ? $email->value : '-';
                    })
                    ->copyable(),

                // 4. Teléfono (Buscamos el primero y formateamos)
                Tables\Columns\TextColumn::make('phone_contact')
                    ->label('Teléfono Principal')
                    ->icon('heroicon-m-phone')
                    ->getStateUsing(function (Company $record) {
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
                //
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
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
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'view' => Pages\ViewCompany::route('/{record}'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
