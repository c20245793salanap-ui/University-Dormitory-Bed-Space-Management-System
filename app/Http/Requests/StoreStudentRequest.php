<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class StoreStudentRequest extends DormitoryRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'string', 'max:30', Rule::unique('students', 'student_id')],
            'student_name' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'Enter the student ID.',
            'student_id.unique' => 'That student ID is already registered.',
            'student_name.required' => 'Enter the student name.',
        ];
    }
}
