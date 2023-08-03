<?php

namespace App\UseCases\Profiles;

use App\Http\Requests\Profiles\ChangeEmailRequest;
use App\Http\Requests\Profiles\VerifyEmailRequest;
use App\Mail\CustomVerifyEmailMail;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Mail\Mailer;

class EmailService
{
    private $mailer;
    private $dispatcher;

    public function __construct(Mailer $mailer, Dispatcher $dispatcher)
    {
        $this->mailer = $mailer;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Method requestChangeEmail
     *
     * @param ChangeEmailRequest $request 
     * @param User $user 
     *
     * @return void
     */
    public function requestChangeEmail(ChangeEmailRequest $request, User $user): void
    {
        DB::transaction(function () use ($request, $user) {
            $user->requestEmailVerification($request->email);
            $this->mailer->to($user->email)->send(new CustomVerifyEmailMail($user));
        });
    }

    /**
     * Method verify
     *
     * @param User $user 
     *
     * @return void
     */
    public function verifyEmail(VerifyEmailRequest $request, User $user): void
    {
        DB::transaction(function () use ($user, $request) {
            $user->verifyEmail($request->hash);
            $this->dispatcher->dispatch(new Verified($user));
        });
    }
}
