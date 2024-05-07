<?php

namespace Javaabu\MobileVerification\Support\Actions;

use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Support\Enums\Countries;
use Javaabu\MobileVerification\Contracts\HasMobileNumber;

class AssociateUserWithMobileNumberAction
{

    public function __construct(
        public string $user_type,
        public string | null $country_code = null,
    )
    {
        $this->country_code ??= Countries::Maldives->getCountryCode();
    }

    public function handle(int $user_id, string $number): MobileNumber
    {
        $mobileNumber = MobileNumber::query()
                                    ->hasPhoneNumberWithoutOwner($this->country_code, $number, $this->user_type)
                                    ->first();
        dd($this->country_code, $number, $this->user_type);

        if (!$mobileNumber) {
            throw new \Exception(__('mobile-verification::strings.validation.number.exists', ['attribute' => 'number']));
        }

        $mobileNumber->user_id = $user_id;
        $mobileNumber->save();

        return $mobileNumber;
    }
}
