<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

test('user can get permissions from all roles', function () {
    $user = User::factory()->create();

    $role1 = Role::create([
        'name' => 'user'
    ]);
    $role2 = Role::create([
        'name' => 'admin'
    ]);

    $permission1 = Permission::create([
        'name' => 'product.view'
    ]);
    $permission2 = Permission::create([
        'name' => 'product.edit'
    ]);

    $role1->permissions()->attach($permission1->id);
    $role2->permissions()->attach([
        $permission1->id,
        $permission2->id
    ]);

    $user->roles()->attach([
        $role1->id,
        $role2->id,
    ]);

    $permissions = $user->getPermissions();
    expect($permissions)->toHaveCount(2);
});

test('user has the role', function () {
    $user = User::factory()->create();

    $role = Role::create([
        'name' => 'admin'
    ]);

    $user->roles()->attach($role);

    expect($user->hasRole('admin'))->toBeTrue();
});

test('user does not have the role', function () {
    $user = User::factory()->create();

    expect($user->hasRole('admin'))->toBeFalse();
});

test('user has the permission', function () {
    $user = User::factory()->create();

    $role = Role::create([
        'name' => 'admin'
    ]);

    $permission = Permission::create([
        'name' => 'product.edit'
    ]);

    $role->permissions()->attach($permission->id);

    $user->roles()->attach($role->id);

    expect($user->hasPermission('product.edit'))->toBeTrue();
});

test('user does not have the permission', function () {
    $user = User::factory()->create();

    expect($user->hasPermission('product.edit'))->toBeFalse();
});

test('allows user with the permission', function () {
    $user = User::factory()->create();

    $role = Role::create([
        'name' => 'admin'
    ]);

    $permission = Permission::create([
        'name' => 'product.edit'
    ]);

    $role->permissions()->attach($permission->id);

    $user->roles()->attach($role->id);

    expect(Gate::forUser($user)->allows('permission', 'product.edit'))->toBeTrue();
});

test('denies user without the permission', function () {
    $user = User::factory()->create();

    expect(Gate::forUser($user)->allows('permission', 'product.edit'))->toBeFalse();
});
