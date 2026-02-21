<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Role::where('name', 'admin')->first();
        if (!$admin) {
            $roleAdmin = Role::create(['name' => 'admin', 'guard_name' => 'api']);
            $roleAdmin->givePermissionTo(Permission::all());
        }

        $store = Role::where('name', 'store')->first();
        if (!$store) {
            $roleStore = Role::create(['name' => 'store', 'guard_name' => 'api']);
        }
        $customer = Role::where('name', 'customer')->first();
        if (!$customer) {
            $rolecustomer = Role::create(['name' => 'customer', 'guard_name' => 'api']);
        }
    }
}
