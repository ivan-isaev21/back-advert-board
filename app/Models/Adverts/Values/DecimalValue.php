<?php

namespace App\Models\Adverts\Values;

use App\Models\Adverts\Advert;
use App\Models\Adverts\Property;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DecimalValue extends BaseValue
{
    use HasFactory;

    protected $table = 'advert_property_decimal_values';

    /**
     * Method add
     *
     * @param Advert $advert 
     * @param Property $property 
     * @param float $value 
     *
     * @return DecimalValue
     */
    public static function add(Advert $advert, Property $property, float $value): DecimalValue
    {
        return static::create([
            'advert_id' => $advert->id,
            'property_id' => $property->id,
            'value' => $value
        ]);
    }
}
