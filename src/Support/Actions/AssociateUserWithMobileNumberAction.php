<?php

namespace Javaabu\MobileVerification\Support\Actions;

use Javaabu\Helpers\Exceptions\InvalidOperationException;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Support\DataObjects\MobileNumberData;

class AssociateUserWithMobileNumberAction
{
    public function handle(MobileNumberData $mobile_number_data): MobileNumber
    {
        $mobileNumber = MobileNumber::query()
                                    ->hasPhoneNumberWithoutOwner($mobile_number_data->country_code, $mobile_number_data->number, $mobile_number_data->user_type)
                                    ->first();

        if (! $mobileNumber) {
            throw new InvalidOperationException(__('mobile-verification::strings.validation.number.exists', ['attribute' => 'number']));
        }

        $mobileNumber->user_id = $mobile_number_data->user_id;
        $mobileNumber->save();

        return $mobileNumber;
    }
}
