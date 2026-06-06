<?php

namespace App\Filament\Admin\Resources\DocumentTypeResource\Pages;

use App\Filament\Admin\Resources\DocumentTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDocumentType extends CreateRecord
{
    protected static string $resource = DocumentTypeResource::class;
}
