<?php

namespace App\Services\Auth;

use App\Exceptions\LoginAppleException;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;
use Throwable;

class AppleIdentityService
{
    public function verifyIdentityToken(string $identityToken, ?string $expectedNonce = null): array
    {
        try {
            $jwksResponse = Http::timeout(10)->get('https://appleid.apple.com/auth/keys');

            if (! $jwksResponse->successful()) {
                throw new LoginAppleException(
                    message: 'Impossibile verificare il token Apple.',
                    errors: [
                        'identity_token' => ['Impossibile verificare il token Apple.'],
                    ],
                    status: 422
                );
            }

            $keys = JWK::parseKeySet($jwksResponse->json(), 'ES256');

            $decoded = JWT::decode($identityToken, $keys);
            $claims = json_decode(json_encode($decoded), true);

            if (($claims['iss'] ?? null) !== 'https://appleid.apple.com') {
                throw new LoginAppleException(
                    message: 'Issuer Apple non valido.',
                    errors: [
                        'identity_token' => ['Issuer Apple non valido.'],
                    ],
                    status: 422
                );
            }

            if (($claims['aud'] ?? null) !== config('services.apple.client_id')) {
                throw new LoginAppleException(
                    message: 'Audience Apple non valida.',
                    errors: [
                        'identity_token' => ['Audience Apple non valida.'],
                    ],
                    status: 422
                );
            }

            if (! empty($expectedNonce) && ($claims['nonce'] ?? null) !== $expectedNonce) {
                throw new LoginAppleException(
                    message: 'Nonce Apple non valido.',
                    errors: [
                        'nonce' => ['Nonce Apple non valido.'],
                    ],
                    status: 422
                );
            }

            if (empty($claims['sub'])) {
                throw new LoginAppleException(
                    message: 'Identificativo Apple non valido.',
                    errors: [
                        'identity_token' => ['Identificativo Apple non valido.'],
                    ],
                    status: 422
                );
            }

            return $claims;
        } catch (LoginAppleException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new LoginAppleException(
                message: 'Token Apple non valido.',
                errors: [
                    'identity_token' => ['Token Apple non valido.'],
                ],
                status: 422
            );
        }
    }

    public function validateAuthorizationCode(string $authorizationCode): void
    {
        $clientId = (string) config('services.apple.client_id');

        if ($clientId === '') {
            throw new LoginAppleException(
                message: 'Configurazione Apple mancante.',
                errors: [
                    'apple' => ['Configurazione Apple mancante.'],
                ],
                status: 500
            );
        }

        $response = Http::asForm()
            ->timeout(10)
            ->post('https://appleid.apple.com/auth/token', [
                'client_id' => $clientId,
                'client_secret' => $this->generateClientSecret(),
                'code' => $authorizationCode,
                'grant_type' => 'authorization_code',
            ]);

        if (! $response->successful()) {
            throw new LoginAppleException(
                message: 'Codice Apple non valido o scaduto.',
                errors: [
                    'authorization_code' => ['Codice Apple non valido o scaduto.'],
                ],
                status: 422
            );
        }
    }

    protected function generateClientSecret(): string
    {
        $teamId = (string) config('services.apple.team_id');
        $clientId = (string) config('services.apple.client_id');
        $keyId = (string) config('services.apple.key_id');
        $privateKey = $this->resolvePrivateKey();

        if ($teamId === '' || $clientId === '' || $keyId === '' || $privateKey === '') {
            throw new LoginAppleException(
                message: 'Configurazione Apple incompleta.',
                errors: [
                    'apple' => ['Configurazione Apple incompleta.'],
                ],
                status: 500
            );
        }

        $now = time();

        return JWT::encode(
            [
                'iss' => $teamId,
                'iat' => $now,
                'exp' => $now + 300,
                'aud' => 'https://appleid.apple.com',
                'sub' => $clientId,
            ],
            $privateKey,
            'ES256',
            $keyId
        );
    }

    protected function resolvePrivateKey(): string
    {
        $rawKey = (string) config('services.apple.private_key');
        $keyPath = (string) config('services.apple.private_key_path');

        if ($rawKey !== '') {
            return str_replace('\n', "\n", $rawKey);
        }

        if ($keyPath !== '' && is_file($keyPath)) {
            return (string) file_get_contents($keyPath);
        }

        return '';
    }
}
