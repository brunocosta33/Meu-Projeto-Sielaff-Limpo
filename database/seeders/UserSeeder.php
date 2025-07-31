<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = array(
            [
                'id' => 1,
                'name' => 'Admin',
                'email' => 'admin@teste.com',
                'password' => Hash::make('QWERTY123'),
                'is_active' => true,
                'role_id' => '1'
            ],
            [
                'id' => 2,
                'name' => 'User',
                'email' => 'user@teste.com',
                'password' => Hash::make('QWERTY123'),
                'is_active' => true,
                'role_id' => '2'
            ]
        );


        $role_user = array(
            [
                'role_id' => '1',
                'user_id' => '1',
                'user_type' => "AppModelsUser"
            ],
            [
                'role_id' => '2',
                'user_id' => '2',
                'user_type' => "AppModelsUser"
            ]
        );
       

        DB::table('users')->insert($users);
        DB::table('role_user')->insert($role_user);
    }
}
