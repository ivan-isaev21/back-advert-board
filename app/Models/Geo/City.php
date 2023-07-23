<?php

namespace App\Models\Geo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Nevadskiy\Translatable\Strategies\ExtraTableExtended\HasTranslations;

class City extends Model
{
    use HasFactory, HasTranslations;

    protected $connection = 'geo-mysql';
    protected $table = 'cities';
    protected $guarded = ['*'];

    /**
     * Attributes that are translatable.
     *
     * @var array
     */
    protected $translatable = [
        'name',
    ];

    /**
     * Get a relationship with a country.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get a relationship with a division.
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }
}
