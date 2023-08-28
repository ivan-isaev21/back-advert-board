<?php

namespace App\Http\Requests\Profiles;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
        return [
            'contact_person' => 'required|string|max:255',
            'division_id' => ['required_with:city_id', 'nullable', 'integer', 'exists:geo-mysql.divisions,id'],
            'city_id' => ['required_with:division_id', 'integer', 'exists:geo-mysql.cities,id'],
        ];
    }
}
