<?php

namespace App\Filament\Admin\Resources\DocumentResource\Pages;

use App\Filament\Admin\Resources\DocumentResource;
use App\Models\Approval;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['submitted_at'] = now();
        $data['status'] = 'pending';

        return $data;
    }

    protected function afterCreate(): void
    {
        Approval::create([
            'document_id' => $this->record->id,
            'status' => 'pending',
        ]);

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