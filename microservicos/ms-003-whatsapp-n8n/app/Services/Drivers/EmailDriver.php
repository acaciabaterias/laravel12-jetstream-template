<?php

namespace App\Services\Drivers;

use App\Contracts\MessageDriver;
use Illuminate\Support\Str;

class EmailDriver implements MessageDriver
{
    public function send(string $to, string $message, array $options = []): array
    {
        return [
            'status' => 'success',
            'channel' => 'email',
            'message_id' => (string) Str::uuid(),
            'to' => $to,
            'message' => $message,
        ];
    }

    public function getStatus(string $messageId): string
    {
        return 'connected';
    }
}
