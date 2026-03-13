<?php

namespace App\Exceptions;

use Exception;

class ChangeEmailException extends Exception
{
    public function __construct(
        string $message = 'Impossibile modificare l\'email.',
        protected array $errors = [],
        protected int $status = 422
    ) {
        parent::__construct($message);
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
