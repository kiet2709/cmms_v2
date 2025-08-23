<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roleUuid = Str::uuid()->toString();
        // Tạo role admin
        DB::table('roles')->insert([
            'uuid' => $roleUuid,
            'name' => 'admin',
        ]);

        DB::table('users')->insert([
            'uuid' => Str::uuid()->toString(),
            'username' => 'admin',
            'password' => Hash::make('password'), // password 8 ký tự
            'role_id' => $roleUuid, // Gán đúng UUID,
            'employment_id' => 'VH00001000',
            'employment_name' => 'Admin',
            'position' => 'Admin',
        ]);

        $admin = DB::table('users')->where('username', '=', 'admin')->first();


        $this->call(RoleSeeder::class);

        $this->call(UserSeeder::class);

        $this->call(CategorySeeder::class);

        $this->call(EquipmentSeeder::class);
    
    }
}
