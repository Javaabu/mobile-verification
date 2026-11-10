<?php

namespace Javaabu\MobileVerification\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Javaabu\Auth\Support\ApiTokens;
use Javaabu\MobileVerification\Actions\AuthenticateMobileNumber;
use Javaabu\MobileVerification\Actions\SendVerificationCode;
use Javaabu\MobileVerification\Enums\Purposes;

/**
 * For mobile apps and other API clients: send a login code, then trade the number and code
 * for a personal access token (Sanctum or Passport, as javaabu/auth's `api` config allows)
 */
class MobileApiController extends Controller
{
    public function code(Request $request, SendVerificationCode $send): JsonResponse
    {
        $input = $this->validateNumber($request);

        $mobile_number = $send($this->userType($request), Purposes::LOGIN, $input['country_code'], $input['number']);

        return new JsonResponse($mobile_number->verificationCodeResponseData());
    }

    public function token(Request $request, AuthenticateMobileNumber $authenticate): JsonResponse
    {
        $type = $this->userType($request);

        $input = $request->validate([
            'number' => ['required', 'string', 'max:20'],
            'country_code' => ['nullable', 'string', 'max:5'],
            'verification_code' => ['required', 'string', 'max:10'],
            'verification_code_id' => ['required', 'string', 'max:64'],
            'device_name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string'],
            'recovery_code' => ['nullable', 'string'],
        ]);

        $user = $authenticate($type, $input);

        if (! ApiTokens::supports($user)) {
            throw ValidationException::withMessages(['number' => __('API tokens are not available for this account.')]);
        }

        return new JsonResponse([
            'token' => ApiTokens::create($user, $input['device_name']),
            'token_type' => 'Bearer',
        ], 201);
    }
}
