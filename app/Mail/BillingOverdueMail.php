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

class BillingOverdueMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Cliente $tenant,
        public ?string $billingUrl = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Atenção: assinatura em atraso',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.billing.overdue',
            with: [
                'tenant' => $this->tenant,
                'billingUrl' => $this->billingUrl ?? rtrim((string) config('app.url'), '/').'/painel/financeiro',
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
