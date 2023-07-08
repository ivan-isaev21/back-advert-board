<?php

namespace App\UseCases\Auth;

use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RegisterService
{    
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
        });
    }
    
    /**
     * Method verify
     *
     * @param User $user 
     *
     * @return void
     */
    public function verify(User $user): void
    {
        DB::transaction(function () use ($user) {
            $user->verify();
        });
    }
}
