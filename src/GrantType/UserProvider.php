<?php

namespace Javaabu\MobileVerification\GrantType;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Log;
use Javaabu\Helpers\Exceptions\InvalidOperationException;
use League\OAuth2\Server\Entities\ClientEntityInterface;

class UserProvider implements MobileGrantUserProvider
{
    public function getUserByAccessToken(string $provider, string $accessToken, ClientEntityInterface $client): ?Authenticatable
    {
        // Return the user that corresponds to provided credentials.
        // If the credentials are invalid, then return NULL.
        $user_provider = $client->provider ?: config('auth.guards.api.provider');

        $user_model = config('auth.providers.'.$user_provider.'.model');

        if (is_null($user_model)) {
            throw new InvalidOperationException('Unable to determine authentication model from configuration.');
        }

        $oauth_user = null;

        try {
            // TODO: how?
        } catch (\Exception $exception) {
            Log::error('Mobile Grant Error: ' . $exception->getMessage());
        }

        if (! $oauth_user) {
            return null;
        }

        return $user_model::findMobileGrantUser($oauth_user, $provider);
    }
}
