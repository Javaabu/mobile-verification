<?php

namespace Javaabu\MobileVerification\Events;

use App\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Javaabu\MobileVerification\Contracts\HasMobileNumber;

class MobileNumberUpdated
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @var User
     */
    public $causer;

    /**
     * @var HasMobileNumber
     */
    public HasMobileNumber $subject;

    /**
     * @var string
     */
    public ?string $old_phone;

    /**
     * @var string
     */
    public ?string $new_phone;

    /**
     * Create a new event instance.
     *
     * @param ?string $old_phone
     * @param ?string $new_phone
     * @param HasMobileNumber $subject
     * @param User|null $causer
     */
    public function __construct(?string $old_phone, ?string $new_phone, HasMobileNumber $subject, $causer = null)
    {
        $this->old_phone = $old_phone;
        $this->new_phone = $new_phone;
        $this->subject = $subject;
        $this->causer = $causer;
    }
}
