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
use Illuminate\Support\Facades\Artisan;

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

        $this->command->info("User ID: " . $user?->id);

        $ownerRole = Role::where('name', 'owner')->first();
        
        $this->command->info("Owner Role ID: " . $ownerRole?->id);

        if ($ownerRole && $user) {
            DB::table('user_role')->updateOrInsert(
                [
                    'user_id' => $user->id,
                    'role_id' => $ownerRole->id,
                ]
            );
            $this->command->info("Role assigned directly to user_role table");
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

        $this->command->info('⏳ Populando obras...');
        $this->populateWorks();
    }

    private function populateWorks(): void
    {
        $sources = [
            ['source' => 'jikan', 'type' => 'anime', 'limit' => 10],
            ['source' => 'jikan', 'type' => 'manga', 'limit' => 10],
            ['source' => 'omdb', 'type' => 'movie', 'limit' => 20],
        ];

        foreach ($sources as $index => $config) {
            $this->command->info("  📡 Populando {$config['type']} via {$config['source']}...");
            
            // Delay para evitar rate limiting (Jikan permite 3 req/s)
            if ($index > 0) {
                sleep(2);
            }
            
            try {
                Artisan::call('works:populate', [
                    '--source' => $config['source'],
                    '--type' => $config['type'],
                    '--limit' => $config['limit'],
                ]);
                $output = Artisan::output();
                if (preg_match('/Concluído! (\d+)/', $output, $matches)) {
                    $this->command->info("     ✅ {$matches[1]} obras de {$config['type']}");
                } else {
                    $this->command->info("     ✅ Concluído");
                }
                
                // Delay adicional entre fontes diferentes
                sleep(2);
                
            } catch (\Exception $e) {
                $this->command->warn("     ❌ Erro: {$e->getMessage()}");
            }
        }
    }
}
