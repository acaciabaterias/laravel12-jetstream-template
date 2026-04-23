<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Bateria;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LowStockAlertMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Bateria $bateria,
        public int $saldoAtual,
        public ?string $inventoryUrl = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Alerta de estoque baixo',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.inventory.low-stock',
            with: [
                'bateria' => $this->bateria,
                'saldoAtual' => $this->saldoAtual,
                'inventoryUrl' => $this->inventoryUrl ?? rtrim((string) config('app.url'), '/').'/dashboard',
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
