<?php

namespace Javaabu\MobileVerification\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * A user's verified number changed: they verified a new one, an admin set it, or it was removed
 */
class MobileNumberUpdated
{
    use Dispatchable;

    public function __construct(
        public ?string $old_number,
        public ?string $new_number,
        public Model $user,
        public ?Model $causer = null,
    ) {}
}
