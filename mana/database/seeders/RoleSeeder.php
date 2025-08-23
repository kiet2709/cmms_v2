<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = DB::table('users')->where('username', '=', 'admin')->first();
        $roles = ['manager', 'user'];

        foreach ($roles as $role) {
            DB::table('roles')->insert([
                'uuid' => Str::uuid()->toString(),
                'name' => $role,
            ]);
        }

    }
}
