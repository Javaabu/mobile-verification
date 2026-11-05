<?php

namespace Javaabu\MobileVerification\Http\Controllers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Javaabu\Auth\Actions\LoginUser;
use Javaabu\Auth\Http\Responses\RegisterResponse;
use Javaabu\Auth\UserType;
use Javaabu\MobileVerification\Contracts\CreatesUsersFromMobileNumbers;
use Javaabu\MobileVerification\Contracts\HasMobileNumber;
use Javaabu\MobileVerification\Enums\Purposes;
use Javaabu\MobileVerification\MobileVerification;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Stencil\MobileRegistrationForm;
use Javaabu\MobileVerification\Support\PendingVerification;
use Javaabu\Stencil\Form;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sign up with a mobile number: verify the number, then fill in the registration form
 */
class MobileRegistrationController extends VerificationFlowController
{
    protected function purpose(): Purposes
    {
        return Purposes::REGISTER;
    }

    protected function routeName(): string
    {
        return 'register.mobile';
    }

    public function create(Request $request): View|Responsable
    {
        return $this->render($request, 'register');
    }

    protected function verified(Request $request, MobileNumber $mobile_number): Response
    {
        $type = $this->userType($request);

        PendingVerification::markVerified($request, $type, $this->purpose(), $mobile_number);

        $redirect = $type->route('register.mobile.details');

        if ($request->expectsJson()) {
            return new JsonResponse(['verified' => true, 'redirect' => $redirect]);
        }

        return redirect()->to($redirect);
    }

    protected function verifyTitle(): string
    {
        return __('Enter the code to verify your number');
    }

    /**
     * The registration form, once the number is verified
     */
    public function details(Request $request): View|Responsable|Response
    {
        $type = $this->userType($request);
        $mobile_number = $this->verifiedMobileNumber($request);

        if (! $mobile_number) {
            return $this->expiredResponse($request);
        }

        return $this->render($request, 'register-details', [
            'form' => $this->form($type),
            'mobile_number' => ['formatted_number' => $mobile_number->formatted_number],
        ]);
    }

    /**
     * Create the user, attach the number and log them in
     */
    public function complete(Request $request, CreatesUsersFromMobileNumbers $creator, LoginUser $login): Response
    {
        $type = $this->userType($request);
        $mobile_number = $this->verifiedMobileNumber($request);

        if (! $mobile_number) {
            return $this->expiredResponse($request);
        }

        if ($mobile_number->hasOwner()) {
            PendingVerification::forgetVerified($request, $type, $this->purpose());

            throw ValidationException::withMessages(['number' => __('This mobile number is already registered.')])
                ->redirectTo($type->route('register.mobile'));
        }

        $data = $this->form($type)->validate($request);

        $user = $creator->create($type, $data, $mobile_number);

        if ($user instanceof HasMobileNumber) {
            $user->updatePhone($mobile_number);
        }

        PendingVerification::forgetVerified($request, $type, $this->purpose());

        event(new Registered($user));

        $login($request, $type, $user);

        return app(RegisterResponse::class)->toResponse($request);
    }

    protected function verifiedMobileNumber(Request $request): ?MobileNumber
    {
        return PendingVerification::verifiedMobileNumber($request, $this->userType($request), $this->purpose());
    }

    protected function form(UserType $type): Form
    {
        /** @var class-string<Form> $class */
        $class = MobileVerification::config('registration_form', MobileRegistrationForm::class);

        return $class::make()
            ->model($type->modelClass())
            ->action($type->route('register.mobile.details.store'))
            ->with(['user_type' => $type->name()]);
    }
}
