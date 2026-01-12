<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notification extends Model
{
    protected $fillable = [
        'recipient_type',
        'recipient_id',
        'type',
        'title',
        'message',
        'data_json',
        'is_read',
        'sent_at',
    ];

    protected $casts = [
        'data_json' => 'array',
        'is_read'   => 'boolean',
        'sent_at'   => 'datetime',
    ];

    /**
     * Polymorphic recipient (Customer or Admin/User).
     */
    public function recipient(): MorphTo
    {
        return $this->morphTo(null, 'recipient_type', 'recipient_id');
    }
}
