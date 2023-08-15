<?php

namespace App\Http\Controllers\Api\v1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\UseCases\Auth\RegisterService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RegisterController extends Controller
{
    private $service;
    private const NEED_VERIFY_EMAIL_TO_LOGIN = 'NEED_VERIFY_EMAIL_TO_LOGIN';

    public function __construct(RegisterService $service)
    {
        $this->service = $service;
    }

    /**
     * Method register
     *
     * @param RegisterRequest $request
     *
     * @return Response
     */
    public function register(RegisterRequest $request)
    {
        $user = $this->service->register($request);

        return response([
            'user' => new UserResource($user),
            'message' => 'Check your email and click on the link to verify.',
            'status' => self::NEED_VERIFY_EMAIL_TO_LOGIN,
        ], Response::HTTP_CREATED);
    }

    /**
     * Method verify
     *
     * @param Request $request 
     * @param User $user 
     * @param string $hash 
     *
     * @return void
     */
    public function verify(Request $request, $id, string $hash)
    {
        $user = User::findOrFail($id);

        $this->service->verify($user, $hash);

        return response([
            'message' => 'Successfully verify email.'
        ], Response::HTTP_ACCEPTED);
    }
}
