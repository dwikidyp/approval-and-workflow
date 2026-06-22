<?php

namespace App\Filament\Admin\Resources\DocumentResource\RelationManagers;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ApprovalsRelationManager extends RelationManager
{
    protected static string $relationship = 'approvals';

    protected static ?string $title = 'Approval History';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('status')
                    ->options([
                        'approved' => 'Approved',
                        'revision' => 'Revision',
                        'rejected' => 'Rejected',
                    ])
                    ->required(),

                Forms\Components\Textarea::make('notes')
                    ->rows(4),

                Forms\Components\Hidden::make('approved_by')
                    ->default(fn () => auth()->id()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('approver.name')
                    ->label('Reviewer')
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'revision' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('notes')
                    ->limit(50),

                Tables\Columns\TextColumn::make('approved_at')
                    ->dateTime('d M Y H:i'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Review Document')

                    ->visible(function () {

                        $document = $this->getOwnerRecord();

                        $user = auth()->user();

                        if (! $user) {
                            return false;
                        }

                        // Dosen hanya review pending/revision
                        if ($user->hasRole('Dosen')) {

                            return in_array(
                                $document->status,
                                [
                                    'pending',
                                    'revision',
                                ]
                            );
                        }

                        // Admin hanya review waiting_admin
                        if ($user->hasRole('Admin Akademik')) {

                            return $document->status === 'waiting_admin';
                        }

                        return false;
                    })

                    ->mutateFormDataUsing(function (array $data): array {

                        $data['approved_by'] = auth()->id();

                        $data['approved_at'] = $data['approved_at'] ?? now();

                        return $data;
                    })

                    ->after(function ($record) {

                        $document = $record->document;

                        switch ($record->status) {

                            case 'revision':

                                $document->update([
                                    'status' => 'revision',
                                ]);

                                Notification::make()
                                    ->title('Dokumen Perlu Revisi')
                                    ->body(
                                        'Silakan revisi dokumen "' .
                                        $document->title .
                                        '"'
                                    )
                                    ->sendToDatabase(
                                        $document->user
                                    );

                                break;

                            case 'rejected':

                                $document->update([
                                    'status' => 'rejected',
                                ]);

                                Notification::make()
                                    ->title('Dokumen Ditolak')
                                    ->body(
                                        'Dokumen "' .
                                        $document->title .
                                        '" ditolak'
                                    )
                                    ->sendToDatabase(
                                        $document->user
                                    );

                                break;

                            case 'approved':

                                // Approval oleh Dosen
                                if (auth()->user()->hasRole('Dosen')) {

                                    $document->update([
                                        'status' => 'waiting_admin',
                                    ]);

                                    $admins = User::role(
                                        'Admin Akademik'
                                    )->get();

                                    foreach ($admins as $admin) {

                                        Notification::make()
                                            ->title(
                                                'Menunggu Final Approval'
                                            )
                                            ->body(
                                                'Dokumen "' .
                                                $document->title .
                                                '" telah disetujui dosen'
                                            )
                                            ->sendToDatabase(
                                                $admin
                                            );
                                    }
                                }

                                // Approval oleh Admin Akademik
                                if (
                                    auth()->user()->hasRole(
                                        'Admin Akademik'
                                    )
                                ) {

                                    $document->update([
                                        'status' => 'approved',
                                    ]);

                                    Notification::make()
                                        ->title('Dokumen Disetujui')
                                        ->body(
                                            'Dokumen "' .
                                            $document->title .
                                            '" telah disetujui'
                                        )
                                        ->sendToDatabase(
                                            $document->user
                                        );
                                }

                                break;
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }
}