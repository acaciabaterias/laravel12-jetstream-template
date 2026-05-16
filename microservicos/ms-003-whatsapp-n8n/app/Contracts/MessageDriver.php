<?php

namespace App\Contracts;

interface MessageDriver
{
    public function send(string $to, string $message, array $options = []): array;

    public function getStatus(string $messageId): string;
}
