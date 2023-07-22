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
            'title' => $this->title,
            'content' => $this->content,
            'property_values' => PropertyValueResource::collection($this->getAllPropertiesWithValues())
        ];
    }
}
