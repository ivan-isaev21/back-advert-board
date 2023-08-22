<?php

namespace App\Http\Controllers\Api\v1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\PhoneVerifyTokenRequest;
use App\Http\Requests\Auth\ResendPhoneVerifyTokenRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\UseCases\Auth\LoginService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    private $loginService;
    private const NEED_VERIFY_PHONE_TO_LOGIN = 'NEED_VERIFY_PHONE_TO_LOGIN';
    private const NEED_VERIFY_EMAIL_TO_LOGIN = 'NEED_VERIFY_EMAIL_TO_LOGIN';
    private const SUCCESS_LOGINED = 'SUCCESS_LOGINED';

    public function __construct(LoginService $loginService)
    {
        $this->loginService = $loginService;
    }

    /**
     * Method login
     *
     * @param LoginRequest $request
     *
     * @return Response
     */
    public function login(LoginRequest $request): Response
    {
        $credentials = $request->only(['email', 'password']);

        if (Auth::attempt($credentials, $request->remember)) {
            $user = $request->user();
            if ($user->isWait()) {
                Auth::logout();
                return response([
                    'user' => new UserResource($user),
                    'message' => 'You need to confirm your account. Please check your email.',
                    'status' => self::NEED_VERIFY_EMAIL_TO_LOGIN,
                ], Response::HTTP_ACCEPTED);
            }

            if ($user->isPhoneAuthEnabled()) {
                Auth::logout();
                $this->loginService->sendPhoneVerifyToken($user);
                return response([
                    'user' => new UserResource($user),
                    'message' => 'Please enter the login code sent to your phone.',
                    'status' => self::NEED_VERIFY_PHONE_TO_LOGIN

                ], Response::HTTP_ACCEPTED);
            }

            $token = $user->createToken('api-token')->plainTextToken;

            return response([
                'user' => new UserResource($user),
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => config('sanctum.expiration') * 60, // Optional: Specify token expiration time
                'status' => self::SUCCESS_LOGINED
            ], Response::HTTP_ACCEPTED);
        }

        return response([
            'error' => 'Invalid login credentials.'
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Method resendPhoneVerifyToken
     *
     * @param ResendPhoneVerifyTokenRequest $request 
     * @param User $user 
     *
     * @return Response
     */
    public function resendPhoneVerifyToken(ResendPhoneVerifyTokenRequest $request, User $user): Response
    {

        $inputDate = Carbon::parse($request->token_expire)->toDateTimeString();
        $userDate = $user->phone_verify_token_expire->toDateTimeString();

        if ($inputDate != $userDate) {
            throw new DomainException("Invalid phone token expire.");
        }

        $this->loginService->sendPhoneVerifyToken($user);

        return response([
            'user' => new UserResource($user),
            'message' => 'Please enter the login code sent to your phone.',
            'status' => self::NEED_VERIFY_PHONE_TO_LOGIN

        ], Response::HTTP_ACCEPTED);
    }

    /**
     * Method validatePhoneVerifyToken
     *
     * @param PhoneVerifyTokenRequest $request 
     *
     * @return Response
     */
    public function validatePhoneVerifyToken(PhoneVerifyTokenRequest $request, $id, $token): Response
    {
        /** @var User $user */
        $user = User::findOrFail($id);

        if ($request->token === $user->phone_verify_token) {
            $user->validatePhoneVerifyToken($token, Carbon::now());
            Auth::login($user, $request->remember);
            $token = $user->createToken('api-token')->plainTextToken;
            return response([
                'user' => new UserResource($user),
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => config('sanctum.expiration') * 60,
                'status' => self::SUCCESS_LOGINED
            ], Response::HTTP_ACCEPTED);
        }

        return response([
            'error' => 'Invalid auth token.'
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Method logout
     *
     * @param Request $request 
     *
     * @return Response
     */
    public function logout(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();
        return response([
            'message' => 'Successfully logged out.'
        ], Response::HTTP_ACCEPTED);
    }

    /**
     * Method logoutOtherDevices
     *
     * @param Request $request 
     *
     * @return Response
     */
    public function logoutOtherDevices(Request $request): Response
    {
        $request->user()->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();

        return response([
            'message' => 'Other devices have been logged out.'
        ], Response::HTTP_ACCEPTED);
    }
}
