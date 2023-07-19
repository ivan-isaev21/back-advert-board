<?php

namespace App\Models\Adverts\Values;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaseValue extends Model
{
    use HasFactory;

    protected $fillable = ['advert_id', 'property_id', 'value'];

    /**
     * Method advert
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function advert()
    {
        return $this->belongsTo(Advert::class, 'id', 'advert_id');
    }

    /**
     * Method advert
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function property()
    {
        return $this->belongsTo(Property::class, 'id', 'property_id');
    }
}
