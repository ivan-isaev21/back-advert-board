<?php

namespace App\Http\Controllers\Api\v1\Profiles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profiles\ChangePhoneRequest;
use App\Http\Requests\Profiles\UpdateProfileRequest;
use App\Http\Requests\Profiles\VerifyPhoneRequest;
use App\UseCases\Profiles\PhoneService;
use Illuminate\Http\Response;

class PhoneController extends Controller
{
    private $service;

    public function __construct(PhoneService $service)
    {
        $this->service = $service;
    }

    /**
     * Method requestChangePhone
     *
     * @param ChangePhoneRequest $request 
     *
     * @return Illuminate\Http\Response
     */
    public function requestChangePhone(ChangePhoneRequest $request): \Illuminate\Http\Response
    {
        $user = $request->user();
        $this->service->requestChangePhone($request, $user);

        return response($user, Response::HTTP_ACCEPTED);
    }

    /**
     * Method verifyPhone
     *
     * @param VerifyPhoneRequest $request 
     *
     * @return Illuminate\Http\Response
     */
    public function verifyPhone(VerifyPhoneRequest $request): \Illuminate\Http\Response
    {
        $user = $request->user();
        $this->service->verifyPhone($request, $user);
        return response($user, Response::HTTP_ACCEPTED);
    }
}
