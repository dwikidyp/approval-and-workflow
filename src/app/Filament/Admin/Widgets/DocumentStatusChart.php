<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Document;
use Filament\Widgets\ChartWidget;

class DocumentStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Document Status';

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Documents',
                    'data' => [
                        Document::where('status', 'pending')->count(),
                        Document::where('status', 'revision')->count(),
                        Document::where('status', 'waiting_admin')->count(),
                        Document::where('status', 'approved')->count(),
                        Document::where('status', 'rejected')->count(),
                    ],
                ],
            ],
            'labels' => [
                'Pending',
                'Revision',
                'Waiting Admin',
                'Approved',
                'Rejected',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}