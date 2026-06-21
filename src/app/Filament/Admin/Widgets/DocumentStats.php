<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Document;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DocumentStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $query = Document::query();

        $user = auth()->user();

        if ($user?->hasRole('Mahasiswa')) {
            $query->where('user_id', $user->id);
        }

        return [
            Stat::make('Total Documents', (clone $query)->count()),

            Stat::make(
                'Pending',
                (clone $query)
                    ->where('status', 'pending')
                    ->count()
            ),

            Stat::make(
                'Approved',
                (clone $query)
                    ->where('status', 'approved')
                    ->count()
            ),

            Stat::make(
                'Rejected',
                (clone $query)
                    ->where('status', 'rejected')
                    ->count()
            ),
        ];
    }
}