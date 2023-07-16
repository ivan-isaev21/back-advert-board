<?php

namespace App\Models\Adverts;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;

class Category extends Model
{
    use HasFactory, NodeTrait;

    protected $table = 'advert_categories';
    protected $fillable = ['name', 'slug', 'parent_id'];
    
}
