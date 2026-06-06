<?php

namespace App\Filament\Admin\Resources\DocumentTypeResource\Pages;

use App\Filament\Admin\Resources\DocumentTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDocumentType extends EditRecord
{
    protected static string $resource = DocumentTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
