<?php

use App\Models\Permission;
use App\Models\Role;

test('returns true if role has the permission', function () {
    $role = Role::create([
        'name' => 'admin'
    ]);

    $permission = Permission::create([
        'name' => 'product.edit'
    ]);

    $role->permissions()->attach($permission);

    expect($role->hasPermission('product.edit'))->toBeTrue();
});

test('returns false if role does not have the permission', function () {
    $role = Role::create([
        'name' => 'admin'
    ]);

    expect($role->hasPermission('product.edit'))->toBeFalse();
});
