<?php

namespace App\Http\Resources\Adverts;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdvertResource extends JsonResource
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
            'category' => new AdvertCategoryResource($this->category),
            'user' =>  new UserResource($this->user),
            'location'  => [
                'country_id' => $this->country_id,
                'division_id' => $this->division_id,
                'city_id' => $this->city_id
            ],
            'geo' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude
            ],
            'title' => $this->title,
            'content' => $this->content,
            'images' => ImageResource::collection($this->images),
            'property_values' => PropertyValueResource::collection($this->getAllPropertiesWithValues())
        ];
    }
}
