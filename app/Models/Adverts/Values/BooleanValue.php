<?php

namespace App\Models\Adverts\Values;

use App\Models\Adverts\Advert;
use App\Models\Adverts\Property;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BooleanValue extends BaseValue
{
    use HasFactory;

    protected $table = 'advert_property_boolean_values';

    protected $casts = ['value' => 'boolean'];

    /**
     * Method add
     *
     * @param Advert $advert 
     * @param Property $property 
     * @param bool $value 
     *
     * @return BooleanValue
     */
    public static function add(Advert $advert, Property $property, bool $value): BooleanValue
    {
        return static::create([
            'advert_id' => $advert->id,
            'property_id' => $property->id,
            'value' => $value
        ]);
    }
}
