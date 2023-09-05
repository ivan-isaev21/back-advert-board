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
            'avatar_url' => !empty($this->avatar_path) ? Storage::url($this->avatar_path) : null,
            'contact_person' => $this->contact_person,
            'division_id' => $this->division_id,
            'city_id' => $this->city_id,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'phone' => $this->phone,
            'phone_verified' => $this->phone_verified,
            'phone_verify_token_expire' => $this->phone_verify_token_expire ? $this->phone_verify_token_expire->toDateTimeString() : null,
            'phone_auth' => $this->phone_auth,
            'role' => $this->role
        ];
    }
}
