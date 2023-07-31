<?php

namespace App\Models\Adverts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $table = 'advert_images';
    protected $fillable = ['advert_id', 'file_hash', 'file_path', 'file_original_name'];


    /**
     * Method advert
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function advert()
    {
        return $this->belongsTo(Advert::class, 'advert_id', 'id');
    }

    /**
     * Method scopeForHash
     *
     * @param Builder $query 
     * @param string $hash 
     *
     * @return \Illuminate\Database\Eloquent\Builder 
     */
    public function scopeForHash(Builder $query, string $hash)
    {
        return $query->where('file_hash', $hash);
    }
}
