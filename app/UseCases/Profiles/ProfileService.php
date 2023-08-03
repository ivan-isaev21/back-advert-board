<?php

namespace App\UseCases\Profiles;

use App\Http\Requests\Profiles\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProfileService
{
    /**
     * Method update
     *
     * @param UpdateProfileRequest $request 
     * @param User $user 
     *
     * @return User
     */
    public function update(UpdateProfileRequest $request, User $user): User
    {
        $userUpdated = DB::transaction(function () use ($request, $user) {
            $user->update([
                'contact_person' => $request->contact_person,
                'city_id' => $request->city_id
            ]);
            return $user;
        });

        return $userUpdated;
    }
}
