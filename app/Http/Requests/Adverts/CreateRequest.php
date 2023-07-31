<?php

namespace App\Http\Requests\Adverts;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
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

            'location' => ['sometimes'],
            'location.country_id' => ['sometimes', 'required', 'integer', 'exists:geo-mysql.countries,id'],
            'location.division_id' => ['sometimes', 'required', 'integer', 'exists:geo-mysql.divisions,id'],
            'location.city_id' => ['sometimes', 'required', 'integer', 'exists:geo-mysql.cities,id'],

            'geo' => ['sometimes'],
            'geo.latitude' => 'required_with:geo|decimal:0,7|between:-90,90',
            'geo.longitude' => 'required_with:geo|decimal:0,7|between:-180,180',

            'title' => 'required|max:255',
            'content' => 'required|max:255',

            'images' => 'sometimes|required|array|max:5',
            'images.*.file' =>  'required_with:images|mimes:jpg,png,gif|max:5120',
        ];

        foreach ($this->category->allProperties() as $property) {
            $rules['properties.' . $property->id] = $property->getValidationRule();
        }

        return $rules;
    }
}
