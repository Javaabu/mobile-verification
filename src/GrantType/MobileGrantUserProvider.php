<?php

namespace Javaabu\MobileVerification\GrantType;

use Illuminate\Contracts\Auth\Authenticatable;
use League\OAuth2\Server\Entities\ClientEntityInterface;

interface MobileGrantUserProvider
{
    public function getUserByAccessToken(string $provider, string $accessToken, ClientEntityInterface $client): ?Authenticatable;
}
