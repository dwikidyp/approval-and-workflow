<?php

namespace App\Filament\Admin\Widgets;

use Spatie\Activitylog\Models\Activity;
use Filament\Widgets\TableWidget;
use Filament\Tables\Table;
use Filament\Tables;

class RecentActivities extends TableWidget
{
    protected static ?int $sort = 5;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Activity::query()->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('causer.name')
                    ->label('User'),

                Tables\Columns\TextColumn::make('description'),

                Tables\Columns\TextColumn::make('created_at')
                    ->since(),
            ]);
    }
}