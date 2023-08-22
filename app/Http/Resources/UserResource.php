<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'login' => $this->login,
            'avatar_url' =>  Storage::url($this->avatar_path),
            'avatar_path' => $this->avatar_path,
            'contact_person' => $this->contact_person,
            'city_id' => $this->city_id,
            'email' => $this->email,
            'phone' => $this->phone,
            'phone_verify_token_expire' => $this->phone_verify_token_expire ? $this->phone_verify_token_expire->toDateTimeString() : null,
            'role' => $this->role
        ];
    }
}
