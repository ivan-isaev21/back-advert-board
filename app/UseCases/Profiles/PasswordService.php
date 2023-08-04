<?php

namespace App\UseCases\Profiles;

use App\Http\Requests\Profiles\ChangePasswordRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PasswordService
{
    /**
     * Method changePassword
     *
     * @param ChangePasswordRequest $request 
     * @param User $user 
     *
     * @return void
     */
    public function changePassword(ChangePasswordRequest $request, User $user): void
    {
        DB::transaction(function () use ($request, $user) {
            $user->update(['password' => $request->password]);
            $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();
        });
    }
}
