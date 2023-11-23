<?php

namespace App\Enums;

enum UserTokenType: string
{
    case Login = 'user:login';
    case ResetPassword = 'user:password-reset';
    case ActivateUser = 'user:activate';
}
