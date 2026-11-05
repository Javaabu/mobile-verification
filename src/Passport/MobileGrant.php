<?php

namespace Javaabu\MobileVerification\Passport;

use DateInterval;
use Illuminate\Validation\ValidationException;
use Javaabu\Auth\UserType;
use Javaabu\Auth\UserTypes;
use Javaabu\MobileVerification\Actions\AuthenticateMobileNumber;
use Laravel\Passport\Bridge\User as UserEntity;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AbstractGrant;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\RequestEvent;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * `grant_type=mobile`: an access token for a number and its code (`number`, `country_code`,
 * `verification_code`, and `code` or `recovery_code` for two-factor users). The client's
 * provider picks the javaabu/auth user type. Clients need the `mobile` grant or the password grant.
 */
class MobileGrant extends AbstractGrant
{
    public function __construct(RefreshTokenRepositoryInterface $refresh_token_repository)
    {
        $this->setRefreshTokenRepository($refresh_token_repository);
        $this->refreshTokenTTL = new DateInterval('P1M');
    }

    public function getIdentifier(): string
    {
        return 'mobile';
    }

    public function respondToAccessTokenRequest(
        ServerRequestInterface $request,
        ResponseTypeInterface $responseType,
        DateInterval $accessTokenTTL,
    ): ResponseTypeInterface {
        $client = $this->validateClient($request);
        $scopes = $this->validateScopes($this->getRequestParameter('scope', $request, $this->defaultScope));
        $user = $this->validateUser($request, $client);

        $finalized_scopes = $this->scopeRepository->finalizeScopes($scopes, $this->getIdentifier(), $client, $user->getIdentifier());

        $access_token = $this->issueAccessToken($accessTokenTTL, $client, $user->getIdentifier(), $finalized_scopes);
        $this->getEmitter()->emit(new RequestEvent(RequestEvent::ACCESS_TOKEN_ISSUED, $request));
        $responseType->setAccessToken($access_token);

        $refresh_token = $this->issueRefreshToken($access_token);

        if ($refresh_token !== null) {
            $this->getEmitter()->emit(new RequestEvent(RequestEvent::REFRESH_TOKEN_ISSUED, $request));
            $responseType->setRefreshToken($refresh_token);
        }

        return $responseType;
    }

    /**
     * @throws OAuthServerException
     */
    protected function validateUser(ServerRequestInterface $request, ClientEntityInterface $client): UserEntityInterface
    {
        $type = $this->userType($request, $client);

        $number = $this->getRequestParameter('number', $request);
        $verification_code = $this->getRequestParameter('verification_code', $request);

        if (blank($number)) {
            throw OAuthServerException::invalidRequest('number');
        }

        if (blank($verification_code)) {
            throw OAuthServerException::invalidRequest('verification_code');
        }

        try {
            $user = app(AuthenticateMobileNumber::class)($type, [
                'number' => (string) $number,
                'country_code' => $this->getRequestParameter('country_code', $request),
                'verification_code' => (string) $verification_code,
                'verification_code_id' => $this->getRequestParameter('verification_code_id', $request),
                'code' => $this->getRequestParameter('code', $request),
                'recovery_code' => $this->getRequestParameter('recovery_code', $request),
            ]);
        } catch (ValidationException $exception) {
            $this->getEmitter()->emit(new RequestEvent(RequestEvent::USER_AUTHENTICATION_FAILED, $request));

            $errors = $exception->errors();
            $parameter = (string) array_key_first($errors);

            throw OAuthServerException::invalidRequest($parameter, $errors[$parameter][0] ?? null);
        }

        return new UserEntity((string) $user->getAuthIdentifier());
    }

    /**
     * The user type whose guard uses the client's provider
     *
     * @throws OAuthServerException
     */
    protected function userType(ServerRequestInterface $request, ClientEntityInterface $client): UserType
    {
        $provider = (property_exists($client, 'provider') ? $client->provider : null)
            ?: config('auth.guards.api.provider')
            ?: 'users';

        foreach (UserTypes::all() as $type) {
            if (config("auth.guards.{$type->guardName()}.provider") === $provider) {
                return $type;
            }
        }

        throw OAuthServerException::invalidClient($request);
    }

    protected function supportsGrantType(ClientEntityInterface $client, string $grantType): bool
    {
        // Clients that can use the password grant can use this one
        return parent::supportsGrantType($client, $grantType)
            || ($grantType === $this->getIdentifier() && parent::supportsGrantType($client, 'password'));
    }
}
