<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\NotificationResource\Pages;
use App\Models\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NotificationResource extends Resource
{
    protected static ?string $model = Notification::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static ?string $navigationLabel = 'Notifications';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 99;

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([

                Tables\Columns\TextColumn::make('data.title')
                    ->label('Title')
                    ->searchable(),

                Tables\Columns\TextColumn::make('data.body')
                    ->label('Message')
                    ->limit(80),

                Tables\Columns\IconColumn::make('read_at')
                    ->label('Read')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Received')
                    ->since(),

            ])
            ->headerActions([

                Tables\Actions\Action::make('markAllRead')
                    ->label('Mark All Read')
                    ->icon('heroicon-o-check-badge')
                    ->action(function () {

                        auth()
                            ->user()
                            ->unreadNotifications
                            ->markAsRead();

                    }),

            ])
            ->actions([

                Tables\Actions\Action::make('markRead')
                    ->label('Mark Read')
                    ->icon('heroicon-o-check')
                    ->visible(fn ($record) => $record->read_at === null)
                    ->action(fn ($record) => $record->markAsRead()),

            ])
            ->bulkActions([]);
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) auth()
            ->user()
            ?->unreadNotifications()
            ->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('notifiable_id', auth()->id())
            ->where('notifiable_type', \App\Models\User::class);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return true;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotifications::route('/'),
        ];
    }
}