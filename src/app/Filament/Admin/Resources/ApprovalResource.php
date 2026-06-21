<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ApprovalResource\Pages;
use App\Models\Approval;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ApprovalResource extends Resource
{
    protected static ?string $model = Approval::class;

    protected static ?string $navigationGroup = 'Document Management';

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $navigationLabel = 'Approvals';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('document_id')
                ->relationship('document', 'title')
                ->required()
                ->searchable()
                ->preload(),

                Forms\Components\Select::make('status')
                    ->options([
                        'approved' => 'Approved',
                        'revision' => 'Revision',
                        'rejected' => 'Rejected',
                    ])
                    ->required(),

                Forms\Components\Textarea::make('notes')
                    ->rows(4)
                    ->columnSpanFull(),

                Forms\Components\Hidden::make('approved_by')
                    ->default(auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('document.title')
                ->label('Document')
                ->searchable(),

                Tables\Columns\TextColumn::make('approver.name')
                    ->label('Reviewer'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'revision' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('approved_at')
                    ->dateTime('d M Y H:i'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y H:i'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'approved' => 'Approved',
                        'revision' => 'Revision',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        // Mahasiswa hanya melihat approval dokumennya
        if ($user?->hasRole('Mahasiswa')) {
            return $query->whereHas('document', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        // Admin hanya melihat dokumen yang menunggu final approval
        if ($user?->hasRole('Admin Akademik')) {
            return $query->whereHas(
                'document',
                fn ($q) => $q->where('status', 'waiting_admin')
            );
        }

        // Dosen melihat semua approval
        return $query;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasAnyRole([
            'Dosen',
            'Admin Akademik',
        ]) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasAnyRole([
            'Dosen',
            'Admin Akademik',
        ]) ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasAnyRole([
            'Dosen',
            'Admin Akademik',
        ]) ?? false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
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
}
