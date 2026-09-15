<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RedefinirSenha extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        private readonly string $token,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Redefinir a senha da sua conta Velaris');
    }

    public function content(): Content
    {
        $link = rtrim((string) config('velaris.loja_url'), '/')
            .'/redefinir-senha?token='.$this->token
            .'&email='.urlencode($this->user->email);

        return new Content(
            view: 'emails.redefinir-senha',
            with: [
                'link' => $link,
                'minutos' => (int) config('auth.passwords.users.expire'),
            ],
        );
    }
}
