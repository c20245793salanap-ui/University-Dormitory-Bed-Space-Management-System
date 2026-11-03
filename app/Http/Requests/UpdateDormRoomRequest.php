<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateDormRoomRequest extends DormitoryRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dorm_building_id' => ['required', 'integer', Rule::exists('dorm_buildings', 'id')],
            'room_number' => [
                'required',
                'string',
                'max:30',
                Rule::unique('dorm_rooms', 'room_number')
                    ->where(fn ($query) => $query->where('dorm_building_id', $this->input('dorm_building_id')))
                    ->ignore($this->route('room')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'dorm_building_id.required' => 'Choose a building.',
            'dorm_building_id.exists' => 'Choose an existing building.',
            'room_number.required' => 'Enter a room number.',
            'room_number.unique' => 'That room number already exists in this building.',
        ];
    }
}
