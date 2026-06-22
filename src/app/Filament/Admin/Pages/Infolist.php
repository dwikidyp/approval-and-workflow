<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;

class Infolist extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.admin.pages.infolist';

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\TextEntry::make('title'),

                \Filament\Infolists\Components\TextEntry::make('documentType.name')
                    ->label('Document Type'),

                \Filament\Infolists\Components\TextEntry::make('status')
                    ->badge(),

                \Filament\Infolists\Components\TextEntry::make('description'),

                \Filament\Infolists\Components\TextEntry::make('file')
                    ->label('Document File')
                    ->url(
                        fn ($record) => asset('storage/' . $record->file)
                    )
                    ->openUrlInNewTab(),
            ]);
    }
}
