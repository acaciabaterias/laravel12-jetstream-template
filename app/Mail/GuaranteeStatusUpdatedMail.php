<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\OrdemServicoGarantia;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GuaranteeStatusUpdatedMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public OrdemServicoGarantia $ordemServico,
        public ?string $portalUrl = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Atualização da sua garantia',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.guarantees.status-updated',
            with: [
                'ordemServico' => $this->ordemServico,
                'portalUrl' => $this->portalUrl ?? rtrim((string) config('app.url'), '/').'/dashboard',
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
