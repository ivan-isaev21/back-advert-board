<?php

namespace App\Models\Adverts\Values;

use App\Models\Adverts\Advert;
use App\Models\Adverts\Property;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StringValue extends BaseValue
{
    use HasFactory;

    protected $table = 'advert_property_string_values';

    /**
     * Method add
     *
     * @param Advert $advert 
     * @param Property $property 
     * @param string $value 
     *
     * @return StringValue
     */
    public static function add(Advert $advert, Property $property, string $value): StringValue
    {
        return static::create([
            'advert_id' => $advert->id,
            'property_id' => $property->id,
            'value' => $value
        ]);
    }
}
