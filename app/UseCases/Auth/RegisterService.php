<?php

namespace App\UseCases\Auth;

use App\Http\Requests\Auth\RegisterRequest;
use App\Mail\CustomVerifyEmailMail;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Mail\Mailer;

class RegisterService
{
    private $mailer;
    private $dispatcher;

    public function __construct(Mailer $mailer, Dispatcher $dispatcher)
    {
        $this->mailer = $mailer;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Method register
     *
     * @param RegisterRequest $request 
     *
     * @return void
     */
    public function register(RegisterRequest $request): void
    {
        DB::transaction(function () use ($request) {
            $user = User::register(
                $request->name,
                $request->email,
                $request->password
            );

            $this->mailer->to($user->email)->send(new CustomVerifyEmailMail($user));

            $this->dispatcher->dispatch(new Registered($user));
        });
    }

    /**
     * Method verify
     *
     * @param User $user 
     *
     * @return void
     */
    public function verify(User $user, string $hash): void
    {
        DB::transaction(function () use ($user, $hash) {
            $user->verifyEmail($hash);
            $this->dispatcher->dispatch(new Verified($user));
        });
    }
}
