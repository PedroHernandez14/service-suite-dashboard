<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactResource\Pages;
use App\Models\Contact;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-phone';

    protected static ?string $modelLabel = 'Contacto';
    protected static ?string $pluralModelLabel = 'Contactos';
    protected static ?string $slug = 'contactos';
    public static function form(Schema $schema): Schema
    {
        // This defines the form for the ContactResource's own pages (Create/Edit)
        return $schema
            ->components([
                Section::make('Datos de Contacto')
                    ->columns(2)
                    ->components(static::formSchema())
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')->label('Tipo'),
                TextColumn::make('value')->label('Dato'),
                TextColumn::make('label')->label('Etiqueta'),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            //
        ];
    }

    /**
     * This reusable schema is now used by the Relation Manager
     * and the resource's own form.
     */
    public static function formSchema(): array
    {
        return [
            Forms\Components\Select::make('type')
                ->label('Tipo')
                ->options([
                    'email' => '✉️ Correo',
                    'phone' => '📱 Teléfono',
                    'whatsapp' => '💬 WhatsApp',
                ])
                ->live()
                ->required()
                ->afterStateUpdated(fn (Set $set) => $set('prefix', '+58'))
                ->columnSpanFull(),

            Forms\Components\Select::make('prefix')
                ->label('Prefijo')
                ->options(['+58' => '🇻🇪 +58', '+1' => '🇺🇸 +1', '+34' => '🇪🇸 +34'])
                ->default('+58')
                ->visible(fn (Get $get) => in_array($get('type'), ['phone', 'whatsapp'])),

            Forms\Components\TextInput::make('value')
                ->label('Dato')
                ->required()
                ->email(fn (Get $get) => $get('type') === 'email')
                ->tel(fn (Get $get) => in_array($get('type'), ['phone', 'whatsapp']))
                ->placeholder(fn (Get $get) => in_array($get('type'), ['phone', 'whatsapp']) ? 'Ej: 4123456789' : 'Ej: example@gmail.com')
                ->columnSpan(fn (Get $get) => in_array($get('type'), ['phone', 'whatsapp']) ? 1 : 2),

            Forms\Components\TextInput::make('label')
                ->label('Etiqueta')
                ->placeholder('Ej: Principal, Trabajo...')
                ->columnSpanFull(),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContacts::route('/'),
            'create' => Pages\CreateContact::route('/create'),
            'edit' => Pages\EditContact::route('/{record}/edit'),
        ];
    }
}
