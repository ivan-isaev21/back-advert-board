<?php

namespace App\Http\Resources\Adverts;

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
            'category_id' => $this->category_id,
            // 'category' => new CategoryResource($this->category),
            'user_id' => $this->user_id,
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
