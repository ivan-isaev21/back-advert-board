<?php

namespace App\Models\Adverts;

use App\Models\Adverts\Values\BaseValue;
use App\Models\Adverts\Values\BooleanValue;
use App\Models\Adverts\Values\DecimalValue;
use App\Models\Adverts\Values\IntegerValue;
use App\Models\Adverts\Values\JsonValue;
use App\Models\Adverts\Values\StringValue;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Builder;

class Advert extends Model
{
    use HasFactory, Searchable;

    protected $table = 'adverts';
    protected $fillable = [
        'category_id', 'user_id', 'country_id', 'division_id', 'city_id',
        'latitude', 'longitude', 'title', 'content', 'status', 'reject_reason',
        'published_at', 'expires_at'
    ];

    protected $cats = [
        'published_at' => 'datetime',
        'expires_at' => 'datetime'
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_MODERATION = 'moderation';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_CLOSED = 'closed';

    /**
     * Method statusesList
     *
     * @return array
     */
    public static function statusesList(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_MODERATION => 'On Moderation',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_CLOSED => 'Closed',
        ];
    }

    /**
     * Method sendToModeration
     *
     * @return void
     */
    public function sendToModeration(): void
    {
        if (!$this->isDraft()) {
            throw new \DomainException('Advert is not draft.');
        }

        // if (!\count($this->photos)) {
        //     throw new \DomainException('Upload photos.');
        // }
        $this->update([
            'status' => self::STATUS_MODERATION,
        ]);
    }

    /**
     * Method moderate
     *
     * @param Carbon $date 
     *
     * @return void
     */
    public function moderate(Carbon $date): void
    {
        if ($this->status !== self::STATUS_MODERATION) {
            throw new \DomainException('Advert is not sent to moderation.');
        }

        $this->update([
            'published_at' => $date,
            'expires_at' => $date->copy()->addDays(15),
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Method reject
     *
     * @param $reason $reason 
     *
     * @return void
     */
    public function reject($reason): void
    {
        $this->update([
            'status' => self::STATUS_DRAFT,
            'reject_reason' => $reason,
        ]);
    }

    /**
     * Method expire
     *
     * @return void
     */
    public function expire(): void
    {
        $this->update([
            'status' => self::STATUS_CLOSED,
        ]);
    }

    /**
     * Method close
     *
     * @return void
     */
    public function close(): void
    {
        $this->update([
            'status' => self::STATUS_CLOSED,
        ]);
    }

    /**
     * Method isDraft
     *
     * @return bool
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Method isOnModeration
     *
     * @return bool
     */
    public function isOnModeration(): bool
    {
        return $this->status === self::STATUS_MODERATION;
    }

    /**
     * Method isActive
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Method isClosed
     *
     * @return bool
     */
    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    /**
     * Method toSearchableArray
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        $data =  [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'user_id' => $this->user_id,
            'country_id' => $this->country_id,
            'division_id' => $this->division_id,
            'city_id' => $this->city_id,
            '_geo' => [
                'lat' => $this->latitude,
                'lng' => $this->longitude
            ],
            'title' => $this->title,
            'content' => $this->content,
            'status' => $this->status,
            'reject_reason' => $this->reject_reason,
            'published_at' => $this->published_at,
            'expires_at' => $this->expires_at,

        ];

        foreach ($this->getAllPropertiesWithValues() as $item) {
            if ($item['property']->isBoolean()) {
                $value = (bool)$item['value'];
            } elseif ($item['property']->isInteger()) {
                $value = (int)$item['value'];
            } elseif ($item['property']->isDecimal()) {
                $value = (float)$item['value'];
            } else {
                $value = $item['value'];
            }

            $data['property_' . $item['property']->id] = $value;
        }

        return $data;
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
                    'property' => $property,
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

    /**
     * Method integerValues
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function integerValues()
    {
        return $this->hasMany(IntegerValue::class, 'advert_id', 'id');
    }

    /**
     * Method stringValues
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stringValues()
    {
        return $this->hasMany(StringValue::class, 'advert_id', 'id');
    }

    /**
     * Method booleanValues
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function booleanValues()
    {
        return $this->hasMany(BooleanValue::class, 'advert_id', 'id');
    }

    /**
     * Method decimalValues
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function decimalValues()
    {
        return $this->hasMany(DecimalValue::class, 'advert_id', 'id');
    }

    /**
     * Method jsonValues
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function jsonValues()
    {
        return $this->hasMany(JsonValue::class, 'advert_id', 'id');
    }

    /**
     * Method scopeActive
     *
     * @param Builder $query 
     *
     * @return \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Method scopeForUser
     *
     * @param Builder $query 
     * @param User $user 
     *
     * @return \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeForUser(Builder $query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * Method scopeForCategory
     *
     * @param Builder $query 
     * @param Category $category 
     *
     * @return \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeForCategory(Builder $query, Category $category)
    {
        return $query->whereIn('category_id', array_merge(
            [$category->id],
            $category->descendants()->pluck('id')->toArray()
        ));
    }

    /**
     * Method scopeForCountry
     *
     * @param Builder $query 
     * @param $countryId 
     *
     * @return \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeForCountry(Builder $query, $countryId)
    {
        return $query->where('country_id', $countryId);
    }

    /**
     * Method scopeForDivision
     *
     * @param Builder $query 
     * @param $divisionId $divisionId 
     *
     * @return \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeForDivision(Builder $query, $divisionId)
    {
        return $query->where('division_id', $divisionId);
    }

    /**
     * Method scopeForCity
     *
     * @param Builder $query 
     * @param $cityId $cityId
     *
     * @return \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeCity(Builder $query, $cityId)
    {
        return $query->where('city_id', $cityId);
    }
}
