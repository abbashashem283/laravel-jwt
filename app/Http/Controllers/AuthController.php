<?php

namespace App\Http\Controllers;

use App\Models\EmailVerificationToken;
use App\Models\User;
use App\Services\JwtAuth\JwtAuthController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Services\JwtAuth\mail\AuthMail;
use Illuminate\Support\Str;

class AuthController extends JwtAuthController{}
