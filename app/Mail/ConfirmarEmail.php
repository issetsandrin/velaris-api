<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfirmarEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        private readonly string $token,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Confirme seu e-mail na Velaris');
    }

    public function content(): Content
    {
        $minutos = (int) config('velaris.acesso.confirmacao_validade');

        return new Content(
            view: 'emails.confirmar-email',
            with: [
                'link' => rtrim((string) config('velaris.loja_url'), '/').'/confirmar-email?token='.$this->token,
                'horas' => max(1, (int) round($minutos / 60)),
            ],
        );
    }
}
