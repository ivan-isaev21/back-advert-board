<?php

namespace App\Http\Controllers\Api\v1\Profiles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profiles\ChangeEmailRequest;
use App\Http\Requests\Profiles\VerifyEmailRequest;
use App\UseCases\Profiles\EmailService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmailController extends Controller
{
    private $service;

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
        return response($user, Response::HTTP_ACCEPTED);
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
        return response($user, Response::HTTP_ACCEPTED);
    }
}
