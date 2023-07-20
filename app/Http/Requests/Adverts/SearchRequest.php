<?php

namespace App\Http\Requests\Adverts;

use App\Models\Adverts\Property;
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
            'country_id' => 'nullable',
            'division_id' => 'nullable',
            'city_id' => 'nullable',
            'properties' => ['nullable', function ($attribute, $value, $fail) {
                $this->validationMustBeFilterable($attribute, $value, $fail, $this->category->allProperties());
            }]
        ];

        if ($this->category) {
            foreach ($this->category->allProperties() as $property) {

                $rules['properties.' . $property->id] = ['nullable', function ($attribute, $value, $fail) {
                    $this->validationOnlyOneFrontendType($attribute, $value, $fail);
                }];

                if ($property->isInteger()) {
                    $rules['properties.' . $property->id . '.equals'] = ['nullable', 'integer'];

                    $rules['properties.' . $property->id . '.range'] = ['nullable', function ($attribute, $value, $fail) {
                        $this->validationRange($attribute, $value, $fail);
                    }];
                    $rules['properties.' . $property->id . '.range.min'] = ['sometimes', 'required', 'integer'];
                    $rules['properties.' . $property->id . '.range.max'] = ['sometimes', 'required', 'integer'];
                    $rules['properties.' . $property->id . '.range.strict'] = ['sometimes', 'required', 'boolean'];
                } elseif ($property->isBoolean()) {
                    $rules['properties.' . $property->id . '.equals'] = ['nullable', 'boolean'];
                } elseif ($property->isString()) {
                    $rules['properties.' . $property->id . '.equals'] = ['nullable', 'string', 'max:255'];
                } elseif ($property->isSelect()) {
                    $rules['properties.' . $property->id . '.select'] = ['nullable',  function ($attribute, $value, $fail) {
                        $this->validationSelect($attribute, $value, $fail);
                    }];
                } elseif ($property->isMultiselect()) {
                    $rules['properties.' . $property->id . '.multiselect'] = ['nullable',  function ($attribute, $value, $fail) {
                        $this->validationMultiselect($attribute, $value, $fail);
                    }];
                } elseif ($property->isDecimal()) {

                    $rules['properties.' . $property->id . '.equals'] = ['nullable', 'decimal:2'];

                    $rules['properties.' . $property->id . '.range'] = ['nullable', function ($attribute, $value, $fail) {
                        $this->validationRange($attribute, $value, $fail);
                    }];

                    $rules['properties.' . $property->id . '.range.min'] = ['sometimes', 'required', 'decimal:2'];
                    $rules['properties.' . $property->id . '.range.max'] = ['sometimes', 'required', 'decimal:2'];
                    $rules['properties.' . $property->id . '.range.strict'] = ['sometimes', 'required', 'boolean'];
                }
            }
        }



        // if ($this->category) {
        //     foreach ($this->category->allProperties() as $property) {
        //         //dump($property->isFilterable());
        //         if ($property->isFilterable()) {
        //             // $rules['properties.' . $property->id] = $property->getSearchFilterValidationRule();

        //             if ($property->isInteger()) {
        //                 $rules['properties.' . $property->id . '.equals'] = ['required', 'integer'];
        //                 //$rules['properties.' . $property->id]['range'] = ['min' => ['nullable', 'integer'], 'max' => ['nullable', 'integer']];
        //                 // $rules['properties.' . $property->id]['strict_range'] = ['min' => ['nullable', 'integer'], 'max' => ['nullable', 'integer']];
        //             } elseif ($property->isString()) {
        //                 $rules['properties.' . $property->id]['value'][] = 'string';
        //                 $rules['properties.' . $property->id]['value'][] = 'max:255';
        //             } elseif ($property->isDecimal()) {
        //                 $rules['properties.' . $property->id]['value'][] = 'numeric';
        //             } elseif ($property->isSelect() or $property->isMultiselect()) {
        //                 if (!count($property->variants) > 0) {
        //                     throw new \DomainException('Variants must be filled.');
        //                 }
        //                 // $rules['properties.' . $property->id]['value'] = [Rule::in(array_keys($property->variants))];
        //             }
        //         }
        //     }
        // }

        //dd($rules);

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
                $fail('Поле ' . $attribute . '.' . $propertyId . ' не фильтруемое.');
            }
        }
    }

    /**
     * Method validationOnlyOneFrontendType
     *
     * @param $attribute
     * @param $values 
     * @param $fail 
     * @return void
     */
    private function validationOnlyOneFrontendType($attribute, $values, $fail)
    {
        if (!is_array($values)) {
            $fail('Поле ' . $attribute . ' должно быть массивом.');
        } elseif (count($values) > 1) {
            $fail('В поле ' . $attribute . ' должно быть только одно правило валидации frontend type.');
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
    private function validationSelect($attribute, $value, $fail)
    {
        $needArray = [1, 2, 3];

        if (!in_array($value, $needArray)) {
            $fail('Поле ' . $attribute . ' должно равнятся: ' . json_encode($needArray));
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
    private function validationMultiSelect($attribute, $values, $fail)
    {
        $variants = [1, 2, 3];

        if (!is_array($values)) {
            $fail('Поле ' . $attribute . ' должно быть массивом.');
        } else {
            foreach ($values as $value) {
                if (!in_array($value, $variants)) {
                    $fail('Поле ' . $attribute . '.' . $value . ' должно равнятся: ' . json_encode($variants));
                }
            }
        }
    }
}
