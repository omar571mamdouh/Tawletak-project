<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRolePermission extends Model
{
    protected $table = 'user_role_permissions';

    // لأن الجدول pivot ومفيهوش id ولا timestamps
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'role_id',
        'permission_id',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(UserRole::class, 'role_id');
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(UserPermission::class, 'permission_id');
    }
}
