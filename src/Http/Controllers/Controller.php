<?php

namespace Javaabu\MobileVerification\Http\Controllers;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Javaabu\Auth\UserType;
use Javaabu\Auth\UserTypes;
use Javaabu\MobileVerification\MobileVerification;
use Javaabu\MobileVerification\Rules\IsValidCountryCode;
use Javaabu\MobileVerification\Rules\IsValidMobileNumber;
use Javaabu\MobileVerification\Views\ViewRenderer;

abstract class Controller extends BaseController
{
    use AuthorizesRequests;

    protected function userType(Request $request): UserType
    {
        return UserTypes::fromRequest($request) ?? UserTypes::default();
    }

    /**
     * The signed-in user of the route's user type
     */
    protected function user(Request $request): ?Model
    {
        return $this->userType($request)->auth()->user();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function render(Request $request, string $page, array $data = [], string $layout = 'auth'): View|Responsable
    {
        return app(ViewRenderer::class)->render($this->userType($request), $page, $data, $layout);
    }

    /**
     * Validate the number and country code of a request for a code
     *
     * @return array{number: string, country_code: string}
     */
    protected function validateNumber(Request $request): array
    {
        $validated = $request->validate([
            'country_code' => ['nullable', 'string', new IsValidCountryCode],
            'number' => ['required', 'string', 'max:20', new IsValidMobileNumber],
            ...(array) MobileVerification::config('request_code_rules', []),
        ], attributes: [
            'number' => __('mobile number'),
            'country_code' => __('country code'),
        ]);

        return [
            'number' => MobileVerification::normalizeNumber($validated['number']),
            'country_code' => MobileVerification::normalizeCountryCode($validated['country_code'] ?? null),
        ];
    }

    protected function validateCode(Request $request): string
    {
        $length = (int) MobileVerification::config('code_length', 6);

        $validated = $request->validate([
            'verification_code' => ['required', 'string', 'max:' . max(10, $length)],
        ], attributes: [
            'verification_code' => __('code'),
        ]);

        return trim($validated['verification_code']);
    }
}
