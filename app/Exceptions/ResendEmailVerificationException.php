<?php

namespace App\Exceptions;

use Exception;

class ResendVerificationEmailException extends Exception
{
    public function __construct(
        string $message = 'Impossibile reinviare l\'email di verifica.',
        protected array $errors = [],
        protected int $status = 422
    ) {
        parent::__construct($message, $status);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function status(): int
    {
        return $this->status;
    }
}