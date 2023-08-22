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

        if ($user->avatar_hash and $user->avatar_path) {
            throw new DomainException('Avatar is already set.');
        }

        $file = $request->file('file');

        $this->service->create($user, $file);
        return response(new UserResource($user), Response::HTTP_CREATED);
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
