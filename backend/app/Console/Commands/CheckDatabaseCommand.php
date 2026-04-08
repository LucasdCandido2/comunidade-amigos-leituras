<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Work;

class CheckDatabase extends Command
{
    protected $signature = 'db:check';
    protected $description = 'Check database records';

    public function handle(): int
    {
        $this->info('Users: ' . User::count());
        $this->info('Works: ' . Work::count());
        
        $user = User::where('email', 'teste1234@teste.com')->first();
        if ($user) {
            $this->info('Test user found: ' . $user->email);
            $this->info('User roles: ' . $user->roles->pluck('name')->implode(', '));
        } else {
            $this->warn('Test user NOT found');
        }

        return Command::SUCCESS;
    }
}