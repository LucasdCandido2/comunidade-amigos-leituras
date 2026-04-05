<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->updateOrInsert([
            'name' => 'Lucas Teste',
            'email' => 'lucas1234@teste.com',
            'password' => Hash::make('lucas12345'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
