<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class StoreBedSlotRequest extends DormitoryRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dorm_room_id' => ['required', 'integer', Rule::exists('dorm_rooms', 'id')],
            'bed_slot' => [
                'required',
                'string',
                'max:20',
                Rule::unique('bed_slots', 'bed_slot')
                    ->where(fn ($query) => $query->where('dorm_room_id', $this->input('dorm_room_id'))),
            ],
            'status' => ['required', Rule::in(['Vacant', 'Occupied'])],
        ];
    }

    public function messages(): array
    {
        return [
            'dorm_room_id.required' => 'Choose a room.',
            'dorm_room_id.exists' => 'Choose an existing room.',
            'bed_slot.required' => 'Enter a bed label.',
            'bed_slot.unique' => 'That bed label already exists in this room.',
            'status.in' => 'Choose either Vacant or Occupied.',
        ];
    }
}
