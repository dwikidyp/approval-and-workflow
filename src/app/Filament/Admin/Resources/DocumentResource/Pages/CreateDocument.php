<?php

namespace App\Filament\Admin\Resources\DocumentResource\Pages;

use App\Filament\Admin\Resources\DocumentResource;
use Filament\Resources\Pages\CreateRecord;


class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['submitted_at'] = now();

        return $data;
    }
}
