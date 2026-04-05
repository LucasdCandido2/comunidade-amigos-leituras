<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Work;
use App\Models\Topic;
use App\Models\Interaction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $user = DB::table('users')->updateOrInsert(
            ['email' => 'teste1234@teste.com'],
            [
                'name' => 'Lucas Teste',
                'password' => Hash::make('teste12345'),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $user = User::where('email', 'teste1234@teste.com')->first();

        $ownerRole = Role::where('name', 'owner')->first();
        if ($ownerRole && $user) {
            $user->roles()->syncWithoutDetaching($ownerRole);
        }

        $work = DB::table('works')->updateOrInsert(
            ['title' => 'One Piece', 'user_id' => $user->id ?? 1],
            [
                'description' => 'Mangá de Eiichiro Oda sobre piratas e tesouros',
                'type' => 'manga',
                'canonical_url' => 'https://onepiece.fandom.com/',
                'bayesian_rating' => 4.5,
                'is_user_suggested' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $work = Work::where('title', 'One Piece')->first();

        $topic = DB::table('topics')->updateOrInsert(
            [
                'title' => 'Meu primeiro tópico de teste',
                'user_id' => $user->id ?? 1,
            ],
            [
                'work_id' => $work->id ?? null,
                'content' => "Olá! Este é um tópico de exemplo para testes.\n\nAqui você pode compartilhar suas experiências de leitura, anime e mangá.\n\n[spoiler]O final do mangá é incrível![/spoiler]",
                'rating' => 5,
                'rating_count' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $topic = Topic::where('title', 'Meu primeiro tópico de teste')->first();

        if ($topic && $user) {
            DB::table('interactions')->updateOrInsert(
                [
                    'user_id' => $user->id,
                    'topic_id' => $topic->id,
                    'content' => 'Adorei este tópico! Ótima experiência compartilhada.',
                ],
                [
                    'rating' => 5,
                    'is_visible' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info('✅ Dados de teste criados com sucesso!');
        $this->command->info('📧 Email: teste1234@teste.com');
        $this->command->info('🔑 Senha: teste12345');
        $this->command->info('📚 Obra: One Piece');
        $this->command->info('📝 Tópico: Meu primeiro tópico de teste');
    }
}
