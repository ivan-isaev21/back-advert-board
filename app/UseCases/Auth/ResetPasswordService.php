<?php

namespace App\UseCases\Auth;

use App\Events\PasswordChanged;
use App\Events\PasswordResetRequested;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Mail\CustomResetPasswordMail;
use App\Mail\PasswordChangedMail;
use App\Models\User;
use App\Services\Sms\SmsSender;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Events\Dispatcher;

class ResetPasswordService
{
    private $mailer;
    private $dispatcher;
    private $smsSender;

    public function __construct(Mailer $mailer, Dispatcher $dispatcher, SmsSender $smsSender)
    {
        $this->mailer = $mailer;
        $this->dispatcher = $dispatcher;
        $this->smsSender = $smsSender;
    }

    /**
     * Method requestPasswordResetToken
     *
     * @param User $user 
     *
     * @return void
     */
    public function requestPasswordResetToken(User $user)
    {
        DB::transaction(function () use ($user) {
            $token = $user->requestPasswordResetToken(Carbon::now());
            $this->mailer->to($user->email)->send(new CustomResetPasswordMail($user));
            $this->dispatcher->dispatch(new PasswordResetRequested($user));
        });
    }


    /**
     * Method changePasswordByToken
     *
     * @param ChangePasswordRequest $request 
     * @param User $user
     *
     * @return void
     */
    public function changePasswordByToken(ChangePasswordRequest $request, User $user)
    {
        DB::transaction(function () use ($request, $user) {
            $user->changePasswordByToken($request->token, $request->password, Carbon::now());
            $this->mailer->to($user->email)->send(new PasswordChangedMail($user));

            if ($user->isPhoneVerified()) {
                $this->smsSender->send($user->phone, 'Your password has been changed ToDo text.');
            }

            $user->tokens()->delete();

            $this->dispatcher->dispatch(new PasswordChanged($user));
        });
    }
}
