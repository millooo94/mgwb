<?php

namespace App\Support;

class PhoneNumberNormalizer
{
    public static function normalize(mixed $phone): mixed
    {
        if (! is_string($phone)) {
            return $phone;
        }

        $phone = trim($phone);

        if ($phone === '') {
            return $phone;
        }

        // Tiene solo cifre e +
        $phone = preg_replace('/[^\d+]/', '', $phone);

        // 0039... -> +39...
        if (str_starts_with($phone, '00')) {
            $phone = '+' . substr($phone, 2);
        }

        // Se ha già il +, lo lasciamo così
        if (str_starts_with($phone, '+')) {
            return $phone;
        }

        $digitsOnly = preg_replace('/\D/', '', $phone);

        // Caso cellulare italiano locale:
        // 3925905360 -> +393925905360
        if (preg_match('/^3\d{9}$/', $digitsOnly)) {
            return '+39' . $digitsOnly;
        }

        // Caso fisso italiano locale completo:
        // 0951234567 -> +390951234567
        if (preg_match('/^0\d{5,10}$/', $digitsOnly)) {
            return '+39' . $digitsOnly;
        }

        // Caso già in formato internazionale senza +
        // 393925905360 -> +393925905360
        if (str_starts_with($digitsOnly, '39')) {
            return '+' . $digitsOnly;
        }

        // Fallback: aggiunge +39
        return '+39' . $digitsOnly;
    }
}
