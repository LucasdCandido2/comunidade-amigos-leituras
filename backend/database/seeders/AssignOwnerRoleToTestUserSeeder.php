<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AssignOwnerRoleToTestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'lucas1234@teste.com')->first();

        if (!$user) {
            echo "Usuario lucas12345 não encontrado.\n";
            return;
        }

        $ownerRole = Role::where('name', 'owner')->first();

        if (!$ownerRole) {
            echo "Cargo 'owner' não encontrado\n";
            return;
        }

        $user->roles()->syncWithoutDetaching($ownerRole);

        echo "Cargo 'Dono' atribuidoao usuario lucas12345 com sucesso.\n";
    }
}
