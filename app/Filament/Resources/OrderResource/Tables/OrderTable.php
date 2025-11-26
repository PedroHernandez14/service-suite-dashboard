<?php

namespace App\Filament\Resources\OrderResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrderTable
{
    public static function configure(Table $table) : Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true), // Oculto por defecto en v4

                TextColumn::make('user.username')
                    ->label('Local')
                    ->searchable(),

                TextColumn::make('service.description')
                    ->label('Servicio')
                    ->limit(30), // Corta el texto si es muy largo

                // BadgeColumn para el estado (Visualmente atractivo)
                TextColumn::make('status.name')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pendiente' => 'gray',
                        'En Proceso' => 'info',
                        'Completado' => 'success',
                        'Cancelado' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('urgency')
                    ->label('Urgencia')
                    ->badge()
                    ->color(fn ($record) => Color::hex($record->status->color ?? '#808080')), // Se ve mejor como "badge"


                TextColumn::make('order_date')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                // Filtros útiles para Filament 4
                SelectFilter::make('status')
                    ->relationship('status', 'name')
                    ->label('Filtrar por Estado'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                bulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
