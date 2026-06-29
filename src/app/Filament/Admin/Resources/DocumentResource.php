<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DocumentResource\Pages;
use App\Filament\Admin\Resources\DocumentResource\RelationManagers\ApprovalsRelationManager;
use App\Models\Document;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationGroup = 'Document Management';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Documents';

    protected static ?int $navigationSort = 2;

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
                    ->label('Document File')
                    ->disk('public')
                    ->directory('documents')
                    ->acceptedFileTypes([
                        'application/pdf',
                    ])
                    ->maxSize(10240)
                    ->required(),

                Forms\Components\Placeholder::make('preview')
                    ->label('PDF Preview')
                    ->visible(fn ($record) => filled($record?->file))
                    ->content(
                        fn ($record) => new HtmlString(
                            '<iframe
                                src="' . asset('storage/' . $record->file) . '"
                                width="100%"
                                height="700">
                            </iframe>'
                        )
                    )
                    ->columnSpanFull(),

                Forms\Components\Hidden::make('user_id')
                    ->default(fn () => auth()->id()),

                Forms\Components\Hidden::make('status')
                    ->default('pending'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Mahasiswa')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('documentType.name')
                    ->label('Jenis Dokumen')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('file')
                    ->label('File')
                    ->formatStateUsing(fn () => 'View PDF')
                    ->url(
                        fn ($record) => asset('storage/' . $record->file)
                    )
                    ->openUrlInNewTab(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'revision' => 'info',
                        'waiting_admin' => 'primary',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Submitted At')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                    ->visible(function ($record) {
                        return auth()->user()?->hasRole('Mahasiswa')
                            && $record->user_id === auth()->id()
                            && in_array($record->status, [
                                'pending',
                                'revision',
                            ]);
                    }),

                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->visible(fn ($record) => filled($record->file))
                    ->url(
                        fn ($record) => asset('storage/' . $record->file)
                    )
                    ->openUrlInNewTab(),

            ])
            ->bulkActions([]);
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
            return $query->whereIn('status', [
                'pending',
                'revision',
            ]);
        }

        return $query;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole('Mahasiswa') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasRole('Mahasiswa')
            && $record->user_id === auth()->id()
            && in_array($record->status, [
                'pending',
                'revision',
            ]);
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('Mahasiswa')
            && $record->user_id === auth()->id()
            && $record->status === 'pending';
    }

    public static function getRelations(): array
    {
        return [
            ApprovalsRelationManager::class,
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
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