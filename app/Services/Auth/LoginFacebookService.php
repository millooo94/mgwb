<?php

namespace App\Services\Auth;

use App\Exceptions\LoginFacebookException;
use App\Models\Utente;
use Illuminate\Support\Facades\Http;

class LoginFacebookService
{
    public function __construct(
        protected AuthUserPayloadService $authUserPayloadService,
        protected AccessTokenService $accessTokenService,
        protected ResolveSocialIdentityService $resolveSocialIdentityService
    ) {}

    public function login(array $data): array
    {
        $profile = $this->fetchFacebookProfile($data['access_token']);

        $user = $this->resolveOrCreateUser($profile);

        $this->ensureFacebookLoginIsAllowed($user);

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

    protected function fetchFacebookProfile(string $accessToken): array
    {
        $this->debugAccessToken($accessToken);

        $response = Http::timeout(10)
            ->get('https://graph.facebook.com/me', [
                'fields' => 'id,name,email',
                'access_token' => $accessToken,
            ]);

        if (! $response->successful()) {
            throw new LoginFacebookException(
                message: 'Impossibile recuperare il profilo Facebook.',
                errors: [
                    'access_token' => ['Impossibile recuperare il profilo Facebook.'],
                ],
                status: 422
            );
        }

        $profile = $response->json();

        if (empty($profile['id'])) {
            throw new LoginFacebookException(
                message: 'Profilo Facebook non valido.',
                errors: [
                    'access_token' => ['Profilo Facebook non valido.'],
                ],
                status: 422
            );
        }

        if (empty($profile['email'])) {
            throw new LoginFacebookException(
                message: 'Email Facebook non disponibile. Verifica di aver richiesto il permesso email.',
                errors: [
                    'email' => ['Email Facebook non disponibile. Verifica di aver richiesto il permesso email.'],
                ],
                status: 422
            );
        }

        return $profile;
    }

    protected function debugAccessToken(string $accessToken): void
    {
        $appId = (string) config('services.facebook.app_id');
        $appSecret = (string) config('services.facebook.app_secret');

        if ($appId === '' || $appSecret === '') {
            throw new LoginFacebookException(
                message: 'Configurazione Facebook mancante.',
                errors: [
                    'facebook' => ['Configurazione Facebook mancante.'],
                ],
                status: 500
            );
        }

        $response = Http::timeout(10)
            ->get('https://graph.facebook.com/debug_token', [
                'input_token' => $accessToken,
                'access_token' => $appId . '|' . $appSecret,
            ]);

        if (! $response->successful()) {
            throw new LoginFacebookException(
                message: 'Token Facebook non valido.',
                errors: [
                    'access_token' => ['Token Facebook non valido.'],
                ],
                status: 422
            );
        }

        $data = $response->json('data', []);

        if (! ($data['is_valid'] ?? false)) {
            throw new LoginFacebookException(
                message: 'Token Facebook non valido.',
                errors: [
                    'access_token' => ['Token Facebook non valido.'],
                ],
                status: 422
            );
        }

        if ((string) ($data['app_id'] ?? '') !== $appId) {
            throw new LoginFacebookException(
                message: 'Il token Facebook non appartiene a questa applicazione.',
                errors: [
                    'access_token' => ['Il token Facebook non appartiene a questa applicazione.'],
                ],
                status: 422
            );
        }
    }

    protected function resolveOrCreateUser(array $profile): Utente
    {
        $facebookId = (string) $profile['id'];
        $email = mb_strtolower(trim((string) $profile['email']));

        [$nome, $cognome] = $this->extractNames((string) ($profile['name'] ?? ''), $email);

        return $this->resolveSocialIdentityService->resolve([
            'provider' => 'facebook',
            'provider_user_id' => $facebookId,
            'email' => $email,
            'nome' => $nome,
            'cognome' => $cognome,
        ]);
    }

    protected function ensureFacebookLoginIsAllowed(Utente $user): void
    {
        if (! $user->hasRole('cliente')) {
            throw new LoginFacebookException(
                message: 'Accesso con Facebook non consentito per questo account.',
                errors: [
                    'facebook' => ['Accesso con Facebook non consentito per questo account.'],
                ],
                status: 403
            );
        }
    }

    protected function extractNames(string $fullName, string $fallbackEmail): array
    {
        $fullName = trim($fullName);

        if ($fullName !== '' && str_contains($fullName, ' ')) {
            $parts = preg_split('/\s+/', $fullName, 2);

            return [
                trim((string) ($parts[0] ?? explode('@', $fallbackEmail)[0])),
                trim((string) ($parts[1] ?? '-')),
            ];
        }

        return [
            $fullName !== '' ? $fullName : explode('@', $fallbackEmail)[0],
            '-',
        ];
    }
}
