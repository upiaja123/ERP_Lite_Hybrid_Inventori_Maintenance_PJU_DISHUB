<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncLog extends Model
{
    use HasFactory;

    protected $table = 'sync_logs';

    protected $fillable = [
        'sync_type',
        'source',
        'target',
        'dataset_name',
        'payload_hash',
        'payload_reference',
        'status',
        'records_synced',
        'attempts',
        'error_message',
        'conflict_details',
        'started_at',
        'completed_at',
        'triggered_by',
    ];

    protected $casts = [
        'payload_reference' => 'array',
        'conflict_details'  => 'array',
        'started_at'        => 'datetime',
        'completed_at'      => 'datetime',
    ];

    public function triggerUser()
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}
