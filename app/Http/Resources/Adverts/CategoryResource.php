<?php

namespace App\Http\Resources\Adverts;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            '_lft' => $this->_lft,
            '_rgt' => $this->_rgt,
            'parent_id' => $this->parent_id,
            'depth' => $this->depth,
            'path' => $this->getPath(),
            'children' => self::collection($this->children),
            'properties' => PropertyResource::collection($this->properties),
            'all_properties' => PropertyResource::collection($this->allProperties())
        ];
    }
}
