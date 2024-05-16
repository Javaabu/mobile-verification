<?php

namespace Javaabu\MobileVerification\GrantType;

use DateInterval;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Validator;
use Javaabu\MobileVerification\Contracts\MobileNumber;
use Javaabu\MobileVerification\MobileVerification;
use Javaabu\MobileVerification\Rules\IsValidCountryCode;
use Javaabu\MobileVerification\Rules\IsValidMobileNumber;
use Javaabu\MobileVerification\Rules\IsValidVerificationCode;
use Laravel\Passport\Bridge\User as UserEntity;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Grant\AbstractGrant;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\RequestEvent;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ServerRequestInterface;

class MobileGrant extends AbstractGrant
{
    public function __construct(
        RefreshTokenRepositoryInterface $refreshTokenRepository,
    ) {
        $this->setRefreshTokenRepository($refreshTokenRepository);
        $this->refreshTokenTTL = new DateInterval('P1M');
    }

    public function getIdentifier(): string
    {
        return 'mobile';
    }

    /**
     * @throws UniqueTokenIdentifierConstraintViolationException
     * @throws OAuthServerException
     */
    public function respondToAccessTokenRequest(
        ServerRequestInterface $request,
        ResponseTypeInterface  $responseType,
        DateInterval           $accessTokenTTL
    ): ResponseTypeInterface {
        $client = $this->validateClient($request);
        $scopes = $this->validateScopes($this->getRequestParameter('scope', $request, $this->defaultScope));
        $user = $this->validateUser($request, $client);

        $finalizedScopes = $this->scopeRepository->finalizeScopes($scopes, $this->getIdentifier(), $client, $user->getIdentifier());

        $accessToken = $this->issueAccessToken(
            $accessTokenTTL,
            $client,
            $user->getIdentifier(),
            $finalizedScopes
        );

        $this->getEmitter()->emit(new RequestEvent(RequestEvent::ACCESS_TOKEN_ISSUED, $request));
        $responseType->setAccessToken($accessToken);

        // Issue and persist a new refresh verification_code
        $refreshToken = $this->issueRefreshToken($accessToken);

        if ($refreshToken !== null) {
            $this->getEmitter()->emit(new RequestEvent(RequestEvent::REFRESH_TOKEN_ISSUED, $request));
            $responseType->setRefreshToken($refreshToken);
        }

        return $responseType;
    }

    /**
     * @throws OAuthServerException
     */
    protected function validateUser(ServerRequestInterface $request, ClientEntityInterface $client): UserEntityInterface
    {
        $validated_data = $this->validateOtp($request, $client);

        /* @var MobileNumber $model */
        $model = MobileVerification::mobileNumberModel();
        $user = $model::getUserByMobileNumber($validated_data['number'], $validated_data['country_code'] ?? null, $validated_data['user_type']);

        if ($user instanceof Authenticatable) {
            $user = new UserEntity($user->getAuthIdentifier());
        }

        if (! $user instanceof UserEntityInterface) {
            $this->getEmitter()->emit(new RequestEvent(RequestEvent::USER_AUTHENTICATION_FAILED, $request));

            throw OAuthServerException::invalidGrant();
        }

        return $user;
    }

    /**
     * @throws OAuthServerException
     */
    protected function validateOtp(ServerRequestInterface $request, ClientEntityInterface $client): array
    {
        // Validate the OTP
        // Return the validated data
        $mobile_number = $this->getParameter('number', $request);
        $otp = $this->getParameter('otp', $request);
        $country_code = $this->getParameter('country_code', $request, false);
        $user_type = $this->getUserType($request, $client);

        $validator = Validator::make([
            'number' => $mobile_number,
            'country_code' => $country_code,
            'otp' => $otp,
        ], [
            'number' => ['required', 'string', (new IsValidMobileNumber($user_type))->registered()],
            'country_code' => ['nullable', 'string', new IsValidCountryCode()],
            'otp' => ['required', 'string', (new IsValidVerificationCode($user_type))->shouldResetAttempts()],
        ]);

        if ($validator->fails()) {
            if ($validator->errors()->has('otp')) {
                throw OAuthServerException::invalidRequest('otp', $validator->errors()->first('otp'));
            }

            if ($validator->errors()->has('number')) {
                throw OAuthServerException::invalidRequest('number', $validator->errors()->first('number'));
            }

            if ($validator->errors()->has('country_code')) {
                throw OAuthServerException::invalidRequest('country_code', $validator->errors()->first('country_code'));
            }

            throw OAuthServerException::invalidCredentials();
        }

        return array_merge($validator->validated(), ['user_type' => $user_type]);
    }

    /**
     * @throws OAuthServerException
     */
    protected function getParameter($param, ServerRequestInterface $request, $required = true): ?string
    {
        $value = $this->getRequestParameter($param, $request);

        if (is_null($value) && $required) {
            throw OAuthServerException::invalidRequest($param);
        }

        return $value;
    }

    /**
     * @throws OAuthServerException
     */
    protected function getUserType(ServerRequestInterface $request, ClientEntityInterface $client): string
    {
        $user_provider = $client->provider ?: config('auth.guards.api.provider');

        if (! in_array($user_provider, config('mobile-verification.mobile_grant_allowed_providers'))) {
            throw OAuthServerException::invalidClient($request);
        }

        $user_model = config('auth.providers.' . $user_provider . '.model');

        if (is_null($user_model)) {
            throw OAuthServerException::invalidRequest('client_id', 'Unable to determine authentication model from configuration.');
        }

        return (new $user_model())->getMorphClass();
    }

}
