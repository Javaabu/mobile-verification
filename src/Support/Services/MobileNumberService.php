<?php

namespace Javaabu\MobileVerification\Support\Services;

use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Support\DataObjects\MobileNumberData;

class MobileNumberService
{

    public function store(MobileNumberData $mobile_number_data): MobileNumber
    {
        $mobile_number = new MobileNumber();
        $mobile_number->number = $mobile_number_data->number;
        $mobile_number->country_code = $mobile_number_data->country_code;
        $mobile_number->user_type = $mobile_number_data->user_type;
        $mobile_number->user_id = null;
        $mobile_number->save();

        return $mobile_number;
    }
}
