<?php

namespace App\Services\JwtAuth\users\enums;

enum UserAuthStatus: int{
    case REVOKED = 0;
    case DENIED = 1;
}