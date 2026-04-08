<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestLoginCommand extends Command
{
    protected $signature = 'test:login';
    protected $description = 'Testa login do usuário teste';

    public function handle(): int
    {
        $response = Http::acceptJson()
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])
            ->timeout(10)
            ->post('http://comunidade_web/api/login', [
                'email' => 'teste1234@teste.com',
                'password' => 'teste12345'
            ]);
        
        if ($response->successful() && $response->json('token')) {
            $this->info('   ✅ Login OK - Token gerado');
            return self::SUCCESS;
        }
        
        $this->error('   ❌ Login falhou');
        $this->info('   Resposta: ' . $response->body());
        return self::FAILURE;
    }
}
