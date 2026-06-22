<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Approval extends Model
{
    use HasFactory;
    use LogsActivity;

    protected static $logName = 'approval';

    protected $fillable = [
        'document_id',
        'approved_by',
        'status',
        'notes',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'status',
                'notes',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected static function booted(): void
    {
        static::created(function ($approval) {

            if (auth()->check()) {

                activity()
                    ->performedOn($approval->document)
                    ->causedBy(auth()->user())
                    ->event('approval')
                    ->log(
                        auth()->user()->name .
                        ' memberikan status "' .
                        $approval->status .
                        '" pada dokumen "' .
                        $approval->document->title .
                        '"'
                    );
            }
        });
    }
}