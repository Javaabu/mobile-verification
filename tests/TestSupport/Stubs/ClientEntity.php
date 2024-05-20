<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Stubs;

use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\ClientEntityInterface;

class ClientEntity implements ClientEntityInterface
{
    use ClientTrait;
    use EntityTrait;

    public function getRedirectUri()
    {
        return "";
    }

    public function isConfidential()
    {
        return true;
    }
}
