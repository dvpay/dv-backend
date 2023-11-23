<?php

declare(strict_types=1);

namespace App\Dto\Models;

use App\Dto\ArrayDto;

class UserDto extends ArrayDto
{
    public readonly string $name;
    public readonly string $email;
    public readonly string $password;
    public readonly bool $isAdmin;
    public readonly string $location;
    public readonly string $language;
    public readonly string $rateSource;
    public readonly string $phone;
    public readonly bool $google2faStatus;
}
