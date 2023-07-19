<?php

namespace App\Models\Adverts\Values;

use App\Models\Adverts\Advert;
use App\Models\Adverts\Property;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IntegerValue extends BaseValue
{
    use HasFactory;

    protected $table = 'advert_property_integer_values';
    
    /**
     * Method add
     *
     * @param Advert $advert 
     * @param Property $property 
     * @param int $value 
     *
     * @return IntegerValue
     */
    public static function add(Advert $advert, Property $property, int $value): IntegerValue
    {
        return static::create([
            'advert_id' => $advert->id,
            'property_id' => $property->id,
            'value' => $value
        ]);
    }
}
