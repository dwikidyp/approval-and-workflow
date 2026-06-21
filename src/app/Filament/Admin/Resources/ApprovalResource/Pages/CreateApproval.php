<?php

namespace App\Filament\Admin\Resources\ApprovalResource\Pages;

use App\Filament\Admin\Resources\ApprovalResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Document;

class CreateApproval extends CreateRecord
{
    protected static string $resource = ApprovalResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['approved_by'] = auth()->id();
        $data['approved_at'] = now();

        return $data;
    }

    protected function afterCreate(): void
    {
        $approval = $this->record;

        $document = Document::find($approval->document_id);

        if (! $document) {
            return;
        }

        if ($approval->status === 'revision') {
            $document->update([
                'status' => 'revision',
            ]);
        }

        if ($approval->status === 'rejected') {
            $document->update([
                'status' => 'rejected',
            ]);
        }

        if ($approval->status === 'approved') {

            if (auth()->user()->hasRole('Dosen')) {

                $document->update([
                    'status' => 'waiting_admin',
                ]);
            }

            if (auth()->user()->hasRole('Admin Akademik')) {

                $document->update([
                    'status' => 'approved',
                ]);
            }
        }
    }
}
