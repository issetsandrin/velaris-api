<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class CodigoDeAcesso extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        private readonly string $codigo,
        private readonly Carbon $vencimento,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Seu código de acesso: '.$this->codigo);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.codigo-de-acesso',
            with: [
                'codigo' => $this->codigo,
                'minutos' => max(1, (int) ceil(now()->diffInMinutes($this->vencimento, absolute: true))),
            ],
        );
    }
}
