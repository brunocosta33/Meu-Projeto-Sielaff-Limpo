<?php

use Database\Seeders\UserSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            [
                'name' => "admin",
                'display_name' => "Admin",
                'description' => 'Permissões para ver e gerenciar o sistema.'
            ],
            [
                'name' => "user",
                'display_name' => "User",
                'description' => 'Permissões para visualização dos registos no sistema.'
            ]
        ];

        $permissions = [
            [
                'name' => "view_leftmenu",
                'display_name' => "View Left Menu",
                'description' => 'Ver menu de acesso à esquerda.'
            ],
            [
                'name' => "view_users",
                'display_name' => "View Users",
                'description' => 'Ver utilizadores.'
            ],
            [
                'name' => "view_roles",
                'display_name' => "View Roles",
                'description' => 'Ver cargos.'
            ],
            [
                'name' => "view_loginactivity",
                'display_name' => "View Login Activity",
                'description' => 'Ver atividades de login.'
            ],
            [
                'name' => "view_permissions",
                'display_name' => "View Permissions",
                'description' => 'Ver permissões.'
            ],
            [
                'name' => "view_configurations",
                'display_name' => "View Configurations",
                'description' => 'Ver configurações.'
            ],
            [
                'name' => "manage_users",
                'display_name' => "Manage Users",
                'description' => 'Gerenciar utilizadores.'
            ],
            [
                'name' => "manage_roles",
                'display_name' => "Manage Roles",
                'description' => 'Gerenciar cargos.'
            ],
            [
                'name' => "manage_permissions",
                'display_name' => "Manage Permissions",
                'description' => 'Gerenciar permissões.'
            ],
            [
                'name' => "manage_configurations",
                'display_name' => "Manage Configurations",
                'description' => 'Gerenciar configurações.'
            ]
        ];        

        $rolesPermissions = [
            [
                'role_id' => 1,
                'permission_id' => 1                
            ],
            [
                'role_id' => 1,
                'permission_id' => 2
            ],
            [
                'role_id' => 1,
                'permission_id' => 3
            ],
            [
                'role_id' => 1,
                'permission_id' => 4
            ],
            [
                'role_id' => 1,
                'permission_id' => 5
            ],
            [
                'role_id' => 1,
                'permission_id' => 6
            ],
            [
                'role_id' => 1,
                'permission_id' => 7
            ],
            [
                'role_id' => 1,
                'permission_id' => 8
            ],
            [
                'role_id' => 1,
                'permission_id' => 9
            ],
            [
                'role_id' => 1,
                'permission_id' => 10
            ],
            [
                'role_id' => 2,
                'permission_id' => 1
            ],
            [
                'role_id' => 2,
                'permission_id' => 2
            ],
            [
                'role_id' => 2,
                'permission_id' => 3
            ],
            [
                'role_id' => 2,
                'permission_id' => 4
            ],
            [
                'role_id' => 2,
                'permission_id' => 5
            ],
            [
                'role_id' => 2,
                'permission_id' => 6
            ]
        ];

        DB::table('roles')->insert($roles);
        DB::table('permissions')->insert($permissions);        
        DB::table('permission_role')->insert($rolesPermissions);

        $this->call(UserSeeder::class);
    }
}
