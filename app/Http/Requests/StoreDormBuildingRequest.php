<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class StoreDormBuildingRequest extends DormitoryRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'building_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('dorm_buildings', 'building_name'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'building_name.required' => 'Enter a building name.',
            'building_name.unique' => 'That building name already exists.',
        ];
    }
}
