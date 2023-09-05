<?php

namespace App\Http\Controllers\Api\v1\Profiles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profiles\ChangeEmailRequest;
use App\Http\Requests\Profiles\VerifyEmailRequest;
use App\Http\Resources\UserResource;
use App\UseCases\Profiles\EmailService;
use Illuminate\Http\Response;

class EmailController extends Controller
{
    private $service;
    private const NEED_VERIFY_EMAIL_TO_LOGIN = 'NEED_VERIFY_EMAIL_TO_LOGIN';

    public function __construct(EmailService $service)
    {
        $this->service = $service;
    }

    /**
     * Method requestChangeEmail
     *
     * @param ChangeEmailRequest $request 
     *
     * @return Illuminate\Http\Response
     */
    public function requestChangeEmail(ChangeEmailRequest $request): \Illuminate\Http\Response
    {
        $user = $request->user();
        $this->service->requestChangeEmail($request, $user);
        return response(['user' => new UserResource($user), 'status' => self::NEED_VERIFY_EMAIL_TO_LOGIN, 'message' => 'Success requested'], Response::HTTP_ACCEPTED);
    }

    /**
     * Method verifyEmail
     *
     * @param VerifyEmailRequest $request 
     *
     * @return Illuminate\Http\Response
     */
    public function verifyEmail(VerifyEmailRequest $request): \Illuminate\Http\Response
    {
        $user = $request->user();
        $this->service->verifyEmail($request, $user);
        return response(['user' => new UserResource($user), 'message' => 'Success updated'], Response::HTTP_ACCEPTED);
    }
}
