<?php

namespace Javaabu\MobileVerification\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Javaabu\Auth\UserType;
use Javaabu\Auth\UserTypes;
use Javaabu\MobileVerification\Contracts\HasMobileNumber;
use Javaabu\MobileVerification\Routing\MobileNumberActionRoutes;
use Javaabu\MobileVerification\Rules\IsValidCountryCode;
use Javaabu\MobileVerification\Rules\IsValidMobileNumber;
use Symfony\Component\HttpFoundation\Response;

/**
 * What admins do to other users' numbers, registered with Route::mobileNumberActions()
 */
class MobileNumberActionsController extends Controller
{
    /**
     * Set a user's number without a code
     */
    public function update(Request $request, string $account): Response
    {
        [$type, $model] = $this->account($request, $account, 'mobile-verification.update');

        $validated = $request->validate([
            'country_code' => ['nullable', 'string', new IsValidCountryCode],
            'number' => ['required', 'string', 'max:20', (new IsValidMobileNumber($type))->notRegistered()->ignore($model)],
        ], attributes: [
            'number' => __('mobile number'),
        ]);

        if (! $model->updateMobileNumber($validated['number'], $validated['country_code'] ?? null, $this->causer($request))) {
            throw ValidationException::withMessages(['number' => __('This mobile number is already registered.')]);
        }

        return $this->respond($request, __('The mobile number has been changed to :number.', ['number' => $model->formatted_mobile_number]));
    }

    /**
     * Forget the wrong codes against the user's number
     */
    public function unlock(Request $request, string $account): Response
    {
        [$type, $model] = $this->account($request, $account, 'mobile-verification.unlock');

        $model->phone?->resetAttempts();

        return $this->respond($request, __('The mobile number has been unlocked.'));
    }

    public function destroy(Request $request, string $account): Response
    {
        [$type, $model] = $this->account($request, $account, 'mobile-verification.remove');

        $model->removeMobileNumber($this->causer($request));

        return $this->respond($request, __('The mobile number has been removed.'));
    }

    /**
     * The managed type and its account, after checking the ability
     *
     * @return array{0: UserType, 1: Model&HasMobileNumber}
     */
    protected function account(Request $request, string $account, string $ability): array
    {
        $type = UserTypes::get($request->route()->getAction(MobileNumberActionRoutes::ROUTE_ACTION));
        $model = $type->newModel()->resolveRouteBinding($account) ?? abort(404);

        abort_unless($model instanceof HasMobileNumber, 404);

        Gate::forUser($this->causer($request))->authorize($ability, [$type, $model]);

        return [$type, $model];
    }

    /**
     * The signed-in admin: the user of whichever guard the request is authenticated on
     */
    protected function causer(Request $request): ?Model
    {
        $type = UserTypes::fromRequest($request);

        return $type?->auth()->user() ?? $request->user();
    }

    protected function respond(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return new JsonResponse(['message' => $message]);
        }

        return back()->with('status', $message);
    }
}
