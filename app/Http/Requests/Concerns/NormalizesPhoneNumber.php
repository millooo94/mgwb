<?php

namespace App\Http\Requests\Concerns;

use App\Support\PhoneNumberNormalizer;

trait NormalizesPhoneNumber
{
    protected function normalizePhone(mixed $phone): mixed
    {
        return PhoneNumberNormalizer::normalize($phone);
    }
}
