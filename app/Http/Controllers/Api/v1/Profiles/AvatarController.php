<?php

namespace App\Http\Controllers\Api\v1\Profiles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profiles\CreateAvatarRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\UseCases\Profiles\AvatarService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AvatarController extends Controller
{
    private $service;

    public function __construct(AvatarService $service)
    {
        $this->service = $service;
    }

    /**
     * Method create
     *
     * @param CreateAvatarRequest $request 
     *
     * @return Illuminate\Http\Response
     */
    public function create(CreateAvatarRequest $request): \Illuminate\Http\Response
    {
        $user = $request->user();

        $file = $request->file('file');

        $this->service->update($user, $file);
        return response(['user' => new UserResource($user), 'message' => 'You success changed avatar.'], Response::HTTP_CREATED);
    }

    /**
     * Method destroy
     *
     * @param Request $request [explicite description]
     *
     * @return Illuminate\Http\Response
     */
    public function destroy(Request $request): \Illuminate\Http\Response
    {
        $user = $request->user();
        $this->service->delete($user);
        return response(null, Response::HTTP_NO_CONTENT);
    }
}
