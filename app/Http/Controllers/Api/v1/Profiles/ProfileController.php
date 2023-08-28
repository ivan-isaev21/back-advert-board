<?php

namespace App\Http\Controllers\Api\v1\Profiles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profiles\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\UseCases\Profiles\ProfileService;
use Illuminate\Http\Response;

class ProfileController extends Controller
{
    private $service;

    public function __construct(ProfileService $service)
    {
        $this->service = $service;
    }

    /**
     * Method update
     *
     * @param UpdateProfileRequest $request 
     *
     * @return Illuminate\Http\Response
     */
    public function update(UpdateProfileRequest $request): \Illuminate\Http\Response
    {
        $user = $request->user();
        $this->service->update($request, $user);
        return response(['user' => new UserResource($user), 'message' => 'Success updated'], Response::HTTP_ACCEPTED);
    }
}
