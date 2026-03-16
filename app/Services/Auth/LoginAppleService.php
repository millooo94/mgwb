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
                message: 'Unable to fetch Facebook profile.',
                errors: [
                    'access_token' => ['Unable to fetch Facebook profile.'],
                ],
                status: 422
            );
        }

        $profile = $response->json();

        if (empty($profile['id'])) {
            throw new LoginFacebookException(
                message: 'Invalid Facebook profile.',
                errors: [
                    'access_token' => ['Invalid Facebook profile.'],
                ],
                status: 422
            );
        }

        if (empty($profile['email'])) {
            throw new LoginFacebookException(
                message: 'Facebook email is not available. Make sure you requested the email permission.',
                errors: [
                    'email' => ['Facebook email is not available. Make sure you requested the email permission.'],
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
                message: 'Facebook configuration is missing.',
                errors: [
                    'facebook' => ['Facebook configuration is missing.'],
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
                message: 'Invalid Facebook token.',
                errors: [
                    'access_token' => ['Invalid Facebook token.'],
                ],
                status: 422
            );
        }

        $data = $response->json('data', []);

        if (! ($data['is_valid'] ?? false)) {
            throw new LoginFacebookException(
                message: 'Invalid Facebook token.',
                errors: [
                    'access_token' => ['Invalid Facebook token.'],
                ],
                status: 422
            );
        }

        if ((string) ($data['app_id'] ?? '') !== $appId) {
            throw new LoginFacebookException(
                message: 'The Facebook token does not belong to this application.',
                errors: [
                    'access_token' => ['The Facebook token does not belong to this application.'],
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
        if (! in_array((int) $user->stato, [1, 3], true)) {
            throw new LoginFacebookException(
                message: 'Account is not allowed to sign in.',
                errors: [
                    'facebook' => ['Account is not allowed to sign in.'],
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
