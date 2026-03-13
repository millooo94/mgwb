<?php

namespace App\Services\Auth;

use App\Exceptions\LoginAppleException;
use App\Models\Utente;

class LoginAppleService
{
    public function __construct(
        protected AuthUserPayloadService $authUserPayloadService,
        protected AccessTokenService $accessTokenService,
        protected AppleIdentityService $appleIdentityService,
        protected ResolveSocialIdentityService $resolveSocialIdentityService
    ) {}

    public function login(array $data): array
    {
        $this->appleIdentityService->validateAuthorizationCode($data['authorization_code']);

        $claims = $this->appleIdentityService->verifyIdentityToken(
            $data['identity_token'],
            $data['nonce'] ?? null
        );

        $user = $this->resolveOrCreateUser($claims, $data);

        $this->ensureAppleLoginIsAllowed($user);

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

    protected function resolveOrCreateUser(array $claims, array $data): Utente
    {
        $appleSub = (string) ($claims['sub'] ?? '');

        if ($appleSub === '') {
            throw new LoginAppleException(
                message: 'Identificativo Apple non valido.',
                errors: [
                    'identity_token' => ['Identificativo Apple non valido.'],
                ],
                status: 422
            );
        }

        $email = $this->resolveEmail($claims);

        if ($email === null) {
            throw new LoginAppleException(
                message: 'Email Apple non disponibile per la creazione dell’account. Revoca l’autorizzazione Apple e riprova.',
                errors: [
                    'email' => ['Email Apple non disponibile per la creazione dell’account. Revoca l’autorizzazione Apple e riprova.'],
                ],
                status: 422
            );
        }

        [$nome, $cognome] = $this->extractNames($data, $email);

        return $this->resolveSocialIdentityService->resolve([
            'provider' => 'apple',
            'provider_user_id' => $appleSub,
            'email' => $email,
            'nome' => $nome,
            'cognome' => $cognome,
        ]);
    }

    protected function resolveEmail(array $claims): ?string
    {
        $email = trim((string) ($claims['email'] ?? ''));

        return $email !== '' ? mb_strtolower($email) : null;
    }

    protected function ensureAppleLoginIsAllowed(Utente $user): void
    {
        if (! $user->hasRole('cliente')) {
            throw new LoginAppleException(
                message: 'Accesso con Apple non consentito per questo account.',
                errors: [
                    'apple' => ['Accesso con Apple non consentito per questo account.'],
                ],
                status: 403
            );
        }
    }

    protected function extractNames(array $data, string $fallbackEmail): array
    {
        $givenName = trim((string) ($data['given_name'] ?? ''));
        $familyName = trim((string) ($data['family_name'] ?? ''));

        if ($givenName !== '' || $familyName !== '') {
            return [
                $givenName !== '' ? $givenName : explode('@', $fallbackEmail)[0],
                $familyName !== '' ? $familyName : '-',
            ];
        }

        return [
            explode('@', $fallbackEmail)[0],
            '-',
        ];
    }
}
