<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class PhoneVerificationException extends Exception
{
    protected array $errors;
    protected int $status;

    public function __construct(
        string $message = 'Impossibile verificare il numero di telefono.',
        array $errors = [],
        int $status = 422
    ) {
        parent::__construct($message);

        $this->errors = $errors;
        $this->status = $status;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function status(): int
    {
        return $this->status;
    }

    public function render($request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'errors' => $this->errors,
        ], $this->status);
    }
}
