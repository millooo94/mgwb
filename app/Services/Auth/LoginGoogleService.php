<?php

namespace App\Services\Auth;

use App\Exceptions\LoginGoogleException;
use App\Models\Utente;
use Google\Client as GoogleClient;

class LoginGoogleService
{
    public function __construct(
        protected AuthUserPayloadService $authUserPayloadService,
        protected AccessTokenService $accessTokenService,
        protected ResolveSocialIdentityService $resolveSocialIdentityService
    ) {}

    public function login(array $data): array
    {
        $payload = $this->verifyGoogleIdToken($data['id_token']);

        $user = $this->resolveOrCreateUser($payload);

        $this->ensureGoogleLoginIsAllowed($user);

        $token = $this->accessTokenService->create(
            user: $user,
            name: 'access',
            deleteExistingTokens: false,
            abilities: ['*']
        );

        return array_merge(
            ['token' => $token],
            $this->authUserPayloadService->build($user)
        );
    }

    protected function verifyGoogleIdToken(string $idToken): array
    {
        $clientId = config('services.google.client_id');

        if (! $clientId) {
            throw new LoginGoogleException(
                message: 'Google configuration is missing.',
                errors: [
                    'google' => ['Google configuration is missing.'],
                ],
                status: 500
            );
        }

        $client = new GoogleClient([
            'client_id' => $clientId,
        ]);

        $payload = $client->verifyIdToken($idToken);

        if (! $payload || ! is_array($payload)) {
            throw new LoginGoogleException(
                message: 'Invalid Google token.',
                errors: [
                    'id_token' => ['Invalid Google token.'],
                ],
                status: 422
            );
        }

        return $payload;
    }

    protected function resolveOrCreateUser(array $payload): Utente
    {
        $email = $this->resolveVerifiedEmail($payload);
        $googleSub = $this->resolveGoogleSub($payload);

        [$nome, $cognome] = $this->extractNames($payload, $email);

        return $this->resolveSocialIdentityService->resolve([
            'provider' => 'google',
            'provider_user_id' => $googleSub,
            'email' => $email,
            'nome' => $nome,
            'cognome' => $cognome,
        ]);
    }

    protected function resolveVerifiedEmail(array $payload): string
    {
        $email = (string) ($payload['email'] ?? '');
        $emailVerified = (bool) ($payload['email_verified'] ?? false);

        if ($email === '' || ! $emailVerified) {
            throw new LoginGoogleException(
                message: 'Google account has no verified email.',
                errors: [
                    'email' => ['Google account has no verified email.'],
                ],
                status: 422
            );
        }

        return mb_strtolower(trim($email));
    }

    protected function resolveGoogleSub(array $payload): string
    {
        $googleSub = (string) ($payload['sub'] ?? '');

        if ($googleSub === '') {
            throw new LoginGoogleException(
                message: 'Invalid Google identifier.',
                errors: [
                    'google' => ['Invalid Google identifier.'],
                ],
                status: 422
            );
        }

        return $googleSub;
    }

    protected function ensureGoogleLoginIsAllowed(Utente $user): void
    {
        if (! in_array((int) $user->stato, [1, 3], true)) {
            throw new LoginGoogleException(
                message: 'Account is not allowed to sign in.',
                errors: [
                    'google' => ['Account is not allowed to sign in.'],
                ],
                status: 403
            );
        }
    }

    protected function extractNames(array $payload, string $email): array
    {
        $givenName = trim((string) ($payload['given_name'] ?? ''));
        $familyName = trim((string) ($payload['family_name'] ?? ''));
        $fullName = trim((string) ($payload['name'] ?? ''));

        if ($givenName !== '' || $familyName !== '') {
            return [
                $givenName !== '' ? $givenName : explode('@', $email)[0],
                $familyName !== '' ? $familyName : '-',
            ];
        }

        if ($fullName !== '' && str_contains($fullName, ' ')) {
            $parts = preg_split('/\s+/', $fullName, 2);

            return [
                trim((string) ($parts[0] ?? explode('@', $email)[0])),
                trim((string) ($parts[1] ?? '-')),
            ];
        }

        return [
            explode('@', $email)[0],
            '-',
        ];
    }
}
