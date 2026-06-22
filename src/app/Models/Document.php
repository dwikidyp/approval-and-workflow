<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;

class Document extends Model
{
    use HasFactory;
    use LogsActivity;

    protected static $logName = 'document';

    protected $fillable = [
        'user_id',
        'document_type_id',
        'title',
        'description',
        'file',
        'status',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function approvals()
    {
        return $this->hasMany(Approval::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('document')
            ->logOnly([
                'title',
                'status',
                'document_type_id',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected static function booted(): void
    {
        static::created(function ($document) {

            if (auth()->check()) {

                activity()
                    ->performedOn($document)
                    ->causedBy(auth()->user())
                    ->event('upload')
                    ->log(
                        'Mengunggah dokumen: ' .
                        $document->title
                    );
            }
        });
    }

    public function activities()
    {
        return $this->morphMany(
            Activity::class,
            'subject'
        );
    }
}