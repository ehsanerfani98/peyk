<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $table = 'roles';

    public function getTitleAttribute(): string
    {
        return $this->attributes['title'] ?: $this->name;
    }
}
