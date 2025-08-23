<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleManager = DB::table('roles')->where('name', '=', 'manager')->first();

        $roleUser = DB::table('roles')->where('name', '=', 'user')->first();

        $admin = DB::table('users')->where('username', '=', 'admin')->first();

        $positions = [
            'Office A',
            'Office B',
            'Factory C',
            'Factory D'
        ];

        for ($i = 1; $i <= 3; $i++) {
            DB::table('users')->insert([
                'uuid' => Str::uuid()->toString(),
                'username' => fake()->userName(),
                'password' => Hash::make('password'), // password 8 ký tự
                'role_id' => $roleManager->uuid, // Gán đúng UUID,
                'employment_name' => fake()->name(),
                'employment_id' => 'VH0000' . strval($i + 1000),
                'position' => $positions[array_rand($positions, 1)],
            ]);
        }

        for ($i = 1; $i <= 10; $i++) {
            DB::table('users')->insert([
                'uuid' => Str::uuid()->toString(),
                'username' => fake()->userName(),
                'password' => Hash::make('password'), // password 8 ký tự
                'role_id' => $roleUser->uuid, // Gán đúng UUID,
                'employment_name' => fake()->name(),
                'employment_id' => 'VH0000' . strval($i + 1000),
                'position' => $positions[array_rand($positions, 1)],
            ]);
        }


    }
}
