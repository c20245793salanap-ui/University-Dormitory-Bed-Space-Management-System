<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateDormContractRequest extends DormitoryRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', Rule::exists('students', 'id')],
            'bed_slot_id' => [
                'required',
                'integer',
                Rule::exists('bed_slots', 'id'),
                Rule::unique('dorm_contracts', 'bed_slot_id')
                    ->where(fn ($query) => $query->where('lease_status', 'Active'))
                    ->ignore($this->route('contract')),
            ],
            'monthly_rate' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
            'contract_start' => ['required', 'date'],
            'contract_end' => ['required', 'date', 'after_or_equal:contract_start'],
            'security_deposit' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
            'total_paid' => ['required', 'numeric', 'min:0'],
            'lease_status' => ['required', Rule::in(['Pending', 'Active'])],
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'Choose a student.',
            'student_id.exists' => 'Choose an existing student.',
            'bed_slot_id.required' => 'Choose a bed slot.',
            'bed_slot_id.exists' => 'Choose an existing bed slot.',
            'bed_slot_id.unique' => 'This bed slot already has an active contract.',
            'monthly_rate.required' => 'Enter the monthly rate.',
            'monthly_rate.decimal' => 'The monthly rate may have up to 2 decimal places.',
            'monthly_rate.max' => 'The monthly rate cannot exceed 99,999,999.99.',
            'contract_start.required' => 'Choose the contract start date.',
            'contract_end.required' => 'Choose the contract end date.',
            'contract_end.after_or_equal' => 'The contract end date must be on or after its start date.',
            'security_deposit.required' => 'Enter the security deposit.',
            'security_deposit.decimal' => 'The security deposit may have up to 2 decimal places.',
            'security_deposit.max' => 'The security deposit cannot exceed 99,999,999.99.',
            'total_paid.required' => 'Enter the total paid so far.',
            'lease_status.in' => 'Choose Pending or Active for the lease status.',
        ];
    }
}
