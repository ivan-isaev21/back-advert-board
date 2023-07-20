<?php

namespace App\Models\Adverts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class Property extends Model
{
    use HasFactory;

    protected $table = 'advert_properties';
    protected $fillable = [
        'category_id', 'name', 'slug',
        'frontend_type', 'required', 'filterable', 'sortable', 'variants'
    ];

    protected $casts = [
        'required' => 'boolean',
        'filterable' => 'boolean',
        'sortable' => 'boolean',
        'variants' => 'json'
    ];

    public const INTEGER_FRONTEND_TYPES = ['integer'];
    public const STRING_FRONTEND_TYPES = ['string', 'text'];
    public const DECIMNAL_FRONTEND_TYPES    = ['decimal'];
    public const BOOLEAN_FRONTEND_TYPES = ['boolean'];
    public const SELECT_FRONTEND_TYPES = ['radio', 'select'];
    public const MULTISELECT_FRONTEND_TYPES = ['checkbox'];


    /**
     * Method getValidationRule
     *
     * @return array
     */
    public function getValidationRule(): array
    {
        $rules = [
            $this->isRequired() ? 'required' : 'nullable',
        ];

        if ($this->isInteger()) {
            $rules[] = 'integer';
        } elseif ($this->isString()) {
            $rules[] = 'string';
            $rules[] = 'max:255';
        } elseif ($this->isDecimal()) {
            $rules[] = 'numeric';
        } elseif ($this->isSelect() or $this->isMultiselect()) {
            if (!count($this->variants) > 0) {
                throw new \DomainException('Variants must be filled.');
            }
            $rules[] = Rule::in(array_keys($this->variants));
        }

        return $rules;
    }

    /**
     * Method getSearchFilterValidationRule
     *
     * @return array
     */
    public function getSearchFilterValidationRule(): array
    {
        $rules = ['required'];

        $rules['value'] = ['required'];

        if ($this->isInteger()) {
            $rules['value'][] = 'integer';
        } elseif ($this->isString()) {
            $rules['value'][] = 'string';
            $rules['value'][] = 'max:255';
        } elseif ($this->isDecimal()) {
            $rules['value'][] = 'numeric';
        } elseif ($this->isSelect() or $this->isMultiselect()) {
            if (!count($this->variants) > 0) {
                throw new \DomainException('Variants must be filled.');
            }
            $rules['value'][] = Rule::in(array_keys($this->variants));
        }

        return $rules;
    }

    /**
     * Method availableFrontendTypes
     *
     * @return void
     */
    public static function getAvailableFrontendTypes(): array
    {
        return
            array_merge(
                self::INTEGER_FRONTEND_TYPES,
                self::STRING_FRONTEND_TYPES,
                self::DECIMNAL_FRONTEND_TYPES,
                self::BOOLEAN_FRONTEND_TYPES,
                self::SELECT_FRONTEND_TYPES,
                self::MULTISELECT_FRONTEND_TYPES
            );
    }

    /**
     * Method isInteger
     *
     * @return bool
     */
    public function isInteger(): bool
    {
        return in_array($this->frontend_type, self::INTEGER_FRONTEND_TYPES);
    }

    /**
     * Method isString
     *
     * @return bool
     */
    public function isString(): bool
    {
        return in_array($this->frontend_type, self::STRING_FRONTEND_TYPES);
    }

    /**
     * Method isDecimal
     *
     * @return bool
     */
    public function isDecimal(): bool
    {
        return in_array($this->frontend_type, self::DECIMNAL_FRONTEND_TYPES);
    }

    /**
     * Method isBoolean
     *
     * @return bool
     */
    public function isBoolean(): bool
    {
        return in_array($this->frontend_type, self::BOOLEAN_FRONTEND_TYPES);
    }

    /**
     * Method isSelect
     *
     * @return bool
     */
    public function isSelect(): bool
    {
        return in_array($this->frontend_type, self::SELECT_FRONTEND_TYPES);
    }

    /**
     * Method isMultiselect
     *
     * @return bool
     */
    public function isMultiselect(): bool
    {
        return in_array($this->frontend_type, self::MULTISELECT_FRONTEND_TYPES);
    }


    /**
     * Method isRequired
     *
     * @return void
     */
    public function isRequired()
    {
        return $this->required == 1;
    }

    /**
     * Method isFilterable
     *
     * @return void
     */
    public function isFilterable()
    {
        return $this->filterable == 1;
    }

    /**
     * Method isSortable
     *
     * @return void
     */
    public function isSortable()
    {
        return $this->sortable == 1;
    }

    /**
     * Method category
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'id', 'category_id');
    }

    /**
     * Method scopeFilterable
     *
     * @param Builder $query 
     *
     * @return void
     */
    public function scopeFilterable(Builder $query): void
    {
        $query->where(['filterable' =>  1]);
    }

    /**
     * Method scopeSortable
     *
     * @param Builder $query 
     *
     * @return void
     */
    public function scopeSortable(Builder $query): void
    {
        $query->where(['sortable' =>  1]);
    }
}
