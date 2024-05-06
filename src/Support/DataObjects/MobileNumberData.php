<?php

namespace Javaabu\MobileVerification\Support\DataObjects;

use Javaabu\MobileVerification\Support\Enums\Countries;

class MobileNumberData
{

    public function __construct(
        public string $number,
        public ?string $country_code,
        public string $user_type,
        public ?int $user_id,
    )
    {
    }

    public static function fromRequestData(array $data): static
    {
        return new static(
            number: $data['number'],
            country_code: $data['country_code'] ?? Countries::Maldives->getCountryCode(),
            user_type: $data['user_type'],
            user_id: $data['user_id'] ?? null,
        );
    }
}
