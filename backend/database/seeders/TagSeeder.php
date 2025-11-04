<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tags')->insert([
            [
                'id' => Str::uuid(),
                'name' => 'Vue.js',
                'slug' => 'vue-js',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Laravel',
                'slug' => 'laravel',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => 'JavaScript',
                'slug' => 'javascript',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Web Development',
                'slug' => 'web-development',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}