<?php
declare(strict_types=1);

namespace App\Service;

class MobileAuthException extends \RuntimeException
{
    public function __construct(
        string $message,
        private readonly int $status,
        private readonly string $errorCode,
    ) {
        parent::__construct($message);
    }

    public function status(): int
    {
        return $this->status;
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }
}
