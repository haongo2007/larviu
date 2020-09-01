<?php

use App\Larviu\Models\User;
use App\Larviu\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Larviu\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@Larviu.dev',
            'password' => Hash::make('larviu'),
        ]);
        $manager = User::create([
            'name' => 'Manager',
            'email' => 'manager@Larviu.dev',
            'password' => Hash::make('larviu'),
        ]);
        $editor = User::create([
            'name' => 'Editor',
            'email' => 'editor@Larviu.dev',
            'password' => Hash::make('larviu'),
        ]);
        $user = User::create([
            'name' => 'User',
            'email' => 'user@Larviu.dev',
            'password' => Hash::make('larviu'),
        ]);
        $visitor = User::create([
            'name' => 'Visitor',
            'email' => 'visitor@Larviu.dev',
            'password' => Hash::make('larviu'),
        ]);

        $adminRole = Role::findByName(\App\Larviu\Acl::ROLE_ADMIN);
        $managerRole = Role::findByName(\App\Larviu\Acl::ROLE_MANAGER);
        $editorRole = Role::findByName(\App\Larviu\Acl::ROLE_EDITOR);
        $userRole = Role::findByName(\App\Larviu\Acl::ROLE_USER);
        $visitorRole = Role::findByName(\App\Larviu\Acl::ROLE_VISITOR);
        $admin->syncRoles($adminRole);
        $manager->syncRoles($managerRole);
        $editor->syncRoles($editorRole);
        $user->syncRoles($userRole);
        $visitor->syncRoles($visitorRole);
        $this->call(UsersTableSeeder::class);
        //$this->call(CategoryTableSeeder::class);
    }
}
