<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DocumentResource\Pages;
use App\Models\Document;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationGroup = 'Document Management';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Documents';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('document_type_id')
                ->relationship('documentType', 'name')
                ->required()
                ->searchable()
                ->preload(),

            Forms\Components\TextInput::make('title')
                ->required()
                ->maxLength(255),

            Forms\Components\Textarea::make('description')
                ->rows(4)
                ->columnSpanFull(),

            Forms\Components\FileUpload::make('file')
                ->directory('documents')
                ->required()
                ->downloadable()
                ->openable(),

            Forms\Components\Hidden::make('user_id')
                ->default(auth()->id()),

            Forms\Components\Hidden::make('status')
                ->default('pending'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Mahasiswa')
                    ->searchable(),

                Tables\Columns\TextColumn::make('documentType.name')
                    ->label('Jenis Dokumen')
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'revision' => 'info',
                        'waiting_admin' => 'primary',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('submitted_at')
                    ->dateTime('d M Y H:i'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y H:i'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'revision' => 'Revision',
                        'waiting_admin' => 'Waiting Admin',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn () => auth()->user()?->hasRole('Mahasiswa')),
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

        $user = auth()->user();

        if ($user?->hasRole('Mahasiswa')) {

            return $query->where(
                'user_id',
                $user->id
            );
        }

        if ($user?->hasRole('Dosen')) {

            return $query->whereIn(
                'status',
                [
                    'pending',
                    'revision',
                    'waiting_admin',
                ]
            );
        }

        if ($user?->hasRole('Admin Akademik')) {

            return $query->where(
                'status',
                'waiting_admin'
            );
        }

        return $query;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole('Mahasiswa') ?? false;
    }

    public static function canDelete($record): bool
    {
        return $record->status === 'pending';
    }

        public static function canEdit($record): bool
    {
        return in_array($record->status, [
            'pending',
            'revision',
        ]);
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
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }
}
