<?php

namespace App\Http\Controllers\Api\v1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\PhoneVerifyTokenRequest;
use App\Models\User;
use App\Services\Sms\SmsSender;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    private $smsSenderService;

    public function __construct(SmsSender $smsSenderService)
    {
        $this->smsSenderService = $smsSenderService;
    }

    /**
     * Method login
     *
     * @param LoginRequest $request
     *
     * @return Response
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only(['email', 'password']);

        if (Auth::attempt($credentials)) {
            $user = $request->user();

            if ($user->isWait()) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'error' => 'You need to confirm your account. Please check your email.',
                ]);
            }

            if ($user->isPhoneAuthEnabled()) {
                Auth::logout();
                $phoneVerifyToken = $user->requestPhoneVerifyToken(Carbon::now());
                $this->smsSenderService->send($user->phone, 'Login code: ' . $phoneVerifyToken);
                return response([
                    'message' => 'Please enter the login code sent to your phone.',
                    'token' => $phoneVerifyToken,
                    'id' => $user->id
                ]);
            }

            $token = $user->createToken('api-token')->plainTextToken;

            return response([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => config('sanctum.expiration') * 60, // Optional: Specify token expiration time
            ]);
        }

        throw ValidationException::withMessages([
            'error' => 'Invalid login credentials.',
        ]);
    }

    /**
     * Method validatePhoneVerifyToken
     *
     * @param PhoneVerifyTokenRequest $request 
     *
     * @return Response
     */
    public function validatePhoneVerifyToken(PhoneVerifyTokenRequest $request)
    {
        /** @var User $user */
        $user = User::findOrFail($request->id);

        if ($request->token === $user->phone_verify_token) {
            $user->validatePhoneVerifyToken($request->token, Carbon::now());
            Auth::login($user, $request->remember);
            $token = $user->createToken('api-token')->plainTextToken;
            return response([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => config('sanctum.expiration') * 60, // Optional: Specify token expiration time
            ]);
        }

        return response(['error' => 'Invalid auth token.'], 422);
    }

    /**
     * Method logout
     *
     * @param Request $request 
     *
     * @return Response
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response(['message' => 'Successfully logged out']);
    }

    /**
     * Method logoutOtherDevices
     *
     * @param Request $request 
     *
     * @return Response
     */
    public function logoutOtherDevices(Request $request)
    {
        $request->user()->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();

        return response(['message' => 'Other devices have been logged out.']);
    }
}
