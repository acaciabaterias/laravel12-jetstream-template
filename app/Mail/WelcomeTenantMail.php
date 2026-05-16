<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Cliente;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeTenantMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Cliente $tenant,
        public ?string $adminUrl = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bem-vindo ao BateriaExpert',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.tenants.welcome',
            with: [
                'tenant' => $this->tenant,
                'adminUrl' => $this->adminUrl ?? rtrim((string) config('app.url'), '/').'/painel',
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
