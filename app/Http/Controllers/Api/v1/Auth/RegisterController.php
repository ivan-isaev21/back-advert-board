<?php

namespace App\Http\Controllers\Api\v1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\UseCases\Auth\RegisterService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RegisterController extends Controller
{
    private $service;

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
        $this->service->register($request);

        return response([
            'success' => 'Check your email and click on the link to verify.'
        ], Response::HTTP_CREATED);
    }

    /**
     * Method verifyEmail
     *
     * @param Request $request 
     * @param User $user 
     * @param string $hash 
     *
     * @return void
     */
    public function verifyEmail(Request $request, $id, string $hash)
    {
        $user = User::findOrFail($id);

        $this->service->verify($user, $hash);

        return response(['message' => 'Successfully verify email'], Response::HTTP_ACCEPTED);
    }
}
