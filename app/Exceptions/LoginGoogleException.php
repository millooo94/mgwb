<?php

namespace App\Exceptions;

use Exception;

class LoginGoogleException extends Exception
{
    public function __construct(
        string $message = 'Accesso con Google non riuscito.',
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
