<?php

namespace App\Services\Notifications;

use Twilio\Rest\Client;

class SmsSender
{
    protected Client $client;
    protected string $verifyServiceSid;

    public function __construct()
    {
        $this->client = new Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );

        $this->verifyServiceSid = config('services.twilio.verify_service_sid');
    }

    /**
     * Invia OTP via Twilio Verify
     */
    public function sendVerification(string $phone): void
    {
        $this->client->verify->v2->services($this->verifyServiceSid)
            ->verifications
            ->create($phone, 'sms');
    }

    /**
     * Verifica OTP inserito dall'utente
     */
    public function checkVerification(string $phone, string $code): bool
    {
        $check = $this->client->verify->v2->services($this->verifyServiceSid)
            ->verificationChecks
            ->create([
                'to' => $phone,
                'code' => $code
            ]);

        return $check->status === 'approved';
    }
}
