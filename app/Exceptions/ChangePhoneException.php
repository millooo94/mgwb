<?php

namespace App\Exceptions;

use Exception;

class ChangePhoneException extends Exception
{
    public function __construct(
        string $message = 'Impossibile modificare il numero di telefono.',
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
