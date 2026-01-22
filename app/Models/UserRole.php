<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UserRole extends Model
{
    protected $table = 'user_roles';

    protected $fillable = [
        'name',
        'label',
        'description',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_roles_assignment',
            'role_id',
            'user_id'
        );
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            UserPermission::class,
            'user_role_permissions',
            'role_id',
            'permission_id'
        );
    }
}
