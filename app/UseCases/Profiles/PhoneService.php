<?php

namespace App\UseCases\Profiles;

use App\Http\Requests\Profiles\ChangePhoneRequest;
use App\Http\Requests\Profiles\VerifyPhoneRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\Sms\SmsSender;

class PhoneService
{
    private $smsSender;

    public function __construct(SmsSender $smsSender)
    {
        $this->smsSender = $smsSender;
    }

    /**
     * Method requestChangePhone
     *
     * @param ChangePhoneRequest $request 
     * @param User $user 
     *
     * @return void
     */
    public function requestChangePhone(ChangePhoneRequest $request, User $user): void
    {
        DB::transaction(function () use ($request, $user) {
            $phone = $request->phone;
            $token = $user->requestPhoneVerification($phone, Carbon::now());
            $this->smsSender->send($phone, 'Your token to change phone: ' . $token);
        });
    }

    /**
     * Method verifyPhone
     *
     * @param VerifyPhoneRequest $request 
     * @param User $user 
     *
     * @return void
     */
    public function verifyPhone(VerifyPhoneRequest $request, User $user): void
    {
        DB::transaction(function () use ($request, $user) {
            $user->verifyPhone($request->token, Carbon::now());
        });
    }
}
