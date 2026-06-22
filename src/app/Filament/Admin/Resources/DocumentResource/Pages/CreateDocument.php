<?php

namespace App\Filament\Admin\Resources\DocumentResource\Pages;

use App\Filament\Admin\Resources\DocumentResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\User;
use Filament\Notifications\Notification;


class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['submitted_at'] = now();

        return $data;
    }

    protected function afterCreate(): void
    {
        $dosen = User::role('Dosen')->get();

        foreach ($dosen as $user) {

            Notification::make()
                ->title('Dokumen Baru')
                ->body(
                    auth()->user()->name .
                    ' mengajukan dokumen "' .
                    $this->record->title . '"'
                )
                ->sendToDatabase($user);
        }
    }
}
