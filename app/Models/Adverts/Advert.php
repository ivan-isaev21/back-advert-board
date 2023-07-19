<?php

namespace App\Models\Adverts;

use App\Models\Adverts\Values\BaseValue;
use App\Models\Adverts\Values\BooleanValue;
use App\Models\Adverts\Values\DecimalValue;
use App\Models\Adverts\Values\IntegerValue;
use App\Models\Adverts\Values\JsonValue;
use App\Models\Adverts\Values\StringValue;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Advert extends Model
{
    use HasFactory, Searchable;

    protected $table = 'adverts';
    protected $fillable = ['category_id', 'user_id', 'title', 'content', 'property_values'];

    /**
     * Method toSearchableArray
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'content' => $this->content,
            'property_values' => $this->getAllPropertyValues()
        ];
    }

    /**
     * Method addValue
     *
     * @param Advert $advert 
     * @param Property $property 
     * @param string|int|bool|array|object $value 
     *
     * @return BaseValue|null
     */
    public static function addValue(Advert $advert, Property $property, string|int|bool|array|object $value): BaseValue|null
    {
        if ($property->isString()) {
            return StringValue::add($advert, $property, $value);
        } elseif ($property->isInteger()) {
            return IntegerValue::add($advert, $property, $value);
        } elseif ($property->isBoolean()) {
            return BooleanValue::add($advert, $property, $value);
        } elseif ($property->isDecimal()) {
            return DecimalValue::add($advert, $property, $value);
        } elseif ($property->isSelect() or $property->isMultiselect()) {
            return JsonValue::add($advert, $property, $value);
        }

        throw new \DomainException('Undefined property frontend_type = ' . $property->frontend_type);
    }

    /**
     * Method getAllPropertyValues
     *
     * @return array
     */
    public function getAllPropertyValues(): array
    {
        //ToDO cache
        $integerValues = $this->integerValues()->get()->pluck('value', 'property_id')->toArray();
        $stringValues = $this->stringValues()->get()->pluck('value', 'property_id')->toArray();
        $booleanValues = $this->booleanValues()->get()->pluck('value', 'property_id')->toArray();
        $decimalValues = $this->decimalValues()->get()->pluck('value', 'property_id')->toArray();
        $jsonValues = $this->jsonValues()->get()->pluck('value', 'property_id')->toArray();

        $all = $stringValues + $integerValues + $booleanValues + $decimalValues + $jsonValues;

        return $all;
    }

    /**
     * Method getAllPropertiesWithValues
     *
     * @return array
     */
    public function getAllPropertiesWithValues(): array
    {
        $allValues = $this->getAllPropertyValues();
        $properties = $this->category->allProperties();

        $propertiesWithValues = [];
        foreach ($properties as $property) {
            if (isset($allValues[$property->id])) {
                $propertiesWithValues[] = [
                    'id' => $property->id,
                    'name' => $property->name,
                    'value' => $allValues[$property->id]
                ];
            }
        }

        return $propertiesWithValues;
    }

    /**
     * Method user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'category_id');
    }

    /**
     * Method category
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function integerValues()
    {
        return $this->hasMany(IntegerValue::class, 'advert_id', 'id');
    }
    public function stringValues()
    {
        return $this->hasMany(StringValue::class, 'advert_id', 'id');
    }
    public function booleanValues()
    {
        return $this->hasMany(BooleanValue::class, 'advert_id', 'id');
    }

    public function decimalValues()
    {
        return $this->hasMany(DecimalValue::class, 'advert_id', 'id');
    }

    public function jsonValues()
    {
        return $this->hasMany(JsonValue::class, 'advert_id', 'id');
    }
}
