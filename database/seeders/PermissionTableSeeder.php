<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            'manage-books',
            'borrow-books',
            'return-books',
            'view-users',

        ];



        foreach ($permissions as $permission) {

            Permission::create(['name' => $permission]);
        }

        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);

        $adminRole->givePermissionTo(['manage-books', 'view-users', 'borrow-books', 'return-books']);
        $userRole->givePermissionTo(['borrow-books', 'return-books']);
    }
}
