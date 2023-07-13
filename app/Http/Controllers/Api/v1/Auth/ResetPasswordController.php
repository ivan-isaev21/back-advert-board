<?php

namespace App\Http\Controllers\Api\v1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use App\UseCases\Auth\ResetPasswordService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ResetPasswordController extends Controller
{
    private $service;

    public function __construct(ResetPasswordService $service)
    {
        $this->service = $service;
    }

    /**
     * Method requestPasswordResetToken
     *
     * @param Request $request
     *
     * @return void
     */
    public function requestPasswordResetToken(ResetPasswordRequest $request)
    {
        $user = User::where(['email' => $request->email])->first();

        if ($user) {
            $this->service->requestPasswordResetToken($user);
        }

        return response([
            'message' => 'Check your email and click on the link to reset password.'
        ], Response::HTTP_ACCEPTED);
    }


    /**
     * Method changePassword
     *
     * @param ChangePasswordRequest $request
     *
     * @return void
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $user = User::where(['email' => $request->email])->first();

        if ($user) {
            $this->service->changePasswordByToken($request, $user);
        }

        return response([
            'message' => 'Password success changed.'
        ], Response::HTTP_ACCEPTED);
    }
}
