<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\SetUserRoleRequest;
use App\Http\Resources\User\UsersIndexResource;
use App\Models\Role;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->get();
        return UsersIndexResource::collection($users);
    }
    public function setRole(SetUserRoleRequest $request, User $user)
    {
        $roleName = $request->validated('role');

        if ($user->hasRole('admin')) {
            return response()->json([
                'message' => 'Cannot change admin roles.'
            ], 403);
        }

        if (! $user->hasRole($roleName)) {
            $user->assignRole($roleName);
        }

        return response()->json([
            'message' => 'Role has been set.'
        ], 200);
    }
}
