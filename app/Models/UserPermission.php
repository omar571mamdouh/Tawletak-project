<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UserPermission extends Model
{
    protected $table = 'user_permissions';

    protected $fillable = [
        'name',
        'label',
        'module',
        'description',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            UserRole::class,
            'user_role_permissions',
            'permission_id',
            'role_id'
        );
    }
}
