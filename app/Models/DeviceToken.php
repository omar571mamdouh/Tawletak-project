<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model
{
    protected $table = 'device_tokens';

    protected $fillable = [
        'owner_type',
        'owner_id',
        'token',
        'platform',
    ];
}
