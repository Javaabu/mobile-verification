<?php

namespace Javaabu\MobileVerification\Support\Services;

use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Contracts\HasMobileNumber;
use Javaabu\MobileVerification\Support\DataObjects\MobileNumberData;
use Javaabu\MobileVerification\Contracts\MobileNumber as MobileNumberContract;

class MobileNumberService
{

    public function firstOrCreate(MobileNumberData $mobile_number_data): MobileNumber
    {
        $mobile_number = MobileNumber::query()
                                     ->hasPhoneNumber(
                                         $mobile_number_data->country_code,
                                         $mobile_number_data->number,
                                         $mobile_number_data->user_type
                                     )
                                     ->first();

        if ($mobile_number) {
            return $mobile_number;
        }

        $mobile_number = new MobileNumber();
        $mobile_number->number = $mobile_number_data->number;
        $mobile_number->country_code = $mobile_number_data->country_code;
        $mobile_number->user_type = $mobile_number_data->user_type;
        $mobile_number->user_id = null;
        $mobile_number->save();

        return $mobile_number;
    }

    public function getMobileNumber(MobileNumberData $mobile_number_data): ?MobileNumber
    {
        return MobileNumber::query()
                           ->hasPhoneNumber(
                               $mobile_number_data->country_code,
                               $mobile_number_data->number,
                               $mobile_number_data->user_type
                           )
                           ->first();
    }

    public function updateMobileNumber(MobileNumberContract $new_mobile_number, MobileNumberContract $previous_mobile_number, HasMobileNumber $user): void
    {
        $previous_mobile_number->user_id = null;
        $previous_mobile_number->save();

        $new_mobile_number->user_id = $user->id;
        $new_mobile_number->save();
    }
}
