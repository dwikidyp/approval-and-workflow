<?php

namespace App\Filament\Admin\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Spatie\Activitylog\Models\Activity;

class RecentActivities extends TableWidget
{
    protected static ?string $heading = 'Recent Activities';

    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Activity::query()
                    ->latest()
                    ->limit(20)
            )
            ->columns([
                Tables\Columns\TextColumn::make('causer.name')
                    ->label('User')
                    ->searchable(),

                Tables\Columns\TextColumn::make('description')
                    ->wrap(),

                Tables\Columns\TextColumn::make('event')
                    ->badge(),

                Tables\Columns\TextColumn::make('created_at')
                    ->since()
                    ->label('Time'),
            ]);
    }
}