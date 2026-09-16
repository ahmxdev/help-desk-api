<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\SetUserRoleRequest;
use App\Models\Role;
use App\Models\User;

class UserController extends Controller
{
    public function setRole(SetUserRoleRequest $request, User $user)
    {
        $roleName = $request->validated('role');
        $role = Role::where('name', $roleName)->firstOrFail();

        if ($user->hasRole('admin')) {
            return response()->json([
                'message' => 'Cannot change admin roles.'
            ], 403);
        }

        if (! $user->hasRole($role->name)) {
            $user->roles()->sync([$role->id]);
        }

        return response()->json([
            'message' => 'Role has been set.'
        ], 200);
    }
}
