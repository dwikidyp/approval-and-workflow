<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ApprovalResource\Pages;
use App\Models\Approval;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Notifications\DocumentWorkflowNotification;

class ApprovalResource extends Resource
{
    protected static ?string $model = Approval::class;

    protected static ?string $navigationGroup = 'Document Management';

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $navigationLabel = 'Approvals';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Placeholder::make('info')
                    ->content(
                        'Approval dibuat otomatis ketika mahasiswa mengunggah dokumen.'
                    ),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')

            ->columns([

                Tables\Columns\TextColumn::make('document.title')
                    ->label('Document')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('document.user.name')
                    ->label('Mahasiswa')
                    ->searchable(),

                Tables\Columns\TextColumn::make('approver.name')
                    ->label('Reviewer')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('document.status')
                    ->label('Document Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'waiting_admin' => 'warning',
                        'approved' => 'success',
                        'revision' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('notes')
                    ->limit(50)
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('approved_at')
                    ->label('Approved At')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])

            ->filters([

                Tables\Filters\SelectFilter::make('document_status')
                    ->label('Document Status')
                    ->options([
                        'pending' => 'Pending',
                        'waiting_admin' => 'Waiting Admin',
                        'approved' => 'Approved',
                        'revision' => 'Revision',
                        'rejected' => 'Rejected',
                    ])
                    ->query(function (Builder $query, array $data) {

                        if (! filled($data['value'])) {
                            return;
                        }

                        $query->whereHas('document', function ($q) use ($data) {
                            $q->where('status', $data['value']);
                        });
                    }),
            ])

            ->actions([

                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(function ($record) {

                        return auth()->user()?->hasRole('Dosen')
                            && $record->status === 'pending'
                            && $record->document?->status === 'pending';
                    })
                    ->requiresConfirmation()
                    ->action(function ($record) {

                        if ($record->status !== 'pending') {
                            return;
                        }

                        $record->update([
                            'status' => 'approved',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);

                        $record->document->update([
                            'status' => 'waiting_admin',
                        ]);

                        $admins = User::role('Admin Akademik')->get();

                        foreach ($admins as $admin) {
                            $admin->notify(
                                new DocumentWorkflowNotification(
                                    'Dokumen Menunggu Persetujuan',
                                    'Dokumen "' .
                                    $record->document->title .
                                    '" telah disetujui dosen dan menunggu persetujuan Anda.'
                                )
                            );
                        }

                        $record->document->user->notify(
                            new DocumentWorkflowNotification(
                                'Dokumen Lolos Review Dosen',
                                'Dokumen telah disetujui dosen dan menunggu persetujuan Admin Akademik.'
                            )
                        );
                    }),

                Tables\Actions\Action::make('finalApprove')
                    ->label('Final Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(function ($record) {

                        return auth()->user()?->hasRole('Admin Akademik')
                            && $record->document?->status === 'waiting_admin';
                    })
                    ->requiresConfirmation()
                    ->action(function ($record) {

                        $record->update([
                            'status' => 'approved',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);

                        $record->document->update([
                            'status' => 'approved',
                        ]);

                        $record->document->user->notify(
                            new DocumentWorkflowNotification(
                                'Dokumen Disetujui',
                                'Dokumen "' .
                                $record->document->title .
                                '" telah disetujui.'
                            )
                        );

                        if ($record->approver) {

                            $record->approver->notify(
                                new DocumentWorkflowNotification(
                                    'Dokumen Selesai Diproses',
                                    'Dokumen "' .
                                    $record->document->title .
                                    '" telah disetujui Admin Akademik.'
                                )
                            );
                        }
                    }),

                Tables\Actions\Action::make('revision')
                    ->label('Revision')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->visible(function ($record) {

                        return auth()->user()?->hasRole('Dosen')
                            && $record->status === 'pending';
                    })
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan Revisi')
                            ->required(),
                    ])
                    ->action(function (array $data, $record) {

                        $record->update([
                            'status' => 'revision',
                            'notes' => $data['notes'],
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);

                        $record->document->update([
                            'status' => 'revision',
                        ]);

                        $record->document->user->notify(
                            new DocumentWorkflowNotification(
                                'Dokumen Perlu Revisi',
                                'Dokumen "' .
                                $record->document->title .
                                '" memerlukan revisi. Catatan: ' .
                                $data['notes']
                            )
                        );

                        logger()->info('Notification sent', [
                            'user' => $record->document->user->id,
                        ]);
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(function ($record) {

                        return auth()->user()?->hasRole('Admin Akademik')
                            && $record->document?->status === 'waiting_admin';
                    })
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->action(function (array $data, $record) {

                        $record->update([
                            'status' => 'rejected',
                            'notes' => $data['notes'],
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);

                        $record->document->update([
                            'status' => 'rejected',
                        ]);

                        $record->document->user->notify(
                            new DocumentWorkflowNotification(
                                'Dokumen Ditolak',
                                'Dokumen "' .
                                $record->document->title .
                                '" ditolak. Alasan: ' .
                                $data['notes']
                            )
                        );
                    }),
            ])

            ->bulkActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        if (! $user) {
            return $query;
        }

        if ($user->hasRole('super_admin')) {
            return $query;
        }

        if ($user->hasRole('Mahasiswa')) {

            return $query->whereHas('document', function ($q) use ($user) {

                $q->where('user_id', $user->id);

            });
        }

        if ($user->hasRole('Dosen')) {

            return $query->whereHas('document', function ($q) {

                $q->where('status', 'pending');

            });
        }

        if ($user->hasRole('Admin Akademik')) {

            return $query->whereHas('document', function ($q) {

                $q->where('status', 'waiting_admin');

            });
        }

        return $query;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasAnyRole([
            'super_admin',
            'Dosen',
            'Admin Akademik',
        ]) ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApprovals::route('/'),
            'create' => Pages\CreateApproval::route('/create'),
            'edit' => Pages\EditApproval::route('/{record}/edit'),
        ];
    }
}