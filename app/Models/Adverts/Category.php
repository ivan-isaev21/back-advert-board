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

    /**
     * Method getPath
     *
     * @return string
     */
    public function getPath(): string
    {
        return implode('/', array_merge($this->ancestors()->defaultOrder()->pluck('slug')->toArray(), [$this->slug]));
    }

    public function getBreadcrumbPath()
    {
        $breadcrumbPath = [];
        $ancestors = $this->ancestors()->defaultOrder()->get();

        foreach ($ancestors as $ancestor) {
            $breadcrumbPath[] = ['path' => $ancestor->getPath(), 'name' => $ancestor->name, 'isActive' => false];
        }

        $breadcrumbPath[] = ['path' => $this->getPath(), 'name' => $this->name, 'isActive' => true];
        return $breadcrumbPath;
    }

    /**
     * Method parentProperties
     *
     * @return array
     */
    public function parentProperties(): array
    {
        return $this->parent ? $this->parent->allProperties() : [];
    }

    /**
     * Method allProperties
     *
     * @return array
     */
    public function allProperties(): array
    {
        return array_merge($this->parentProperties(), $this->properties()->getModels());
    }

    /**
     * Method allFilterableProperties
     *
     * @return array
     */
    public function allFilterableProperties(): array
    {
        return array_filter(array_map(function (Property $property) {
            if ($property->isFilterable()) {
                return $property->id;
            }
        }, $this->allProperties()), function ($item) {
            return $item !== null;
        });
    }

    /**
     * Method allSortableProperties
     *
     * @return array
     */
    public function allSortableProperties(): array
    {
        return array_filter(array_map(function (Property $property) {
            if ($property->isSortable()) {
                return $property->id;
            }
        }, $this->allProperties()), function ($item) {
            return $item !== null;
        });
    }

    /**
     * Method properties
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function properties()
    {
        return $this->hasMany(Property::class, 'category_id', 'id');
    }
}
