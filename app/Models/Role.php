<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name'])]
class Role extends Model
{
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
    public function hasPermission(string $permission)
    {
        $permissions = $this->permissions->pluck('name');

        return $permissions->contains($permission);
    }
}
