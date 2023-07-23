<?php

namespace App\Http\Resources\Adverts;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyValueResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['property']->id,
            'category_id' => $this['property']->category_id,
            'slug' => $this['property']->slug,
            'name' => $this['property']->name,
            'frontend_type' => $this['property']->frontend_type,
            'required' => $this['property']->required,
            'filterable' => $this['property']->filterable,
            'sortable' => $this['property']->sortable,
            'variants' => $this['property']->variants,
            'raw_value' => $this['value'],
        ];
    }
}
