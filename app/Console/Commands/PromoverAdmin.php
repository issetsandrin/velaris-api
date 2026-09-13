<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class PromoverAdmin extends Command
{
    protected $signature = 'velaris:admin {email : E-mail do administrador} {--nome=Administrador} {--senha= : Senha inicial, só usada se a conta ainda não existir}';

    protected $description = 'Dá acesso ao painel administrativo para o e-mail informado, criando a conta se necessário';

    public function handle(): int
    {
        $email = strtolower(trim($this->argument('email')));
        $user = User::where('email', $email)->first();

        if (! $user) {
            $senha = $this->option('senha') ?: Str::password(14);
            $user = User::create([
                'name' => $this->option('nome'),
                'email' => $email,
                'password' => $senha,
                'email_verified_at' => now(),
            ]);
            $this->info("Conta criada para {$email}.");
            if (! $this->option('senha')) {
                $this->warn("Senha gerada: {$senha}");
            }
        }

        $user->update(['is_admin' => true]);
        $this->info("{$email} agora é administrador. Painel: ".rtrim(config('app.url'), '/').'/admin');

        return self::SUCCESS;
    }
}
