<?php

namespace App\Models\Adverts;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected $table = 'advert_properties';
    protected $fillable = ['category_id', 'name', 'slug', 'frontend_type', 'required', 'filterable'];

    protected $casts = [
        'required' => 'boolean',
        'filterable' => 'boolean'
    ];

    /**
     * Method category
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'id', 'category_id');
    }
}
