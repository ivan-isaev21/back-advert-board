<?php

namespace App\Http\Controllers\Api\v1\Profiles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profiles\ChangePasswordRequest;
use App\UseCases\Profiles\PasswordService;
use Illuminate\Http\Response;

class PasswordController extends Controller
{
    private $service;

    public function __construct(PasswordService $service)
    {
        $this->service = $service;
    }

    /**
     * Method changePassword
     *
     * @param ChangePasswordRequest $request 
     *
     * @return Illuminate\Http\Response
     */
    public function changePassword(ChangePasswordRequest $request): \Illuminate\Http\Response
    {
        $user = $request->user();
        $this->service->changePassword($request, $user);
        return response($user, Response::HTTP_ACCEPTED);
    }
}
