<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;


class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'code' => 'AC',
                'name' => 'Injection mold'
            ],
            [
                'code' => 'VT',
                'name' => 'Tufting'
            ],
            [
                'code' => 'VI',
                'name' => 'Injection'
            ],
            [
                'code' => 'VP',
                'name' => 'Packing' 
            ]
        ]; 

        $admin = DB::table('users')->where('username', '=', 'admin')->first();

        foreach ($categories as $category) {
            DB::table('categories')->insert([
                'uuid' => Str::uuid()->toString(),
                'name' => $category['name'],
                'code' => $category['code']
            ]);
        }


    }
}
