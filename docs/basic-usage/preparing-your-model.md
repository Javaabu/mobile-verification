---
title: Preparing Your Model
sidebar_position: 1.6
---

## Preparing Your Model

To associate a mobile number with a user, the user model must implement the following interface and trait:
```php
<?php

namespace App\Models;

use Javaabu\Auth\User as Authenticatable;
use Javaabu\MobileVerification\Contracts\HasMobileNumber;
use Javaabu\MobileVerification\Traits\InteractsWithMobileNumbers;

class User extends Authenticatable implements HasMobileNumber
{
    use InteractsWithMobileNumbers;
}
```

