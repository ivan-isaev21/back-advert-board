<?php

namespace App\Http\Requests\Adverts;

use App\Models\Adverts\Advert;
use App\Models\Adverts\Property;
use DomainException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules =  [
            'search' => 'nullable',
            'per_page' => ['sometimes', 'integer', 'in:1,3,6,9,12,24,36,54'],
            'location' => ['sometimes', 'missing_with:geo'],
            'location.country_id' => ['sometimes', 'required', 'integer', 'exists:geo-mysql.countries,id'],
            'location.division_id' => ['sometimes', 'required', 'integer', 'exists:geo-mysql.divisions,id'],
            'location.city_id' => ['sometimes', 'required', 'integer', 'exists:geo-mysql.cities,id'],

            'geo' => ['sometimes', 'missing_with:location'],
            'geo.latitude' => 'required_with:geo|decimal:0,7|between:-90,90',
            'geo.longitude' => 'required_with:geo|decimal:0,7|between:-180,180',
            'geo.radius' => 'required_with:geo|in:0,2,5,10,15,30,50,75,100',

            'statuses' => 'sometimes|array',
            'statuses.*' => Rule::in(Advert::STATUS_ACTIVE, Advert::STATUS_CLOSED, Advert::STATUS_DRAFT, Advert::STATUS_MODERATION),
        ];

        if ($this->properties) {

            if (!$this->category or $this->category == null) {
                throw new DomainException('Для фильтрации по параметрам нужно указать категорию.');
            }

            $rules['properties'] = ['sometimes', function ($attribute, $value, $fail) {
                $this->validationMustBeFilterable($attribute, $value, $fail);
            }];

            foreach ($this->category->allProperties() as $property) {
                $rules['properties.' . $property->id] = ['sometimes', 'required', function ($attribute, $value, $fail) use ($property) {
                    $this->validationOnlyOneFrontendType($attribute, $value, $fail, $property);
                }];

                if ($property->isInteger()) {
                    $rules['properties.' . $property->id . '.equals'] = ['sometimes', 'required', 'integer'];
                    $rules['properties.' . $property->id . '.range'] = ['sometimes', 'required', function ($attribute, $value, $fail) {
                        $this->validationRange($attribute, $value, $fail);
                    }];
                    $rules['properties.' . $property->id . '.range.min'] = ['sometimes', 'required', 'integer'];
                    $rules['properties.' . $property->id . '.range.max'] = ['sometimes', 'required', 'integer'];
                    $rules['properties.' . $property->id . '.range.strict'] = ['sometimes', 'required', 'boolean'];
                } elseif ($property->isBoolean()) {
                    $rules['properties.' . $property->id . '.equals'] = ['sometimes', 'required', 'boolean'];
                } elseif ($property->isString()) {
                    $rules['properties.' . $property->id . '.equals'] = ['sometimes', 'required', 'string', 'max:255'];
                } elseif ($property->isSelect()) {
                    $rules['properties.' . $property->id . '.select'] = ['sometimes', 'required',  function ($attribute, $value, $fail) use ($property) {
                        $this->validationSelect($attribute, $value, $fail, $property);
                    }];
                } elseif ($property->isMultiselect()) {
                    $rules['properties.' . $property->id . '.multiselect'] = ['sometimes', 'required',  function ($attribute, $value, $fail) use ($property) {
                        $this->validationMultiselect($attribute, $value, $fail, $property);
                    }];
                } elseif ($property->isDecimal()) {
                    $rules['properties.' . $property->id . '.equals'] = ['sometimes', 'required', 'decimal:0'];
                    $rules['properties.' . $property->id . '.range'] = ['sometimes', 'required', function ($attribute, $value, $fail) {
                        $this->validationRange($attribute, $value, $fail);
                    }];
                    $rules['properties.' . $property->id . '.range.min'] = ['sometimes', 'required', 'decimal:0'];
                    $rules['properties.' . $property->id . '.range.max'] = ['sometimes', 'required', 'decimal:0'];
                    $rules['properties.' . $property->id . '.range.strict'] = ['sometimes', 'required', 'boolean'];
                }
            }
        }

        return $rules;
    }

    /**
     * Method validationMustBeFilterable
     *
     * @param $attribute 
     * @param $values 
     * @param $fail 
     *
     * @return void
     */
    private function validationMustBeFilterable($attribute, $values, $fail)
    {
        $properties = array_keys($values);
        $filterableProperties = $this->category->allFilterableProperties();
        foreach ($properties as $propertyId) {
            if (!in_array($propertyId, $filterableProperties)) {
                $fail('Поле ' . $attribute . '.' . $propertyId . ' не фильтруемое или не принадлежит категории ' . $this->category->id . '.');
            }
        }
    }

    /**
     * Method getAvailableFilterKeys
     *
     * @param Property 
     *
     * @return array
     */
    private function getAvailableFilterKeys(Property $property): array
    {
        if ($property->isInteger() or $property->isDecimal()) {
            return ['equals', 'range'];
        } elseif ($property->isString() or $property->isBoolean()) {
            return ['equals'];
        } elseif ($property->isSelect()) {
            return ['select'];
        } elseif ($property->isMultiselect()) {
            return ['multiselect'];
        }
        return [];
    }

    /**
     * Method validationOnlyOneFrontendType
     *
     * @param $attribute
     * @param $values 
     * @param $fail 
     * @return void
     */
    private function validationOnlyOneFrontendType($attribute, $values, $fail, Property $property)
    {
        $validKeys = $this->getAvailableFilterKeys($property);

        if (count($validKeys) == 0) {
            $fail('Не найдено доступных фильтров для данного frontend_type (' . $property->frontend_type . ')');
        } elseif (!is_array($values)) {
            $fail('Поле ' . $attribute . ' должно быть массивом.');
        } elseif (count($values) > 1) {
            $fail('В поле ' . $attribute . ' должен быть только один фильтр frontend_type.');
        } else {
            $key = array_keys($values)[0];

            if (!in_array($key, $validKeys)) {
                $fail('Поле ' . $attribute . '.' . $key . ' не найдено в доступных фильтрах для данного frontend_type (' . $property->frontend_type . ') : ' . json_encode($validKeys));
            }
        }
    }

    /**
     * Method validationRange
     *
     * @param $attribute 
     * @param $value 
     * @param $fail 
     *
     * @return void
     */
    private function validationRange($attribute, $value, $fail)
    {
        $min = $this->input($attribute . '.min');
        $max = $this->input($attribute . '.max');

        if (!$min && !$max) {
            $fail('Поле ' . $attribute . ' должно содержать хотя бы одно из полей min или max.');
        }

        if ($min and $max and $min > $max) {
            $fail('Поле ' . $attribute . '.min не может быть > ' . $attribute . '.max.');
        }
    }

    /**
     * Method validationSelect
     *
     * @param $attribute 
     * @param $value 
     * @param $fail 
     *
     * @return void
     */
    private function validationSelect($attribute, $value, $fail, Property $property)
    {
        if (!is_array($property->variants)) {
            $fail('В поле ' . $attribute . 'не заполнены variants, сообщите администратору');
        } elseif (!in_array($value, array_keys($property->variants))) {
            $fail('Поле ' . $attribute . ' должно равнятся: ' . json_encode(array_keys($property->variants)));
        }
    }

    /**
     * Method validationMultiSelect
     *
     * @param $attribute 
     * @param $values 
     * @param $fail
     *
     * @return void
     */
    private function validationMultiSelect($attribute, $values, $fail, Property $property)
    {
        if (!is_array($property->variants)) {
            $fail('В поле ' . $attribute . 'не заполнены variants, сообщите администратору');
        } elseif (!is_array($values)) {
            $fail('Поле ' . $attribute . ' должно быть массивом.');
        } else {
            foreach ($values as $key => $value) {
                if (is_array($value)) {
                    $fail('Значение поля ' . $attribute . '.' . $key . ' не должно быть массивом.');
                } elseif (!in_array($value, array_keys($property->variants))) {
                    $fail('Поле ' . $attribute . '.' . $value . ' должно равнятся: ' . json_encode(array_keys($property->variants)));
                }
            }
        }
    }
}
