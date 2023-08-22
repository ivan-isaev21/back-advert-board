<?php

namespace App\UseCases\Auth;

use App\Models\User;
use App\Services\Sms\SmsSender;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LoginService
{
    private $smsSender;

    public function __construct(SmsSender $smsSender)
    {
        $this->smsSender = $smsSender;
    }

    public function sendPhoneVerifyToken(User $user)
    {
        DB::transaction(function () use ($user) {
            $phoneVerifyToken = $user->requestPhoneVerifyToken(Carbon::now());
            $this->smsSender->send($user->phone, 'Login code: ' . $phoneVerifyToken);
        });
    }
}
