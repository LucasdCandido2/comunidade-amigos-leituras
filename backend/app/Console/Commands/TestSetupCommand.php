<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Work;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestSetupCommand extends Command
{
    protected $signature = 'test:setup {--fresh : Executar migrate:fresh antes}';
    protected $description = 'Verifica se o ambiente de teste está configurado corretamente';

    public function handle(): int
    {
        $this->info('🔍 Verificando configuração do ambiente de teste...');
        $this->newLine();

        $hasErrors = false;

        // 1. Verificar usuário teste
        $this->info('1️⃣ Verificando usuário teste...');
        $user = User::where('email', 'teste1234@teste.com')->first();
        
        if (!$user) {
            $this->error('   ❌ Usuário teste NÃO encontrado');
            $hasErrors = true;
        } else {
            $this->info("   ✅ Usuário: {$user->name} (ID: {$user->id})");
            
            // Verificar cargo
            $user->load('roles');
            $role = $user->roles->first();
            
            if ($role && $role->name === 'owner') {
                $this->info("   ✅ Cargo: {$role->display_name} ({$role->name})");
            } else {
                $this->error("   ❌ Cargo: " . ($role ? $role->name : 'NENHUM'));
                $hasErrors = true;
            }
        }
        $this->newLine();

        // 2. Verificar obras
        $this->info('2️⃣ Verificando obras populadas...');
        $worksCount = Work::count();
        $animeCount = Work::where('type', 'anime')->count();
        $mangaCount = Work::where('type', 'manga')->count();
        $movieCount = Work::where('type', 'movie')->count();

        $this->info("   📊 Total: {$worksCount}");
        $this->info("   🎌 Anime: {$animeCount}");
        $this->info("   🈶 Manga: {$mangaCount}");
        $this->info("   🎬 Movies: {$movieCount}");

        if ($worksCount < 100) {
            $this->error("   ❌ OBras insuficientes! Esperado: >100, Atual: {$worksCount}");
            $hasErrors = true;
        } else {
            $this->info("   ✅ Obras populadas com sucesso");
        }
        $this->newLine();

        // 3. Verificar categorias
        $this->info('3️⃣ Verificando categorias...');
        $categoriesCount = Category::count();
        $this->info("   📁 Categorias: {$categoriesCount}");
        
        if ($categoriesCount < 10) {
            $this->warn("   ⚠️ Poucas categorias");
        } else {
            $this->info("   ✅ Categorias OK");
        }
        $this->newLine();

        // 4. Testar login
        $this->info('4️⃣ Testando login API...');
        try {
            $response = $this->call('test:login');
        } catch (\Exception $e) {
            $this->error("   ❌ Erro: {$e->getMessage()}");
            $hasErrors = true;
        }
        $this->newLine();

        // Resultado final
        if ($hasErrors) {
            $this->error('❌ VERIFICAÇÃO FALHOU!');
            $this->info('Execute: php artisan migrate:fresh --force && php artisan db:seed --force');
            return self::FAILURE;
        }

        $this->info('✅ VERIFICAÇÃO PASSOU - Ambiente pronto para testes!');
        return self::SUCCESS;
    }
}
