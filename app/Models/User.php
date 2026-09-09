<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
    public function getPermissions()
    {
        $this->roles->load('permissions');

        return $this->roles
            ->pluck('permissions')
            ->flatten()
            ->unique('id')
            ->pluck('name');
    }
    public function hasRole(string $role)
    {
        $roles = $this->roles->pluck('name');

        return $roles->contains($role);
    }
    public function hasPermission(string $permission)
    {
        $permissions = $this->getPermissions();

        return $permissions->contains($permission);
    }
    public function customerTickets()
    {
        return $this->hasMany(Ticket::class, 'customer_id');
    }

    public function agentTickets()
    {
        return $this->hasMany(Ticket::class, 'agent_id');
    }
}
