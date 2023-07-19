<?php

namespace App\Models\Adverts\Values;

use App\Models\Adverts\Advert;
use App\Models\Adverts\Property;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JsonValue extends BaseValue
{
    use HasFactory;

    protected $table = 'advert_property_json_values';

    protected $casts = ['value' => 'json'];

    /**
     * Method add
     *
     * @param Advert $advert 
     * @param Property $property 
     * @param array|object|string|int $value 
     *
     * @return JsonValue
     */
    public static function add(Advert $advert, Property $property, array|object|string|int $value): JsonValue
    {
        return static::create([
            'advert_id' => $advert->id,
            'property_id' => $property->id,
            'value' => $value
        ]);
    }
}
